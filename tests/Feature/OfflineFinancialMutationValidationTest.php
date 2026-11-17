<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Event;
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
