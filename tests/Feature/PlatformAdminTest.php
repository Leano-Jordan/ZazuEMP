<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
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

        $response = $this->actingAs($admin)->get(route('admin.landing.settings'));

        $response
            ->assertOk()
            ->assertSee('Landing page positions')
            ->assertSee('Venue image')
            ->assertSee('Decor and reception image')
            ->assertSee('Complete image library')
            ->assertSee('data-zazu-library-image', false)
            ->assertSee('data-zazu-slot-preview', false)
            ->assertSee('click any image below to assign it')
            ->assertDontSee('/images/landing/hero.svg')
            ->assertDontSee('/images/landing/operations.svg')
            ->assertDontSee('/images/landing/resources.svg')
            ->assertDontSee('/images/landing/control.svg')
            ->assertSee('Upload image experiments')
            ->assertSee('data-zazu-upload-previews', false);

        foreach (app(\App\Services\LandingMediaService::class)->library() as $image) {
            $response->assertSee($image['url'], false);
        }

        preg_match_all('/<img\\b[^>]*\\bdata-zazu-landing-preview\\b/i', $response->getContent(), $previewImages);
        $this->assertCount(6, $previewImages[0], 'Each of the six landing positions must render exactly one preview image.');

        $this->actingAs($admin)
            ->put(route('admin.landing.settings.update'), [
                'hero_image' => 'sound_stage',
                'operations_image' => 'event_catering',
                'resources_image' => 'sound_stage',
                'control_image' => 'wedding_catering',
                'venue_image' => 'venue',
                'decor_image' => 'luxury_banquet',
            ])
            ->assertRedirect(route('admin.landing.settings'));

        $row = DB::table('platform_landing_settings')->first();

        $this->assertSame(config('zazu.landing_image_library.sound_stage.url'), $row->hero_image_path);
        $this->assertSame(config('zazu.landing_image_library.event_catering.url'), $row->operations_image_path);
        $this->assertSame(config('zazu.landing_image_library.wedding_catering.url'), $row->control_image_path);
        $this->assertSame(config('zazu.landing_image_library.venue.url'), $row->venue_image_path);
        $this->assertSame(config('zazu.landing_image_library.luxury_banquet.url'), $row->decor_image_path);
    }


    public function test_platform_admin_can_upload_preview_and_reuse_landing_media(): void
    {
        $admin = \App\Models\User::factory()->create(['email' => 'admin@zazu.test']);
        Config::set('zazu.platform_admin_email', 'admin@zazu.test');

        $directory = storage_path('framework/testing/landing-uploads-'.Str::uuid());
        Config::set('zazu.landing_media_uploads.directory', $directory);

        $this->actingAs($admin)
            ->put(route('admin.landing.settings.update'), [
                'hero_image' => 'catering_service',
                'operations_image' => 'event_catering',
                'resources_image' => 'sound_stage',
                'control_image' => 'wedding_catering',
                'venue_image' => 'venue',
                'decor_image' => 'luxury_banquet',
                'uploads' => [UploadedFile::fake()->image('stage-experiment.jpg', 1200, 800)],
            ])
            ->assertRedirect(route('admin.landing.settings'));

        $media = DB::table('platform_landing_media')->first();

        $this->assertNotNull($media);
        $this->assertSame('stage experiment', $media->label);
        $this->assertStringStartsWith('/images/landing/uploads/', $media->path);
        $this->assertStringNotContainsString('stage-experiment', $media->path);
        $this->assertFileExists($directory.'/'.basename($media->path));

        $this->actingAs($admin)
            ->get(route('admin.landing.settings'))
            ->assertOk()
            ->assertSee('stage experiment')
            ->assertSee($media->path);

        $this->actingAs($admin)
            ->put(route('admin.landing.settings.update'), [
                'hero_image' => 'uploaded_'.$media->id,
                'operations_image' => 'event_catering',
                'resources_image' => 'sound_stage',
                'control_image' => 'wedding_catering',
                'venue_image' => 'venue',
                'decor_image' => 'luxury_banquet',
            ])
            ->assertRedirect(route('admin.landing.settings'));

        $this->assertSame(
            $media->path,
            DB::table('platform_landing_settings')->value('hero_image_path')
        );
        $this->assertSame($media->path, app(\App\Services\LandingMediaService::class)->images()['hero']);

        File::deleteDirectory($directory);
    }


    public function test_platform_admin_upload_rejects_svg_and_untrusted_media_types(): void
    {
        $admin = \App\Models\User::factory()->create(['email' => 'admin@zazu.test']);
        Config::set('zazu.platform_admin_email', 'admin@zazu.test');

        $this->actingAs($admin)
            ->from(route('admin.landing.settings'))
            ->put(route('admin.landing.settings.update'), [
                'hero_image' => 'catering_service',
                'operations_image' => 'event_catering',
                'resources_image' => 'sound_stage',
                'control_image' => 'wedding_catering',
                'venue_image' => 'venue',
                'decor_image' => 'luxury_banquet',
                'uploads' => [UploadedFile::fake()->create('unsafe-markup.svg', 20, 'image/svg+xml')],
            ])
            ->assertSessionHasErrors(['uploads.0']);

        $this->assertDatabaseCount('platform_landing_media', 0);
    }

    public function test_public_landing_uses_platform_media_configuration(): void
    {
        DB::table('platform_landing_settings')->update([
            'hero_image_path' => config('zazu.landing_image_library.sound_stage.url'),
            'operations_image_path' => config('zazu.landing_image_library.event_catering.url'),
            'resources_image_path' => config('zazu.landing_image_library.wedding_catering.url'),
            'control_image_path' => config('zazu.landing_image_library.catering_service.url'),
            'venue_image_path' => config('zazu.landing_image_library.venue.url'),
            'decor_image_path' => config('zazu.landing_image_library.luxury_banquet.url'),
        ]);

        $response = $this->get(route('landing'));

        $response->assertOk();

        foreach ([
            config('zazu.landing_image_library.sound_stage.url'),
            config('zazu.landing_image_library.event_catering.url'),
            config('zazu.landing_image_library.wedding_catering.url'),
            config('zazu.landing_image_library.catering_service.url'),
            config('zazu.landing_image_library.venue.url'),
            config('zazu.landing_image_library.luxury_banquet.url'),
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

        $this->actingAs($user)
            ->put(route('admin.landing.settings.update'), [
                'hero_image' => 'catering_service',
                'operations_image' => 'event_catering',
                'resources_image' => 'sound_stage',
                'control_image' => 'wedding_catering',
                'venue_image' => 'venue',
                'decor_image' => 'luxury_banquet',
                'uploads' => [UploadedFile::fake()->image('unauthorised.jpg', 1200, 800)],
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('platform_landing_media', 0);
    }

    public function test_business_owner_sees_owner_admin_and_not_the_founder_control_centre(): void
    {
        $this->signInAsOwner();

        $response = $this->get(route('settings.index'));

        $response->assertOk()
            ->assertSee('Owner Admin')
            ->assertDontSee('Founder Control Centre')
            ->assertDontSee('Sentinel Rescue');

        $this->assertStringContainsString('href="'.route('owner.dashboard').'"', $response->getContent());
        $this->assertStringContainsString('href="'.route('exports.index').'"', $response->getContent());
        $this->assertStringNotContainsString('href="'.route('sentinel.rescue').'"', $response->getContent());
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
            ->assertSee('Founder Control Centre')
            ->assertSee('href="'.route('admin.sentinel').'"', false)
            ->assertDontSee('href="'.route('dashboard').'"', false);
    }

    public function test_founder_control_centre_has_a_return_path_when_the_founder_has_a_business_workspace(): void
    {
        $founder = \App\Models\User::factory()->create(['email' => 'admin@zazu.test']);
        $business = \App\Models\Business::create([
            'name' => 'Founder Workspace',
            'slug' => 'founder-workspace',
            'status' => 'active',
            'currency' => 'ZAR',
        ]);
        $business->users()->attach($founder->id, ['role' => 'owner']);
        Config::set('zazu.platform_admin_email', 'admin@zazu.test');

        $this->actingAs($founder)
            ->get(route('admin.sentinel'))
            ->assertOk()
            ->assertSee('Founder Control Centre')
            ->assertSee('Business workspace')
            ->assertSee('aria-controls="zazu-nav-system"', false)
            ->assertSee('href="'.route('admin.sentinel').'"', false)
            ->assertSee('href="'.route('owner.dashboard').'"', false)
            ->assertSee('href="'.route('exports.index').'"', false)
            ->assertSee('href="'.route('dashboard').'"', false);

        $html = $this->get(route('admin.sentinel'))->getContent();
        $systemStart = strpos($html, 'id="zazu-nav-system"');
        $this->assertNotFalse($systemStart);
        $systemEnd = strpos($html, '</div></div>', $systemStart);
        $this->assertNotFalse($systemEnd);
        $systemPanel = substr($html, $systemStart, $systemEnd - $systemStart);
        $this->assertStringContainsString(route('admin.sentinel'), $systemPanel);
        $this->assertStringContainsString(route('owner.dashboard'), $systemPanel);
        $this->assertStringContainsString(route('exports.index'), $systemPanel);
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
                'exception_class' => 'Illuminate\Database\QueryException',
                'source_file' => 'DashboardController.php',
                'timestamp' => '2026-11-18T10:00:00+02:00',
                'sql' => 'private SQL must not appear',
            ],
            [
                'request_id' => '895e2c23-bc6b-4919-a891-1e8e5c1e27e4',
                'failure_fingerprint' => $fingerprint,
                'error_code' => 'DB-001',
                'database_failure_class' => 'sqlite_missing_column',
                'exception_class' => 'Illuminate\Database\QueryException',
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
                ->assertSee('Founder Control Centre')
                ->assertSee($fingerprint)
                ->assertSee('895e2c23-bc6b-4919-a891-1e8e5c1e27e4')
                ->assertSee('DashboardController.php')
                ->assertSee('/api/support/incidents/895e2c23-bc6b-4919-a891-1e8e5c1e27e4', false)
                ->assertSee('signature=', false)
                ->assertSee('expires=', false)
                ->assertSee('Download safe DB report')
                ->assertDontSee('private SQL');
        } finally {
            File::deleteDirectory($directory);
        }
    }
}
