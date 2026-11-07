<?php

namespace App\Services;

use App\Models\Business;
use App\Models\TaxRate;
use App\Models\QuoteItem;
use App\Models\PurchaseOrderReceipt;
use App\Models\InvoiceItem;
use App\Models\EventRequirement;
use App\Models\EventPreparationItem;
use App\Models\CustomerContact;
use App\Models\BusinessCapability;
use App\Models\AssetAllocation;
use App\Models\Asset;
use App\Models\Customer;
use App\Models\Event;
use App\Models\EventCost;
use App\Models\FinanceExpense;
use App\Models\InventoryItem;
use App\Models\InventoryMovement;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Quote;
use App\Models\Supplier;
use App\Models\TravelCost;
use App\Support\Audit;
use Closure;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Str;

final class BusinessSpreadsheetExportService
{
    /**
     * @return array<int, array{resource:string,label:string,description:string,scope:string}>
     */
    public function catalog(Business $business): array
    {
        $definitions = $this->definitions($business);

        return array_map(
            fn (string $resource, array $definition): array => [
                'resource' => $resource,
                'label' => $definition['label'],
                'description' => $definition['description'],
                'scope' => 'Current business workspace',
            ],
            array_keys($definitions),
            array_values($definitions)
        );
    }

    public function download(Business $business, string $resource): StreamedResponse
    {
        $definition = $this->definitions($business)[$resource] ?? abort(404);
        $filename = sprintf(
            'zazu-%s-%s-%s.csv',
            Str::slug($business->slug ?: $business->name),
            $resource,
            now()->format('Ymd-His')
        );

        Audit::record(
            'business.data_exported',
            null,
            ['resource' => $resource, 'format' => 'csv'],
            (int) $business->id
        );

        return response()->streamDownload(function () use ($definition): void {
            $this->writeCsv($definition);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Cache-Control' => 'private, no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    /**
     * @return array<string, array{
     *     label:string,
     *     description:string,
     *     query:Closure,
     *     columns:array<int, array{label:string,path:string,numeric:bool}>
     * }>
     */
    private function definitions(Business $business): array
    {
        return array_merge(
            $this->peopleDefinitions($business),
            $this->customerContactDefinitions($business),
            $this->commercialDefinitions($business),
            $this->operationsDefinitions($business),
            $this->assetDefinitions($business),
            $this->workDetailDefinitions($business),
            $this->lineItemDefinitions($business),
            $this->receivingDefinitions($business),
            $this->taxDefinitions($business)
        );
    }

    private function peopleDefinitions(Business $business): array
    {
        return [
            'customers' => [
                'label' => 'Customers',
                'description' => 'Customer identity, billing details and primary contact information.',
                'query' => fn () => Customer::query()->where('business_id', $business->id)
                    ->with('primaryContact')->orderBy('id')->lazy(500),
                'columns' => [
                    $this->column('Customer name', 'name'),
                    $this->column('Legal name', 'legal_name'),
                    $this->column('Registration number', 'registration_number'),
                    $this->column('Tax number', 'tax_number'),
                    $this->column('VAT number', 'vat_number'),
                    $this->column('Billing address', 'billing_address'),
                    $this->column('Primary contact', 'primaryContact.name'),
                    $this->column('Contact phone', 'primaryContact.phone'),
                    $this->column('Contact email', 'primaryContact.email'),
                    $this->column('Notes', 'notes'),
                    $this->column('Created at', 'created_at'),
                ],
            ],
            'events' => [
                'label' => 'Events and jobs',
                'description' => 'The current work register, customer links, dates and lifecycle status.',
                'query' => fn () => Event::query()->where('business_id', $business->id)
                    ->with('customer')->orderBy('id')->lazy(500),
                'columns' => [
                    $this->column('Reference', 'reference'),
                    $this->column('Event name', 'name'),
                    $this->column('Customer', 'customer.name'),
                    $this->column('Event type', 'event_type'),
                    $this->column('Event date', 'event_date'),
                    $this->column('Address', 'event_address'),
                    $this->column('Status', 'status'),
                    $this->column('Notes', 'notes'),
                    $this->column('Created at', 'created_at'),
                ],
            ],
            'suppliers' => [
                'label' => 'Suppliers',
                'description' => 'Supplier contact and reference details.',
                'query' => fn () => Supplier::query()->where('business_id', $business->id)
                    ->orderBy('id')->lazy(500),
                'columns' => [
                    $this->column('Supplier name', 'name'),
                    $this->column('Contact name', 'contact_name'),
                    $this->column('Email', 'email'),
                    $this->column('Phone', 'phone'),
                    $this->column('Notes', 'notes'),
                    $this->column('Created at', 'created_at'),
                ],
            ],
        ];
    }

    private function commercialDefinitions(Business $business): array
    {
        return array_merge(
            $this->quoteInvoiceDefinitions($business),
            $this->paymentExpenseDefinitions($business),
            $this->eventCostDefinitions($business)
        );
    }

    private function quoteInvoiceDefinitions(Business $business): array
    {
        return [
                        'quotes' => [
                            'label' => 'Quotes',
                            'description' => 'Quote status, latest saved version, values and deposit terms.',
                            'query' => fn () => Quote::query()
                                ->whereHas('event', fn ($query) => $query->where('business_id', $business->id))
                                ->with(['event.customer', 'latestVersion'])
                                ->orderBy('id')->lazy(500),
                            'columns' => [
                                $this->column('Quote reference', 'reference'),
                                $this->column('Event reference', 'event.reference'),
                                $this->column('Event name', 'event.name'),
                                $this->column('Customer', 'event.customer.name'),
                                $this->column('Quote status', 'status'),
                                $this->column('Currency', 'currency'),
                                $this->column('Version', 'latestVersion.version', true),
                                $this->column('Version status', 'latestVersion.status'),
                                $this->column('Subtotal', 'latestVersion.subtotal', true),
                                $this->column('Tax', 'latestVersion.tax_total', true),
                                $this->column('Total', 'latestVersion.total', true),
                                $this->column('Deposit percent', 'latestVersion.deposit_percent', true),
                                $this->column('Deposit amount', 'latestVersion.deposit_amount', true),
                                $this->column('Notes', 'latestVersion.notes'),
                            ],
                        ],
                        'invoices' => [
                            'label' => 'Invoices',
                            'description' => 'Issued and paid invoices, customer snapshots, dates and balances before payments.',
                            'query' => fn () => Invoice::query()->where('business_id', $business->id)
                                ->with(['event', 'quote'])->whereIn('status', ['issued', 'paid'])
                                ->orderBy('id')->lazy(500),
                            'columns' => [
                                $this->column('Invoice number', 'number'),
                                $this->column('Event reference', 'event.reference'),
                                $this->column('Event name', 'event.name'),
                                $this->column('Quote reference', 'quote.reference'),
                                $this->column('Customer', 'customer_name'),
                                $this->column('Customer email', 'customer_email'),
                                $this->column('Status', 'status'),
                                $this->column('Currency', 'currency'),
                                $this->column('Subtotal', 'subtotal', true),
                                $this->column('Tax', 'tax_total', true),
                                $this->column('Total', 'total', true),
                                $this->column('Issued at', 'issued_at'),
                                $this->column('Due at', 'due_at'),
                                $this->column('Notes', 'notes'),
                            ],
                        ]
        ];
    }

    private function paymentExpenseDefinitions(Business $business): array
    {
        return [
                        'payments' => [
                            'label' => 'Payments',
                            'description' => 'Deposits and other payments linked to their invoice and event.',
                            'query' => fn () => Payment::query()->where('business_id', $business->id)
                                ->with(['invoice', 'event'])->orderBy('id')->lazy(500),
                            'columns' => [
                                $this->column('Event reference', 'event.reference'),
                                $this->column('Event name', 'event.name'),
                                $this->column('Invoice number', 'invoice.number'),
                                $this->column('Payment type', 'type'),
                                $this->column('Amount', 'amount', true),
                                $this->column('Currency', 'currency'),
                                $this->column('Method', 'method'),
                                $this->column('Reference', 'reference'),
                                $this->column('Paid at', 'paid_at'),
                                $this->column('Notes', 'notes'),
                            ],
                        ],
                        'expenses' => [
                            'label' => 'Finance expenses',
                            'description' => 'Recorded finance expenses, linked suppliers and purchase orders.',
                            'query' => fn () => FinanceExpense::query()->where('business_id', $business->id)
                                ->with(['supplier', 'purchaseOrder', 'event'])->orderBy('id')->lazy(500),
                            'columns' => [
                                $this->column('Expense date', 'expense_date'),
                                $this->column('Event reference', 'event.reference'),
                                $this->column('Event name', 'event.name'),
                                $this->column('Supplier', 'supplier.name'),
                                $this->column('Purchase order', 'purchaseOrder.reference'),
                                $this->column('Description', 'description'),
                                $this->column('Amount', 'amount', true),
                                $this->column('Currency', 'currency'),
                                $this->column('Status', 'status'),
                                $this->column('Reference', 'reference'),
                                $this->column('Notes', 'notes'),
                            ],
                        ]
        ];
    }

    private function eventCostDefinitions(Business $business): array
    {
        return [
                        'event_costs' => [
                            'label' => 'Event costs',
                            'description' => 'Projected and actual cost records kept against individual events.',
                            'query' => fn () => EventCost::query()->where('business_id', $business->id)
                                ->with('event')->orderBy('id')->lazy(500),
                            'columns' => [
                                $this->column('Event reference', 'event.reference'),
                                $this->column('Event name', 'event.name'),
                                $this->column('Category', 'category'),
                                $this->column('Description', 'description'),
                                $this->column('Currency', 'currency'),
                                $this->column('Projected amount', 'projected_amount', true),
                                $this->column('Actual amount', 'actual_amount', true),
                                $this->column('Status', 'status'),
                                $this->column('Notes', 'notes'),
                            ],
                        ]
        ];
    }

    private function operationsDefinitions(Business $business): array
    {
        return array_merge(
            $this->purchaseOrderDefinitions($business),
            $this->stockDefinitions($business),
            $this->travelCostDefinitions($business)
        );
    }

    private function purchaseOrderDefinitions(Business $business): array
    {
        return [
                        'purchase_orders' => [
                            'label' => 'Purchase orders',
                            'description' => 'Supplier orders, event links, dates, currency and order state.',
                            'query' => fn () => PurchaseOrder::query()->where('business_id', $business->id)
                                ->with(['supplier', 'event'])->orderBy('id')->lazy(500),
                            'columns' => [
                                $this->column('Purchase order', 'reference'),
                                $this->column('Supplier', 'supplier.name'),
                                $this->column('Event reference', 'event.reference'),
                                $this->column('Event name', 'event.name'),
                                $this->column('Status', 'status'),
                                $this->column('Currency', 'currency'),
                                $this->column('Total amount', 'total_amount', true),
                                $this->column('Ordered at', 'ordered_at'),
                                $this->column('Expected at', 'expected_at'),
                                $this->column('Notes', 'notes'),
                            ],
                        ],
                        'purchase_order_items' => [
                            'label' => 'Purchase order lines',
                            'description' => 'Ordered and received quantities and line values for each purchase order.',
                            'query' => fn () => PurchaseOrderItem::query()->where('business_id', $business->id)
                                ->with(['purchaseOrder.supplier', 'purchaseOrder.event', 'capability'])
                                ->orderBy('id')->lazy(500),
                            'columns' => [
                                $this->column('Purchase order', 'purchaseOrder.reference'),
                                $this->column('Supplier', 'purchaseOrder.supplier.name'),
                                $this->column('Event reference', 'purchaseOrder.event.reference'),
                                $this->column('Description', 'description'),
                                $this->column('Capability', 'capability.name'),
                                $this->column('Quantity', 'quantity', true),
                                $this->column('Received quantity', 'received_quantity', true),
                                $this->column('Unit', 'unit'),
                                $this->column('Unit price', 'unit_price', true),
                                $this->column('Line total', 'line_total', true),
                            ],
                        ]
        ];
    }

    private function stockDefinitions(Business $business): array
    {
        return [
                        'inventory_items' => [
                            'label' => 'Inventory items',
                            'description' => 'Product identifiers, units, reorder thresholds and on-hand quantity.',
                            'query' => fn () => InventoryItem::query()->where('inventory_items.business_id', $business->id)
                                ->with('capability')
                                ->select('inventory_items.*')
                                ->selectSub(
                                    InventoryMovement::query()
                                        ->selectRaw("COALESCE(SUM(CASE WHEN type IN ('receipt', 'return', 'adjustment_in') THEN quantity ELSE -quantity END), 0)")
                                        ->whereColumn('inventory_item_id', 'inventory_items.id')
                                        ->where('business_id', $business->id),
                                    'export_on_hand'
                                )
                                ->orderBy('inventory_items.id')->lazy(250),
                            'columns' => [
                                $this->column('SKU', 'sku'),
                                $this->column('Item name', 'name'),
                                $this->column('Capability', 'capability.name'),
                                $this->column('Unit', 'unit'),
                                $this->column('Reorder level', 'reorder_level', true),
                                $this->column('On hand', 'export_on_hand', true),
                            ],
                        ],
                        'inventory_movements' => [
                            'label' => 'Stock movements',
                            'description' => 'Receipts, issues and adjustments with their event and purchase references.',
                            'query' => fn () => InventoryMovement::query()->where('business_id', $business->id)
                                ->with(['item', 'event', 'purchaseOrder'])->orderBy('id')->lazy(500),
                            'columns' => [
                                $this->column('Date', 'movement_date'),
                                $this->column('Item SKU', 'item.sku'),
                                $this->column('Item', 'item.name'),
                                $this->column('Movement type', 'type'),
                                $this->column('Quantity', 'quantity', true),
                                $this->column('Unit cost', 'unit_cost', true),
                                $this->column('Event reference', 'event.reference'),
                                $this->column('Purchase order', 'purchaseOrder.reference'),
                                $this->column('Reference', 'reference'),
                                $this->column('Notes', 'notes'),
                            ],
                        ]
        ];
    }

    private function travelCostDefinitions(Business $business): array
    {
        return [
                        'travel_costs' => [
                            'label' => 'Travel calculations',
                            'description' => 'Event-linked distance, fuel estimates and customer travel charges.',
                            'query' => fn () => TravelCost::query()
                                ->whereHas('event', fn ($query) => $query->where('business_id', $business->id))
                                ->with('event')->orderBy('id')->lazy(500),
                            'columns' => [
                                $this->column('Event reference', 'event.reference'),
                                $this->column('Event name', 'event.name'),
                                $this->column('Route', 'route_label'),
                                $this->column('Provider', 'provider'),
                                $this->column('Origin', 'origin'),
                                $this->column('Destination', 'destination'),
                                $this->column('Distance km', 'distance_km', true),
                                $this->column('Total distance km', 'total_distance_km', true),
                                $this->column('Fuel litres', 'fuel_litres', true),
                                $this->column('Fuel cost', 'fuel_cost', true),
                                $this->column('Customer charge', 'customer_charge', true),
                                $this->column('Notes', 'notes'),
                            ],
                        ]
        ];
    }

    private function customerContactDefinitions(Business $business): array
    {
        return [
            'customer_contacts' => [
                'label' => 'Customer contacts',
                'description' => 'Every active contact record, not only the primary contact shown in the customer export.',
                'query' => fn () => CustomerContact::query()
                    ->whereHas('customer', fn ($query) => $query->where('business_id', $business->id))
                    ->with('customer')->orderBy('id')->lazy(500),
                'columns' => [
                    $this->column('Customer', 'customer.name'),
                    $this->column('Contact name', 'name'),
                    $this->column('Label', 'label'),
                    $this->column('Phone', 'phone'),
                    $this->column('Email', 'email'),
                    $this->column('Primary contact', 'is_primary'),
                ],
            ],
        ];
    }

    private function assetDefinitions(Business $business): array
    {
        return [
            'business_capabilities' => [
                'label' => 'Products and services',
                'description' => 'The business catalogue, pricing basis, default rates and service/product descriptions.',
                'query' => fn () => BusinessCapability::query()
                    ->where('business_id', $business->id)->orderBy('id')->lazy(500),
                'columns' => [
                    $this->column('Name', 'name'),
                    $this->column('Category', 'category'),
                    $this->column('Type', 'capability_type'),
                    $this->column('Pricing basis', 'pricing_basis'),
                    $this->column('Default price', 'default_price', true),
                    $this->column('Currency', 'currency'),
                    $this->column('Default unit', 'default_unit'),
                    $this->column('Description', 'description'),
                    $this->column('Active', 'is_active'),
                ],
            ],
            'assets' => [
                'label' => 'Assets and equipment',
                'description' => 'Equipment tags, condition, location, acquisition cost and catalogue links.',
                'query' => fn () => Asset::query()->where('business_id', $business->id)
                    ->with('capability')->orderBy('id')->lazy(500),
                'columns' => [
                    $this->column('Asset tag', 'asset_tag'),
                    $this->column('Asset name', 'name'),
                    $this->column('Capability', 'capability.name'),
                    $this->column('Status', 'status'),
                    $this->column('Condition', 'condition'),
                    $this->column('Location', 'location'),
                    $this->column('Acquired at', 'acquired_at'),
                    $this->column('Purchase cost', 'purchase_cost', true),
                    $this->column('Currency', 'currency'),
                    $this->column('Notes', 'notes'),
                ],
            ],
            'asset_allocations' => [
                'label' => 'Asset allocations',
                'description' => 'Equipment bookings and event allocation history.',
                'query' => fn () => AssetAllocation::query()->where('business_id', $business->id)
                    ->with(['asset', 'event'])->orderBy('id')->lazy(500),
                'columns' => [
                    $this->column('Asset tag', 'asset.asset_tag'),
                    $this->column('Asset name', 'asset.name'),
                    $this->column('Event reference', 'event.reference'),
                    $this->column('Event name', 'event.name'),
                    $this->column('Allocated from', 'allocated_from'),
                    $this->column('Allocated until', 'allocated_until'),
                    $this->column('Status', 'status'),
                    $this->column('Notes', 'notes'),
                ],
            ],
        ];
    }

    private function workDetailDefinitions(Business $business): array
    {
        return [
            'event_requirements' => [
                'label' => 'Event requirements',
                'description' => 'Event product/service requirements, quantities and fulfilment status.',
                'query' => fn () => EventRequirement::query()
                    ->whereHas('event', fn ($query) => $query->where('business_id', $business->id))
                    ->with(['event', 'capability'])->orderBy('id')->lazy(500),
                'columns' => [
                    $this->column('Event reference', 'event.reference'),
                    $this->column('Event name', 'event.name'),
                    $this->column('Capability', 'capability.name'),
                    $this->column('Description', 'description'),
                    $this->column('Category', 'category'),
                    $this->column('Quantity', 'quantity', true),
                    $this->column('Unit', 'unit'),
                    $this->column('Status', 'status'),
                    $this->column('Notes', 'notes'),
                ],
            ],
            'event_preparation' => [
                'label' => 'Event preparation',
                'description' => 'Run-sheet and preparation tasks with due dates, completion state and quantities.',
                'query' => fn () => EventPreparationItem::query()->where('business_id', $business->id)
                    ->with('event')->orderBy('id')->lazy(500),
                'columns' => [
                    $this->column('Event reference', 'event.reference'),
                    $this->column('Event name', 'event.name'),
                    $this->column('Title', 'title'),
                    $this->column('Category', 'category'),
                    $this->column('Quantity', 'quantity', true),
                    $this->column('Unit', 'unit'),
                    $this->column('Status', 'status'),
                    $this->column('Due date', 'due_date'),
                    $this->column('Completed at', 'completed_at'),
                    $this->column('Notes', 'notes'),
                ],
            ],
        ];
    }

    private function lineItemDefinitions(Business $business): array
    {
        return [
            'quote_items' => [
                'label' => 'Quote line items',
                'description' => 'Versioned quote detail, source requirements and product/service pricing.',
                'query' => fn () => QuoteItem::query()
                    ->whereHas('version.quote.event', fn ($query) => $query->where('business_id', $business->id))
                    ->with(['version.quote.event', 'requirement', 'capability'])
                    ->orderBy('id')->lazy(500),
                'columns' => [
                    $this->column('Quote reference', 'version.quote.reference'),
                    $this->column('Quote version', 'version.version', true),
                    $this->column('Event reference', 'version.quote.event.reference'),
                    $this->column('Event name', 'version.quote.event.name'),
                    $this->column('Requirement', 'requirement.description'),
                    $this->column('Capability', 'capability.name'),
                    $this->column('Description', 'description'),
                    $this->column('Quantity', 'quantity', true),
                    $this->column('Unit', 'unit'),
                    $this->column('Unit price', 'unit_price', true),
                    $this->column('Line total', 'line_total', true),
                    $this->column('Pricing basis', 'pricing_basis'),
                ],
            ],
            'invoice_items' => [
                'label' => 'Invoice line items',
                'description' => 'Invoice detail retained with the invoice and quote-item references.',
                'query' => fn () => InvoiceItem::query()
                    ->whereHas('invoice', fn ($query) => $query->where('business_id', $business->id))
                    ->with(['invoice.event', 'quoteItem'])
                    ->orderBy('id')->lazy(500),
                'columns' => [
                    $this->column('Invoice number', 'invoice.number'),
                    $this->column('Event reference', 'invoice.event.reference'),
                    $this->column('Event name', 'invoice.event.name'),
                    $this->column('Quote item ID', 'quote_item_id', true),
                    $this->column('Description', 'description'),
                    $this->column('Quantity', 'quantity', true),
                    $this->column('Unit', 'unit'),
                    $this->column('Unit price', 'unit_price', true),
                    $this->column('Line total', 'line_total', true),
                ],
            ],
        ];
    }

    private function receivingDefinitions(Business $business): array
    {
        return [
            'purchase_order_receipts' => [
                'label' => 'Receiving records',
                'description' => 'Purchase-order receipt and idempotency records for receiving audit trails.',
                'query' => fn () => PurchaseOrderReceipt::query()->where('business_id', $business->id)
                    ->with('purchaseOrder')->orderBy('id')->lazy(500),
                'columns' => [
                    $this->column('Purchase order', 'purchaseOrder.reference'),
                    $this->column('Idempotency key', 'idempotency_key'),
                    $this->column('Received at', 'created_at'),
                ],
            ],
        ];
    }

    private function taxDefinitions(Business $business): array
    {
        return [
            'tax_rates' => [
                'label' => 'Tax rates',
                'description' => 'Business tax codes, treatments, effective dates and configured rates.',
                'query' => fn () => TaxRate::query()->where('business_id', $business->id)
                    ->orderBy('id')->lazy(500),
                'columns' => [
                    $this->column('Name', 'name'),
                    $this->column('Code', 'code'),
                    $this->column('Tax type', 'tax_type'),
                    $this->column('Treatment', 'treatment'),
                    $this->column('Rate', 'rate', true),
                    $this->column('Effective from', 'effective_from'),
                    $this->column('Effective to', 'effective_to'),
                    $this->column('Default', 'is_default'),
                    $this->column('Active', 'is_active'),
                    $this->column('Source reference', 'source_reference'),
                ],
            ],
        ];
    }

    /**
     * @return array{label:string,path:string,numeric:bool}
     */
    private function column(string $label, string $path, bool $numeric = false): array
    {
        return compact('label', 'path', 'numeric');
    }

    private function writeCsv(array $definition): void
    {
        $handle = fopen('php://output', 'wb');

        if ($handle === false) {
            return;
        }

        fwrite($handle, "\xEF\xBB\xBF");
        fputcsv($handle, array_column($definition['columns'], 'label'), ',', '"', '');

        foreach (($definition['query'])() as $record) {
            $values = [];

            foreach ($definition['columns'] as $column) {
                $values[] = $this->safeCell(data_get($record, $column['path']), $column['numeric']);
            }

            fputcsv($handle, $values, ',', '"', '');
        }

        fclose($handle);
    }

    private function safeCell(mixed $value, bool $numeric): string
    {
        if ($value === null) {
            return '';
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d H:i:s');
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        $cell = is_scalar($value) ? (string) $value : (string) json_encode($value);

        if (! $numeric && preg_match('/^[\x00-\x20]*[=+\-@]/', $cell) === 1) {
            return "'".$cell;
        }

        return $cell;
    }
}
