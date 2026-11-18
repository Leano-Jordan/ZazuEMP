<?php

namespace Tests\Feature;

use App\Models\Business;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class BusinessLicensingIdentityTest extends TestCase
{
    use RefreshDatabase;

    public function test_each_business_receives_a_persistent_unique_licensing_uuid(): void
    {
        $first = Business::query()->create([
            'name' => 'First local business',
            'slug' => 'first-local-business',
            'status' => 'active',
        ]);
        $second = Business::query()->create([
            'name' => 'Second local business',
            'slug' => 'second-local-business',
            'status' => 'active',
        ]);

        $this->assertTrue(Str::isUuid($first->licensing_uuid));
        $this->assertTrue(Str::isUuid($second->licensing_uuid));
        $this->assertNotSame($first->licensing_uuid, $second->licensing_uuid);
        $this->assertSame($first->licensing_uuid, $first->fresh()->licensing_uuid);
    }
}
