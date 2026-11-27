<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class PwaOfflineFoundationTest extends TestCase
{
    use RefreshDatabase;

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
        // Desktop installs must retain the native draggable title bar and keep OS controls clear of Zazu actions.
        $this->assertSame(['standalone'], $manifest['display_override']);
        $this->assertCount(2, $manifest['icons']);
        $this->assertSame('/icons/zazu-192.svg', $manifest['icons'][0]['src']);
        $this->assertSame('192x192', $manifest['icons'][0]['sizes']);
        $this->assertSame('/icons/zazu-512.svg', $manifest['icons'][1]['src']);
        $this->assertSame('512x512', $manifest['icons'][1]['sizes']);
    }

    public function test_real_zazu_shell_contains_install_metadata_and_service_worker_registration(): void
    {
        $this->signInAsOwner();
        $response = $this->get('/dashboard');

        $response->assertOk()
            ->assertSee('/manifest.webmanifest')
            ->assertSee('/zazu-offline.js')
            ->assertSee('data-zazu-user-id="', false)
            ->assertSee('data-zazu-business-id="', false);

        $html = html_entity_decode($response->getContent(), ENT_QUOTES | ENT_HTML5);
        $this->assertStringNotContainsString("navigator.serviceWorker.register('/sw.js')", $html);
        $this->assertStringNotContainsString('zazu-phone-workspace', $html);

        $app = File::get(resource_path('js/app.js'));
        $worker = File::get(public_path('sw.js'));
        $client = File::get(public_path('zazu-offline.js'));
        $workspaceOpen = substr($client, strpos($client, 'function openDb('), strpos($client, 'async function readState()') - strpos($client, 'function openDb('));
        $this->assertStringContainsString('new BroadcastChannel(\'zazu-offline-session-\' + ACTIVE_BUSINESS_ID)', $client);
        $this->assertStringContainsString('db.onversionchange = () => {', $workspaceOpen);
        $this->assertStringContainsString('for an older tab to release its connection', $workspaceOpen);
        $this->assertStringNotContainsString('request.onblocked = () => reject', $workspaceOpen);
        $attachmentOpen = substr($client, strpos($client, 'async function openAttachmentDb()'), strpos($client, 'async function countPendingAttachments()') - strpos($client, 'async function openAttachmentDb()'));
        $this->assertStringContainsString('db.onversionchange = () => db.close();', $attachmentOpen);
        $this->assertStringContainsString('Do not reject prematurely.', $attachmentOpen);
        $this->assertStringNotContainsString('request.onblocked = () => reject', $attachmentOpen);
        $this->assertStringContainsString("navigator.serviceWorker.register('/sw.js',", $app);
        $this->assertStringContainsString("scope: '/',", $app);
        $this->assertStringContainsString("target.postMessage({ type: 'prime-pages', userId, businessId, routes }, [channel.port2])", $app);
        $this->assertStringContainsString("event.data?.type !== 'prime-pages-complete'", $app);
        $this->assertStringContainsString("zazuOfflinePagesPrimed = event.data.success ? 'true' : 'false'", $app);
        $this->assertStringContainsString("event.ports?.[0]?.postMessage({ type: 'prime-pages-complete', success: completed })", $worker);
        $this->assertStringContainsString("const requiredOfflineRoutes = ['/dashboard', '/customers', '/customers/create', '/work', '/work/create'];", $worker);
        $this->assertStringContainsString('requiredCachedPages.every(Boolean)', $worker);
        $this->assertStringContainsString('const requiredCachedPages = await Promise.all(requiredOfflineRoutes.map', $worker);
        $this->assertStringContainsString('let activePrimeTask = null;', $worker);
        $this->assertStringContainsString('activePrimeTaskContext === taskContext', $worker);
        $this->assertStringContainsString('activePrimeTask = null;', $worker);
        $this->assertStringContainsString('activePrimeTaskContext = null;', $worker);
        $this->assertStringContainsString('await cache.put(pageRequest, response.clone());', $worker);
        $this->assertStringContainsString('await primeStaticAssetsFromHtml(html, primeGeneration, primedStaticAssetUrls, controller.signal);', $worker);
        $this->assertStringNotContainsString('Real navigation takes priority over background cache warming', $worker);
        $this->assertStringContainsString('responseUrl.pathname !== requestedUrl.pathname', $worker);
        $this->assertSame(1, substr_count($app, 'primeInstalledPages(ready)'));
        $this->assertStringNotContainsString('primeInstalledPages(registration)', $app);
        $this->assertStringNotContainsString('handleZazuPhonePairing', $app);
        $this->assertStringNotContainsString('data-zazu-phone-pairing', $html);
        $this->assertStringContainsString("'/customers/create'", $app);
        $this->assertStringContainsString("'/work/create'", $app);
        $this->assertStringContainsString('Offline workspace is not fully prepared.', $app);
        $this->assertStringNotContainsString("document.querySelectorAll('a[href]')", $app);
        $layout = File::get(resource_path('views/components/app-layout.blade.php'));
        $offlineScriptPosition = strpos($layout, '<script src="/zazu-offline.js" defer></script>');
        $viteScriptPosition = strpos($layout, "@vite(['resources/css/app.css'");
        $this->assertNotFalse($offlineScriptPosition);
        $this->assertNotFalse($viteScriptPosition);
        $this->assertLessThan($viteScriptPosition, $offlineScriptPosition);
        $this->assertStringContainsString('<script src="/zazu-offline.js" defer></script>', $html);
        $this->assertStringContainsString('window.__ZAZU_OFFLINE_DEFER_INITIAL_SYNC !== true', $client);
        $boot = substr($client, strpos($client, 'async function boot()'));
        $readyPosition = strpos($boot, 'resolveReady(state);');
        $syncPosition = strpos($boot, 'sync().catch');
        $this->assertNotFalse($readyPosition);
        $this->assertNotFalse($syncPosition);
        $this->assertLessThan($syncPosition, $readyPosition);
        $this->assertStringNotContainsString('canReachZazuLogoutServer', $app);
        $this->assertStringNotContainsString("fetch('/up'", $app);
        $this->assertStringContainsString('async function handleZazuLogout(form)', $app);
        $logoutHandler = substr($app, strpos($app, 'async function handleZazuLogout(form)'));
        $this->assertStringContainsString("method: 'POST'", $logoutHandler);
        $this->assertStringContainsString('body: new FormData(form)', $logoutHandler);
        $this->assertStringContainsString("redirect: 'follow'", $logoutHandler);
        $this->assertStringContainsString('response.redirected', $logoutHandler);
        $this->assertStringContainsString('could not reach the server to end your session', $logoutHandler);
        $this->assertStringContainsString('business-scoped offline workspace', $logoutHandler);
        $this->assertStringNotContainsString('offline.assessLogout()', $logoutHandler);
        $this->assertStringNotContainsString('offline.clearLocalSession()', $logoutHandler);
        $this->assertStringContainsString("target?.postMessage({ type: 'clear-user' })", $app);
    }

    public function test_service_worker_uses_the_real_zazu_shell_and_cached_pages_for_offline_navigation(): void
    {
        $worker = File::get(public_path('sw.js'));

        $this->assertMatchesRegularExpression("/const CACHE_NAME = 'zazu-static-v\\d+';/", $worker);
        $this->assertStringContainsString("const OFFLINE_SHELL = '/dashboard';", $worker);
        $this->assertStringContainsString('const timeout = setTimeout(() => controller.abort(), timeoutMs);', $worker);
        $this->assertStringContainsString('async function fetchWithTimeout(request, timeoutMs = 5000)', $worker);
        $this->assertStringContainsString('fetchWithTimeout(request)', $worker);
        $this->assertStringContainsString('OFFLINE_SHELL,', $worker);
        $this->assertStringContainsString("'/zazu-offline.js'", $worker);
        $this->assertMatchesRegularExpression("/const CACHE_NAME = 'zazu-static-v[0-9]+';/", $worker);
        $this->assertStringContainsString('await cache.put(request, response.clone());', $worker);
        $this->assertStringContainsString('const cached = await caches.match(request);', $worker);
        $this->assertStringContainsString('if (cached) return cached;', $worker);
        $this->assertStringContainsString("url.pathname === '/zazu-offline.js'", $worker);
        $this->assertStringContainsString('const cached = await cache.match(cacheKey);', $worker);
        $this->assertStringContainsString('if (cached) return cached;', $worker);
        $this->assertStringContainsString("const acceptsHtml = (request.headers.get('accept') || '').includes('text/html');", $worker);
        $this->assertStringContainsString("const isAppPage = request.method === 'GET'", $worker);
        $this->assertStringContainsString('await cache.match(new Request(request.url))', $worker);
        $this->assertStringContainsString('caches.match(request)', $worker);
        $this->assertStringNotContainsString('cache.put(request, copy)', $worker);
        $this->assertStringContainsString("event.data?.type === 'clear-user'", $worker);
        $this->assertStringContainsString('async function clearActiveContext()', $worker);
        $this->assertStringContainsString('let activeUserGeneration = 0;', $worker);
        $this->assertStringContainsString('requestGeneration !== activeUserGeneration', $worker);
        $this->assertStringContainsString("url.pathname === '/logout'", $worker);
        $logoutBoundary = substr($worker, strpos($worker, "url.pathname === '/logout'"), strpos($worker, "url.pathname === '/business/switch'") - strpos($worker, "url.pathname === '/logout'"));
        $this->assertStringContainsString('event.respondWith((async () => {', $logoutBoundary);
        $this->assertStringContainsString('const response = await fetch(request);', $logoutBoundary);
        $this->assertStringContainsString('if (response.ok && response.redirected) await clearActiveContext();', $logoutBoundary);
        $this->assertStringNotContainsString('event.waitUntil(clearActiveContext())', $logoutBoundary);
        $this->assertStringContainsString("url.pathname === '/business/switch'", $worker);
        $this->assertStringContainsString('data-zazu-business-id', $worker);
        $this->assertStringContainsString('cacheRenderedPage(request, response.clone(), requestGeneration)', $worker);
        $this->assertStringContainsString('async function setContext(userId, businessId, expectedGeneration = null)', $worker);
        $this->assertStringContainsString('async function loadActiveContext(expectedGeneration = activeUserGeneration)', $worker);
        $this->assertStringContainsString('await loadActiveContext(requestGeneration);', $worker);
        $this->assertStringContainsString('if (expectedGeneration !== activeUserGeneration) return null;', $worker);
        $this->assertStringContainsString('activePrimeController?.abort()', $worker);
        $this->assertStringContainsString('const offlineShell = cache ? await cache.match(new Request(new URL(OFFLINE_SHELL, self.location.origin))) : null;', $worker);
        $this->assertStringNotContainsString('return cachedPage || caches.match(OFFLINE_SHELL);', $worker);
        $this->assertStringContainsString("event.data?.type !== 'prime-pages'", $worker);
        $this->assertStringContainsString('const MAX_PRIME_ROUTES = 100;', $worker);
        $this->assertStringContainsString('const PRIME_CONCURRENCY = 3;', $worker);
        $this->assertStringContainsString('async function migratePreviousCaches()', $worker);
        $this->assertStringContainsString('await target.put(request, response.clone())', $worker);
        $this->assertStringContainsString('uniqueRoutes.length >= MAX_PRIME_ROUTES', $worker);
        $this->assertStringContainsString('addRoute(match[1].replace(/&amp;/g,', $worker);
        $this->assertStringContainsString('for (let offset = 0; offset < uniqueRoutes.length; offset += PRIME_CONCURRENCY)', $worker);
        $this->assertStringContainsString('function contextCacheName()', $worker);
        $this->assertStringContainsString("const ACTIVE_CONTEXT_STATE = new Request('/__zazu-active-context__');", $worker);
        $this->assertStringContainsString('data-zazu-user-id="(\\d+)"', $worker);
        $this->assertStringContainsString("CACHE_NAME + '-user-' + activeUserId + '-business-' + activeBusinessId", $worker);
        $this->assertStringContainsString('await cache.put(pageRequest, response.clone())', $worker);
    }

    public function test_disconnected_navigation_preserves_the_normal_application_and_marks_only_real_fallbacks(): void
    {
        $worker = File::get(public_path('sw.js'));
        $app = File::get(resource_path('js/app.js'));

        $this->assertStringContainsString('async function cachedAppPage(request)', $worker);
        $this->assertStringContainsString('if (cachedPage) return cachedPage;', $worker);
        $this->assertStringContainsString('return offlineShell ? markOfflineFallback(offlineShell) : Response.error();', $worker);
        $this->assertStringContainsString('async function markOfflineFallback(response)', $worker);
        $this->assertStringContainsString('zazu-offline-fallback', $worker);
        $this->assertStringContainsString('const isFallbackDocument = document.querySelector(', $app);
        $this->assertStringContainsString('workspace.hidden = true;', $app);
        $this->assertStringContainsString('data-zazu-offline-route-unavailable', $app);
        $this->assertStringContainsString('This page is not saved on this device yet', $app);
        $this->assertStringContainsString("retry.addEventListener('click', () => window.location.reload());", $app);
        $this->assertStringNotContainsString('const offlineRouteViews = [', $app);
        $this->assertStringNotContainsString('workspace.dataset.zazuOfflineActiveView = activeView;', $app);
        $this->assertStringNotContainsString('workspace.dataset.zazuOfflineActiveView = \'native-page\';', $app);
        $this->assertStringNotContainsString('.zazu-content > :not([data-zazu-offline-workspace])', $app);

        $this->assertStringContainsString('const uniqueRoutes = [];', $worker);
        $this->assertStringContainsString('uniqueRoutes.length >= MAX_PRIME_ROUTES', $worker);
        $this->assertStringContainsString('for (const match of html.matchAll(/<a\\b', $worker);
        $this->assertStringContainsString('addRoute(match[1].replace(/&amp;/g, \'&\'));', $worker);
        $this->assertStringContainsString('const routes = new Set([', $app);
        $this->assertStringContainsString("document.querySelectorAll('a[href]')", $app);
        $this->assertStringContainsString('routes: [...routes].slice(0, 100)', $app);
        $this->assertStringContainsString("window.addEventListener('popstate', applyOfflineRouteView);", $app);
        $this->assertStringContainsString('Zazu is offline', $app);
        $this->assertStringNotContainsString('Only supported offline screens can be used', $app);
    }


    public function test_offline_workspace_navigation_remains_available_in_offline_mode(): void
    {
        $styles = File::get(resource_path('css/app.css'));

        $this->assertStringNotContainsString(
            '.zazu-offline-mode [aria-label="Offline workspace navigation"]',
            $styles
        );
        $this->assertStringNotContainsString('.zazu-offline-mode .zazu-command-search', $styles);
        $this->assertStringNotContainsString('.zazu-offline-mode .zazu-section-tabs', $styles);
    }

    public function test_insecure_network_origins_explain_the_offline_installation_requirement(): void
    {
        $app = File::get(resource_path('js/app.js'));
        $styles = File::get(resource_path('css/app.css'));

        $this->assertStringContainsString('if (window.isSecureContext === false)', $app);
        $this->assertStringContainsString('showZazuOfflineOriginNotice();', $app);
        $this->assertStringContainsString('showZazuOfflineRegistrationNotice();', $app);
        $this->assertStringContainsString('Zazu could not enable offline app support.', $app);
        $this->assertStringContainsString('Until offline setup succeeds, this device may still need the server to open the app.', $app);
        $this->assertStringContainsString('This plain-HTTP network address depends on a reachable PC/server', $app);
        $this->assertStringContainsString('Use a secure HTTPS install address or a self-contained app build', $app);
        $this->assertStringContainsString('.zazu-offline-origin-notice', $styles);
    }

    public function test_existing_zazu_create_forms_are_marked_for_local_mutation_queueing(): void
    {
        $customer = File::get(resource_path('views/customers/create.blade.php'));
        $job = File::get(resource_path('views/work/create.blade.php'));
        $quote = File::get(resource_path('views/quotes/create.blade.php'));

        $this->assertStringContainsString('data-zazu-offline-entity="customer"', $customer);
        $this->assertStringContainsString('data-zazu-offline-entity="job"', $job);
        $this->assertStringContainsString('<button type="submit" class="zazu-btn zazu-btn-primary">', $job);
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

        $quoteShow = File::get(resource_path('views/quotes/show.blade.php'));
        $purchaseShow = File::get(resource_path('views/purchasing/show.blade.php'));
        $assets = File::get(resource_path('views/assets/index.blade.php'));
        $capabilityEdit = File::get(resource_path('views/capabilities/edit.blade.php'));
        $app = File::get(resource_path('js/app.js'));

        $this->assertStringContainsString('data-zazu-offline-entity="quote_acceptance"', $quoteShow);
        $this->assertStringContainsString('data-zazu-offline-quote-id=', $quoteShow);
        $this->assertStringContainsString('data-zazu-offline-entity="purchase_receipt"', $purchaseShow);
        $this->assertStringContainsString('data-zazu-offline-purchase-order-id=', $purchaseShow);
        $this->assertStringContainsString('data-zazu-offline-entity="asset_allocation"', $assets);
        $this->assertStringContainsString('data-zazu-offline-allocation-id=', $assets);
        $this->assertStringContainsString('data-zazu-offline-entity="service"', $capabilityEdit);
        $this->assertStringContainsString("entity === 'quote_acceptance'", $app);
        $this->assertStringContainsString("entity === 'purchase_receipt'", $app);
        $this->assertStringContainsString("entity === 'asset_allocation'", $app);
        $this->assertStringContainsString("const entitiesRequiringName = ['customer', 'job', 'supplier', 'inventory_item', 'asset', 'service'];", $app);
        $this->assertStringContainsString('invoice.payments = invoice.payments || [];', $app);
        $this->assertStringContainsString('const remaining = toHundredths(item.quantity) - toHundredths(item.received_quantity);', $app);
        $this->assertStringContainsString('if (incoming > remaining)', $app);
        $this->assertStringContainsString('const sameCapability = capabilityId === null', $app);
        $this->assertStringContainsString('inventoryItem.on_hand = Number(((onHandHundredths + quantityHundredths) / 100).toFixed(2));', $app);
        $this->assertStringContainsString("entity === 'purchase_receipt'", $app);
        $this->assertStringContainsString('Illuminate\\Support\\Str::uuid()', $purchaseShow);

        $inventory = File::get(resource_path('views/inventory/index.blade.php'));

        $this->assertStringContainsString('data-zazu-offline-entity="inventory_movement"', $inventory);
        $this->assertStringContainsString("entity === 'service'", $app);
        $this->assertStringContainsString("entity === 'inventory_movement'", $app);
        $this->assertStringContainsString('capability_type', File::get(app_path('Support/Offline/OfflineDomainMutationHandler.php')));
        $this->assertStringContainsString('pricing_basis', File::get(app_path('Support/Offline/OfflineDomainMutationHandler.php')));
    }

    public function test_shared_zazu_local_client_is_real_application_infrastructure_not_a_second_workspace(): void
    {
        $app = File::get(resource_path('js/app.js'));
        $client = File::get(public_path('zazu-offline.js'));

        $this->assertStringContainsString("const LEGACY_DB_NAME = 'zazu-client';", $client);
        $this->assertStringContainsString('const DB_NAME = ACTIVE_BUSINESS_ID > 0', $client);
        $this->assertStringContainsString('async function migrateLegacyWorkspace(targetDb)', $client);
        $this->assertStringContainsString('window.ZazuOffline =', $client);
        $this->assertStringContainsString('async function pair(pairingCode)', $client);
        $this->assertStringContainsString('async function assessLogout()', $client);
        $this->assertStringContainsString('async function clearLocalSession()', $client);
        $this->assertStringContainsString("'zazu-phone-attachments-business-' + ACTIVE_BUSINESS_ID", $client);
        $this->assertStringContainsString('async function countPendingAttachments()', $client);
        $this->assertStringContainsString('async function clearAttachmentWorkspace()', $client);
        $this->assertStringContainsString('await clearAttachmentWorkspace();', $client);
        $this->assertStringContainsString('attachmentPendingCount', $client);
        $this->assertStringContainsString('async function queueAttachment(eventId, file, description', $client);
        $this->assertStringContainsString('async function queueAttachments(eventId, files, description = \'\', localEventId = null)', $client);
        $this->assertStringContainsString('event_local_id: targetLocalEventId', $client);
        $this->assertStringContainsString('async function loadAttachments(eventId, localEventId = null)', $client);
        $this->assertStringContainsString('        queueAttachments,', $client);
        $this->assertStringContainsString('window.ZazuOffline.queueAttachments(null, files, \'\', job.local_id)', $app);
        $this->assertStringContainsString('attachmentInput.dataset.zazuLocalJobAttachments = job.local_id;', $app);
        $this->assertStringContainsString("attach.textContent = 'Attach files';", $app);
        $this->assertStringContainsString('async function syncAttachments()', $client);
        $this->assertStringContainsString('async function reconcileAttachmentEventIds()', $client);
        $this->assertStringContainsString('record.event_local_id', $client);
        $this->assertStringContainsString("const job = jobs.find(item => String(item.local_id || '') === String(record.event_local_id));", $client);
        $this->assertStringContainsString('async function loadAttachments(eventId, localEventId = null)', $client);
        $this->assertStringContainsString('async function getAttachmentFile(idempotencyKey)', $client);
        $this->assertStringContainsString('await syncAttachments();', $client);
        $this->assertStringContainsString('confirmedEventId !== Number(record.event_id)', $client);
        $this->assertStringContainsString('confirmedKey !== record.idempotency_key', $client);
        $this->assertStringContainsString('attachmentEventId !== targetEventId', $client);
        $this->assertStringContainsString("'/api/sync/attachments'", $client);

        $attachmentView = File::get(resource_path('views/work/attachments.blade.php'));
        $this->assertStringContainsString('data-zazu-offline-attachment-form', $attachmentView);
        $this->assertStringContainsString('data-zazu-offline-attachment-list', $attachmentView);
        $this->assertStringContainsString('data-zazu-offline-attachment-status', $attachmentView);
        $this->assertStringContainsString('data-zazu-server-attachment-ids', $attachmentView);
        $this->assertStringContainsString('setupZazuOfflineAttachments();', $app);
        $this->assertStringContainsString('client.queueAttachments(eventId, files,', $app);
        $this->assertStringContainsString('const queueSessionGeneration = sessionGeneration;', $client);
        $this->assertStringContainsString('sessionGeneration !== queueSessionGeneration', $client);
        $this->assertStringContainsString('The device session changed before these attachments could be saved.', $client);
        $this->assertStringContainsString('client.loadAttachments(eventId)', $app);
        $this->assertStringContainsString('client.getAttachmentFile(record.idempotency_key)', $app);
        $this->assertStringContainsString("fetch('/api/sync/revoke'", $client);
        $this->assertStringContainsString('const result = await revokeDeviceToken(token);', $client);
        $this->assertStringContainsString('rememberPendingDeviceRevocation(installationId)', $client);
        $this->assertStringContainsString('retryPendingDeviceRevocation()', $client);
        $this->assertStringContainsString('let activeSyncController = null;', $client);
        $this->assertStringContainsString('activeSyncController?.abort();', $client);
        $this->assertStringContainsString('sessionCleared || !state?.sync?.token', $client);
        $this->assertStringContainsString('tx.objectStore(QUEUE).clear();', $client);
        $this->assertStringContainsString("new BroadcastChannel('zazu-offline-session-' + ACTIVE_BUSINESS_ID)", $client);
        $this->assertStringContainsString('sessionCleared', $client);
        $this->assertStringContainsString('requestToken !== (state?.sync?.token || null)', $client);
        $this->assertStringContainsString('activeSyncController?.abort();', $client);
        $this->assertStringContainsString('async function sync()', $client);
        $this->assertStringContainsString('/api/sync/push', $client);
        $this->assertStringContainsString('/api/sync/pull?stream=business&limit=100', $client);
        $this->assertStringContainsString("deliveryQuery.append('delivery_sequences[]', String(sequence));", $client);
        $this->assertStringContainsString("'/api/sync/bootstrap?' + deliveryQueryString", $client);
        $syncController = File::get(app_path('Http/Controllers/SyncController.php'));
        $bootstrapBuilder = File::get(app_path('Support/Offline/SyncBootstrapPayloadBuilder.php'));
        $this->assertStringContainsString("'delivery_sequences' => ['sometimes', 'array', 'max:100']", $syncController);
        $this->assertStringContainsString('SyncBootstrapDeliveryTargetResolver::class)->resolve($device, $deliverySequences)', $bootstrapBuilder);
        $deliveryTargetResolver = File::get(app_path('Support/Offline/SyncBootstrapDeliveryTargetResolver.php'));
        $this->assertStringContainsString('where(\'destination_device_id\', $device->id)', $deliveryTargetResolver);
        $this->assertStringContainsString('where(\'business_id\', $device->business_id)', $deliveryTargetResolver);
        $this->assertStringContainsString('$this->applyBoundedWorkingSet($query, Supplier::class, $business, $includedIds', $bootstrapBuilder);
        $this->assertStringContainsString('/api/sync/acknowledge', $client);
        $this->assertStringContainsString("status: 'failed'", $client);
        $this->assertOfflineMutationAcknowledgementIsAtomic($client);
        $this->assertStringContainsString('crypto.randomUUID()', $client);
        $this->assertStringContainsString('Local offline state write was aborted; this change was not saved.', $client);
        $this->assertStringContainsString('Offline queue acknowledgement was aborted.', $client);
        $app = File::get(resource_path('js/app.js'));
        $this->assertStringContainsString('data-zazu-offline-save-error', $app);
        $this->assertStringContainsString('Check the offline queue before trying again.', $app);
        $this->assertStringContainsString('delete form.dataset.zazuSubmitting;', $app);
    }

    private function assertOfflineMutationAcknowledgementIsAtomic(string $client): void
    {
        $pushStart = strpos($client, 'async function push()');
        $pullStart = strpos($client, 'async function applyBootstrapIfQueueClean', $pushStart);
        $this->assertNotFalse($pushStart);
        $this->assertNotFalse($pullStart);
        $push = substr($client, $pushStart, $pullStart - $pushStart);
        $ackTransactionStart = strpos($push, "db.transaction([QUEUE, STATE], 'readwrite')");
        $this->assertNotFalse($ackTransactionStart);
        $acknowledgement = substr($push, $ackTransactionStart);
        $this->assertStringContainsString('const queueRequest = queueStore.getAll();', $acknowledgement);
        $this->assertStringContainsString('queueStore.delete(mutation.id);', $acknowledgement);
        $this->assertStringContainsString("stateStore.put(nextState, 'current');", $acknowledgement);
        $this->assertStringContainsString('const remainingMutations = [...queueById.values()]', $acknowledgement);
        $this->assertStringContainsString('nextState.local_dirty = remainingMutations.length > 0;', $acknowledgement);
        $this->assertFalse(
            str_contains($acknowledgement, 'await writeState();'),
            'Acknowledgement state must not depend on a second state-only transaction.',
        );
        $this->assertStringContainsString('sameRecordStillPending', $acknowledgement);
        $this->assertStringContainsString('if (record?.pending_sync === true) record.pending_sync = false;', $acknowledgement);
        $this->assertStringContainsString('remote.conflict_id', $acknowledgement);
    }

    public function test_phone_pairing_remains_owner_controlled_in_settings_not_on_the_dashboard(): void
    {
        $this->signInAsOwner();

        $dashboard = $this->get('/dashboard');
        $dashboard->assertOk()
            ->assertDontSee('data-zazu-phone-pairing', false)
            ->assertDontSee('Pair this phone');

        $settings = $this->get('/settings');
        $settings->assertOk()
            ->assertSee('data-zazu-generate-pairing', false)
            ->assertSee('data-zazu-pairing-result', false)
            ->assertSee('Generate phone pairing code')
            ->assertSee('12-digit code:');

        $client = File::get(public_path('zazu-offline.js'));
        $settingsView = File::get(resource_path('views/settings/index.blade.php'));
        $app = File::get(resource_path('js/app.js'));
        $worker = File::get(public_path('sw.js'));

        $this->assertStringContainsString('if (!/^\\d{12}$/.test(normalizedCode))', $client);
        $this->assertStringContainsString('pairing_code: normalizedCode', $client);
        $this->assertStringContainsString('/^\\d{12}$/', $client);
        $this->assertStringNotContainsString('slice(0, 6)', $client);
        $this->assertStringContainsString("route('settings.sync.pairing')", $settingsView);
        $this->assertStringNotContainsString('data-zazu-phone-pairing-submit', $app);
        $this->assertStringNotContainsString("window.prompt('Enter the 8-digit Zazu phone pairing code", $app);
        $this->assertStringContainsString('zazu-static-v66', $worker);
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
