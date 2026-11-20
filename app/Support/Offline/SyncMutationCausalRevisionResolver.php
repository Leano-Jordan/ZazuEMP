<?php

namespace App\Support\Offline;

use App\Models\SyncMutation;
use Illuminate\Support\Str;

final class SyncMutationCausalRevisionResolver
{
    private const MAX_SUPERSESSION_HOPS = 256;

    public function expectedRevision(SyncMutation $mutation): mixed
    {
        $predecessorId = $mutation->payload['predecessor_mutation_id'] ?? null;

        if (! is_string($predecessorId) || ! Str::isUuid($predecessorId)) {
            return $mutation->payload['expected_revision'] ?? null;
        }

        $visited = [];

        for ($hop = 0; $hop < self::MAX_SUPERSESSION_HOPS; $hop++) {
            if (isset($visited[$predecessorId])) {
                return null;
            }

            $visited[$predecessorId] = true;
            $predecessor = $this->findPredecessorMutation($mutation, $predecessorId);

            if (! $this->matchesAggregate($mutation, $predecessor)) {
                return null;
            }

            if ($predecessor->status === 'applied') {
                return is_numeric($predecessor->result_revision)
                    ? (int) $predecessor->result_revision
                    : null;
            }

            if ($predecessor->status === 'pending') {
                throw new PendingSyncMutationPredecessorException(
                    'This offline edit is waiting for an earlier edit to be resolved.'
                );
            }

            if ($predecessor->status !== 'discarded') {
                return null;
            }

            $successorId = $predecessor->superseded_by_mutation_id;
            if (! is_string($successorId)) {
                return is_numeric($predecessor->result_revision)
                    ? (int) $predecessor->result_revision
                    : null;
            }

            $predecessorId = $successorId;
        }

        // A bounded traversal protects the sync transaction from pathological
        // chains while supporting repeated, legitimate keep-local rebases.
        return null;
    }

    private function findPredecessorMutation(SyncMutation $mutation, string $predecessorId): ?SyncMutation
    {
        return SyncMutation::query()
            ->where('business_id', $mutation->business_id)
            ->where('sync_device_id', $mutation->sync_device_id)
            ->where('mutation_id', $predecessorId)
            ->lockForUpdate()
            ->first();
    }

    private function matchesAggregate(SyncMutation $mutation, ?SyncMutation $predecessor): bool
    {
        return $predecessor !== null
            && $predecessor->entity_type === $mutation->entity_type
            && $predecessor->entity_id === $mutation->entity_id;
    }
}
