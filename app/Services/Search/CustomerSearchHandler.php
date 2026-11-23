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
        if ($term !== '') {
            $like = '%'.$term.'%';

            $query->where(function (Builder $builder) use ($like): void {
                $builder
                    ->where('name', 'like', $like)
                    ->orWhere('legal_name', 'like', $like)
                    ->orWhere('registration_number', 'like', $like)
                    ->orWhere('tax_number', 'like', $like)
                    ->orWhere('vat_number', 'like', $like)
                    ->orWhere('billing_address', 'like', $like)
                    ->orWhereHas('contacts', function (Builder $contacts) use ($like): void {
                        $contacts
                            ->where('name', 'like', $like)
                            ->orWhere('phone', 'like', $like)
                            ->orWhere('email', 'like', $like)
                            ->orWhere('label', 'like', $like);
                    });
            });
        }
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
