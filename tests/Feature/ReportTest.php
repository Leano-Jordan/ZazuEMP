<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Customer;
use App\Models\Event;
use App\Models\EventPreparationItem;
use App\Models\EventCost;
use App\Models\Invoice;
use App\Services\EventProfitabilityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_operational_report_metrics_are_scoped_to_the_active_business(): void
    {
        $business = $this->makeBusiness('Report Primary');
        $foreignBusiness = $this->makeBusiness('Report Foreign');

        Customer::create([
            'business_id' => $foreignBusiness->id,
            'name' => 'Foreign report customer',
        ]);
        $foreignJob = Event::create([
            'business_id' => $foreignBusiness->id,
            'reference' => 'REPORT-FOREIGN-'.Str::upper(Str::random(5)),
            'name' => 'Foreign report job',
            'status' => 'confirmed',
            'event_date' => now()->addDays(7)->toDateString(),
        ]);
        EventPreparationItem::create([
            'business_id' => $foreignBusiness->id,
            'event_id' => $foreignJob->id,
            'title' => 'Foreign open report preparation',
            'category' => 'Other',
            'quantity' => '1.00',
            'unit' => 'item',
            'status' => 'open',
            'due_date' => now()->addDays(2)->toDateString(),
        ]);

        $user = $this->signInAsOwner($business);

        Customer::create([
            'business_id' => $business->id,
            'name' => 'Primary report customer',
        ]);
        $primaryJob = Event::create([
            'business_id' => $business->id,
            'reference' => 'REPORT-PRIMARY-'.Str::upper(Str::random(5)),
            'name' => 'Primary report job',
            'status' => 'confirmed',
            'event_date' => now()->addDays(7)->toDateString(),
        ]);
        EventPreparationItem::create([
            'business_id' => $business->id,
            'event_id' => $primaryJob->id,
            'title' => 'Primary open report preparation',
            'category' => 'Other',
            'quantity' => '1.00',
            'unit' => 'item',
            'status' => 'open',
            'due_date' => now()->addDays(2)->toDateString(),
        ]);

        $this->actingAs($user)
            ->get(route('reports.index'))
            ->assertOk()
            ->assertViewHas('metrics', static fn (array $metrics): bool =>
                $metrics['active_jobs'] === 1
                && $metrics['upcoming_jobs'] === 1
                && $metrics['customers'] === 1
                && $metrics['outstanding_preparation'] === 1
            );
    }


    public function test_event_contribution_excludes_output_vat_and_discloses_unadjusted_cost_tax(): void
    {
        $business = $this->makeBusiness('VAT Report Business');
        $user = $this->signInAsOwner($business);

        $event = Event::create([
            'business_id' => $business->id,
            'reference' => 'REPORT-VAT-001',
            'name' => 'VAT Contribution Event',
            'status' => 'confirmed',
            'event_date' => now()->addDays(7)->toDateString(),
        ]);

        Invoice::create([
            'business_id' => $business->id,
            'event_id' => $event->id,
            'number' => 'INV-REPORT-VAT-001',
            'idempotency_key' => (string) Str::uuid(),
            'status' => 'paid',
            'currency' => 'ZAR',
            'subtotal' => '10000.00',
            'tax_total' => '1500.00',
            'total' => '11500.00',
            'tax_code' => 'VAT15',
            'tax_label' => 'VAT',
            'tax_treatment' => 'standard',
            'tax_rate' => '15.00',
        ]);

        EventCost::create([
            'business_id' => $business->id,
            'event_id' => $event->id,
            'category' => 'Supplies',
            'description' => 'Recorded event supplies',
            'currency' => 'ZAR',
            'projected_amount' => '4000.00',
            'actual_amount' => '4000.00',
            'status' => 'paid',
        ]);

        $result = app(EventProfitabilityService::class)
            ->forBusiness($business)
            ->firstWhere('event_id', $event->id);

        $this->assertNotNull($result);
        $this->assertSame(1000000, $result['net_sales_cents']);
        $this->assertSame(400000, $result['actual_cost_cents']);
        $this->assertSame(600000, $result['indicative_result_cents']);
        $this->assertSame(60.0, $result['indicative_margin_percentage']);

        $this->actingAs($user)
            ->get(route('reports.index'))
            ->assertOk()
            ->assertSee('Event contribution')
            ->assertSee('Net invoiced value excluding output VAT')
            ->assertSee('operational estimate rather than an accounting profit figure');
    }

    private function makeBusiness(string $name): Business
    {
        return Business::create([
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::lower(Str::random(8)),
            'status' => 'active',
            'currency' => 'ZAR',
        ]);
    }
}
