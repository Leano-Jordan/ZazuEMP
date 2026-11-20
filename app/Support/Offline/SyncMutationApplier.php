<?php

namespace App\Support\Offline;

use App\Models\Customer;
use App\Models\Event;
use App\Models\SyncDevice;
use App\Models\SyncEntityIdentity;
use App\Models\SyncMutation;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use LogicException;

class SyncMutationApplier
{
    public function __construct(private readonly SyncMutationRecorder $recorder)
    {
    }

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
            // Keep lock order consistent with SyncMutationRecorder: business,
            // device, mutation, then aggregate identity and domain rows.
            DB::table('businesses')->where('id', $mutation->business_id)->lockForUpdate()->first();

            $lockedDevice = SyncDevice::query()
                ->where('business_id', $mutation->business_id)
                ->whereKey($mutation->sync_device_id)
                ->active()
                ->lockForUpdate()
                ->first();

            abort_unless(
                $lockedDevice,
                401,
                'This sync device was revoked before the offline change could be applied.'
            );

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

            $identity = $this->lockEntityIdentity($locked);
            $hadIdentity = $identity !== null;
            $this->assertExpectedRevision($locked, $identity);

            SyncEntityRevisionTracker::withoutTracking(fn () => $handler->apply($locked));
            $this->advanceParentAggregateRevision($locked);

            $identity = $this->lockEntityIdentity($locked);
            if ($identity && $hadIdentity) {
                $identity->increment('revision');
                $identity->refresh();
            }

            $locked->update([
                'result_revision' => $identity?->revision,
                'status' => 'applied',
                'applied_at' => now(),
                'attempts' => $locked->attempts + 1,
                'last_error' => null,
            ]);

            // Publish inside the same database transaction as the domain mutation.
            // If delivery creation fails, the domain mutation and applied marker roll back together.
            $this->recorder->publish($locked);

            return $locked->fresh();
        });
    }

    private function lockEntityIdentity(SyncMutation $mutation): ?SyncEntityIdentity
    {
        $uuid = $mutation->payload['local_id'] ?? null;
        if (! is_string($uuid) || ! Str::isUuid($uuid)) {
            return null;
        }

        return SyncEntityIdentity::query()
            ->where('business_id', $mutation->business_id)
            ->where('entity_type', $mutation->entity_type)
            ->where('entity_uuid', Str::lower($uuid))
            ->lockForUpdate()
            ->first();
    }

    private function assertExpectedRevision(SyncMutation $mutation, ?SyncEntityIdentity $identity): void
    {
        if (! in_array($mutation->operation, ['update', 'upsert'], true) || ! $identity) {
            return;
        }

        $predecessorId = $mutation->payload['predecessor_mutation_id'] ?? null;
        $expectedRevision = is_string($predecessorId) && Str::isUuid($predecessorId)
            ? $this->predecessorResultRevision($mutation, $predecessorId)
            : ($mutation->payload['expected_revision'] ?? null);

        if (! is_numeric($expectedRevision)
            || (int) $expectedRevision < 1
            || (int) $expectedRevision !== (int) $identity->revision) {
            $remote = app(SyncEntitySnapshotBuilder::class)->build($identity);

            throw new StaleSyncRevisionException(
                'This record changed on another device. Review the latest server version before keeping your local edit.',
                $remote,
            );
        }
    }


    private function advanceParentAggregateRevision(SyncMutation $mutation): void
    {
        $payload = $mutation->payload['record'] ?? [];
        $parent = match ($mutation->entity_type) {
            'quote_acceptance' => ['quote', $payload['quote_local_id'] ?? null],
            'payment' => ['invoice', $payload['invoice_local_id'] ?? null],
            'purchase_receipt' => ['purchase_order', $payload['purchase_order_local_id'] ?? null],
            'inventory_movement' => ['inventory_item', $payload['inventory_item_local_id'] ?? null],
            'asset_allocation' => ['asset', $payload['asset_local_id'] ?? null],
            default => null,
        };

        if (! $parent || ! is_string($parent[1]) || ! Str::isUuid($parent[1])) {
            return;
        }

        $identity = SyncEntityIdentity::query()
            ->where('business_id', $mutation->business_id)
            ->where('entity_type', $parent[0])
            ->where('entity_uuid', Str::lower($parent[1]))
            ->lockForUpdate()
            ->first();

        $identity?->increment('revision');
    }

    private function predecessorResultRevision(SyncMutation $mutation, string $predecessorId): ?int
    {
        $visited = [];
        for ($depth = 0; $depth < 8; $depth++) {
            if (isset($visited[$predecessorId])) {
                return null;
            }
            $visited[$predecessorId] = true;

            $predecessor = SyncMutation::query()
                ->where('business_id', $mutation->business_id)
                ->where('sync_device_id', $mutation->sync_device_id)
                ->where('mutation_id', $predecessorId)
                ->lockForUpdate()
                ->first();

            if (! $predecessor
                || $predecessor->entity_type !== $mutation->entity_type
                || $predecessor->entity_id !== $mutation->entity_id) {
                return null;
            }

            if ($predecessor->status === 'applied' && is_numeric($predecessor->result_revision)) {
                return (int) $predecessor->result_revision;
            }

            if ($predecessor->status !== 'discarded' || ! is_string($predecessor->superseded_by_mutation_id)) {
                return null;
            }

            $predecessorId = $predecessor->superseded_by_mutation_id;
        }

        return null;
    }
}
