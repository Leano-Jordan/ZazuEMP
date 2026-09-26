<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\BusinessCapability;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OnboardingTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_save_catalogue_setup_without_a_currency_field(): void
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

    public function test_catalogue_skip_marks_setup_complete_and_moves_to_business_setup(): void
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

        $this->assertNotNull($business->fresh()->catalogue_setup_completed_at);
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
}
