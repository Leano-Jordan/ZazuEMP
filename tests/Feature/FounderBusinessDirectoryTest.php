<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class FounderBusinessDirectoryTest extends TestCase
{
    use RefreshDatabase;

    private function business(string $name, User $owner): Business
    {
        $business = Business::create([
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::lower(Str::random(6)),
            'status' => 'active',
            'currency' => 'ZAR',
        ]);

        $business->users()->attach($owner, ['role' => 'owner']);

        return $business;
    }

    public function test_founder_can_view_live_business_directory_and_account_membership(): void
    {
        config(['zazu.platform_admin_email' => 'founder@example.test']);

        $founder = User::factory()->create(['email' => 'founder@example.test']);
        $owner = User::factory()->create([
            'name' => 'North Owner',
            'email' => 'north.owner@example.test',
        ]);
        $business = $this->business('North Catering', $owner);

        $otherOwner = User::factory()->create(['email' => 'south.owner@example.test']);
        $this->business('South Events', $otherOwner);

        $this->actingAs($founder)
            ->get(route('admin.sentinel'))
            ->assertOk()
            ->assertSee('Platform business accounts')
            ->assertSee('North Catering')
            ->assertSee('South Events');

        $this->actingAs($founder)
            ->get(route('admin.sentinel.businesses.show', $business))
            ->assertOk()
            ->assertSee('North Catering')
            ->assertSee('north.owner@example.test')
            ->assertSee('Owner')
            ->assertSee('Founder support access');
    }

    public function test_non_founder_cannot_view_platform_business_directory_or_details(): void
    {
        config(['zazu.platform_admin_email' => 'founder@example.test']);

        $owner = User::factory()->create(['email' => 'owner@example.test']);
        $business = $this->business('Private Catering', $owner);
        $ordinaryUser = User::factory()->create(['email' => 'ordinary@example.test']);

        $this->actingAs($ordinaryUser)
            ->get(route('admin.sentinel'))
            ->assertForbidden();

        $this->actingAs($ordinaryUser)
            ->get(route('admin.sentinel.businesses.show', $business))
            ->assertForbidden();
    }
}
