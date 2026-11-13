<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Customer;
use App\Models\Event;
use App\Models\EventPreparationItem;
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
