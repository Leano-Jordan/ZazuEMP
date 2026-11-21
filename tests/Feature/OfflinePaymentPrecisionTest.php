<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Event;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Quote;
use App\Models\SyncDevice;
use App\Models\SyncEntityIdentity;
use App\Support\Offline\OfflineDomainMutationHandler;
use App\Support\Offline\SyncEntityIdentityRegistry;
use App\Support\Offline\SyncMutationApplier;
use App\Support\Offline\SyncMutationRecorder;
use App\Support\Offline\SyncMutationRelatedIdentityResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class OfflinePaymentPrecisionTest extends TestCase
{
    use RefreshDatabase;

    public function test_offline_payment_cannot_target_an_invoice_from_another_business(): void
    {
        $business = Business::create([
            'name' => 'Offline Payment Boundary Business',
            'slug' => 'offline-payment-boundary-'.Str::lower(Str::random(8)),
            'status' => 'active',
            'currency' => 'ZAR',
        ]);
        $foreignBusiness = Business::create([
            'name' => 'Foreign Invoice Business',
            'slug' => 'foreign-invoice-'.Str::lower(Str::random(8)),
            'status' => 'active',
            'currency' => 'ZAR',
        ]);
        $device = SyncDevice::create([
            'business_id' => $business->id,
            'installation_id' => (string) Str::uuid(),
        ]);
        $foreignEvent = Event::create([
            'business_id' => $foreignBusiness->id,
            'reference' => 'FOREIGN-OFF-PAYMENT',
            'name' => 'Foreign Payment Event',
            'status' => 'draft',
        ]);
        $foreignInvoice = Invoice::create([
            'business_id' => $foreignBusiness->id,
            'event_id' => $foreignEvent->id,
            'number' => 'INV-FOREIGN-OFFLINE',
            'status' => 'issued',
            'currency' => 'ZAR',
            'subtotal' => '100.00',
            'tax_total' => '0.00',
            'total' => '100.00',
            'tax_label' => 'No tax',
            'tax_treatment' => 'out_of_scope',
            'tax_rate' => '0.00',
            'issued_at' => now()->toDateString(),
        ]);
        $foreignIdentity = app(SyncEntityIdentityRegistry::class)->identify($foreignInvoice, 'invoice');

        $recorder = app(SyncMutationRecorder::class);
        $applier = app(SyncMutationApplier::class);
        $handler = app(OfflineDomainMutationHandler::class);
        $mutationId = (string) Str::uuid();
        $mutation = $recorder->record($device, 'payment', $mutationId, 'create', [
            'local_id' => $mutationId,
            'record' => [
                'invoice_local_id' => $foreignIdentity->entity_uuid,
                'type' => 'payment',
                'amount' => '25.00',
                'method' => 'bank_transfer',
                'paid_at' => now()->toDateString(),
            ],
        ]);

        try {
            $applier->apply($mutation, ['payment' => $handler]);
            $this->fail('A trusted device must not post a payment against another business invoice.');
        } catch (ValidationException) {
            $this->assertSame(0, Payment::query()->where('business_id', $business->id)->count());
            $this->assertSame(0, Payment::query()->where('business_id', $foreignBusiness->id)->count());
            $this->assertSame('pending', $mutation->fresh()->status);
        }
    }

    public function test_offline_payment_rejects_amount_precision_loss_without_posting(): void
    {
        $business = Business::create([
            'name' => 'Offline Payment Precision Business',
            'slug' => 'offline-payment-precision-'.Str::lower(Str::random(8)),
            'status' => 'active',
            'currency' => 'ZAR',
        ]);
        $device = SyncDevice::create([
            'business_id' => $business->id,
            'installation_id' => (string) Str::uuid(),
        ]);
        $event = Event::create([
            'business_id' => $business->id,
            'reference' => 'OFF-PAYMENT-PRECISION',
            'name' => 'Precision Payment Event',
            'status' => 'draft',
        ]);
        $quote = Quote::create([
            'event_id' => $event->id,
            'reference' => 'QUO-OFF-PAYMENT-PRECISION',
            'status' => 'sent',
            'currency' => 'ZAR',
        ]);
        $version = $quote->versions()->create([
            'version' => 1,
            'status' => 'sent',
            'subtotal' => '1000.00',
            'tax_total' => '0.00',
            'total' => '1000.00',
            'deposit_percent' => '25.00',
            'deposit_amount' => '250.00',
            'tax_label' => 'No tax',
            'tax_treatment' => 'out_of_scope',
            'tax_rate' => '0.00',
            'tax_snapshot_at' => now(),
        ]);
        $version->items()->create([
            'description' => 'Offline payment precision service',
            'quantity' => '1.00',
            'unit' => 'service',
            'unit_price' => '1000.00',
            'line_total' => '1000.00',
            'source_snapshot' => ['offline' => false],
        ]);

        $registry = app(SyncEntityIdentityRegistry::class);
        $quoteIdentity = $registry->identify($quote, 'quote');
        $eventIdentity = $registry->identify($event, 'job');
        $handler = app(OfflineDomainMutationHandler::class);
        $applier = app(SyncMutationApplier::class);
        $recorder = app(SyncMutationRecorder::class);

        $acceptanceId = (string) Str::uuid();
        $acceptance = $recorder->record($device, 'quote_acceptance', $acceptanceId, 'create', [
            'local_id' => $acceptanceId,
            'record' => [
                'quote_local_id' => $quoteIdentity->entity_uuid,
                'customer_name' => 'Offline Customer',
            ],
        ]);
        $applier->apply($acceptance, ['quote_acceptance' => $handler]);
        $this->assertContains('job', array_column(app(SyncMutationRelatedIdentityResolver::class)->resolve($acceptance), 'entity_type'));
        $this->assertSame([2, 2], [(int) $quoteIdentity->fresh()->revision, (int) $eventIdentity->fresh()->revision]);
        $this->assertSame('confirmed', $event->fresh()->status);

        $invoiceId = (string) Str::uuid();
        $invoiceMutation = $recorder->record($device, 'invoice', $invoiceId, 'create', [
            'local_id' => $invoiceId,
            'record' => ['quote_local_id' => $quoteIdentity->entity_uuid],
        ]);
        $applier->apply($invoiceMutation, ['invoice' => $handler]);

        $invoice = \App\Models\Invoice::query()
            ->where('business_id', $business->id)
            ->firstOrFail();
        $invoiceIdentity = $registry->identify($invoice, 'invoice');

        $paymentId = (string) Str::uuid();
        $paymentMutation = $recorder->record($device, 'payment', $paymentId, 'create', [
            'local_id' => $paymentId,
            'record' => [
                'invoice_local_id' => $invoiceIdentity->entity_uuid,
                'type' => 'deposit',
                'amount' => '250.005',
                'method' => 'bank_transfer',
                'paid_at' => now()->toDateString(),
            ],
        ]);

        try {
            $applier->apply($paymentMutation, ['payment' => $handler]);
            $this->fail('Offline payments must reject amounts with more than two decimal places.');
        } catch (ValidationException) {
            $this->assertSame(0, Payment::query()->where('business_id', $business->id)->count());
            $this->assertSame('issued', $invoice->fresh()->status);
            $this->assertSame('0.00', $invoice->fresh()->paid_amount);
        }
    }

    public function test_offline_payment_updates_invoice_totals_and_returns_its_related_revision(): void
    {
        $business = Business::create([
            'name' => 'Offline Payment Reconciliation Business',
            'slug' => 'offline-payment-reconciliation-'.Str::lower(Str::random(8)),
            'status' => 'active',
            'currency' => 'ZAR',
        ]);
        $device = SyncDevice::create([
            'business_id' => $business->id,
            'installation_id' => (string) Str::uuid(),
        ]);
        $event = Event::create([
            'business_id' => $business->id,
            'reference' => 'OFF-PAYMENT-RECONCILIATION',
            'name' => 'Payment Reconciliation Work',
            'status' => 'confirmed',
        ]);
        $invoice = Invoice::create([
            'business_id' => $business->id,
            'event_id' => $event->id,
            'number' => 'INV-OFFLINE-RECONCILIATION',
            'status' => 'issued',
            'currency' => 'ZAR',
            'subtotal' => '100.00',
            'tax_total' => '0.00',
            'total' => '100.00',
            'tax_label' => 'No tax',
            'tax_treatment' => 'out_of_scope',
            'tax_rate' => '0.00',
            'issued_at' => now()->toDateString(),
        ]);

        $invoiceIdentity = app(SyncEntityIdentityRegistry::class)->identify($invoice, 'invoice');
        $mutationId = (string) Str::uuid();
        $mutation = app(SyncMutationRecorder::class)->record(
            $device,
            'payment',
            $mutationId,
            'create',
            [
                'local_id' => $mutationId,
                'record' => [
                    'invoice_local_id' => $invoiceIdentity->entity_uuid,
                    'type' => 'payment',
                    'amount' => '25.00',
                    'method' => 'bank_transfer',
                    'paid_at' => now()->toDateString(),
                ],
            ],
        );

        $handler = app(OfflineDomainMutationHandler::class);
        $applier = app(SyncMutationApplier::class);
        $applied = $applier->apply($mutation, ['payment' => $handler]);

        $payment = Payment::query()
            ->where('business_id', $business->id)
            ->where('idempotency_key', $mutationId)
            ->firstOrFail();
        $reconciledInvoice = $invoice->fresh();

        $this->assertSame('applied', $applied->status);
        $this->assertSame('25.00', $payment->amount);
        $this->assertSame('issued', $reconciledInvoice->status);
        $this->assertSame('25.00', $reconciledInvoice->paid_amount);
        $this->assertSame('75.00', $reconciledInvoice->balance);
        $this->assertSame(2, (int) $invoiceIdentity->fresh()->revision);
        $this->assertSame([
            [
                'entity_type' => 'invoice',
                'local_id' => $invoiceIdentity->entity_uuid,
                'server_id' => (int) $invoice->id,
                'revision' => 2,
            ],
        ], app(SyncMutationRelatedIdentityResolver::class)->resolve($applied));

        // Replaying an already-applied mutation must not post a second payment
        // or advance the parent invoice revision again.
        $applier->apply($applied, ['payment' => $handler]);

        $this->assertSame(1, Payment::query()->where('business_id', $business->id)->count());
        $this->assertSame('25.00', $invoice->fresh()->paid_amount);
        $this->assertSame(2, (int) $invoiceIdentity->fresh()->revision);
    }


}
