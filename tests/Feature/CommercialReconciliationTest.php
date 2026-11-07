<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Event;
use App\Models\EventCost;
use App\Models\InventoryMovement;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PurchaseOrder;
use App\Models\Quote;
use App\Models\User;
use App\Services\EventProfitabilityService;
use Database\Seeders\DemoScenarioSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Tests\TestCase;

class CommercialReconciliationTest extends TestCase
{
    use RefreshDatabase;

    public function test_populated_quote_acceptance_invoice_deposit_and_final_payment_reconcile(): void
    {
        [$user, $business, $event, $quote] = $this->prepareCommercialScenario();

        $this->acceptQuote($business, $quote, $event);
        $invoice = $this->createInvoice($user, $business, $quote);
        $this->recordDeposit($business, $invoice);
        $this->recordFinalPayment($business, $invoice);

        $this->assertPaidInvoice($business, $invoice);
    }

    public function test_populated_commercial_and_operations_spine_reconciles_event_profit(): void
    {
        [$user, $business, $event, $quote] = $this->prepareCommercialScenario();

        $this->acceptQuote($business, $quote, $event);
        $invoice = $this->createInvoice($user, $business, $quote);
        $this->recordDeposit($business, $invoice);
        $this->recordFinalPayment($business, $invoice);
        $this->assertPaidInvoice($business, $invoice);

        $this->receivePurchaseOrderAndRecordActualCost($business, $event);
        $this->completeEvent($event);

        Invoice::create([
            'business_id' => $business->id,
            'event_id' => $event->id,
            'number' => 'INV-VOID-PROFIT-001',
            'status' => 'void',
            'currency' => 'ZAR',
            'subtotal' => '500.00',
            'tax_total' => '0.00',
            'total' => '500.00',
            'issued_at' => now()->toDateString(),
        ]);
        EventCost::create([
            'business_id' => $business->id,
            'event_id' => $event->id,
            'category' => 'Food',
            'description' => 'Unincurred planned purchase',
            'currency' => 'ZAR',
            'projected_amount' => '999.00',
            'actual_amount' => null,
            'status' => 'planned',
        ]);
        EventCost::create([
            'business_id' => $business->id,
            'event_id' => $event->id,
            'category' => 'Food',
            'description' => 'Cancelled purchase',
            'currency' => 'ZAR',
            'projected_amount' => '500.00',
            'actual_amount' => '500.00',
            'status' => 'cancelled',
        ]);

        $profit = app(EventProfitabilityService::class)
            ->forBusiness($business)
            ->first(fn (array $row): bool => $row['event_id'] === $event->id && $row['currency'] === 'ZAR');

        $this->assertNotNull($profit);
        $this->assertSame(1150000, $profit['invoiced_cents']);
        $this->assertSame(280000, $profit['actual_cost_cents']);
        $this->assertSame(870000, $profit['profit_cents']);
        $this->assertSame(75.65, $profit['margin_percentage']);
        $this->assertCount(
            1,
            app(EventProfitabilityService::class)->forBusiness($business)
                ->filter(fn (array $row): bool => $row['event_id'] === $event->id)
        );

        $this->get(route('reports.index'))
            ->assertOk()
            ->assertSee('Event profitability')
            ->assertSee('ZAR 8,700.00')
            ->assertSee('75.65%')
            ->assertViewHas('financeTotalsByCurrency', fn ($totals): bool =>
                $totals->get('ZAR')['invoiced_cents'] === 1150000
            );
    }

    private function prepareCommercialScenario(): array
    {
        $this->seed(DemoScenarioSeeder::class);

        $user = User::query()->where('email', 'demo@zazu.local')->firstOrFail();
        $business = $user->businesses()->where('slug', 'zazu-demo-catering')->firstOrFail();
        $event = Event::query()->where('business_id', $business->id)->where('reference', 'ZAZU-DEMO-001')->firstOrFail();
        $quote = Quote::query()->where('event_id', $event->id)->where('reference', 'QUO-ZAZU-DEMO-001')->firstOrFail();

        Payment::query()->where('business_id', $business->id)->where('event_id', $event->id)->delete();
        Invoice::query()->where('business_id', $business->id)->where('event_id', $event->id)->each(function (Invoice $invoice): void {
            $invoice->items()->delete();
            $invoice->delete();
        });

        $event->update(['status' => 'confirmed']);
        $quote->update(['status' => 'sent']);
        $quote->latestVersion()->update(['status' => 'sent']);

        return [$user, $business, $event, $quote];
    }

