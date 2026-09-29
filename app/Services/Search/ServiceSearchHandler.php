<?php

namespace App\Services\Search;

use App\Models\Business;
use App\Models\BusinessCapability;

class ServiceSearchHandler extends AbstractWorkspaceSearchHandler
{
    public function search(Business $business, string $term, string $status, ?string $from, ?string $to): array
    {
        $query = BusinessCapability::query()->where('business_id', $business->id);
        $this->text($query, $term, ['name', 'category', 'capability_type', 'description']);
        if ($status === 'active') {
            $query->where('is_active', true);
        } elseif ($status === 'inactive') {
            $query->where('is_active', false);
        } elseif ($status !== '') {
            return [];
        }

        $this->applyStatusAndDate($query, '', $from, $to, 'created_at', false);

        return $query->latest()->limit(10)->get()->map(fn (BusinessCapability $service) => [
            'type' => 'service',
            'type_label' => 'Service',
            'title' => $service->name,
            'meta' => trim(($service->category ?: 'Service').' · '.($service->capability_type ?: 'Offering')),
            'status' => $service->is_active ? 'active' : 'inactive',
            'date' => $service->created_at?->format('d M Y'),
            'href' => route('capabilities.index'),
            'sort_date' => $service->created_at?->toDateTimeString(),
        ])->all();
    }
}
