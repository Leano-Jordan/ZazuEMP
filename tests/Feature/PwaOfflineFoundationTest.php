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
            ->assertSee('/zazu-offline.js')
            ->assertSee('data-zazu-user-id="', false);

        $html = html_entity_decode($response->getContent(), ENT_QUOTES | ENT_HTML5);
        $this->assertStringNotContainsString("navigator.serviceWorker.register('/sw.js')", $html);
        $this->assertStringNotContainsString('zazu-phone-workspace', $html);

        $app = File::get(resource_path('js/app.js'));
        $this->assertStringContainsString("navigator.serviceWorker.register('/sw.js', { scope: '/' })", $app);
        $this->assertStringContainsString("target?.postMessage({ type: 'prime-pages', userId, routes })", $app);
        $this->assertStringContainsString('window.ZazuOffline.pair(code)', $app);
    }

    public function test_service_worker_uses_the_real_zazu_shell_and_cached_pages_for_offline_navigation(): void
    {
        $worker = File::get(public_path('sw.js'));

        $this->assertStringContainsString("const CACHE_NAME = 'zazu-static-v11';", $worker);
        $this->assertStringContainsString("const OFFLINE_SHELL = '/dashboard';", $worker);
        $this->assertStringContainsString("OFFLINE_SHELL,", $worker);
        $this->assertStringContainsString("'/offline-attachments.js'", $worker);
        $this->assertStringContainsString("'/zazu-offline.js'", $worker);
        $this->assertStringContainsString("const acceptsHtml = (request.headers.get('accept') || '').includes('text/html');", $worker);
        $this->assertStringContainsString("const isAppPage = request.method === 'GET'", $worker);
        $this->assertStringContainsString('await cache.match(new Request(request.url))', $worker);
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

    public function test_existing_zazu_create_forms_are_marked_for_local_mutation_queueing(): void
    {
        $customer = File::get(resource_path('views/customers/create.blade.php'));
        $job = File::get(resource_path('views/work/create.blade.php'));
        $quote = File::get(resource_path('views/quotes/create.blade.php'));

        $this->assertStringContainsString('data-zazu-offline-entity="customer"', $customer);
        $this->assertStringContainsString('data-zazu-offline-entity="job"', $job);
        $this->assertStringContainsString('data-zazu-offline-entity="quote"', $quote);
        $this->assertStringContainsString('data-zazu-offline-job-id=', $quote);

        $expense = File::get(resource_path('views/finance/expense-create.blade.php'));
        $payment = File::get(resource_path('views/finance/payment-create.blade.php'));
        $preparation = File::get(resource_path('views/preparation/create.blade.php'));
        $cost = File::get(resource_path('views/costs/create.blade.php'));

        $this->assertStringContainsString('data-zazu-offline-entity="expense"', $expense);
        $this->assertStringContainsString('data-zazu-offline-entity="payment"', $payment);
        $this->assertStringContainsString('data-zazu-offline-entity="preparation"', $preparation);
        $this->assertStringContainsString('data-zazu-offline-entity="event_cost"', $cost);
        $this->assertStringContainsString("entity === 'expense'", File::get(resource_path('js/app.js')));
        $this->assertStringContainsString("entity === 'payment'", File::get(resource_path('js/app.js')));
        $this->assertStringContainsString("entity === 'preparation'", File::get(resource_path('js/app.js')));
        $this->assertStringContainsString("entity === 'event_cost'", File::get(resource_path('js/app.js')));

        $service = File::get(resource_path('views/capabilities/create.blade.php'));
        $inventory = File::get(resource_path('views/inventory/index.blade.php'));
        $app = File::get(resource_path('js/app.js'));

        $this->assertStringContainsString('data-zazu-offline-entity="service"', $service);
        $this->assertStringContainsString('data-zazu-offline-entity="inventory_movement"', $inventory);
        $this->assertStringContainsString("entity === 'service'", $app);
        $this->assertStringContainsString("entity === 'inventory_movement'", $app);
        $this->assertStringContainsString("capability_type", File::get(app_path('Support/Offline/OfflineDomainMutationHandler.php')));
        $this->assertStringContainsString("pricing_basis", File::get(app_path('Support/Offline/OfflineDomainMutationHandler.php')));
    }

    public function test_shared_zazu_local_client_is_real_application_infrastructure_not_a_second_workspace(): void
    {
        $client = File::get(public_path('zazu-offline.js'));

        $this->assertStringContainsString("const DB_NAME = 'zazu-client';", $client);
        $this->assertStringContainsString("window.ZazuOffline =", $client);
        $this->assertStringContainsString("async function pair(pairingCode)", $client);
        $this->assertStringContainsString("async function sync()", $client);
        $this->assertStringContainsString("/api/sync/push", $client);
        $this->assertStringContainsString("/api/sync/pull?stream=business&limit=100", $client);
        $this->assertStringContainsString("/api/sync/acknowledge", $client);
        $this->assertStringContainsString("crypto.randomUUID()", $client);
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

    public function test_invoice_create_form_is_offline_enabled(): void
    {
        $view = file_get_contents(resource_path('views/finance/invoice-create.blade.php'));

        $this->assertStringContainsString('data-zazu-offline-entity="invoice"', $view);
        $this->assertStringContainsString('name="quote_id"', $view);
    }

    public function test_offline_route_no_longer_exposes_a_second_zazu_workspace(): void
    {
        $response = $this->get('/offline');
        $response->assertRedirect('/dashboard');
    }

}