    private function receivePurchaseOrderAndRecordActualCost($business, Event $event): void
    {
        $order = PurchaseOrder::query()
            ->where('business_id', $business->id)
            ->where('reference', 'PO-ZAZU-DEMO-001')
            ->with('items')
            ->firstOrFail();
        $item = $order->items->firstOrFail();

        $order->update(['status' => 'sent']);
        $item->update(['received_quantity' => '0.00']);

        $this->patch(route('purchasing.status', $order), ['status' => 'ordered'])->assertRedirect();
        $this->post(route('purchasing.receive', $order), [
            'idempotency_key' => (string) Str::uuid(),
            'received_quantity' => [$item->id => '1.00'],
        ])->assertRedirect();

        $receipt = InventoryMovement::query()
            ->where('business_id', $business->id)
            ->where('purchase_order_id', $order->id)
            ->where('purchase_order_item_id', $item->id)
            ->where('type', 'receipt')
            ->latest('id')
            ->firstOrFail();

        $this->assertSame($event->id, $receipt->event_id);
        $this->assertSame('2800.00', (string) $receipt->unit_cost);
        $this->assertSame('received', $order->fresh()->status);

        EventCost::query()
            ->where('business_id', $business->id)
            ->where('event_id', $event->id)
            ->delete();

        $this->post(route('work.costs.store', $event), [
            'category' => 'Food',
            'description' => 'Fresh catering supplies received',
            'currency' => 'ZAR',
            'projected_amount' => '2800.00',
            'actual_amount' => '2800.00',
            'status' => 'incurred',
            'notes' => 'Integrated commercial reconciliation test.',
        ])->assertRedirect();
    }

    private function completeEvent(Event $event): void
    {
        $payload = [
            'customer_id' => $event->customer_id,
            'name' => $event->name,
            'event_type' => $event->event_type,
            'event_date' => $event->event_date?->toDateString(),
            'event_address' => $event->event_address,
            'notes' => $event->notes,
        ];

        $this->put(route('work.update', $event), $payload + ['status' => 'in_progress'])->assertRedirect();
        $this->put(route('work.update', $event), $payload + ['status' => 'completed'])->assertRedirect();

        $this->assertSame('completed', $event->fresh()->status);
    }

    private function acceptQuote($business, $quote, $event): void
    {
        $this->withSession(['zazu_business_id' => $business->id]);

        $acceptUrl = URL::temporarySignedRoute(
            'quotes.public.accept',
            now()->addMinutes(10),
            ['quote' => $quote->id]
        );

        $this->post($acceptUrl, [
            'customer_name' => 'Naledi Mokoena',
            'acceptance' => '1',
        ])->assertRedirect();

        $quote->refresh()->load('latestVersion');
        $event->refresh();

        $this->assertSame('accepted', $quote->status);
        $this->assertSame('accepted', $quote->latestVersion->status);
        $this->assertSame('confirmed', $event->status);

        $this->assertDatabaseHas('audit_logs', [
            'business_id' => $business->id,
            'action' => 'quote.customer.accepted',
            'subject_id' => $quote->id,
        ]);
    }

    private function createInvoice($user, $business, $quote): Invoice
    {
        $invoicePayload = [
            'quote_id' => $quote->id,
            'idempotency_key' => '11111111-1111-4111-8111-111111111111',
            'issued_at' => now()->toDateString(),
            'due_at' => now()->addDays(7)->toDateString(),
        ];

        $this->actingAs($user)
            ->withSession(['zazu_business_id' => $business->id])
            ->post(route('finance.invoices.store'), $invoicePayload)
            ->assertRedirect();

        // The quote version is now invoiced, but retrying the original request
        // with the same key must return that invoice instead of failing early.
        $this->post(route('finance.invoices.store'), $invoicePayload)->assertRedirect();
        $this->assertDatabaseCount('invoices', 1);

        $invoice = Invoice::query()->where('quote_id', $quote->id)->firstOrFail();

        $this->assertSame('10000.00', (string) $invoice->subtotal);
        $this->assertSame('1500.00', (string) $invoice->tax_total);
        $this->assertSame('11500.00', (string) $invoice->total);
        $this->assertCount(3, $invoice->items()->get());

        return $invoice;
    }

    private function recordDeposit($business, $invoice): void
    {
        $this->post(route('finance.payments.store'), [
            'invoice_id' => $invoice->id,
            'type' => 'deposit',
            'amount' => '3450.00',
            'method' => 'bank_transfer',
            'reference' => 'DEP-COMMERCIAL-01',
            'paid_at' => now()->toDateString(),
            'idempotency_key' => '22222222-2222-4222-8222-222222222222',
        ])->assertRedirect();

        $invoice->refresh();
        $this->assertSame('issued', $invoice->status);
        $this->assertSame('8050.00', number_format((float) $invoice->total - (float) $invoice->payments()->sum('amount'), 2, '.', ''));
    }

    private function recordFinalPayment($business, $invoice): void
    {
        $this->post(route('finance.payments.store'), [
            'invoice_id' => $invoice->id,
            'type' => 'payment',
            'amount' => '8050.00',
            'method' => 'bank_transfer',
            'reference' => 'FINAL-COMMERCIAL-01',
            'paid_at' => now()->toDateString(),
            'idempotency_key' => '33333333-3333-4333-8333-333333333333',
        ])->assertRedirect();
    }

    private function assertPaidInvoice($business, $invoice): void
    {
        $invoice->refresh()->load('payments');

        $this->assertSame('paid', $invoice->status);
        $this->assertSame('11500.00', number_format((float) $invoice->payments->sum('amount'), 2, '.', ''));
        $this->assertSame(2, $invoice->payments->count());

        $this->assertDatabaseHas('audit_logs', [
            'business_id' => $business->id,
            'action' => 'finance.invoice.created',
            'subject_id' => $invoice->id,
        ]);
        $this->assertSame(
            2,
            AuditLog::query()
                ->where('business_id', $business->id)
                ->where('action', 'finance.payment.recorded')
                ->whereIn('subject_id', $invoice->payments->pluck('id'))
                ->count()
        );
    }

}
