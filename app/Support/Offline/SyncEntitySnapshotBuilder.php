<?php

namespace App\Support\Offline;

use App\Models\Asset;
use App\Models\Customer;
use App\Models\Event;
use App\Models\Invoice;
use App\Models\InventoryItem;
use App\Models\PurchaseOrder;
use App\Models\Quote;
use App\Models\SyncEntityIdentity;

final class SyncEntitySnapshotBuilder
{
    /**
     * @return array{revision:int,record:array<string,mixed>}
     */
    public function build(SyncEntityIdentity $identity): array
    {
        $record = $identity->record;
        $data = $record?->toArray() ?? [];

        if ($record instanceof Customer) {
            $record->loadMissing('contacts');
            $contacts = $record->contacts->toArray();
            $primary = $record->contacts->firstWhere('is_primary', true) ?? $record->contacts->first();
            $data['contacts'] = $contacts;
            $data['contact_name'] = $primary?->name;
            $data['phone'] = $primary?->phone;
            $data['email'] = $primary?->email;
        } elseif ($record instanceof Event) {
            $data['requirements'] = $record->requirements()->get()->toArray();
        } elseif ($record instanceof Quote) {
            $record->loadMissing('latestVersion.items');
            $data['latest_version'] = $record->latestVersion?->toArray();
        } elseif ($record instanceof PurchaseOrder) {
            $data['items'] = $record->items()->get()->toArray();
        } elseif ($record instanceof Invoice) {
            $record->loadMissing(['items', 'payments']);
            $data['items'] = $record->items->toArray();
            $data['payments'] = $record->payments->toArray();
            $data['paid_amount'] = $record->paid_amount;
            $data['balance'] = $record->balance;
        } elseif ($record instanceof InventoryItem) {
            $data['movements'] = $record->movements()->get()->toArray();
            $data['on_hand'] = $record->on_hand;
        } elseif ($record instanceof Asset) {
            $data['allocations'] = $record->allocations()->where('status', 'allocated')->get()->toArray();
        }

        return [
            'revision' => (int) $identity->revision,
            'record' => $data,
        ];
    }
}
