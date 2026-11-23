<?php

namespace Tests\Feature;

use App\Models\BusinessCapability;
use App\Models\Customer;
use App\Models\Event;
use App\Models\EventRequirement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogueJobIntakeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->signInAsOwner();
    }

    public function test_new_job_intake_presents_only_active_business_catalogue_offerings(): void
    {
        $customer = Customer::create(['name' => 'Catalogue Owner Customer']);
        BusinessCapability::create([
            'business_id' => $customer->business_id,
            'name' => 'Wedding Sound System',
            'category' => 'Audio',
            'capability_type' => 'rental',
            'pricing_basis' => 'per_day',
            'default_unit' => 'system',
            'is_active' => true,
        ]);
        BusinessCapability::create([
            'business_id' => $customer->business_id,
            'name' => 'Retired Catering Package',
            'category' => 'Catering',
            'capability_type' => 'package',
            'pricing_basis' => 'custom',
            'is_active' => false,
        ]);

        $response = $this->get(route('work.create'));

        $response->assertOk()
            ->assertSee('When and where?')
            ->assertSee('Select the services involved')
            ->assertSee('These selections create starting service requirements.')
            ->assertSee('Event type')
            ->assertSee('Wedding Sound System')
            ->assertDontSee('Retired Catering Package');

        $content = $response->getContent();
        $schedulePosition = strpos($content, 'When and where?');
        $servicesPosition = strpos($content, 'Select the services involved');

        $this->assertNotFalse($schedulePosition);
        $this->assertNotFalse($servicesPosition);
        $this->assertLessThan($servicesPosition, $schedulePosition);
        $this->assertStringNotContainsString('Choose the job type', $content);
    }

    public function test_initial_service_selection_is_saved_as_a_starter_requirement(): void
    {
        $customer = Customer::create(['name' => 'Starter Requirement Customer']);
        $capability = BusinessCapability::create([
            'business_id' => $customer->business_id,
            'name' => 'Premium Sound Package',
            'category' => 'Audio',
            'capability_type' => 'package',
            'pricing_basis' => 'fixed',
            'default_unit' => 'package',
            'is_active' => true,
        ]);
        $service = $capability->name;

        $response = $this->post(route('work.store'), [
            'customer_id' => $customer->id,
            'name' => 'Starter Requirement Work',
            'event_type' => 'Catering order',
            'event_date' => '2026-12-10',
            'services' => [$service],
        ]);

        $event = Event::query()->where('name', 'Starter Requirement Work')->firstOrFail();

        $response->assertRedirect(route('work.show', $event));

        $requirement = EventRequirement::query()
            ->where('event_id', $event->id)
            ->where('description', $service)
            ->firstOrFail();

        $this->assertSame(1.0, (float) $requirement->quantity);
        $this->assertSame('package', $requirement->unit);
        $this->assertSame($capability->id, $requirement->capability_id);
        $this->assertSame('Audio', $requirement->category);
    }

    public function test_one_off_job_service_remains_available_outside_the_catalogue(): void
    {
        $customer = Customer::create(['name' => 'One-off Service Customer']);

        $response = $this->post(route('work.store'), [
            'customer_id' => $customer->id,
            'name' => 'One-off Service Work',
            'event_type' => 'Other',
            'event_date' => '2026-12-11',
            'other_service' => 'Late-night transport',
        ]);

        $event = Event::query()->where('name', 'One-off Service Work')->firstOrFail();
        $response->assertRedirect(route('work.show', $event));

        $requirement = EventRequirement::query()
            ->where('event_id', $event->id)
            ->where('description', 'Late-night transport')
            ->firstOrFail();

        $this->assertNull($requirement->capability_id);
        $this->assertSame('Other', $requirement->category);
        $this->assertSame('service', $requirement->unit);
    }

}
