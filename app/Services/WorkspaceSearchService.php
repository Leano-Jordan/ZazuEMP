<?php

namespace App\Services;

use App\Models\Business;
use App\Models\BusinessCapability;
use App\Models\Customer;
use App\Models\Event;
use App\Models\Invoice;
use App\Models\PurchaseOrder;
use App\Models\Quote;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class WorkspaceSearchService
{
    public const TYPES = [
        'customer',
        'event',
        'service',
        'supplier',
        'purchase_order',
        'quote',
        'invoice',
    ];

    public function search(Business $business, User $user, array $filters): array
    {
        $query = trim((string) ($filters['q'] ?? ''));
        $type = (string) ($filters['type'] ?? '');
        $status = trim((string) ($filters['status'] ?? ''));
        $from = $filters['from'] ?? null;
        $to = $filters['to'] ?? null;
        $types = $type && in_array($type, self::TYPES, true) ? [$type] : self::TYPES;

        $permissions = $this->permissions($user, $business);
        $results = [];

        foreach ($types as $candidate) {
            if (!$this->allowed($candidate, $permissions)) {
                continue;
            }

            foreach ($this->{'search'.str_replace(' ', '', ucwords(str_replace('_', ' ', $candidate)))}($business, $query, $status, $from, $to) as $result) {
                $results[] = $result;
            }
        }

        usort($results, static fn (array $left, array $right): int => strcmp(
            ($right['sort_date'] ?? ''),
            ($left['sort_date'] ?? '')
        ));

        foreach ($results as &$result) {
            unset($result['sort_date']);
        }

        return array_values($results);
    }

    private function permissions(User $user, Business $business): array
    {
        $membership = $user->businesses()
            ->whereKey($business->id)
            ->first();

        $role = $membership?->pivot?->role;

        if ($role === 'owner') {
            return ['*'];
        }

        return config('zazu.permissions.roles.'.$role, []);
    }

    private function allowed(string $type, array $permissions): bool
    {
        return in_array('*', $permissions, true)
            || match ($type) {
                'customer' => in_array('customers.view', $permissions, true),
                'event' => in_array('work.view', $permissions, true),
                'service' => in_array('capabilities.view', $permissions, true),
                'supplier' => in_array('suppliers.view', $permissions, true),
                'purchase_order' => in_array('purchasing.view', $permissions, true),
                'quote' => in_array('quotes.view', $permissions, true),
                'invoice' => in_array('finance.view', $permissions, true),
                default => false,
            };
    }

    private function applyStatusAndDate(Builder $query, string $status, ?string $from, ?string $to, string $dateColumn): void
    {
        if ($status !== '') {
            $query->where('status', $status);
        }

        if ($from) {
            $query->whereDate($dateColumn, '>=', $from);
        }

        if ($to) {
            $query->whereDate($dateColumn, '<=', $to);
        }
    }

    private function text(Builder $query, string $term, array $columns): void
    {
        if ($term === '') {
            return;
        }

        $query->where(function (Builder $builder) use ($term, $columns) {
            foreach ($columns as $column) {
                $builder->orWhere($column, 'like', '%'.$term.'%');
            }
        });
    }

    private function searchCustomer(Business $business, string $term, string $status, ?string $from, ?string $to): array
    {
        $query = Customer::query()->where('business_id', $business->id);
        $this->text($query, $term, ['name', 'legal_name', 'registration_number', 'tax_number', 'vat_number', 'billing_address']);
        $this->applyStatusAndDate($query, $status, $from, $to, 'created_at');

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

    private function searchEvent(Business $business, string $term, string $status, ?string $from, ?string $to): array
    {
        $query = Event::query()->where('business_id', $business->id);
        $this->text($query, $term, ['reference', 'name', 'event_type', 'customer_name', 'customer_phone', 'customer_email', 'event_address']);
        $this->applyStatusAndDate($query, $status, $from, $to, 'event_date');

        return $query->latest('event_date')->limit(10)->get()->map(fn (Event $event) => [
            'type' => 'event',
            'type_label' => 'Job',
            'title' => $event->name,
            'meta' => $event->customer_name.' · '.$event->reference,
            'status' => $event->status,
            'date' => $event->event_date?->format('d M Y'),
            'href' => route('work.show', $event),
            'sort_date' => $event->event_date?->toDateString(),
        ])->all();
    }

    private function searchService(Business $business, string $term, string $status, ?string $from, ?string $to): array
    {
        $query = BusinessCapability::query()->where('business_id', $business->id);
        $this->text($query, $term, ['name', 'category', 'capability_type', 'description']);
        $this->applyStatusAndDate($query, $status, $from, $to, 'created_at');

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

    private function searchSupplier(Business $business, string $term, string $status, ?string $from, ?string $to): array
    {
        $query = Supplier::query()->where('business_id', $business->id);
        $this->text($query, $term, ['name', 'contact_name', 'email', 'phone', 'notes']);
        $this->applyStatusAndDate($query, $status, $from, $to, 'created_at');

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

    private function searchPurchaseOrder(Business $business, string $term, string $status, ?string $from, ?string $to): array
    {
        $query = PurchaseOrder::query()
            ->where('business_id', $business->id)
            ->with(['supplier', 'event']);

        if ($term !== '') {
            $query->where(function (Builder $builder) use ($term) {
                $builder
                    ->where('reference', 'like', '%'.$term.'%')
                    ->orWhere('status', 'like', '%'.$term.'%')
                    ->orWhereHas('supplier', fn (Builder $supplier) => $supplier->where('name', 'like', '%'.$term.'%'))
                    ->orWhereHas('event', fn (Builder $event) => $event
                        ->where('business_id', request()->user() ? app(CurrentBusiness::class)->id(request()->user()) : 0)
                        ->where(function (Builder $nested) use ($term) {
                            $nested
                                ->where('reference', 'like', '%'.$term.'%')
                                ->orWhere('name', 'like', '%'.$term.'%');
                        }));
            });
        }

        $this->applyStatusAndDate($query, $status, $from, $to, 'ordered_at');

        return $query->latest()->limit(10)->get()->map(fn (PurchaseOrder $order) => [
            'type' => 'purchase_order',
            'type_label' => 'Purchase order',
            'title' => $order->reference,
            'meta' => trim(($order->supplier?->name ?: 'Supplier pending').' · '.($order->event?->name ?: 'Unassigned')),
            'status' => $order->status,
            'date' => $order->ordered_at?->format('d M Y'),
            'href' => route('purchasing.show', $order),
            'sort_date' => ($order->ordered_at ?: $order->created_at)?->toDateString(),
        ])->all();
    }

    private function searchQuote(Business $business, string $term, string $status, ?string $from, ?string $to): array
    {
        $query = Quote::query()
            ->whereHas('event', fn (Builder $event) => $event->where('business_id', $business->id));

        if ($term !== '') {
            $query->where(function (Builder $builder) use ($term) {
                $builder
                    ->where('reference', 'like', '%'.$term.'%')
                    ->orWhere('status', 'like', '%'.$term.'%')
                    ->orWhereHas('event', fn (Builder $event) => $event
                        ->where('name', 'like', '%'.$term.'%')
                        ->orWhere('reference', 'like', '%'.$term.'%')
                        ->orWhere('customer_name', 'like', '%'.$term.'%'));
            });
        }

        if ($status !== '') {
            $query->where('status', $status);
        }

        if ($from) {
            $query->whereDate('created_at', '>=', $from);
        }

        if ($to) {
            $query->whereDate('created_at', '<=', $to);
        }

        return $query->with('event')->latest()->limit(10)->get()->map(fn (Quote $quote) => [
            'type' => 'quote',
            'type_label' => 'Quote',
            'title' => $quote->reference,
            'meta' => trim(($quote->event?->name ?: 'Job').' · '.($quote->currency ?: '')),
            'status' => $quote->status,
            'date' => $quote->created_at?->format('d M Y'),
            'href' => route('quotes.show', $quote),
            'sort_date' => $quote->created_at?->toDateTimeString(),
        ])->all();
    }

    private function searchInvoice(Business $business, string $term, string $status, ?string $from, ?string $to): array
    {
        $query = Invoice::query()->where('business_id', $business->id);
        $this->text($query, $term, ['number', 'customer_name', 'customer_email', 'customer_phone', 'business_legal_name', 'business_trading_name']);
        $this->applyStatusAndDate($query, $status, $from, $to, 'issued_at');

        return $query->latest('issued_at')->limit(10)->get()->map(fn (Invoice $invoice) => [
            'type' => 'invoice',
            'type_label' => 'Invoice',
            'title' => $invoice->number,
            'meta' => trim(($invoice->customer_name ?: 'Customer').' · '.($invoice->currency ?: '')),
            'status' => $invoice->status,
            'date' => $invoice->issued_at?->format('d M Y'),
            'href' => route('finance.invoices.show', $invoice),
            'sort_date' => ($invoice->issued_at ?: $invoice->created_at)?->toDateString(),
        ])->all();
    }
}
