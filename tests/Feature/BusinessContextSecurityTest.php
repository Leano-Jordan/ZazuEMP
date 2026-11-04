<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BusinessContextSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_without_an_active_business_cannot_enter_workspace(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertForbidden();
    }
    public function test_user_without_a_business_can_still_log_out(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('logout'))
            ->assertRedirect(route('landing'));

        $this->assertGuest();
    }

    public function test_business_switch_is_membership_scoped_and_preserves_the_selected_workspace(): void
    {
        $first = \App\Models\Business::create([
            'name' => 'Switch First',
            'slug' => 'switch-first-'.\Illuminate\Support\Str::lower(\Illuminate\Support\Str::random(8)),
            'status' => 'active',
            'currency' => 'ZAR',
        ]);
        $second = \App\Models\Business::create([
            'name' => 'Switch Second',
            'slug' => 'switch-second-'.\Illuminate\Support\Str::lower(\Illuminate\Support\Str::random(8)),
            'status' => 'active',
            'currency' => 'ZAR',
        ]);
        $foreign = \App\Models\Business::create([
            'name' => 'Switch Foreign',
            'slug' => 'switch-foreign-'.\Illuminate\Support\Str::lower(\Illuminate\Support\Str::random(8)),
            'status' => 'active',
            'currency' => 'ZAR',
        ]);

        $user = User::factory()->create();
        $first->users()->attach($user->id, ['role' => 'owner']);
        $second->users()->attach($user->id, ['role' => 'staff']);

        $this->actingAs($user)
            ->post(route('business.switch'), ['business_id' => $first->id])
            ->assertRedirect();
        $this->assertSame($first->id, app(\App\Support\CurrentBusiness::class)->id($user));

        $this->post(route('business.switch'), ['business_id' => $second->id])
            ->assertRedirect();
        $this->assertSame($second->id, app(\App\Support\CurrentBusiness::class)->id($user));

        $this->post(route('business.switch'), ['business_id' => $foreign->id])
            ->assertForbidden();
        $this->assertSame($second->id, app(\App\Support\CurrentBusiness::class)->id($user));
    }

}
