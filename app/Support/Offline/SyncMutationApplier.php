<?php

namespace App\Support\Offline;

use App\Models\SyncMutation;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use LogicException;

class SyncMutationApplier
{
    /**
     * @param array<string, SyncMutationHandler> $handlers
     */
    public function apply(SyncMutation $mutation, array $handlers): SyncMutation
    {
        if (! $mutation->exists) {
            throw new LogicException('A persisted sync mutation is required.');
        }

        if ($mutation->status !== 'pending') {
            return $mutation;
        }

        $handler = $handlers[$mutation->entity_type] ?? null;

        if (! $handler instanceof SyncMutationHandler) {
            throw ValidationException::withMessages([
                'entity_type' => 'This sync entity type has no registered safe handler.',
            ]);
        }

        return DB::transaction(function () use ($mutation, $handler): SyncMutation {
            $locked = SyncMutation::query()
                ->whereKey($mutation->id)
                ->lockForUpdate()
                ->first();

            if (! $locked) {
                throw (new ModelNotFoundException)->setModel(SyncMutation::class, [$mutation->id]);
            }

            if ($locked->status !== 'pending') {
                return $locked;
            }

            $handler->apply($locked);

            $locked->update([
                'status' => 'applied',
                'applied_at' => now(),
                'attempts' => $locked->attempts + 1,
                'last_error' => null,
            ]);

            return $locked->fresh();
        });
    }
}
