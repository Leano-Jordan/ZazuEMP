<?php

namespace App\Support\Offline;

use App\Models\SyncConflict;
use App\Models\SyncDevice;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SyncConflictRecorder
{
    public function record(
        int $businessId,
        string $entityType,
        string $entityId,
        string $conflictType,
        ?SyncDevice $device = null,
        ?string $mutationId = null,
        ?array $localPayload = null,
        ?array $remotePayload = null,
    ): SyncConflict {
        if ($device && (int) $device->business_id !== $businessId) {
            throw ValidationException::withMessages([
                'sync_device_id' => 'The sync device does not belong to the supplied business.',
            ]);
        }

        if ($device && ! $device->isActive()) {
            throw new \LogicException('Cannot attach a conflict to an inactive sync device.');
        }

        return SyncConflict::create([
            'business_id' => $businessId,
            'sync_device_id' => $device?->id,
            'mutation_id' => $mutationId ?? (string) Str::uuid(),
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'conflict_type' => $conflictType,
            'status' => 'open',
            'local_payload' => $localPayload,
            'remote_payload' => $remotePayload,
        ]);
    }
}
