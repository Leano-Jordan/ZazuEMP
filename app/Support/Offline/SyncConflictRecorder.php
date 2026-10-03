<?php

namespace App\Support\Offline;

use App\Models\SyncConflict;
use App\Models\SyncDevice;
use Illuminate\Support\Str;

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
