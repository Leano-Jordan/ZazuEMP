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
use Illuminate\Database\Eloquent\Model;

final class SyncEntitySnapshotBuilder
{
    /**
     * @return array{revision:int,deleted:bool,record:array<string,mixed>}
     */
    public function build(SyncEntityIdentity $identity): array
    {
        $record = $identity->record;
        $deleted = $record === null || (method_exists($record, 'trashed') && $record->trashed());
        $data = $this->aggregateData($record, $record?->toArray() ?? []);

        return [
            'revision' => (int) $identity->revision,
            'deleted' => $deleted,
            'record' => $data,
        ];
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function aggregateData(?Model $record, array $data): array
    {
        if ($record instanceof Customer) {
            return $this->customerData($record, $data);
        }

        if ($record instanceof Event) {
            $data['requirements'] = $record->requirements()->get()->toArray();

            return $data;
        }

        if ($record instanceof Quote) {
            $record->loadMissing('latestVersion.items');
            $data['latest_version'] = $record->latestVersion?->toArray();

            return $data;
        }

        if ($record instanceof PurchaseOrder) {
            $data['items'] = $record->items()->get()->toArray();

            return $data;
        }

        if ($record instanceof Invoice) {
            $record->loadMissing(['items', 'payments']);
            $data['items'] = $record->items->toArray();
            $data['payments'] = $record->payments->toArray();
            $data['paid_amount'] = $record->paid_amount;
            $data['balance'] = $record->balance;

            return $data;
        }

        if ($record instanceof InventoryItem) {
            $data['movements'] = $record->movements()->get()->toArray();
            $data['on_hand'] = $record->on_hand;

            return $data;
        }

        if ($record instanceof Asset) {
            $data['allocations'] = $record->allocations()->where('status', 'allocated')->get()->toArray();
        }

        return $data;
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function customerData(Customer $customer, array $data): array
    {
        $customer->loadMissing('contacts');
        $contacts = $customer->contacts;
        $primary = $contacts->firstWhere('is_primary', true) ?? $contacts->first();

        $data['contacts'] = $contacts->toArray();
        $data['contact_name'] = $primary?->name;
        $data['phone'] = $primary?->phone;
        $data['email'] = $primary?->email;

        return $data;
    }
}
