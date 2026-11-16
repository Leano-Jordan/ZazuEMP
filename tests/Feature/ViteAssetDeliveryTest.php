<?php

namespace Tests\Feature;

use Illuminate\Foundation\Vite;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class ViteAssetDeliveryTest extends TestCase
{
    public function test_remote_dashboard_uses_compiled_same_origin_css_when_a_stale_vite_hot_file_exists(): void
    {
        $hotFile = public_path('hot');
        $hadHotFile = is_file($hotFile);
        $originalHotFile = $hadHotFile ? file_get_contents($hotFile) : null;

        try {
            File::ensureDirectoryExists(dirname($hotFile));
            file_put_contents($hotFile, 'http://127.0.0.1:5173');

            $this->signInAsOwner();

            $response = $this
                ->withServerVariables(['REMOTE_ADDR' => '192.168.137.20'])
                ->get('/dashboard');

            $response->assertOk();
            $response->assertDontSee('http://127.0.0.1:5173');
            $response->assertSee('/build/assets/', false);
            $response->assertSee('app-', false);
            $response->assertSee('zazu-dashboard-authority-', false);

            $this->assertFalse(app(Vite::class)->isRunningHot());
            $this->assertSame(
                storage_path('framework/zazu/remote-vite-hot-disabled'),
                app(Vite::class)->hotFile(),
            );
        } finally {
            if ($hadHotFile) {
                file_put_contents($hotFile, (string) $originalHotFile);
            } else {
                @unlink($hotFile);
            }

            app(Vite::class)->useHotFile(public_path('hot'));
        }
    }

    public function test_loopback_dashboard_requests_keep_local_vite_hmr_enabled(): void
    {
        $hotFile = public_path('hot');
        $hadHotFile = is_file($hotFile);
        $originalHotFile = $hadHotFile ? file_get_contents($hotFile) : null;

        try {
            File::ensureDirectoryExists(dirname($hotFile));
            file_put_contents($hotFile, 'http://127.0.0.1:5173');

            $this
                ->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
                ->get('/');

            $this->assertSame(public_path('hot'), app(Vite::class)->hotFile());
            $this->assertTrue(app(Vite::class)->isRunningHot());
        } finally {
            if ($hadHotFile) {
                file_put_contents($hotFile, (string) $originalHotFile);
            } else {
                @unlink($hotFile);
            }

            app(Vite::class)->useHotFile(public_path('hot'));
        }
    }

    public function test_compiled_visual_stylesheets_exist_and_contain_the_active_zazu_design_rules(): void
    {
        $manifestPath = public_path('build/manifest.json');

        $this->assertFileExists($manifestPath);
        $manifest = json_decode((string) file_get_contents($manifestPath), true, 512, JSON_THROW_ON_ERROR);

        $requiredStylesheets = [
            'resources/css/app.css',
            'resources/css/zazu-responsive-theme.css',
            'resources/css/zazu-mobile-refinement.css',
            'resources/css/zazu-final-visual-sweep.css',
            'resources/css/zazu-dashboard-authority.css',
            'resources/css/zazu-product-authority.css',
        ];

        foreach ($requiredStylesheets as $entry) {
            $this->assertArrayHasKey($entry, $manifest, "The Vite manifest is missing {$entry}.");
            $assetPath = public_path('build/'.$manifest[$entry]['file']);
            $this->assertFileExists($assetPath, "The compiled stylesheet for {$entry} is missing.");
            $this->assertGreaterThan(1000, filesize($assetPath), "The compiled stylesheet for {$entry} is unexpectedly small.");
        }

        $dashboardEntry = $manifest['resources/css/zazu-dashboard-authority.css'];
        $dashboardCss = (string) file_get_contents(public_path('build/'.$dashboardEntry['file']));
        $this->assertStringContainsString('.zazu-dashboard-page', $dashboardCss);
        $this->assertStringContainsString('.zazu-dash-hero', $dashboardCss);
        $this->assertStringContainsString('zazu-dash-metric', $dashboardCss);

        $appEntry = $manifest['resources/css/app.css'];
        $appCss = (string) file_get_contents(public_path('build/'.$appEntry['file']));
        $this->assertStringContainsString('.zazu-shell', $appCss);
    }
}
