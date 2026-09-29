<?php

namespace App\Services\Search;

use App\Models\Business;
use App\Models\Asset;

class AssetSearchHandler extends AbstractWorkspaceSearchHandler
{
    public function search(Business $business, string $term, string $status, ?string $from, ?string $to): array
    {
        $query = Asset::query()->where('business_id', $business->id);
        $this->text($query, $term, ['asset_tag', 'name', 'condition', 'location', 'notes']);
        $this->applyStatusAndDate($query, $status, $from, $to, 'acquired_at');

        return $query->latest()->limit(10)->get()->map(fn (Asset $asset) => [
            'type' => 'asset',
            'type_label' => 'Asset',
            'title' => $asset->name,
            'meta' => trim(($asset->asset_tag ?: 'Asset'). ' · '.($asset->location ?: 'Location not set')),
            'status' => $asset->status,
            'date' => $asset->acquired_at?->format('d M Y'),
            'href' => route('assets.index'),
            'sort_date' => ($asset->acquired_at ?: $asset->created_at)?->toDateString(),
        ])->all();
    }
}
