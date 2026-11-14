<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Services\BusinessArchiveService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Tests\TestCase;

class BusinessArchiveFileCollectorTest extends TestCase
{
    use RefreshDatabase;

    public function test_export_rejects_a_single_backslash_in_a_tenant_file_path(): void
    {
        $business = Business::create([
            'name' => 'Unsafe Archive Path',
            'slug' => 'unsafe-archive-path',
            'status' => 'active',
            'currency' => 'ZAR',
        ]);

        DB::table('customers')->insert([
            'business_id' => $business->id,
            'name' => 'Unsafe Path Customer',
            'profile_photo_path' => 'avatars\\private-photo.jpg',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        try {
            try {
                app(BusinessArchiveService::class)->download($business);
                $this->fail('A file path containing a backslash must be rejected.');
            } catch (RuntimeException $exception) {
                $this->assertStringContainsString('file path is unsafe', $exception->getMessage());
            }
        }
    }
}
