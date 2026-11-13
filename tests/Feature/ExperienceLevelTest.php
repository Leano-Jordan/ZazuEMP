<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\BusinessCapability;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Tests\TestCase;
use App\Support\NicheFocus;
use App\Support\ExperienceLevel;

class ExperienceLevelTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_select_and_change_workspace_experience_level(): void
    {
        $business = Business::create([
            'name' => 'Experience Business',
            'slug' => 'experience-'.Str::lower(Str::random(8)),
            'status' => 'active',
            'currency' => 'ZAR',
        ]);

        $user = User::factory()->create();
        $business->users()->attach($user->id, ['role' => 'owner']);

        $this->actingAs($user);

        $this->get(route('onboarding.experience'))
            ->assertOk()
            ->assertSee('Basic')
            ->assertSee('Intermediate')
            ->assertSee('Advanced');

        $this->put(route('preferences.experience.update'), [
            'experience_level' => 'advanced',
            'primary_niche' => NicheFocus::SOUND_DJ,
        ])->assertRedirect(route('preferences.experience'));

        $membership = $user->businesses()->whereKey($business->id)->first()->pivot;

        $this->assertSame('advanced', $membership->experience_level);
        $this->assertSame(NicheFocus::SOUND_DJ, $membership->primary_niche);
    }

    public function test_staff_can_change_their_presentation_level_without_changing_role(): void
    {
        $business = Business::create([
            'name' => 'Staff Experience Business',
            'slug' => 'staff-experience-'.Str::lower(Str::random(8)),
            'status' => 'active',
            'currency' => 'ZAR',
        ]);

        $user = User::factory()->create();
        $business->users()->attach($user->id, ['role' => 'staff']);

        $this->actingAs($user);

        $this->put(route('preferences.experience.update'), [
            'experience_level' => 'basic',
            'primary_niche' => NicheFocus::SOUND_DJ,
        ])->assertRedirect(route('preferences.experience'));

        $membership = $user->businesses()->whereKey($business->id)->first()->pivot;
        $this->assertSame('staff', $membership->role);
        $this->assertSame('basic', $membership->experience_level);
        $this->assertSame(NicheFocus::SOUND_DJ, $membership->primary_niche);
    }

    public function test_onboarding_requires_an_experience_level_before_business_setup(): void
    {
        $business = Business::create([
            'name' => 'Onboarding Experience Business',
            'slug' => 'onboarding-experience-'.Str::lower(Str::random(8)),
            'status' => 'active',
            'currency' => 'ZAR',
        ]);

        $user = User::factory()->create();
        $business->users()->attach($user->id, ['role' => 'owner']);

        $this->actingAs($user);

        $this->get(route('onboarding.experience'))
            ->assertOk()
            ->assertSee('What do you mainly provide?')
            ->assertSee('Sound &amp; DJ', false);

        $this->post(route('onboarding.experience.store'), [
            'experience_level' => 'advanced',
        ])->assertRedirect(route('onboarding.business'));

        $this->assertSame(
            'advanced',
            $user->businesses()->whereKey($business->id)->first()->pivot->experience_level
        );
    }

    public function test_onboarding_saves_niche_and_experience_together(): void
    {
        $business = Business::create([
            'name' => 'Sound Business',
            'slug' => 'sound-business-'.Str::lower(Str::random(8)),
            'status' => 'active',
            'currency' => 'ZAR',
        ]);

        $user = User::factory()->create();
        $business->users()->attach($user->id, ['role' => 'owner']);

        $this->actingAs($user);

        $this->post(route('onboarding.experience.store'), [
            'experience_level' => 'advanced',
            'primary_niche' => NicheFocus::SOUND_DJ,
        ])->assertRedirect(route('onboarding.business'));

        $membership = $user->businesses()->whereKey($business->id)->first()->pivot;

        $this->assertSame('advanced', $membership->experience_level);
        $this->assertSame(NicheFocus::SOUND_DJ, $membership->primary_niche);
    }

    public function test_niche_focus_keeps_overlapping_catalogue_capabilities_visible(): void
    {
        $business = Business::create([
            'name' => 'DJ Hire Business',
            'slug' => 'dj-hire-business-'.Str::lower(Str::random(8)),
            'status' => 'active',
            'currency' => 'ZAR',
        ]);

        $user = User::factory()->create();
        $business->users()->attach($user->id, [
            'role' => 'owner',
            'experience_level' => 'intermediate',
            'primary_niche' => NicheFocus::SOUND_DJ,
        ]);

        BusinessCapability::create([
            'business_id' => $business->id,
            'name' => 'DJ Package',
            'category' => 'Sound & entertainment',
            'capability_type' => 'service',
            'pricing_basis' => 'per event',
            'currency' => 'ZAR',
            'is_active' => true,
        ]);

        BusinessCapability::create([
            'business_id' => $business->id,
            'name' => 'Banquet Chairs',
            'category' => 'Furniture & equipment',
            'capability_type' => 'rental',
            'pricing_basis' => 'per unit',
            'currency' => 'ZAR',
            'is_active' => true,
        ]);

        $this->assertSame(
            ['Chairs & tents'],
            app(NicheFocus::class)->supporting($business, NicheFocus::SOUND_DJ)
        );
    }

    public function test_new_workspace_receives_an_advanced_trial_for_exactly_thirty_days(): void
    {
        $business = Business::create([
            'name' => 'Trial Business',
            'slug' => 'trial-'.Str::lower(Str::random(8)),
            'status' => 'active',
            'currency' => 'ZAR',
        ]);
        $createdAt = Carbon::parse('2026-11-01 09:00:00', 'UTC');
        $business->forceFill(['created_at' => $createdAt])->saveQuietly();

        $levels = app(ExperienceLevel::class);
        $this->assertSame('advanced', $levels->maximumFor($business, $createdAt->copy()->addDays(29)->endOfDay()));
        $this->assertTrue($levels->isTrial($business, $createdAt->copy()->addDays(29)));
        $this->assertSame('2026-12-01 09:00:00', $levels->trialEndsAt($business)?->format('Y-m-d H:i:s'));
        $this->assertSame('basic', $levels->maximumFor($business, $createdAt->copy()->addDays(30)));
        $this->assertFalse($levels->isTrial($business, $createdAt->copy()->addDays(30)));
    }

    public function test_expired_trial_clamps_presentation_without_overwriting_saved_preference(): void
    {
        $business = Business::create([
            'name' => 'Expired Trial Business',
            'slug' => 'expired-trial-'.Str::lower(Str::random(8)),
            'status' => 'active',
            'currency' => 'ZAR',
        ]);
        $business->forceFill(['created_at' => Carbon::parse('2026-10-01 09:00:00', 'UTC')])->saveQuietly();

        $user = User::factory()->create();
        $business->users()->attach($user->id, [
            'role' => 'owner',
            'experience_level' => 'advanced',
            'primary_niche' => NicheFocus::DEFAULT,
        ]);

        $levels = app(ExperienceLevel::class);
        $this->assertSame('advanced', $levels->selected($user, $business));
        $this->assertSame('basic', $levels->for($user, $business));
        $this->assertSame('advanced', $levels->selected($user, $business));
        $this->assertSame(['basic'], $levels->availableLevels($business));
    }

    public function test_intermediate_signed_entitlement_allows_only_basic_and_intermediate_experience(): void
    {
        $business = Business::create([
            'name' => 'Intermediate Business',
            'slug' => 'intermediate-'.Str::lower(Str::random(8)),
            'status' => 'active',
            'currency' => 'ZAR',
        ]);
        $business->license()->create([
            'license_id' => (string) Str::uuid(),
            'plan' => 'intermediate',
            'status' => 'active',
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addDays(30),
            'features' => [],
        ]);

        $levels = app(ExperienceLevel::class);
        $this->assertSame('intermediate', $levels->maximumFor($business));
        $this->assertSame(['basic', 'intermediate'], $levels->availableLevels($business));
        $this->assertTrue($levels->canSelect($business, 'intermediate'));
        $this->assertFalse($levels->canSelect($business, 'advanced'));
    }

    public function test_legacy_solo_signed_entitlement_remains_an_advanced_compatibility_alias(): void
    {
        $business = Business::create([
            'name' => 'Legacy Licence Business',
            'slug' => 'legacy-'.Str::lower(Str::random(8)),
            'status' => 'active',
            'currency' => 'ZAR',
        ]);
        $business->license()->create([
            'license_id' => (string) Str::uuid(),
            'plan' => 'solo',
            'status' => 'active',
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addDays(30),
            'features' => [],
        ]);

        $this->assertSame('advanced', app(ExperienceLevel::class)->maximumFor($business));
    }

}
