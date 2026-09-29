<?php

namespace App\Services\Search;

use App\Models\Business;
use App\Models\InventoryItem;

class InventorySearchHandler extends AbstractWorkspaceSearchHandler
{
    public function search(Business $business, string $term, string $status, ?string $from, ?string $to): array
    {
        $query = InventoryItem::query()->where('business_id', $business->id);
        if ($status !== '') {
            return [];
        }
        $this->text($query, $term, ['sku', 'name', 'unit']);
        $this->applyStatusAndDate($query, '', $from, $to, 'created_at', false);

        return $query->latest()->limit(10)->get()->map(fn (InventoryItem $item) => [
            'type' => 'inventory',
            'type_label' => 'Inventory',
            'title' => $item->name,
            'meta' => trim(($item->sku ?: 'No SKU').' · '.($item->unit ?: 'Unit')),
            'status' => '',
            'date' => $item->created_at?->format('d M Y'),
            'href' => route('inventory.index'),
            'sort_date' => $item->created_at?->toDateTimeString(),
        ])->all();
    }
}
