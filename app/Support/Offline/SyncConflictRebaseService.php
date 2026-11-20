<?php

namespace App\Support\Offline;

use App\Models\SyncConflict;
use App\Models\SyncDevice;
use App\Models\SyncEntityIdentity;
use App\Models\SyncMutation;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

final class SyncConflictRebaseService
{
    public function resolveStaleRecordVersion(
        SyncConflict $conflict,
        ?SyncMutation $mutation,
        SyncDevice $device,
        array $validated,
    ): JsonResponse {
        $remotePayload = $this->currentRemotePayload($conflict);
        $conflict->update(['remote_payload' => $remotePayload]);

        if ($validated['action'] === 'retry') {
            abort_unless($mutation, 404);
            $this->assertCausalPredecessorApplied($mutation, $device);
            abort_unless(
                (int) ($remotePayload['revision'] ?? 0) > 0,
                409,
                'The latest server revision is unavailable. Refresh the workspace before rebasing.'
            );
            abort_unless(
                ! ($remotePayload['deleted'] ?? false),
                409,
                'This record was deleted on the server. Discard the local edit or restore the record through the normal business workflow.'
            );

            $newMutationId = (string) Str::uuid();
            $mutation->update([
                'status' => 'discarded',
                'last_error' => 'Superseded by an explicit rebase onto the latest server revision.',
                'superseded_by_mutation_id' => $newMutationId,
            ]);
            $mutationStatus = 'new_mutation_required';
            $rebase = [
                'new_mutation_id' => $newMutationId,
                'expected_revision' => (int) $remotePayload['revision'],
            ];
        } else {
            $mutation?->update([
                'status' => 'discarded',
                'result_revision' => (int) ($remotePayload['revision'] ?? 0) ?: null,
                'last_error' => 'Local version discarded in favour of the latest server version.',
            ]);
            $mutationStatus = 'discarded';
            $rebase = null;
        }

        $conflict->update([
            'status' => 'resolved',
            'resolution_note' => $this->conflictResolutionNote($conflict, $validated),
            'resolved_at' => now(),
        ]);

        return response()->json([
            'conflict' => app(SyncConflictPresenter::class)->payload($conflict->fresh()),
            'mutation_status' => $mutationStatus,
            'rebase' => $rebase,
        ]);
    }

    public function resolveIdentityMismatchRetry(
        SyncConflict $conflict,
        ?SyncMutation $mutation,
        SyncDevice $device,
        array $validated,
    ): JsonResponse {
        abort_unless($mutation, 404);
        $this->assertCausalPredecessorApplied($mutation, $device);
        $newMutationId = (string) Str::uuid();
        $mutation->update([
            'status' => 'discarded',
            'last_error' => 'Superseded by a new immutable mutation identifier after payload correction.',
            'superseded_by_mutation_id' => $newMutationId,
        ]);
        $conflict->update([
            'status' => 'resolved',
            'resolution_note' => $this->conflictResolutionNote($conflict, $validated),
            'resolved_at' => now(),
        ]);

        return response()->json([
            'conflict' => app(SyncConflictPresenter::class)->payload($conflict->fresh()),
            'mutation_status' => 'new_mutation_required',
            'rebase' => ['new_mutation_id' => $newMutationId],
        ]);
    }

    private function assertCausalPredecessorApplied(SyncMutation $mutation, SyncDevice $device): void
    {
        $predecessorId = $mutation->payload['predecessor_mutation_id'] ?? null;
        if (! is_string($predecessorId) || ! Str::isUuid($predecessorId)) {
            return;
        }

        $visited = [];
        for ($depth = 0; $depth < 8; $depth++) {
            abort_if(isset($visited[$predecessorId]), 409, 'The earlier offline change has a broken causal chain.');
            $visited[$predecessorId] = true;
            $predecessor = $this->findCausalPredecessor($mutation, $device, $predecessorId);

            if ($predecessor?->status === 'applied' && is_numeric($predecessor->result_revision)) {
                return;
            }

            if ($predecessor?->status === 'discarded' && ! $predecessor->superseded_by_mutation_id) {
                // The earlier local edit was explicitly discarded in favour of
                // the server version. This edit can now be rebased directly on it.
                return;
            }

            abort_unless(
                $predecessor?->status === 'discarded' && is_string($predecessor->superseded_by_mutation_id),
                409,
                'Resolve the earlier offline change before rebasing this edit.'
            );

            $predecessorId = $predecessor->superseded_by_mutation_id;
        }

        abort(409, 'The earlier offline change has too many rebase links.');
    }

    private function findCausalPredecessor(
        SyncMutation $mutation,
        SyncDevice $device,
        string $predecessorId,
    ): ?SyncMutation {
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

        return $predecessor;
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
}
