<?php

namespace App\Support\Offline;

use App\Models\Asset;
use Illuminate\Support\Facades\DB;
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
        if (
            ! $record instanceof Model
            || ! $this->recordBelongsToBusiness($record, (int) $identity->business_id)
            || $identity->record_type !== $this->expectedMorphType($identity->entity_type)
        ) {
            $record = null;
        }

        $deleted = $record === null || (method_exists($record, 'trashed') && $record->trashed());
        $data = $this->aggregateData($record, $record?->toArray() ?? []);

        $related = $this->relatedAggregateData($identity);

        return [
            'revision' => (int) $identity->revision,
            'deleted' => $deleted,
            'record' => $data,
            'related' => $related,
        ];
    }


    private function expectedMorphType(string $entityType): ?string
    {
        $model = match ($entityType) {
            'customer' => Customer::class,
            'job' => Event::class,
            'quote' => Quote::class,
            'invoice' => Invoice::class,
            'purchase_order' => PurchaseOrder::class,
            'inventory_item' => InventoryItem::class,
            'asset' => Asset::class,
            default => null,
        };

        return $model ? (new $model)->getMorphClass() : null;
    }

    private function recordBelongsToBusiness(Model $record, int $businessId): bool
    {
        $recordBusinessId = $record->getAttribute('business_id');

        if ($recordBusinessId === null && method_exists($record, 'event')) {
            $recordBusinessId = $record->event()->withTrashed()->value('business_id');
        }

        return is_numeric($recordBusinessId) && (int) $recordBusinessId === $businessId;
    }

    /**
     * @return array<string, mixed>
     */
    private function relatedAggregateData(SyncEntityIdentity $identity): array
    {
        if ($identity->entity_type !== 'asset_allocation') {
            return [];
        }

        $allocation = DB::table('asset_allocations')
            ->where('business_id', $identity->business_id)
            ->where('id', $identity->record_id)
            ->first();

        if (! $allocation) {
            return [];
        }

        $assetIdentity = SyncEntityIdentity::query()
            ->where('business_id', $identity->business_id)
            ->where('entity_type', 'asset')
            ->where('record_type', (new Asset)->getMorphClass())
            ->where('record_id', $allocation->asset_id)
            ->first();

        if (! $assetIdentity) {
            return [];
        }

        $asset = $assetIdentity->record;
        if (
            ! $asset instanceof Asset
            || ! $this->recordBelongsToBusiness($asset, (int) $identity->business_id)
        ) {
            return [];
        }

        return [
            'entity_type' => 'asset',
            'local_id' => $assetIdentity->entity_uuid,
            'revision' => (int) $assetIdentity->revision,
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
