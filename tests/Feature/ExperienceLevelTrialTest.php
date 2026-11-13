<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\User;
use App\Support\ExperienceLevel;
use App\Support\NicheFocus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Tests\TestCase;

class ExperienceLevelTrialTest extends TestCase
{
    use RefreshDatabase;

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

    public function test_setup_centre_displays_effective_level_after_trial_expiry_without_rewriting_preference(): void
    {
        $business = Business::create([
            'name' => 'Expired Setup Business',
            'slug' => 'expired-setup-'.Str::lower(Str::random(8)),
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

        $this->actingAs($user)
            ->get(route('onboarding.index'))
            ->assertOk()
            ->assertSee('Basic')
            ->assertDontSee('Advanced.');

        $this->assertSame(
            'advanced',
            $user->businesses()->whereKey($business->id)->first()->pivot->experience_level
        );
    }
}
