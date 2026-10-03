<?php

namespace App\Support\Offline;

use App\Models\Business;
use App\Models\SyncEntityIdentity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use LogicException;

class SyncEntityIdentityRegistry
{
    public function identify(Model $record, string $entityType): SyncEntityIdentity
    {
        [$businessId, $recordType, $recordId] = $this->recordCoordinates($record, $entityType);

        return DB::transaction(function () use ($businessId, $recordType, $recordId, $entityType): SyncEntityIdentity {
            Business::query()->whereKey($businessId)->lockForUpdate()->firstOrFail();

            $identity = SyncEntityIdentity::query()
                ->where('business_id', $businessId)
                ->where('record_type', $recordType)
                ->where('record_id', $recordId)
                ->first();

            if ($identity) {
                if ($identity->entity_type !== $entityType) {
                    throw ValidationException::withMessages([
                        'entity_type' => 'A record cannot change its synchronization entity type.',
                    ]);
                }

                return $identity;
            }

            return SyncEntityIdentity::create([
                'business_id' => $businessId,
                'entity_type' => $entityType,
                'entity_uuid' => (string) Str::uuid(),
                'record_type' => $recordType,
                'record_id' => $recordId,
            ]);
        });
    }

    public function register(
        Model $record,
        string $entityType,
        string $entityUuid,
    ): SyncEntityIdentity {
        [$businessId, $recordType, $recordId] = $this->recordCoordinates($record, $entityType);

        if (! Str::isUuid($entityUuid)) {
            throw ValidationException::withMessages([
                'entity_uuid' => 'A valid synchronization entity identifier is required.',
            ]);
        }

        $entityUuid = Str::lower($entityUuid);

        return DB::transaction(function () use ($businessId, $recordType, $recordId, $entityType, $entityUuid): SyncEntityIdentity {
            Business::query()->whereKey($businessId)->lockForUpdate()->firstOrFail();

            $recordIdentity = SyncEntityIdentity::query()
                ->where('business_id', $businessId)
                ->where('record_type', $recordType)
                ->where('record_id', $recordId)
                ->first();

            if ($recordIdentity) {
                if ($recordIdentity->entity_type !== $entityType || $recordIdentity->entity_uuid !== $entityUuid) {
                    throw ValidationException::withMessages([
                        'entity_uuid' => 'A synchronization identity cannot be reassigned to another record or type.',
                    ]);
                }

                return $recordIdentity;
            }

            $uuidIdentity = SyncEntityIdentity::query()
                ->where('business_id', $businessId)
                ->where('entity_uuid', $entityUuid)
                ->first();

            if ($uuidIdentity) {
                throw ValidationException::withMessages([
                    'entity_uuid' => 'This synchronization identity is already assigned to another record.',
                ]);
            }

            return SyncEntityIdentity::create([
                'business_id' => $businessId,
                'entity_type' => $entityType,
                'entity_uuid' => $entityUuid,
                'record_type' => $recordType,
                'record_id' => $recordId,
            ]);
        });
    }

    /**
     * @return array{int, string, int}
     */
    private function recordCoordinates(Model $record, string $entityType): array
    {
        $recordId = $record->getRawOriginal($record->getKeyName());

        if (
            ! $record->exists
            || ! is_numeric($record->getKey())
            || ! is_numeric($recordId)
            || (int) $record->getKey() !== (int) $recordId
        ) {
            throw new LogicException('A persisted record with an integer key is required for synchronization identity.');
        }

        $businessId = $record->getAttribute('business_id');
        $persistedBusinessId = $record->newQueryWithoutScopes()
            ->whereKey($recordId)
            ->value('business_id');
        $recordType = $record->getMorphClass();

        if (
            ! is_numeric($businessId)
            || ! is_numeric($persistedBusinessId)
            || (int) $businessId !== (int) $persistedBusinessId
            || $recordType === ''
            || strlen($recordType) > 191
        ) {
            throw new LogicException('Synchronization identity requires a business-owned record.');
        }

        if (! preg_match('/^[a-z][a-z0-9._-]{0,63}$/', $entityType)) {
            throw ValidationException::withMessages([
                'entity_type' => 'The synchronization entity type is invalid.',
            ]);
        }

        return [(int) $businessId, $recordType, (int) $recordId];
    }
}
