<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Customer;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class InterfaceRegressionTest extends TestCase
{
    use RefreshDatabase;

    private function workspace(): array
    {
        $business = Business::create([
            'name' => 'Regression Business',
            'slug' => 'regression-' . Str::lower(Str::random(6)),
            'status' => 'active',
            'currency' => 'ZAR',
        ]);

        $user = User::factory()->create();
        $business->users()->attach($user, ['role' => 'owner']);

        return [$business, $user];
    }

    public function test_calendar_page_loads_and_shows_scheduled_work(): void
    {
        [$business, $user] = $this->workspace();

        $customer = Customer::create([
            'business_id' => $business->id,
            'name' => 'Calendar Test Customer',
        ]);

        Event::create([
            'business_id' => $business->id,
            'customer_id' => $customer->id,
            'reference' => 'CAL-REGRESSION',
            'name' => 'Calendar Regression Job',
            'event_type' => 'Catering order',
            'event_date' => now()->addDays(3)->toDateString(),
            'status' => 'confirmed',
        ]);

        $response = $this->actingAs($user)->get(route('calendar.index', [
            'view' => 'agenda',
            'month' => now()->format('Y-m'),
        ]));

        $response->assertOk()
            ->assertSee('Calendar Regression Job')
            ->assertSee('Agenda');
    }

    public function test_resource_register_pages_render_without_horizontal_layout_assumptions(): void
    {
        [$business, $user] = $this->workspace();

        $assets = $this->actingAs($user)->get(route('assets.index'));
        $assets->assertOk()
            ->assertSee('Asset register')
            ->assertSee('Add asset')
            ->assertSee('zazu-assets-directory');

        $inventory = $this->actingAs($user)->get(route('inventory.index'));
        $inventory->assertOk()
            ->assertSee('Stock register')
            ->assertSee('New inventory item')
            ->assertSee('zazu-inventory-directory');
    }

    public function test_navigation_places_calendar_under_work_and_uses_compact_group_labels(): void
    {
        [$business, $user] = $this->workspace();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk()
            ->assertSee('>Work<', false)
            ->assertSee('>Sales<', false)
            ->assertSee('>Resources<', false)
            ->assertSee('>Finance<', false)
            ->assertSee('>Calendar<', false)
            ->assertSee('>Setup<', false)
            ->assertDontSee('Sales &amp; operations', false)
            ->assertDontSee('Purchasing &amp; resources', false)
            ->assertDontSee('Money &amp; control', false);
    }
}
