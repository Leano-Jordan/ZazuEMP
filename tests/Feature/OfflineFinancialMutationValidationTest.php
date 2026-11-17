<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Event;
use App\Models\Invoice;
use App\Models\Quote;
use App\Models\FinanceExpense;
use App\Models\Payment;
use App\Models\SyncDevice;
use App\Support\Offline\OfflineDomainMutationHandler;
use App\Support\Offline\SyncEntityIdentityRegistry;
use App\Support\Offline\SyncMutationApplier;
use App\Support\Offline\SyncMutationRecorder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class OfflineFinancialMutationValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_offline_invoice_rejects_impossible_issue_and_due_dates(): void
    {
        $business = Business::create([
            'name' => 'Offline Invoice Date Validation',
            'slug' => 'offline-invoice-date-'.Str::lower(Str::random(8)),
            'status' => 'active',
            'currency' => 'ZAR',
        ]);
        $device = SyncDevice::create([
            'business_id' => $business->id,
            'installation_id' => (string) Str::uuid(),
        ]);
        $event = Event::create([
            'business_id' => $business->id,
            'reference' => 'OFF-INVOICE-DATE',
            'name' => 'Offline invoice date validation',
            'status' => 'confirmed',
        ]);
        $quote = Quote::create([
            'event_id' => $event->id,
            'reference' => 'QUO-OFF-INVOICE-DATE',
            'status' => 'accepted',
            'currency' => 'ZAR',
        ]);
        $version = $quote->versions()->create([
            'version' => 1,
            'status' => 'accepted',
            'subtotal' => '100.00',
            'tax_total' => '0.00',
            'total' => '100.00',
            'deposit_percent' => '0.00',
            'deposit_amount' => '0.00',
            'tax_label' => 'No tax',
            'tax_treatment' => 'out_of_scope',
            'tax_rate' => '0.00',
            'tax_snapshot_at' => now(),
        ]);
        $version->items()->create([
            'description' => 'Invoice date validation item',
            'quantity' => '1.00',
            'unit' => 'service',
            'unit_price' => '100.00',
            'line_total' => '100.00',
            'source_snapshot' => ['offline' => true],
        ]);
        $quoteIdentity = app(SyncEntityIdentityRegistry::class)->identify($quote, 'quote');
        $recorder = app(SyncMutationRecorder::class);
        $applier = app(SyncMutationApplier::class);
        $handler = app(OfflineDomainMutationHandler::class);

        foreach (['issued_at', 'due_at'] as $dateField) {
            $localId = (string) Str::uuid();
            $mutation = $recorder->record(
                $device,
                'invoice',
                $localId,
                'create',
                [
                    'local_id' => $localId,
                    'record' => [
                        'quote_local_id' => $quoteIdentity->entity_uuid,
                        $dateField => '2026-02-31',
                    ],
                ],
            );

            try {
                $applier->apply($mutation, ['invoice' => $handler]);
                $this->fail('Offline invoices must reject impossible calendar dates.');
            } catch (ValidationException) {
                $this->assertSame('pending', $mutation->fresh()->status);
            }
        }

        $this->assertSame(0, Invoice::query()->where('business_id', $business->id)->count());
    }

    public function test_offline_financial_mutations_reject_invalid_enums_and_dates(): void
    {
        $business = Business::create([
            'name' => 'Offline Financial Validation Business',
            'slug' => 'offline-financial-validation-'.Str::lower(Str::random(8)),
            'status' => 'active',
            'currency' => 'ZAR',
        ]);
        $device = SyncDevice::create([
            'business_id' => $business->id,
            'installation_id' => (string) Str::uuid(),
        ]);
        $event = Event::create([
            'business_id' => $business->id,
            'reference' => 'OFF-FIN-VALIDATION',
            'name' => 'Offline finance validation',
            'status' => 'draft',
        ]);
        $eventIdentity = app(SyncEntityIdentityRegistry::class)->identify($event, 'job');

        $cases = [
            [
                'entity' => 'quote',
                'record' => [
                    'event_local_id' => $eventIdentity->entity_uuid,
                    'currency' => 'XXX',
                ],
            ],
            [
                'entity' => 'payment',
                'record' => [
                    'type' => 'refund',
                    'amount' => '10.00',
                    'method' => 'cash',
                ],
            ],
            [
                'entity' => 'payment',
                'record' => [
                    'type' => 'payment',
                    'amount' => '10.00',
                    'method' => 'crypto',
                ],
            ],
            [
                'entity' => 'expense',
                'record' => [
                    'description' => 'Invalid offline expense',
                    'amount' => '10.00',
                    'status' => 'approved',
                ],
            ],
            [
                'entity' => 'expense',
                'record' => [
                    'description' => 'Invalid offline expense date',
                    'amount' => '10.00',
                    'status' => 'unpaid',
                    'expense_date' => 'not-a-date',
                ],
            ],
            [
                'entity' => 'expense',
                'record' => [
                    'description' => 'Impossible offline expense date',
                    'amount' => '10.00',
                    'status' => 'unpaid',
                    'expense_date' => '2026-02-31',
                ],
            ],
        ];

        $recorder = app(SyncMutationRecorder::class);
        $applier = app(SyncMutationApplier::class);
        $handler = app(OfflineDomainMutationHandler::class);

        foreach ($cases as $case) {
            $localId = (string) Str::uuid();
            $mutation = $recorder->record(
                $device,
                $case['entity'],
                $localId,
                'create',
                ['local_id' => $localId, 'record' => $case['record']],
            );

            try {
                $applier->apply($mutation, [$case['entity'] => $handler]);
                $this->fail('Offline financial mutations must reject values outside the online validation contract.');
            } catch (ValidationException) {
                $this->assertSame('pending', $mutation->fresh()->status);
            }
        }

        $this->assertSame(0, Payment::query()->where('business_id', $business->id)->count());
        $this->assertSame(0, FinanceExpense::query()->where('business_id', $business->id)->count());
    }
}
