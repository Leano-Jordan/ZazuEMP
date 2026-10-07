<?php

namespace App\Support\Offline;

use App\Models\SyncDelivery;
use App\Models\SyncDevice;
use App\Models\SyncMutation;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\SyncStream;
use Illuminate\Validation\ValidationException;
use LogicException;

class SyncMutationRecorder
{
    public function record(
        SyncDevice $device,
        string $entityType,
        string $entityId,
        string $operation,
        array $payload = [],
        ?string $mutationId = null,
        string $stream = 'business',
    ): SyncMutation {
        if (! $device->isActive()) {
            throw new LogicException('Cannot record a mutation for an inactive sync device.');
        }

        $mutationId ??= (string) Str::uuid();

        $existing = SyncMutation::query()
            ->where('mutation_id', $mutationId)
            ->first();

        if ($existing) {
            if (
                $existing->business_id !== $device->business_id
                || $existing->sync_device_id !== $device->id
            ) {
                throw ValidationException::withMessages([
                    'mutation_id' => 'The mutation identifier is already owned by another business or device.',
                ]);
            }

            return $existing;
        }

        return DB::transaction(function () use ($device, $stream, $mutationId, $entityType, $entityId, $operation, $payload): SyncMutation {
            $counter = SyncStream::query()
                ->where('business_id', $device->business_id)
                ->where('stream', $stream)
                ->lockForUpdate()
                ->first();

            if (! $counter) {
                $counter = SyncStream::create([
                    'business_id' => $device->business_id,
                    'stream' => $stream,
                    'next_sequence' => 1,
                ]);
            }

            $sequence = $counter->next_sequence;
            $counter->increment('next_sequence');

            $mutation = SyncMutation::create([
                'business_id' => $device->business_id,
                'sync_device_id' => $device->id,
                'stream' => $stream,
                'sequence' => $sequence,
                'mutation_id' => $mutationId,
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'operation' => $operation,
                'status' => 'pending',
                'payload' => $payload,
                'occurred_at' => now(),
            ]);

            return $mutation;
        });
    }

    public function publish(SyncMutation $mutation): void
    {
        if (! $mutation->exists || $mutation->status !== 'applied') {
            throw new LogicException('Only an applied sync mutation can be published.');
        }

        DB::transaction(function () use ($mutation): void {
            $this->queueDeliveries($mutation);
        });
    }

    private function queueDeliveries(SyncMutation $mutation): void
    {
        SyncDevice::query()
            ->where('business_id', $mutation->business_id)
            ->where('id', '!=', $mutation->sync_device_id)
            ->where('status', 'active')
            ->whereNull('revoked_at')
            ->orderBy('id')
            ->get()
            ->each(function (SyncDevice $destination) use ($mutation): void {
                SyncDevice::query()->whereKey($destination->id)->lockForUpdate()->firstOrFail();

                $existing = SyncDelivery::query()
                    ->where('sync_mutation_id', $mutation->id)
                    ->where('destination_device_id', $destination->id)
                    ->first();

                if ($existing) {
                    return;
                }

                $next = SyncDelivery::query()
                    ->where('destination_device_id', $destination->id)
                    ->where('stream', $mutation->stream)
                    ->lockForUpdate()
                    ->max('delivery_sequence');

                SyncDelivery::create([
                    'business_id' => $mutation->business_id,
                    'sync_mutation_id' => $mutation->id,
                    'destination_device_id' => $destination->id,
                    'stream' => $mutation->stream,
                    'delivery_sequence' => ((int) $next) + 1,
                    'status' => 'pending',
                ]);
            });
    }
}

