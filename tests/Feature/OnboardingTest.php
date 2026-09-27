<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\BusinessCapability;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OnboardingTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_save_catalogue_setup_with_minimum_information(): void
    {
        $business = Business::create([
            'name' => 'Small Catering Business',
            'slug' => 'small-catering-business',
            'status' => 'active',
            'currency' => 'ZAR',
        ]);

        $this->signInAsOwner($business);

        $response = $this->post(route('onboarding.catalogue.store'), [
            'name' => 'Wedding catering',
        ]);

        $response->assertRedirect(route('onboarding.business'));

        $this->assertDatabaseHas('business_capabilities', [
            'business_id' => $business->id,
            'name' => 'Wedding catering',
            'capability_type' => 'service',
            'pricing_basis' => 'custom',
            'currency' => 'ZAR',
        ]);

        $this->assertNotNull($business->fresh()->catalogue_setup_completed_at);
    }

    public function test_owner_can_save_catalogue_setup_with_optional_details(): void
    {
        $business = Business::create([
            'name' => 'Onboarding Business',
            'slug' => 'onboarding-business',
            'status' => 'active',
            'currency' => 'USD',
        ]);

        $this->signInAsOwner($business);

        $response = $this->post(route('onboarding.catalogue.store'), [
            'name' => 'Full Service Catering',
            'capability_type' => 'service',
            'pricing_basis' => 'per_person',
            'default_price' => '125.00',
            'default_unit' => 'guest',
            'category' => 'Catering',
            'description' => 'Wedding and event catering.',
        ]);

        $response->assertRedirect(route('onboarding.business'));

        $this->assertDatabaseHas('business_capabilities', [
            'business_id' => $business->id,
            'name' => 'Full Service Catering',
            'currency' => 'USD',
            'category' => 'Catering',
        ]);

        $this->assertNotNull($business->fresh()->catalogue_setup_completed_at);
    }

    public function test_catalogue_skip_is_deferred_and_can_be_resumed(): void
    {
        $business = Business::create([
            'name' => 'Skip Business',
            'slug' => 'skip-business',
            'status' => 'active',
            'currency' => 'ZAR',
        ]);

        $this->signInAsOwner($business);

        $this->post(route('onboarding.catalogue.skip'))
            ->assertRedirect(route('onboarding.business'))
            ->assertSessionHas('info');

        $business->refresh();

        $this->assertNull($business->catalogue_setup_completed_at);
        $this->assertNotNull($business->catalogue_setup_skipped_at);

        $this->get(route('onboarding.catalogue'))->assertOk();
        $this->get(route('onboarding.index'))->assertOk();

        $this->post(route('onboarding.catalogue.finish'))
            ->assertRedirect(route('onboarding.business'));

        $this->assertNotNull($business->fresh()->catalogue_setup_completed_at);
        $this->assertNull($business->fresh()->catalogue_setup_skipped_at);
    }

    public function test_business_setup_can_finish_with_only_the_existing_business_name(): void
    {
        $business = Business::create([
            'name' => 'Lean Business',
            'slug' => 'lean-business',
            'status' => 'active',
            'currency' => 'ZAR',
        ]);

        $this->signInAsOwner($business);

        $this->post(route('onboarding.business'), [
            'name' => 'Lean Business',
        ])->assertRedirect(route('dashboard'));

        $business->refresh();

        $this->assertNotNull($business->business_setup_completed_at);
        $this->assertSame('ZAR', $business->currency);
    }

    public function test_business_skip_is_deferred_and_can_be_resumed(): void
    {
        $business = Business::create([
            'name' => 'Business Setup Skip',
            'slug' => 'business-setup-skip',
            'status' => 'active',
            'currency' => 'ZAR',
        ]);

        $this->signInAsOwner($business);

        $this->post(route('onboarding.business.skip'))
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('info');

        $business->refresh();

        $this->assertNull($business->business_setup_completed_at);
        $this->assertNotNull($business->business_setup_skipped_at);

        $this->get(route('onboarding.business'))->assertOk();

        $this->post(route('onboarding.business'), [
            'name' => 'Completed Business Setup',
            'email' => 'owner@example.com',
            'phone' => '0123456789',
            'address' => 'Pretoria',
            'website' => 'https://example.com',
            'tax_number' => '1234567890',
            'currency' => 'ZAR',
        ])->assertRedirect(route('dashboard'));

        $this->assertNotNull($business->fresh()->business_setup_completed_at);
        $this->assertNull($business->fresh()->business_setup_skipped_at);
    }

    public function test_staff_cannot_enter_or_mutate_onboarding(): void
    {
        $business = Business::create([
            'name' => 'Staff Onboarding Business',
            'slug' => 'staff-onboarding-business',
            'status' => 'active',
            'currency' => 'ZAR',
        ]);

        $staff = User::factory()->create();
        $business->users()->attach($staff->id, ['role' => 'staff']);

        $this->actingAs($staff)
            ->get(route('onboarding.catalogue'))
            ->assertForbidden();

        $this->actingAs($staff)
            ->post(route('onboarding.catalogue.skip'))
            ->assertForbidden();

        $this->assertDatabaseCount('business_capabilities', 0);
    }

    public function test_dashboard_stays_lean_until_resource_data_exists(): void
    {
        $business = Business::create([
            'name' => 'Lean Dashboard Business',
            'slug' => 'lean-dashboard-business',
            'status' => 'active',
            'currency' => 'ZAR',
        ]);

        $this->signInAsOwner($business);

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Services & prices')
            ->assertSee('Jobs')
            ->assertSee('Customers')
            ->assertSee('Quotes')
            ->assertDontSee('Purchase orders')
            ->assertDontSee('Inventory')
            ->assertDontSee('Assets');

        Supplier::create([
            'business_id' => $business->id,
            'name' => 'Fresh Supplier',
        ]);

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Purchasing');
    }
}
