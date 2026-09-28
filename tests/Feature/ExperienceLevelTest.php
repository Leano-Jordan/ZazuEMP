<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

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
        ])->assertRedirect(route('preferences.experience'));

        $this->assertSame(
            'advanced',
            $user->businesses()->whereKey($business->id)->first()->pivot->experience_level
        );
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
        ])->assertRedirect(route('preferences.experience'));

        $membership = $user->businesses()->whereKey($business->id)->first()->pivot;
        $this->assertSame('staff', $membership->role);
        $this->assertSame('basic', $membership->experience_level);
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
            ->assertSee('Choose how much of Zazu you want surfaced.');

        $this->post(route('onboarding.experience.store'), [
            'experience_level' => 'advanced',
        ])->assertRedirect(route('onboarding.business'));

        $this->assertSame(
            'advanced',
            $user->businesses()->whereKey($business->id)->first()->pivot->experience_level
        );
    }

}
