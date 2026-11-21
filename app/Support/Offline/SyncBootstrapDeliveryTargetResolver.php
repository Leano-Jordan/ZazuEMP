<?php

namespace App\Support\Offline;

use App\Models\BusinessCapability;
use App\Models\Customer;
use App\Models\Event;
use App\Models\EventCost;
use App\Models\EventPreparationItem;
use App\Models\FinanceExpense;
use App\Models\Invoice;
use App\Models\InventoryItem;
use App\Models\PurchaseOrder;
use App\Models\Quote;
use App\Models\Supplier;
use App\Models\SyncDelivery;
use App\Models\SyncDevice;
use App\Models\SyncEntityIdentity;
use App\Models\SyncMutation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

final class SyncBootstrapDeliveryTargetResolver
{
    /**
     * @param array<int, mixed> $deliverySequences
     * @return array<string, array<int, mixed>>
     */
    public function resolve(SyncDevice $device, array $deliverySequences): array
    {
        $sequences = $this->normaliseSequences($deliverySequences);
        if ($sequences === []) {
            return [];
        }

        $deliveries = SyncDelivery::query()
            ->where('business_id', $device->business_id)
            ->where('destination_device_id', $device->id)
            ->where('stream', 'business')
            ->whereIn('delivery_sequence', $sequences)
            ->with('mutation')
            ->get();

        $targets = [];
        $identityResolver = app(SyncMutationRelatedIdentityResolver::class);
        foreach ($deliveries as $delivery) {
            if (! $delivery->mutation) {
                continue;
            }

            $identities = $this->identitiesForMutation($device, $delivery->mutation, $identityResolver);
            foreach ($identities as $identity) {
                $this->appendTarget($targets, $identity);
            }
        }

        return $this->normaliseTargets($targets);
    }

    /**
     * @param array<int, mixed> $deliverySequences
     * @return array<int, int>
     */
    private function normaliseSequences(array $deliverySequences): array
    {
        return array_values(array_unique(array_filter(
            array_map(static fn ($sequence): int => (int) $sequence, $deliverySequences),
            static fn (int $sequence): bool => $sequence > 0,
        )));
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function identitiesForMutation(
        SyncDevice $device,
        SyncMutation $mutation,
        SyncMutationRelatedIdentityResolver $identityResolver,
    ): array {
        $identities = $identityResolver->resolve($mutation);
        if ($identities !== []) {
            return $identities;
        }

        $localId = $mutation->payload['local_id'] ?? $mutation->entity_id;
        if (! is_string($localId) || ! Str::isUuid($localId)) {
            return [];
        }

        $expectedModel = $this->expectedModel($mutation->entity_type);
        if ($expectedModel === null) {
            return [];
        }

        $identity = SyncEntityIdentity::query()
            ->where('business_id', $device->business_id)
            ->where('entity_type', $mutation->entity_type)
            ->where('record_type', (new $expectedModel)->getMorphClass())
            ->where('entity_uuid', Str::lower($localId))
            ->first();

        if (! $identity) {
            return [];
        }

        $record = $identity->record;
        if (! $record instanceof Model || ! $record instanceof $expectedModel) {
            return [];
        }

        $recordBusinessId = $record->getAttribute('business_id');
        if ($recordBusinessId === null && method_exists($record, 'event')) {
            $recordBusinessId = $record->event()->withTrashed()->value('business_id');
        }

        if (! is_numeric($recordBusinessId) || (int) $recordBusinessId !== (int) $device->business_id) {
            return [];
        }

        return [[
            'entity_type' => $identity->entity_type,
            'server_id' => (int) $identity->record_id,
        ]];
    }

    /**
     * @return class-string<Model>|null
     */
    private function expectedModel(string $entityType): ?string
    {
        return match ($entityType) {
            'service' => BusinessCapability::class,
            'customer' => Customer::class,
            'job' => Event::class,
            'supplier' => Supplier::class,
            'preparation' => EventPreparationItem::class,
            'purchase_order' => PurchaseOrder::class,
            'quote' => Quote::class,
            'invoice' => Invoice::class,
            'expense' => FinanceExpense::class,
            'inventory_item' => InventoryItem::class,
            'event_cost' => EventCost::class,
            'asset' => \\App\\Models\\Asset::class,
            default => null,
        };
    }

    /**
     * @param array<string, array<int, mixed>> $targets
     * @param array<string, mixed> $identity
     */
    private function appendTarget(array &$targets, array $identity): void
    {
        $type = (string) ($identity['entity_type'] ?? '');
        $recordId = (int) ($identity['server_id'] ?? 0);
        if ($recordId < 1) {
            return;
        }

        $collection = match ($type) {
            'supplier' => 'supplier',
            'purchase_order' => 'purchase_order',
            'invoice' => 'invoice',
            'expense' => 'expense',
            'inventory_item' => 'inventory_item',
            'asset' => 'asset',
            'event_cost' => 'event_cost',
            default => null,
        };

        if ($collection !== null) {
            $targets[$collection][] = $recordId;
        }

        if (in_array($type, ['customer', 'job', 'quote', 'preparation'], true)) {
            $targets['selected'][] = ['type' => $type, 'id' => $recordId];
        }
    }

    /**
     * @param array<string, array<int, mixed>> $targets
     * @return array<string, array<int, mixed>>
     */
    private function normaliseTargets(array $targets): array
    {
        foreach ($targets as $key => $ids) {
            if ($key === 'selected') {
                $unique = [];
                foreach ($ids as $item) {
                    $unique[$item['type'].':'.$item['id']] = $item;
                }
                $targets[$key] = array_values($unique);
                continue;
            }

            $targets[$key] = array_values(array_unique(array_map('intval', $ids)));
        }

        return $targets;
    }
}
