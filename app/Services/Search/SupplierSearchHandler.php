<?php

namespace App\Services\Search;

use App\Models\Business;
use App\Models\Supplier;

class SupplierSearchHandler extends AbstractWorkspaceSearchHandler
{
    public function search(Business $business, string $term, string $status, ?string $from, ?string $to): array
    {
        $query = Supplier::query()->where('business_id', $business->id);
        if ($status !== '') {
            return [];
        }
        $this->text($query, $term, ['name', 'contact_name', 'email', 'phone', 'notes']);
        $this->applyStatusAndDate($query, '', $from, $to, 'created_at', false);

        return $query->latest()->limit(10)->get()->map(fn (Supplier $supplier) => [
            'type' => 'supplier',
            'type_label' => 'Supplier',
            'title' => $supplier->name,
            'meta' => $supplier->contact_name ?: 'Supplier record',
            'status' => '',
            'date' => $supplier->created_at?->format('d M Y'),
            'href' => route('suppliers.index'),
            'sort_date' => $supplier->created_at?->toDateTimeString(),
        ])->all();
    }
}
