<?php

namespace App\Support\Offline;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

final class SyncEntityRevisionTracker
{
    private static int $trackingSuspended = 0;

    public static function withoutTracking(callable $callback): mixed
    {
        self::$trackingSuspended++;

        try {
            return $callback();
        } finally {
            self::$trackingSuspended--;
        }
    }

    public function touch(Model $record, string $entityType): void
    {
        if (self::$trackingSuspended > 0 || ! $record->exists || ! is_numeric($record->getKey())) {
            return;
        }

        $businessId = $record->getAttribute('business_id');
        if (! is_numeric($businessId)) {
            foreach (['event', 'customer', 'quote', 'invoice', 'purchaseOrder', 'inventoryItem', 'asset'] as $relation) {
                if (! method_exists($record, $relation)) {
                    continue;
                }

                $businessId = $record->{$relation}()->value('business_id');
                if (is_numeric($businessId)) {
                    break;
                }
            }
        }

        if (! is_numeric($businessId)) {
            return;
        }

        DB::table('sync_entity_identities')
            ->where('business_id', (int) $businessId)
            ->where('entity_type', $entityType)
            ->where('record_type', $record->getMorphClass())
            ->where('record_id', (int) $record->getKey())
            ->increment('revision', 1, ['updated_at' => now()]);
    }
}
