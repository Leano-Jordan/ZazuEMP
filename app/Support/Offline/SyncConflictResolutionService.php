<?php

namespace App\Support\Offline;

use App\Models\SyncConflict;
use App\Models\SyncDevice;
use App\Models\SyncMutation;
use App\Models\SyncEntityIdentity;
use Illuminate\Support\Str;
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
            $mutationStatus = $this->resolvedConflictMutationStatus($lockedConflict, $mutation);
            $rebase = $mutationStatus === 'new_mutation_required'
                ? array_filter([
                    'new_mutation_id' => $mutation?->superseded_by_mutation_id,
                    'expected_revision' => $lockedConflict->conflict_type === 'stale_record_version'
                        ? (int) ($lockedConflict->remote_payload['revision'] ?? 0)
                        : null,
                ], static fn ($value) => $value !== null)
                : null;

            return response()->json([
                'conflict' => $this->payload($lockedConflict),
                'mutation_status' => $mutationStatus,
                'rebase' => $rebase,
            ]);
        }

        if ($lockedConflict->conflict_type === 'mutation_identity_mismatch' && $validated['action'] === 'retry') {
            abort_unless($mutation, 404);
            $this->assertCausalPredecessorApplied($mutation, $device);
            $newMutationId = (string) Str::uuid();
            $mutation->update([
                'status' => 'discarded',
                'last_error' => 'Superseded by a new immutable mutation identifier after payload correction.',
                'superseded_by_mutation_id' => $newMutationId,
            ]);
            $lockedConflict->update([
                'status' => 'resolved',
                'resolution_note' => $this->conflictResolutionNote($lockedConflict, $validated),
                'resolved_at' => now(),
            ]);

            return response()->json([
                'conflict' => $this->payload($lockedConflict->fresh()),
                'mutation_status' => 'new_mutation_required',
                'rebase' => ['new_mutation_id' => $newMutationId],
            ]);
        }

        if ($lockedConflict->conflict_type === 'stale_record_version') {
            $remotePayload = $this->currentRemotePayload($lockedConflict);
            $lockedConflict->update(['remote_payload' => $remotePayload]);

            if ($validated['action'] === 'retry') {
                abort_unless($mutation, 404);
                $this->assertCausalPredecessorApplied($mutation, $device);
                abort_unless((int) ($remotePayload['revision'] ?? 0) > 0, 409, 'The latest server revision is unavailable. Refresh the workspace before rebasing.');
                $newMutationId = (string) Str::uuid();
                $mutation->update([
                    'status' => 'discarded',
                    'last_error' => 'Superseded by an explicit rebase onto the latest server revision.',
                    'superseded_by_mutation_id' => $newMutationId,
                ]);

                $mutationStatus = 'new_mutation_required';
                $rebase = [
                    'new_mutation_id' => $newMutationId,
                    'expected_revision' => (int) ($remotePayload['revision'] ?? 0),
                ];
            } else {
                $mutation?->update([
                    'status' => 'discarded',
                    'last_error' => 'Local version discarded in favour of the latest server version.',
                ]);
                $mutationStatus = 'discarded';
                $rebase = null;
            }

            $lockedConflict->update([
                'status' => 'resolved',
                'resolution_note' => $this->conflictResolutionNote($lockedConflict, $validated),
                'resolved_at' => now(),
            ]);

            return response()->json([
                'conflict' => $this->payload($lockedConflict->fresh()),
                'mutation_status' => $mutationStatus,
                'rebase' => $rebase,
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
            'conflict' => $this->payload($lockedConflict->fresh()),
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
            in_array($conflict->conflict_type, ['mutation_identity_mismatch', 'stale_record_version'], true)
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
        if ($conflict->conflict_type === 'stale_record_version' && $mutation?->superseded_by_mutation_id) {
            return 'new_mutation_required';
        }

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



    private function assertCausalPredecessorApplied(SyncMutation $mutation, SyncDevice $device): void
    {
        $predecessorId = $mutation->payload['predecessor_mutation_id'] ?? null;
        if (! is_string($predecessorId) || ! Str::isUuid($predecessorId)) {
            return;
        }

        $visited = [];
        for ($depth = 0; $depth < 8; $depth++) {
            if (isset($visited[$predecessorId])) {
                abort(409, 'The earlier offline change has a broken causal chain.');
            }
            $visited[$predecessorId] = true;

            $predecessor = SyncMutation::query()
                ->where('business_id', $device->business_id)
                ->where('sync_device_id', $device->id)
                ->where('mutation_id', $predecessorId)
                ->lockForUpdate()
                ->first();

            abort_unless(
                $predecessor
                    && $predecessor->entity_type === $mutation->entity_type
                    && $predecessor->entity_id === $mutation->entity_id,
                409,
                'Resolve the earlier offline change before rebasing this edit.'
            );

            if ($predecessor->status === 'applied' && is_numeric($predecessor->result_revision)) {
                return;
            }

            if ($predecessor->status !== 'discarded' || ! is_string($predecessor->superseded_by_mutation_id)) {
                abort(409, 'Resolve the earlier offline change before rebasing this edit.');
            }

            $predecessorId = $predecessor->superseded_by_mutation_id;
        }

        abort(409, 'The earlier offline change has too many rebase links.');
    }

    /**
     * @return array<string, mixed>
     */
    private function currentRemotePayload(SyncConflict $conflict): array
    {
        $identity = SyncEntityIdentity::query()
            ->where('business_id', $conflict->business_id)
            ->where('entity_type', $conflict->entity_type)
            ->where('entity_uuid', $conflict->entity_id)
            ->lockForUpdate()
            ->first();

        if (! $identity) {
            return $conflict->remote_payload ?? [];
        }

        return app(SyncEntitySnapshotBuilder::class)->build($identity);
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
