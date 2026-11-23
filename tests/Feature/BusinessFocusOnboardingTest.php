<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\BusinessCapability;
use App\Models\SyncDevice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BusinessFocusOnboardingTest extends TestCase
{
    use RefreshDatabase;

    public function test_onboarding_starts_with_business_focus_before_the_catalogue(): void
    {
        $business = Business::create([
            'name' => 'Business First Setup',
            'slug' => 'business-first-setup',
            'status' => 'active',
            'currency' => 'ZAR',
        ]);

        $this->signInAsOwner($business);

        $this->get(route('onboarding.catalogue'))
            ->assertRedirect(route('onboarding.experience'));

        $this->get(route('onboarding.experience'))
            ->assertOk()
            ->assertSee('What kind of business do you run?')
            ->assertSee('name="business_focuses[]"', false)
            ->assertSee('name="primary_niche"', false);
    }


    public function test_owner_can_save_multiple_business_focuses_and_a_primary_focus(): void
    {
        $business = Business::create([
            'name' => 'Mixed Event Business',
            'slug' => 'mixed-event-business',
            'status' => 'active',
            'currency' => 'ZAR',
        ]);

        $user = $this->signInAsOwner($business);

        $this->post(route('onboarding.experience.store'), [
            'experience_level' => 'basic',
            'primary_niche' => 'sound_dj',
            'business_focuses_present' => '1',
            'business_focuses' => ['sound_dj', 'catering_baking', 'photography'],
        ])->assertRedirect(route('onboarding.catalogue'));

        $this->assertDatabaseHas('business_user', [
            'business_id' => $business->id,
            'user_id' => $user->id,
            'primary_niche' => 'sound_dj',
            'business_focuses' => '["sound_dj","catering_baking","photography"]',
        ]);

        $niches = app(\App\Support\NicheFocus::class);
        $this->assertSame(
            ['sound_dj', 'catering_baking', 'photography'],
            $niches->selectedMany($user, $business)
        );

        $staff = User::factory()->create();
        $business->users()->attach($staff->id, ['role' => 'staff']);
        $this->assertSame(
            ['sound_dj', 'catering_baking', 'photography'],
            $niches->selectedMany($staff, $business)
        );
    }


    public function test_legacy_single_focus_submission_remains_compatible(): void
    {
        $business = Business::create([
            'name' => 'Legacy Focus Business',
            'slug' => 'legacy-focus-business',
            'status' => 'active',
            'currency' => 'ZAR',
        ]);

        $user = $this->signInAsOwner($business);

        $this->post(route('onboarding.experience.store'), [
            'experience_level' => 'basic',
            'primary_niche' => 'sound_dj',
        ])->assertRedirect(route('onboarding.catalogue'));

        $this->assertDatabaseHas('business_user', [
            'business_id' => $business->id,
            'user_id' => $user->id,
            'primary_niche' => 'sound_dj',
            'business_focuses' => '["sound_dj"]',
        ]);
    }


    public function test_dj_only_business_gets_relevant_catalogue_categories_without_catering_clutter(): void
    {
        $business = Business::create([
            'name' => 'DJ Only Business',
            'slug' => 'dj-only-business',
            'status' => 'active',
            'currency' => 'ZAR',
        ]);

        $this->signInAsOwner($business);

        $this->post(route('onboarding.experience.store'), [
            'experience_level' => 'basic',
            'primary_niche' => 'sound_dj',
            'business_focuses_present' => '1',
            'business_focuses' => ['sound_dj'],
        ])->assertRedirect(route('onboarding.catalogue'));

        $this->get(route('onboarding.catalogue'))
            ->assertOk()
            ->assertSee('Sound &amp; entertainment', false)
            ->assertSee('Other')
            ->assertDontSee('value="Catering"', false)
            ->assertDontSee('value="Baking"', false);
    }


    public function test_setup_centre_summarises_active_and_hidden_owner_capabilities(): void
    {
        $business = Business::create([
            'name' => 'Capability Summary Business',
            'slug' => 'capability-summary-business',
            'status' => 'active',
            'currency' => 'ZAR',
        ]);

        $this->signInAsOwner($business);

        foreach ([
            ['name' => 'DJ service', 'capability_type' => 'service', 'is_active' => true],
            ['name' => 'Chair hire', 'capability_type' => 'rental', 'is_active' => true],
            ['name' => 'Archived decor', 'capability_type' => 'service', 'is_active' => false],
        ] as $capability) {
            BusinessCapability::create($capability + [
                'business_id' => $business->id,
                'category' => 'Other',
                'pricing_basis' => 'custom',
                'currency' => 'ZAR',
            ]);
        }

        $this->get(route('onboarding.index'))
            ->assertOk()
            ->assertSee('data-zazu-capability-summary', false)
            ->assertSee('2 active · 1 hidden')
            ->assertSee('Service: 1')
            ->assertSee('Rental: 1');
    }


    public function test_workspace_navigation_reflects_active_business_mix_without_hiding_shared_surfaces(): void
    {
        $business = Business::create([
            'name' => 'Mixed Services Workspace',
            'slug' => 'mixed-services-workspace',
            'status' => 'active',
            'currency' => 'ZAR',
        ]);

        $user = $this->signInAsOwner($business);
        $this->selectBusinessFocus($user, $business);

        foreach ([
            ['name' => 'DJ and sound package', 'category' => 'Sound & entertainment', 'capability_type' => 'service', 'is_active' => true],
            ['name' => 'Buffet catering', 'category' => 'Catering', 'capability_type' => 'service', 'is_active' => true],
            ['name' => 'Archived photo service', 'category' => 'Photography & video', 'capability_type' => 'service', 'is_active' => false],
        ] as $capability) {
            BusinessCapability::create($capability + [
                'business_id' => $business->id,
                'pricing_basis' => 'custom',
                'currency' => 'ZAR',
            ]);
        }

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('data-zazu-workspace-profile', false)
            ->assertSee('YOUR BUSINESS MIX')
            ->assertSee('2 active offerings')
            ->assertSee('Catering & baking')
            ->assertSee('Sound & DJ')
            ->assertSee('Catering')
            ->assertSee('Sound &amp; entertainment')
            ->assertDontSee('Photography &amp; video', false)
            ->assertSee('Jobs')
            ->assertSee('Quotes')
            ->assertSee('Finance');
    }


    public function test_offline_bootstrap_includes_presentation_only_workspace_profile(): void
    {
        $business = Business::create([
            'name' => 'Offline Mixed Workspace',
            'slug' => 'offline-mixed-workspace',
            'status' => 'active',
            'currency' => 'ZAR',
        ]);

        $user = $this->signInAsOwner($business);
        $this->selectBusinessFocus($user, $business);

        foreach ([
            ['name' => 'DJ package', 'category' => 'Sound & entertainment', 'capability_type' => 'service', 'is_active' => true],
            ['name' => 'Catering package', 'category' => 'Catering', 'capability_type' => 'service', 'is_active' => true],
            ['name' => 'Archived photography', 'category' => 'Photography & video', 'capability_type' => 'service', 'is_active' => false],
            ['name' => 'Chair hire', 'category' => 'Equipment rental', 'capability_type' => 'rental', 'is_active' => true],
        ] as $capability) {
            BusinessCapability::create($capability + [
                'business_id' => $business->id,
                'pricing_basis' => 'custom',
                'currency' => 'ZAR',
            ]);
        }

        $device = SyncDevice::create([
            'business_id' => $business->id,
            'approved_by_user_id' => $user->id,
            'installation_id' => (string) \Illuminate\Support\Str::uuid(),
            'status' => 'active',
        ]);

        $profile = app(\App\Support\Offline\SyncBootstrapPayloadBuilder::class)
            ->build($device)['workspace_profile'];

        $this->assertContains('Catering & baking', $profile['focus_labels']);
        $this->assertContains('Sound & DJ', $profile['focus_labels']);
        $this->assertSame(['Catering', 'Equipment rental', 'Sound & entertainment'], $profile['catalogue_categories']);
        $this->assertSame(3, $profile['active_offerings']);
        $this->assertSame(1, $profile['active_rentals']);
        $this->assertNotContains('Photography & video', $profile['catalogue_categories']);
    }


    private function selectBusinessFocus(User $user, Business $business): void
    {
        $user->businesses()->updateExistingPivot($business->id, [
            'experience_level' => 'basic',
            'primary_niche' => 'catering_baking',
            'business_focuses' => json_encode(['catering_baking']),
        ]);
    }

}
