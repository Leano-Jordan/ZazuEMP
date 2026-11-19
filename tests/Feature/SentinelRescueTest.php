<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\User;
use App\Support\ZazuBusinessHealth;
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

    public function test_authenticated_business_owner_uses_owner_admin_for_business_health(): void
    {
        $this->signInAsOwner();

        $this->get(route('owner.dashboard'))
            ->assertOk()
            ->assertSee('Business health')
            ->assertSee('Workspace database access')
            ->assertSee('Business access')
            ->assertSee('Business data access')
            ->assertSee('Owner tools')
            ->assertDontSee('Sentinel Rescue');

        $this->get(route('sentinel.rescue'))
            ->assertRedirect(route('owner.dashboard').'#business-health');

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Owner Admin')
            ->assertSee(route('owner.dashboard'));
    }

    public function test_business_health_is_scoped_and_does_not_expose_the_global_incident_ledger(): void
    {
        $business = Business::create([
            'name' => 'Scoped Health Business',
            'slug' => 'scoped-health-business',
            'status' => 'active',
            'currency' => 'ZAR',
        ]);
        $owner = User::factory()->create();
        $business->users()->attach($owner->id, ['role' => 'owner']);

        $health = app(ZazuBusinessHealth::class)->inspect($business, $owner);

        $this->assertSame('active_business', $health['scope']);
        $this->assertSame(
            ['database_connection', 'business_access', 'business_data_access'],
            array_column($health['checks'], 'key')
        );
        $this->assertStringNotContainsString('incident', strtolower(json_encode($health)));
    }
}
