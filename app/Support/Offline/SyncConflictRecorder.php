<?php

namespace App\Support\Offline;

use App\Models\SyncConflict;
use App\Models\SyncDevice;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SyncConflictRecorder
{
    /**
     * Record one active conflict per device/mutation/type, serializing concurrent
     * push retries on the device row so duplicate requests cannot create duplicate UI entries.
     *
     * @param array<string, mixed>|null $localPayload
     * @param array<string, mixed>|null $remotePayload
     */
    public function recordOnce(
        SyncDevice $device,
        string $entityType,
        string $entityId,
        string $conflictType,
        string $mutationId,
        ?array $localPayload = null,
        ?array $remotePayload = null,
    ): SyncConflict {
        return \Illuminate\Support\Facades\DB::transaction(function () use (
            $device,
            $entityType,
            $entityId,
            $conflictType,
            $mutationId,
            $localPayload,
            $remotePayload,
        ): SyncConflict {
            $lockedDevice = SyncDevice::query()
                ->whereKey($device->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ((int) $lockedDevice->business_id !== (int) $device->business_id || ! $lockedDevice->isActive()) {
                throw new \LogicException('Cannot record a conflict for an inactive or mismatched sync device.');
            }

            $existing = SyncConflict::query()
                ->where('business_id', $lockedDevice->business_id)
                ->where('sync_device_id', $lockedDevice->id)
                ->where('mutation_id', $mutationId)
                ->where('conflict_type', $conflictType)
                ->where('status', 'open')
                ->lockForUpdate()
                ->first();

            if ($existing) {
                $existing->update([
                    'entity_type' => $entityType,
                    'entity_id' => $entityId,
                    'local_payload' => $localPayload,
                    'remote_payload' => $remotePayload,
                ]);

                return $existing->fresh();
            }

            return $this->record(
                (int) $lockedDevice->business_id,
                $entityType,
                $entityId,
                $conflictType,
                $lockedDevice,
                $mutationId,
                $localPayload,
                $remotePayload,
            );
        });
    }

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
