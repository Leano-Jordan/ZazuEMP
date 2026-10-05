<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Event;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Quote;
use App\Models\User;
use Database\Seeders\DemoScenarioSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class CommercialReconciliationTest extends TestCase
{
    use RefreshDatabase;

    public function test_populated_quote_acceptance_invoice_deposit_and_final_payment_reconcile(): void
    {
        $this->seed(DemoScenarioSeeder::class);

        $user = User::query()->where('email', 'demo@zazu.local')->firstOrFail();
        $business = $user->businesses()->where('slug', 'zazu-demo-catering')->firstOrFail();
        $event = Event::query()->where('business_id', $business->id)->where('reference', 'ZAZU-DEMO-001')->firstOrFail();
        $quote = Quote::query()->where('event_id', $event->id)->where('reference', 'QUO-ZAZU-DEMO-001')->firstOrFail();

        Invoice::query()->where('business_id', $business->id)->where('event_id', $event->id)->delete();
        Payment::query()->where('business_id', $business->id)->where('event_id', $event->id)->delete();

        $event->update(['status' => 'confirmed']);
        $quote->update(['status' => 'sent']);
        $quote->latestVersion()->update(['status' => 'sent']);

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

        $quote->refresh();
        $quote->load('latestVersion');
        $event->refresh();

        $this->assertSame('accepted', $quote->status);
        $this->assertSame('accepted', $quote->latestVersion->status);
        $this->assertSame('confirmed', $event->status);

        $this->assertDatabaseHas('audit_logs', [
            'business_id' => $business->id,
            'action' => 'quote.customer.accepted',
            'subject_id' => $quote->id,
        ]);

        $this->actingAs($user)
            ->withSession(['zazu_business_id' => $business->id])
            ->post(route('finance.invoices.store'), [
                'quote_id' => $quote->id,
                'idempotency_key' => '11111111-1111-4111-8111-111111111111',
                'issued_at' => now()->toDateString(),
                'due_at' => now()->addDays(7)->toDateString(),
            ])
            ->assertRedirect();

        $invoice = Invoice::query()->where('quote_id', $quote->id)->firstOrFail();

        $this->assertSame('10000.00', (string) $invoice->subtotal);
        $this->assertSame('1500.00', (string) $invoice->tax_total);
        $this->assertSame('11500.00', (string) $invoice->total);
        $this->assertCount(3, $invoice->items()->get());

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

        $this->post(route('finance.payments.store'), [
            'invoice_id' => $invoice->id,
            'type' => 'payment',
            'amount' => '8050.00',
            'method' => 'bank_transfer',
            'reference' => 'FINAL-COMMERCIAL-01',
            'paid_at' => now()->toDateString(),
            'idempotency_key' => '33333333-3333-4333-8333-333333333333',
        ])->assertRedirect();

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
