<?php

namespace App\Support\Offline;

use App\Models\SyncMutation;
use Illuminate\Support\Str;

final class SyncMutationCausalRevisionResolver
{
    public const MAX_SUPERSESSION_HOPS = 256;

    public function expectedRevision(SyncMutation $mutation): mixed
    {
        $predecessorId = $mutation->payload['predecessor_mutation_id'] ?? null;

        if (! is_string($predecessorId) || ! Str::isUuid($predecessorId)) {
            return $mutation->payload['expected_revision'] ?? null;
        }

        return $this->resolveChain($mutation, $predecessorId);
    }

    private function resolveChain(SyncMutation $mutation, string $predecessorId): ?int
    {
        $visited = [];

        for ($hop = 0; $hop < self::MAX_SUPERSESSION_HOPS; $hop++) {
            if (isset($visited[$predecessorId])) {
                return null;
            }

            $visited[$predecessorId] = true;
            $step = $this->inspectPredecessor($mutation, $predecessorId);

            if ($step['pending']) {
                throw new PendingSyncMutationPredecessorException(
                    'This offline edit is waiting for an earlier edit to be resolved.'
                );
            }

            if ($step['terminal']) {
                return $step['revision'];
            }

            if (! is_string($step['next'])) {
                return null;
            }

            $predecessorId = $step['next'];
        }

        return null;
    }

    /**
     * @return array{terminal:bool,pending:bool,revision:?int,next:?string}
     */
    private function inspectPredecessor(SyncMutation $mutation, string $predecessorId): array
    {
        $predecessor = $this->findPredecessorMutation($mutation, $predecessorId);

        if (! $this->matchesAggregate($mutation, $predecessor)) {
            return ['terminal' => true, 'pending' => false, 'revision' => null, 'next' => null];
        }

        if ($predecessor->status === 'pending') {
            return ['terminal' => false, 'pending' => true, 'revision' => null, 'next' => null];
        }

        if ($predecessor->status === 'applied') {
            return [
                'terminal' => true,
                'pending' => false,
                'revision' => is_numeric($predecessor->result_revision) ? (int) $predecessor->result_revision : null,
                'next' => null,
            ];
        }

        if ($predecessor->status !== 'discarded') {
            return ['terminal' => true, 'pending' => false, 'revision' => null, 'next' => null];
        }

        $successorId = $predecessor->superseded_by_mutation_id;
        if (is_string($successorId)) {
            return ['terminal' => false, 'pending' => false, 'revision' => null, 'next' => $successorId];
        }

        return [
            'terminal' => true,
            'pending' => false,
            'revision' => is_numeric($predecessor->result_revision) ? (int) $predecessor->result_revision : null,
            'next' => null,
        ];
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
