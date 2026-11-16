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
}
