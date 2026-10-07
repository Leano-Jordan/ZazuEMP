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
        $this->assertSame('/dashboard', $manifest['start_url']);
        $this->assertSame('standalone', $manifest['display']);
        $this->assertCount(2, $manifest['icons']);
        $this->assertSame('/icons/zazu-192.svg', $manifest['icons'][0]['src']);
        $this->assertSame('192x192', $manifest['icons'][0]['sizes']);
        $this->assertSame('/icons/zazu-512.svg', $manifest['icons'][1]['src']);
        $this->assertSame('512x512', $manifest['icons'][1]['sizes']);
    }

    public function test_real_zazu_shell_contains_install_metadata_and_service_worker_registration(): void
    {
        $response = $this->get('/dashboard');

        $response->assertOk()
            ->assertSee('/manifest.webmanifest')
            ->assertSee('data-zazu-user-id="', false);

        $html = html_entity_decode($response->getContent(), ENT_QUOTES | ENT_HTML5);
        $this->assertStringNotContainsString("navigator.serviceWorker.register('/sw.js')", $html);
        $this->assertStringNotContainsString('zazu-phone-workspace', $html);

        $app = File::get(resource_path('js/app.js'));
        $this->assertStringContainsString("navigator.serviceWorker.register('/sw.js', { scope: '/' })", $app);
        $this->assertStringContainsString("target?.postMessage({ type: 'prime-pages', userId, routes })", $app);
    }

    public function test_service_worker_uses_the_real_zazu_shell_and_cached_pages_for_offline_navigation(): void
    {
        $worker = File::get(public_path('sw.js'));

        $this->assertStringContainsString("const CACHE_NAME = 'zazu-static-v10';", $worker);
        $this->assertStringContainsString("const OFFLINE_SHELL = '/dashboard';", $worker);
        $this->assertStringContainsString("OFFLINE_SHELL,", $worker);
        $this->assertStringContainsString("'/offline-attachments.js'", $worker);
        $this->assertStringContainsString('caches.match(request)', $worker);
        $this->assertStringContainsString('cache.put(request, copy)', $worker);
        $this->assertStringContainsString("event.data?.type !== 'prime-pages'", $worker);
        $this->assertStringContainsString('function userCacheName()', $worker);
        $this->assertStringContainsString("const ACTIVE_USER_STATE = new Request('/__zazu-active-user__');", $worker);
        $this->assertStringContainsString('async function loadActiveUser()', $worker);
        $this->assertStringContainsString('async function cacheRenderedPage(request, response)', $worker);
        $this->assertStringContainsString('data-zazu-user-id="(\\d+)"', $worker);
        $this->assertStringContainsString("CACHE_NAME + '-user-' + activeUserId", $worker);
        $this->assertStringContainsString('await cache.put(new Request(new URL(path, self.location.origin)), response.clone())', $worker);
    }

    public function test_pwa_icon_files_are_present(): void
    {
        $this->assertFileExists(public_path('icons/zazu-192.svg'));
        $this->assertFileExists(public_path('icons/zazu-512.svg'));
    }
    public function test_legacy_offline_workspace_view_is_removed(): void
    {
        $this->assertFileDoesNotExist(resource_path('views/offline.blade.php'));
    }

    public function test_offline_route_no_longer_exposes_a_second_zazu_workspace(): void
    {
        $response = $this->get('/offline');
        $response->assertRedirect('/dashboard');
    }

}
