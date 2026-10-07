<?php

namespace Tests\Feature;

use Tests\TestCase;

class OfflineAssetCachePolicyTest extends TestCase
{
    public function test_service_worker_static_asset_policy_remains_scoped_to_known_public_paths(): void
    {
        $source = file_get_contents(public_path('sw.js'));

        $this->assertIsString($source);
        $this->assertMatchesRegularExpression("/const CACHE_NAME = 'zazu-static-v\\d+';/", $source);
        $this->assertStringContainsString("path.startsWith('/build/')", $source);
        $this->assertStringContainsString("'/images/landing/stock/hero.jpg'", $source);
        $this->assertStringContainsString("'/offline-attachments.js'", $source);
        $this->assertStringContainsString("'/images/landing/stock/catering.jpg'", $source);
        $this->assertStringContainsString("'/images/landing/stock/sound.jpg'", $source);
        $this->assertStringContainsString("'/images/landing/stock/venue.jpg'", $source);
        $this->assertStringContainsString("'/images/landing/stock/tent.jpg'", $source);
        $this->assertStringContainsString("'/images/landing/stock/decor.jpg'", $source);
        $this->assertStringContainsString("path.startsWith('/images/')", $source);
        $this->assertStringNotContainsString("['style', 'script', 'font', 'image'].includes(request.destination)", $source);
        $this->assertStringNotContainsString("path.startsWith('/media/')", $source);
        $this->assertStringNotContainsString("return cachedPage || caches.match(OFFLINE_SHELL);", $source);
        $this->assertStringContainsString("async function clearActiveUser()", $source);
    }
}
