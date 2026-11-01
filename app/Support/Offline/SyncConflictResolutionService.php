<?php

namespace App\Support\Offline;

use App\Models\SyncConflict;
use App\Models\SyncDevice;
use App\Models\SyncMutation;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

final class SyncConflictResolutionService
{
    /**
     * @param array{action:string,note?:?string} $validated
     */
    public function resolve(SyncDevice $device, SyncConflict $conflict, array $validated): JsonResponse
    {
        return DB::transaction(
            fn (): JsonResponse => $this->resolveLockedConflict($device, $conflict, $validated)
        );
    }

    /**
     * @param array{action:string,note?:?string} $validated
     */
    private function resolveLockedConflict(
        SyncDevice $device,
        SyncConflict $conflict,
        array $validated
    ): JsonResponse {
        $lockedConflict = SyncConflict::query()
            ->whereKey($conflict->id)
            ->lockForUpdate()
            ->firstOrFail();

        $this->assertConflictOwnership($lockedConflict, $device);
        $mutation = $this->lockConflictMutation($lockedConflict, $device);

        if ($lockedConflict->status !== 'open') {
            return response()->json([
                'conflict' => $this->conflictPayload($lockedConflict),
                'mutation_status' => $this->resolvedConflictMutationStatus($lockedConflict, $mutation),
            ]);
        }

        $mutationStatus = $this->applyOpenConflictResolution(
            $lockedConflict,
            $mutation,
            $validated['action'],
            $validated['note'] ?? null,
        );

        $lockedConflict->update([
            'status' => 'resolved',
            'resolution_note' => $this->conflictResolutionNote($lockedConflict, $validated),
            'resolved_at' => now(),
        ]);

        return response()->json([
            'conflict' => $this->conflictPayload($lockedConflict->fresh()),
            'mutation_status' => $mutationStatus,
        ]);
    }

    private function assertConflictOwnership(SyncConflict $conflict, SyncDevice $device): void
    {
        abort_unless(
            (int) $conflict->business_id === (int) $device->business_id
                && (int) $conflict->sync_device_id === (int) $device->id,
            404
        );
    }

    private function lockConflictMutation(SyncConflict $conflict, SyncDevice $device): ?SyncMutation
    {
        if (! $conflict->mutation_id) {
            return null;
        }

        return SyncMutation::query()
            ->where('business_id', $device->business_id)
            ->where('sync_device_id', $device->id)
            ->where('mutation_id', $conflict->mutation_id)
            ->lockForUpdate()
            ->first();
    }

    private function applyOpenConflictResolution(
        SyncConflict $conflict,
        ?SyncMutation $mutation,
        string $action,
        ?string $note
    ): ?string {
        if ($conflict->conflict_type === 'mutation_identity_mismatch') {
            // The original mutation remains immutable, but the edited local
            // payload must still be retried under a fresh mutation ID.
            return $this->resolveMutationIdentityMismatch($mutation, $action);
        }

        // An already-applied domain operation is immutable. Conflict UI
        // actions may acknowledge it but must never requeue or discard it.
        if ($mutation?->status === 'applied') {
            return 'applied';
        }

        if ($action === 'retry') {
            abort_unless($mutation, 404);
            $mutation->update([
                'status' => 'pending',
                'last_error' => null,
            ]);

            return 'pending';
        }

        if ($mutation) {
            $mutation->update([
                'status' => 'discarded',
                'last_error' => $note ?? 'Offline change discarded after conflict resolution.',
            ]);

            return 'discarded';
        }

        return null;
    }

    /**
     * @param array{action:string,note?:?string} $validated
     */
    private function conflictResolutionNote(SyncConflict $conflict, array $validated): string
    {
        $note = $validated['note'] ?? ($validated['action'] === 'retry' ? 'Queued for retry.' : 'Discarded.');

        if (
            $conflict->conflict_type === 'mutation_identity_mismatch'
            && $validated['action'] === 'retry'
        ) {
            return '[retry-new-mutation-id] '.$note;
        }

        return $note;
    }

    private function resolvedConflictMutationStatus(
        SyncConflict $conflict,
        ?SyncMutation $mutation
    ): ?string {
        if (
            $conflict->conflict_type === 'mutation_identity_mismatch'
            && str_starts_with((string) $conflict->resolution_note, '[retry-new-mutation-id]')
        ) {
            return 'new_mutation_required';
        }

        return $mutation?->status;
    }

    private function resolveMutationIdentityMismatch(?SyncMutation $mutation, string $action): string
    {
        if ($mutation && $mutation->status === 'pending') {
            $mutation->update([
                'status' => 'discarded',
                'last_error' => 'Superseded by a new mutation identifier after payload mismatch.',
            ]);
        }

        return $action === 'retry' ? 'new_mutation_required' : 'discarded';
    }

    public function payload(SyncConflict $conflict): array
    {
        return [
            'id' => $conflict->id,
            'mutation_id' => $conflict->mutation_id,
            'entity_type' => $conflict->entity_type,
            'entity_id' => $conflict->entity_id,
            'conflict_type' => $conflict->conflict_type,
            'status' => $conflict->status,
            'local_payload' => $conflict->local_payload,
            'remote_payload' => $conflict->remote_payload,
            'resolution_note' => $conflict->resolution_note,
            'resolved_at' => $conflict->resolved_at?->toIso8601String(),
            'created_at' => $conflict->created_at?->toIso8601String(),
        ];
    }
}
