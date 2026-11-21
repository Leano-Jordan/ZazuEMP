<?php

namespace App\Support\Offline;

use App\Models\SyncDelivery;
use App\Models\SyncDevice;
use App\Models\SyncEntityIdentity;
use App\Models\SyncMutation;
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

        $identity = SyncEntityIdentity::query()
            ->where('business_id', $device->business_id)
            ->where('entity_type', $mutation->entity_type)
            ->where('entity_uuid', Str::lower($localId))
            ->first();

        if (! $identity) {
            return [];
        }

        return [[
            'entity_type' => $identity->entity_type,
            'server_id' => (int) $identity->record_id,
        ]];
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
