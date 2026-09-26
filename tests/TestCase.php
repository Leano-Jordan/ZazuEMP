<?php

namespace Tests;

use App\Models\Business;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Str;

abstract class TestCase extends BaseTestCase
{
    protected function signInAsOwner(?Business $business = null): User
    {
        $business ??= Business::create([
            'name' => 'Test Business',
            'slug' => 'test-business-'.Str::lower(Str::random(8)),
            'status' => 'active',
            'currency' => 'ZAR',
        ]);

        $user = User::factory()->create();

        $business->users()->attach($user->id, ['role' => 'owner']);

        $this->actingAs($user);

        return $user;
    }
}
