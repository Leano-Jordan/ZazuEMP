<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\Payment;
use App\Support\CurrentBusiness;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Tests\TestCase;

class InvoiceCustomerShareTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->signInAsOwner();
    }

    public function test_issued_invoice_has_a_whatsapp_handoff_and_signed_read_only_customer_view(): void
    {
        $invoice = $this->makeInvoice('issued');

        $ownerView = $this->get(route('finance.invoices.show', $invoice));
        $ownerView->assertOk()
            ->assertSee('Share via WhatsApp')
            ->assertSee('https://wa.me/?text=', false)
            ->assertSee('Print / Save as PDF');

        $customerUrl = URL::temporarySignedRoute(
            'finance.invoices.public',
            now()->addHour(),
            ['invoice' => $invoice]
        );

        $customerView = $this->get($customerUrl);
        $customerView->assertOk()
            ->assertSee('INV-CUSTOMER-SHARE-001')
            ->assertSee('Public invoice service')
            ->assertSee('ZAR 1150.00')
            ->assertSee('Print / Save as PDF')
            ->assertHeader('Cache-Control', 'private, no-store, no-cache, must-revalidate')
            ->assertHeader('Referrer-Policy', 'no-referrer')
            ->assertDontSee('Private internal note')
            ->assertDontSee('Payment history')
            ->assertDontSee('Record payment');
    }

    public function test_invoice_customer_view_rejects_unsigned_expired_and_void_invoice_links(): void
    {
        $invoice = $this->makeInvoice('issued');

        $this->get(route('finance.invoices.public', $invoice))
            ->assertForbidden();

        $expiredUrl = URL::temporarySignedRoute(
            'finance.invoices.public',
            now()->subMinute(),
            ['invoice' => $invoice]
        );
        $this->get($expiredUrl)->assertForbidden();

        $voidInvoice = $this->makeInvoice('void', 'INV-CUSTOMER-SHARE-VOID');
        $voidUrl = URL::temporarySignedRoute(
            'finance.invoices.public',
            now()->addHour(),
            ['invoice' => $voidInvoice]
        );
        $this->get($voidUrl)->assertNotFound();
    }


    public function test_invoice_payment_has_a_signed_customer_receipt_and_whatsapp_handoff(): void
    {
        $invoice = $this->makeInvoice('issued');

        $payment = Payment::create([
            'business_id' => $invoice->business_id,
            'invoice_id' => $invoice->id,
            'type' => 'deposit',
            'idempotency_key' => (string) Str::uuid(),
            'amount' => '250.00',
            'currency' => 'ZAR',
            'method' => 'cash',
            'reference' => 'CASH-REF-001',
            'paid_at' => now()->toDateString(),
            'notes' => 'Private payment note',
        ]);

        $ownerView = $this->get(route('finance.invoices.show', $invoice));
        $ownerView->assertOk()
            ->assertSee('View receipt')
            ->assertSee('Share receipt')
            ->assertSee('https://wa.me/?text=', false);

        $receiptUrl = URL::temporarySignedRoute(
            'finance.payments.receipt',
            now()->addHour(),
            ['payment' => $payment]
        );

        $receipt = $this->get($receiptUrl);
        $receipt->assertOk()
            ->assertSee('Payment receipt')
            ->assertSee('INV-CUSTOMER-SHARE-001')
            ->assertSee('Public Invoice Customer')
            ->assertSee('Deposit received')
            ->assertSee('ZAR 250.00')
            ->assertSee('CASH-REF-001')
            ->assertSee('Print / Save as PDF')
            ->assertHeader('Cache-Control', 'private, no-store, no-cache, must-revalidate')
            ->assertHeader('Referrer-Policy', 'no-referrer')
            ->assertDontSee('Private payment note');
    }

    private function makeInvoice(string $status, string $number = 'INV-CUSTOMER-SHARE-001'): Invoice
    {
        $businessId = app(CurrentBusiness::class)->id(auth()->user());

        $invoice = Invoice::create([
            'business_id' => $businessId,
            'number' => $number,
            'idempotency_key' => (string) Str::uuid(),
            'business_legal_name' => 'Zazu Customer Share Test Business',
            'business_trading_name' => 'Customer Share Catering',
            'business_address' => '12 Market Street',
            'business_email' => 'accounts@example.test',
            'business_phone' => '0100000000',
            'customer_name' => 'Public Invoice Customer',
            'customer_email' => 'customer@example.test',
            'status' => $status,
            'currency' => 'ZAR',
            'subtotal' => '1000.00',
            'tax_total' => '150.00',
            'total' => '1150.00',
            'issued_at' => now()->toDateString(),
            'due_at' => now()->addDays(7)->toDateString(),
            'notes' => 'Private internal note',
        ]);

        $invoice->items()->create([
            'description' => 'Public invoice service',
            'quantity' => '1.00',
            'unit' => 'event',
            'unit_price' => '1000.00',
            'line_total' => '1000.00',
        ]);

        return $invoice;
    }
}
