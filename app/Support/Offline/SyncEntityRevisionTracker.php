<?php

namespace App\Support\Offline;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

final class SyncEntityRevisionTracker
{
    public function touch(Model $record, string $entityType): void
    {
        if (! $record->exists || ! is_numeric($record->getKey())) {
            return;
        }

        $businessId = $record->getAttribute('business_id');
        if (! is_numeric($businessId) && method_exists($record, 'event')) {
            $businessId = $record->event()->withTrashed()->value('business_id');
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
