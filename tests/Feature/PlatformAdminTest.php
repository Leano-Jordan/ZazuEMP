<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Tests\TestCase;

class PlatformAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_platform_admin_can_manage_landing_media(): void
    {
        $admin = \App\Models\User::factory()->create(['email' => 'admin@zazu.test']);
        Config::set('zazu.platform_admin_email', 'admin@zazu.test');

        $this->actingAs($admin)
            ->get(route('admin.landing.settings'))
            ->assertOk()
            ->assertSee('Landing page imagery');

        $this->actingAs($admin)
            ->put(route('admin.landing.settings.update'), [
                'hero_image' => 'sound_stage',
                'operations_image' => 'event_catering',
                'resources_image' => 'sound_stage',
                'control_image' => 'wedding_catering',
            ])
            ->assertRedirect(route('admin.landing.settings'));

        $row = DB::table('platform_landing_settings')->first();

        $this->assertSame(config('zazu.landing_image_library.sound_stage.url'), $row->hero_image_path);
        $this->assertSame(config('zazu.landing_image_library.event_catering.url'), $row->operations_image_path);
        $this->assertSame(config('zazu.landing_image_library.wedding_catering.url'), $row->control_image_path);
    }

    public function test_public_landing_uses_platform_media_configuration(): void
    {
        DB::table('platform_landing_settings')->update([
            'hero_image_path' => config('zazu.landing_image_library.sound_stage.url'),
            'operations_image_path' => config('zazu.landing_image_library.event_catering.url'),
            'resources_image_path' => config('zazu.landing_image_library.wedding_catering.url'),
            'control_image_path' => config('zazu.landing_image_library.catering_service.url'),
        ]);

        $response = $this->get(route('landing'));

        $response->assertOk();

        foreach ([
            config('zazu.landing_image_library.sound_stage.url'),
            config('zazu.landing_image_library.event_catering.url'),
            config('zazu.landing_image_library.wedding_catering.url'),
            config('zazu.landing_image_library.catering_service.url'),
        ] as $imageUrl) {
            $response->assertSee(e($imageUrl), false);
        }
    }

    public function test_non_platform_admin_cannot_manage_landing_media(): void
    {
        $user = \App\Models\User::factory()->create(['email' => 'owner@zazu.test']);
        Config::set('zazu.platform_admin_email', 'admin@zazu.test');

        $this->actingAs($user)
            ->get(route('admin.landing.settings'))
            ->assertForbidden();
    }

    public function test_business_owner_sees_a_private_sentinel_rescue_link(): void
    {
        $this->signInAsOwner();

        $response = $this->get(route('settings.index'));

        $response->assertOk()
            ->assertSee('Sentinel Rescue')
            ->assertDontSee('Sentinel Control Centre');

        $this->assertStringContainsString('href="'.route('sentinel.rescue').'"', $response->getContent());
        $this->assertStringNotContainsString('/api/sentinel/rescue?', $response->getContent());
    }

    public function test_sentinel_console_is_restricted_to_platform_administrators(): void
    {
        $this->get(route('admin.sentinel'))->assertRedirect(route('login'));

        $user = \App\Models\User::factory()->create(['email' => 'owner@zazu.test']);
        Config::set('zazu.platform_admin_email', 'admin@zazu.test');

        $this->actingAs($user)
            ->get(route('admin.sentinel'))
            ->assertForbidden();

        $founder = \App\Models\User::factory()->create(['email' => 'admin@zazu.test']);
        Config::set('zazu.platform_admin_email', 'admin@zazu.test');

        $this->actingAs($founder)
            ->get(route('admin.sentinel'))
            ->assertOk()
            ->assertSee('Sentinel Control Centre')
            ->assertSee('href="'.route('admin.sentinel').'"', false)
            ->assertDontSee('href="'.route('dashboard').'"', false);
    }

    public function test_legacy_admin_list_does_not_grant_founder_access(): void
    {
        $user = \App\Models\User::factory()->create(['email' => 'admin@zazu.test']);
        Config::set('zazu.platform_admin_email', '');
        Config::set('zazu.platform_admin_emails', ['admin@zazu.test']);

        $this->actingAs($user)
            ->get(route('admin.sentinel'))
            ->assertForbidden();
    }

    public function test_platform_admin_can_search_sentinel_and_open_safe_timeline(): void
    {
        $admin = \App\Models\User::factory()->create(['email' => 'admin@zazu.test']);
        Config::set('zazu.platform_admin_email', 'admin@zazu.test');

        $directory = storage_path('app/private/zazu-diagnostics');
        File::deleteDirectory($directory);
        File::ensureDirectoryExists($directory);

        $fingerprint = 'ZAZU-0123456789ABCDEF';
        $records = [
            [
                'request_id' => 'b17e9a51-891c-48b6-9d71-1f24e6ab4c82',
                'failure_fingerprint' => $fingerprint,
                'error_code' => 'DB-001',
                'database_failure_class' => 'sqlite_missing_column',
                'exception_class' => 'Illuminate\\Database\\QueryException',
                'source_file' => 'DashboardController.php',
                'timestamp' => '2026-11-18T10:00:00+02:00',
                'sql' => 'private SQL must not appear',
            ],
            [
                'request_id' => '895e2c23-bc6b-4919-a891-1e8e5c1e27e4',
                'failure_fingerprint' => $fingerprint,
                'error_code' => 'DB-001',
                'database_failure_class' => 'sqlite_missing_column',
                'exception_class' => 'Illuminate\\Database\\QueryException',
                'source_file' => 'DashboardController.php',
                'timestamp' => '2026-11-18T11:00:00+02:00',
            ],
        ];

        File::put(
            $directory.DIRECTORY_SEPARATOR.'incidents.jsonl',
            implode(PHP_EOL, array_map(
                static fn (array $record): string => json_encode($record, JSON_THROW_ON_ERROR),
                $records,
            )).PHP_EOL,
        );

        try {
            $this->actingAs($admin)
                ->get(route('admin.sentinel', [
                    'q' => 'sqlite_missing_column',
                    'fingerprint' => $fingerprint,
                ]))
                ->assertOk()
                ->assertSee('Sentinel Control Centre')
                ->assertSee($fingerprint)
                ->assertSee('895e2c23-bc6b-4919-a891-1e8e5c1e27e4')
                ->assertSee('DashboardController.php')
                ->assertSee(route('support.incidents.show', ['requestId' => '895e2c23-bc6b-4919-a891-1e8e5c1e27e4']), false)
                ->assertSee('Download safe DB report')
                ->assertDontSee('private SQL');
        } finally {
            File::deleteDirectory($directory);
        }
    }

    public function test_sentinel_overview_counts_all_retained_fingerprints_not_only_the_top_hundred(): void
    {
        $admin = \App\Models\User::factory()->create(['email' => 'admin@zazu.test']);
        Config::set('zazu.platform_admin_email', 'admin@zazu.test');

        $directory = storage_path('app/private/zazu-diagnostics');
        File::deleteDirectory($directory);
        File::ensureDirectoryExists($directory);

        $records = [];
        for ($index = 1; $index <= 100; $index++) {
            $fingerprint = sprintf('ZAZU-%016X', $index);
            foreach (['10:00:00', '10:01:00'] as $time) {
                $records[] = [
                    'request_id' => (string) Str::uuid(),
                    'failure_fingerprint' => $fingerprint,
                    'error_code' => 'APP-001',
                    'timestamp' => '2026-11-18T'.$time.'+02:00',
                ];
            }
        }

        // This low-frequency fingerprint is outside the 100-row display list,
        // but must still contribute to totals and the true latest-incident time.
        $records[] = [
            'request_id' => (string) Str::uuid(),
            'failure_fingerprint' => 'ZAZU-FFFFFFFFFFFFFFFF',
            'error_code' => 'APP-002',
            'timestamp' => '2026-11-18T23:00:00+02:00',
        ];

        File::put(
            $directory.DIRECTORY_SEPARATOR.'incidents.jsonl',
            implode(PHP_EOL, array_map(
                static fn (array $record): string => json_encode($record, JSON_THROW_ON_ERROR),
                $records,
            )).PHP_EOL,
        );

        try {
            $this->actingAs($admin)
                ->get(route('admin.sentinel'))
                ->assertOk()
                ->assertSee('101</strong>', false)
                ->assertSee('201</strong>', false)
                ->assertSee('2026-11-18T23:00:00+02:00');

            $this->actingAs($admin)
                ->get(route('admin.sentinel', ['q' => 'APP-002']))
                ->assertOk()
                ->assertSee('ZAZU-FFFFFFFFFFFFFFFF')
                ->assertSee('APP-002')
                ->assertDontSee('ZAZU-0000000000000001');
        } finally {
            File::deleteDirectory($directory);
        }
    }

}
