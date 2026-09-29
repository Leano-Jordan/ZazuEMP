<?php

namespace App\Services\Search;

use App\Models\Business;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Builder;

class CustomerSearchHandler extends AbstractWorkspaceSearchHandler
{
    public function search(Business $business, string $term, string $status, ?string $from, ?string $to): array
    {
        $query = Customer::query()->where('business_id', $business->id);
        if ($status !== '') {
            return [];
        }
        $this->text($query, $term, ['name', 'legal_name', 'registration_number', 'tax_number', 'vat_number', 'billing_address']);
        $this->applyStatusAndDate($query, '', $from, $to, 'created_at', false);

        return $query->latest()->limit(10)->get()->map(fn (Customer $customer) => [
            'type' => 'customer',
            'type_label' => 'Customer',
            'title' => $customer->name,
            'meta' => $customer->legal_name ?: 'Customer record',
            'status' => '',
            'date' => $customer->created_at?->format('d M Y'),
            'href' => route('customers.show', $customer),
            'sort_date' => $customer->created_at?->toDateTimeString(),
        ])->all();
    }
}
