<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\BusinessCapability;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;
use App\Support\NicheFocus;

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
}
