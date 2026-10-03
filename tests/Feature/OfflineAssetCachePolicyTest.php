<?php

namespace Tests\Feature;

use Tests\TestCase;

class OfflineAssetCachePolicyTest extends TestCase
{
    public function test_service_worker_only_caches_public_static_asset_paths(): void
    {
        $source = file_get_contents(public_path('sw.js'));

        $this->assertIsString($source);
        $this->assertStringContainsString("const CACHE_NAME = 'zazu-static-v2';", $source);
        $this->assertStringContainsString("path.startsWith('/build/')", $source);
        $this->assertStringContainsString("path.startsWith('/images/')", $source);
        $this->assertStringNotContainsString("['style', 'script', 'font', 'image'].includes(request.destination)", $source);
        $this->assertStringNotContainsString("path.startsWith('/media/')", $source);
    }
}
