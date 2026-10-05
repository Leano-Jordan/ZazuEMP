<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class PwaOfflineFoundationTest extends TestCase
{
    public function test_manifest_exposes_installable_zazu_icons(): void
    {
        $manifestPath = public_path('manifest.webmanifest');

        $this->assertFileExists($manifestPath);
        $manifest = json_decode(File::get($manifestPath), true, 512, JSON_THROW_ON_ERROR);

        $response = $this->get('/manifest.webmanifest');
        $response->assertOk()->assertHeader('Content-Type', 'application/manifest+json');

        $this->assertSame('Zazu', $manifest['short_name']);
        $this->assertSame('/offline', $manifest['start_url']);
        $this->assertSame('standalone', $manifest['display']);
        $this->assertCount(2, $manifest['icons']);
        $this->assertSame('/icons/zazu-192.svg', $manifest['icons'][0]['src']);
        $this->assertSame('192x192', $manifest['icons'][0]['sizes']);
        $this->assertSame('/icons/zazu-512.svg', $manifest['icons'][1]['src']);
        $this->assertSame('512x512', $manifest['icons'][1]['sizes']);
    }

    public function test_phone_workspace_contains_install_metadata_and_local_bootstrap(): void
    {
        $response = $this->get('/offline');

        $response->assertOk()
            ->assertSee('/manifest.webmanifest')
            ->assertSee('Install Zazu')
            ->assertSee("const DB='zazu-phone-workspace'")
            ->assertSee('data.local_dirty=true');

        $this->assertStringContainsString(
            "navigator.serviceWorker.register('/sw.js')",
            html_entity_decode($response->getContent(), ENT_QUOTES | ENT_HTML5)
        );
    }

    public function test_service_worker_guarantees_the_offline_shell_is_precached(): void
    {
        $worker = File::get(public_path('sw.js'));

        $this->assertStringContainsString("const CACHE_NAME = 'zazu-static-v6';", $worker);
        $this->assertStringContainsString("const OFFLINE_SHELL = '/offline';", $worker);
        $this->assertStringContainsString("OFFLINE_SHELL,", $worker);
        $this->assertStringContainsString(".catch(() => caches.match(OFFLINE_SHELL))", $worker);
    }

    public function test_pwa_icon_files_are_present(): void
    {
        $this->assertFileExists(public_path('icons/zazu-192.svg'));
        $this->assertFileExists(public_path('icons/zazu-512.svg'));
    }
}
