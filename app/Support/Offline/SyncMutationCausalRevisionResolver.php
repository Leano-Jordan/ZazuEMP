<?php

namespace App\Support\Offline;

use App\Models\SyncMutation;
use Illuminate\Support\Str;

final class SyncMutationCausalRevisionResolver
{
    public function expectedRevision(SyncMutation $mutation): mixed
    {
        $predecessorId = $mutation->payload['predecessor_mutation_id'] ?? null;

        if (! is_string($predecessorId) || ! Str::isUuid($predecessorId)) {
            return $mutation->payload['expected_revision'] ?? null;
        }

        $visited = [];

        return $this->resolvePredecessor($mutation, $predecessorId, $visited, 0);
    }

    /**
     * @param array<string, bool> $visited
     */
    private function resolvePredecessor(
        SyncMutation $mutation,
        string $predecessorId,
        array &$visited,
        int $depth,
    ): ?int {
        if ($depth >= 8 || isset($visited[$predecessorId])) {
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

        return $this->resolveDiscardedPredecessor($mutation, $predecessor, $visited, $depth);
    }

    /**
     * @param array<string, bool> $visited
     */
    private function resolveDiscardedPredecessor(
        SyncMutation $mutation,
        SyncMutation $predecessor,
        array &$visited,
        int $depth,
    ): ?int {
        $successorId = $predecessor->superseded_by_mutation_id;

        if (! is_string($successorId)) {
            return is_numeric($predecessor->result_revision)
                ? (int) $predecessor->result_revision
                : null;
        }

        if (! $this->findPredecessorMutation($mutation, $successorId)) {
            throw new PendingSyncMutationPredecessorException(
                'This offline edit is waiting for its earlier rebased edit to synchronize.'
            );
        }

        return $this->resolvePredecessor($mutation, $successorId, $visited, $depth + 1);
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
