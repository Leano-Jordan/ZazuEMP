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

        $businessId = $this->resolveBusinessId($record);
        if (! $businessId) {
            return;
        }

        DB::table('sync_entity_identities')
            ->where('business_id', $businessId)
            ->where('entity_type', $entityType)
            ->where('record_type', $record->getMorphClass())
            ->where('record_id', (int) $record->getKey())
            ->increment('revision', 1, ['updated_at' => now()]);
    }

    private function resolveBusinessId(Model $record, int $depth = 0): ?int
    {
        if ($depth > 5) {
            return null;
        }

        $businessId = $record->getAttribute('business_id');
        if (is_numeric($businessId)) {
            return (int) $businessId;
        }

        foreach (['event', 'customer', 'quote', 'quoteVersion', 'invoice', 'purchaseOrder', 'inventoryItem', 'asset'] as $relation) {
            if (! method_exists($record, $relation)) {
                continue;
            }

            $related = $record->{$relation}()->first();
            if ($related instanceof Model) {
                $resolved = $this->resolveBusinessId($related, $depth + 1);
                if ($resolved) {
                    return $resolved;
                }
            }
        }

        return null;
    }
}
