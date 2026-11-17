<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Tests\TestCase;

class DashboardPaymentsMetricTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_monthly_payments_are_scoped_to_business_month_and_primary_currency(): void
    {
        Carbon::setTestNow('2026-11-17 12:00:00');

        try {
            $business = Business::create([
                'name' => 'Dashboard Payments Business',
                'slug' => 'dashboard-payments-'.Str::lower(Str::random(8)),
                'status' => 'active',
                'currency' => 'ZAR',
            ]);
            $user = $this->signInAsOwner($business);

            Payment::create([
                'business_id' => $business->id,
                'amount' => '3450.00',
                'currency' => 'ZAR',
                'method' => 'bank_transfer',
                'paid_at' => '2026-11-02',
            ]);
            Payment::create([
                'business_id' => $business->id,
                'amount' => '8050.00',
                'currency' => 'ZAR',
                'method' => 'cash',
                'paid_at' => '2026-11-16',
            ]);

            // Do not mix currencies, older receipts or another business into this KPI.
            Payment::create([
                'business_id' => $business->id,
                'amount' => '900.00',
                'currency' => 'USD',
                'method' => 'bank_transfer',
                'paid_at' => '2026-11-16',
            ]);
            Payment::create([
                'business_id' => $business->id,
                'amount' => '5000.00',
                'currency' => 'ZAR',
                'method' => 'bank_transfer',
                'paid_at' => '2026-10-31',
            ]);

            $foreignBusiness = Business::create([
                'name' => 'Foreign Dashboard Business',
                'slug' => 'foreign-dashboard-'.Str::lower(Str::random(8)),
                'status' => 'active',
                'currency' => 'ZAR',
            ]);
            Payment::create([
                'business_id' => $foreignBusiness->id,
                'amount' => '7000.00',
                'currency' => 'ZAR',
                'method' => 'bank_transfer',
                'paid_at' => '2026-11-12',
            ]);

            $this->actingAs($user)
                ->withSession(['zazu_business_id' => $business->id])
                ->get(route('dashboard'))
                ->assertOk()
                ->assertViewHas('metrics', fn (array $metrics): bool =>
                    ($metrics['payments_received_this_month'] ?? null) === '11,500.00'
                )
                ->assertSee('Payments received (this month)')
                ->assertSee('ZAR 11,500.00');
        } finally {
            Carbon::setTestNow();
        }
    }
}
