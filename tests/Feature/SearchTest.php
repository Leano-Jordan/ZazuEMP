<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Customer;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class SearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_returns_only_records_from_the_active_business(): void
    {
        $first = Business::create([
            'name' => 'Search First',
            'slug' => 'search-first-'.Str::lower(Str::random(8)),
            'status' => 'active',
            'currency' => 'ZAR',
        ]);
        $second = Business::create([
            'name' => 'Search Second',
            'slug' => 'search-second-'.Str::lower(Str::random(8)),
            'status' => 'active',
            'currency' => 'ZAR',
        ]);

        $user = User::factory()->create();
        $first->users()->attach($user->id, ['role' => 'owner']);

        Customer::create(['business_id' => $first->id, 'name' => 'Visible Search Customer']);
        Customer::create(['business_id' => $second->id, 'name' => 'Hidden Search Customer']);

        $this->actingAs($user);

        $this->get(route('search.index', ['q' => 'Search Customer']))
            ->assertOk()
            ->assertSee('Visible Search Customer')
            ->assertDontSee('Hidden Search Customer');
    }
}
