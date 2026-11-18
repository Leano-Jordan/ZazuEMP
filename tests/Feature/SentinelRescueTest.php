<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SentinelRescueTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_users_cannot_open_sentinel_rescue(): void
    {
        $this->get(route('sentinel.rescue'))
            ->assertRedirect(route('login'));

        $this->get('/api/sentinel/rescue')
            ->assertNotFound();
    }

    public function test_non_owner_business_member_cannot_open_sentinel_rescue(): void
    {
        $business = Business::create([
            'name' => 'Staff Test Business',
            'slug' => 'staff-test-business',
            'status' => 'active',
            'currency' => 'ZAR',
        ]);
        $staff = User::factory()->create();
        $business->users()->attach($staff->id, ['role' => 'staff']);

        $this->actingAs($staff)
            ->get(route('sentinel.rescue'))
            ->assertForbidden();
    }

    public function test_authenticated_business_owner_can_open_local_rescue_and_return_to_workspace(): void
    {
        $this->signInAsOwner();

        $this->get(route('sentinel.rescue'))
            ->assertOk()
            ->assertSee('Sentinel Rescue')
            ->assertSee('A read-only health snapshot for this installation.')
            ->assertSee('Database connection')
            ->assertSee('Diagnostic write/read test')
            ->assertSee('Back to workspace')
            ->assertSee('data-filter="failed"', false)
            ->assertSee('data-check-search', false)
            ->assertSee('data-refresh', false)
            ->assertSee('data-expand-details', false)
            ->assertSee('data-check-card', false)
            ->assertSee(route('dashboard'))
            ->assertHeader('Cache-Control', 'no-store, private')
            ->assertHeader('Referrer-Policy', 'no-referrer');
    }
}
