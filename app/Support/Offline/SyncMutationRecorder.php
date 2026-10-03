<?php

namespace App\Support\Offline;

use App\Models\SyncDevice;
use App\Models\SyncMutation;
use Illuminate\Support\Str;
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

        $nextSequence = (int) (SyncMutation::query()
            ->where('business_id', $device->business_id)
            ->where('stream', $stream)
            ->max('sequence') ?? 0) + 1;

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

        return SyncMutation::create([
            'business_id' => $device->business_id,
            'sync_device_id' => $device->id,
            'stream' => $stream,
            'sequence' => $nextSequence,
            'mutation_id' => $mutationId,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'operation' => $operation,
            'status' => 'pending',
            'payload' => $payload,
            'occurred_at' => now(),
        ]);
    }
}
