<?php

namespace App\Support\Offline;

use App\Models\SyncCursor;
use App\Models\SyncDelivery;
use App\Models\SyncDevice;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use LogicException;

class SyncMutationProtocol
{
    public function pull(SyncDevice $device, string $stream = 'business', int $limit = 100): Collection
    {
        $this->assertDeviceContext($device, $stream);

        $cursor = SyncCursor::query()->firstOrCreate(
            [
                'business_id' => $device->business_id,
                'sync_device_id' => $device->id,
                'stream' => $stream,
            ],
            ['last_acknowledged_sequence' => 0],
        );

        return SyncDelivery::query()
            ->where('business_id', $device->business_id)
            ->where('destination_device_id', $device->id)
            ->where('stream', $stream)
            ->where('delivery_sequence', '>', $cursor->last_acknowledged_sequence)
            ->where('status', 'pending')
            ->with('mutation')
            ->orderBy('delivery_sequence')
            ->limit(max(1, min($limit, 500)))
            ->get()
            ->map(fn (SyncDelivery $delivery) => $delivery->mutation);
    }

    public function acknowledgeThrough(
        SyncDevice $device,
        int $sequence,
        string $stream = 'business',
    ): SyncCursor {
        $this->assertDeviceContext($device, $stream);

        if ($sequence < 0) {
            throw ValidationException::withMessages([
                'sequence' => 'The acknowledgement sequence cannot be negative.',
            ]);
        }

        return DB::transaction(function () use ($device, $stream, $sequence): SyncCursor {
            $cursor = SyncCursor::query()
                ->where('business_id', $device->business_id)
                ->where('sync_device_id', $device->id)
                ->where('stream', $stream)
                ->lockForUpdate()
                ->first();

            if (! $cursor) {
                $cursor = SyncCursor::create([
                    'business_id' => $device->business_id,
                    'sync_device_id' => $device->id,
                    'stream' => $stream,
                    'last_acknowledged_sequence' => 0,
                ]);
            }

            if ($sequence <= $cursor->last_acknowledged_sequence) {
                return $cursor;
            }

            $expected = $cursor->last_acknowledged_sequence + 1;
            $deliveries = SyncDelivery::query()
                ->where('business_id', $device->business_id)
                ->where('destination_device_id', $device->id)
                ->where('stream', $stream)
                ->whereBetween('delivery_sequence', [$expected, $sequence])
                ->orderBy('delivery_sequence')
                ->lockForUpdate()
                ->get();

            if ($deliveries->count() !== ($sequence - $expected + 1)) {
                throw ValidationException::withMessages([
                    'sequence' => 'The acknowledgement would skip a device delivery sequence.',
                ]);
            }

            $deliveries->each(function (SyncDelivery $delivery): void {
                $delivery->update([
                    'status' => 'applied',
                    'applied_at' => now(),
                    'attempts' => $delivery->attempts + 1,
                    'last_error' => null,
                ]);
            });

            $cursor->update([
                'last_acknowledged_sequence' => $sequence,
                'last_synced_at' => now(),
            ]);

            return $cursor->fresh();
        });
    }

    private function assertDeviceContext(SyncDevice $device, string $stream): void
    {
        if (! $device->exists) {
            throw new LogicException('A persisted sync device is required.');
        }

        if (! $device->isActive()) {
            throw new LogicException('Cannot synchronize an inactive sync device.');
        }

        if ($stream === '') {
            throw ValidationException::withMessages([
                'stream' => 'A sync stream is required.',
            ]);
        }
    }
}
