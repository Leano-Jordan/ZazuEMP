import { test, expect } from '@playwright/test';

const DEMO_EMAIL = process.env.ZAZU_DEMO_EMAIL || 'demo@zazu.local';
const DEMO_PASSWORD = process.env.ZAZU_DEMO_PASSWORD || 'password';
const MANAGER_EMAIL = process.env.ZAZU_MANAGER_EMAIL || 'operations@zazu.local';
const ROLE_PASSWORD = process.env.ZAZU_ROLE_PASSWORD || 'password';

async function login(page, email = DEMO_EMAIL, password = DEMO_PASSWORD) {
    await page.goto('/?auth=login', { waitUntil: 'domcontentloaded' });
    await expect(page.locator('[data-auth-modal]')).toBeVisible();
    await page.getByLabel('Username or email').fill(email);
    await page.locator('#auth_password').fill(password);
    await page.locator('[data-auth-submit]').click();
    await page.waitForURL(url => url.pathname !== '/login');
    await page.goto('/dashboard', { waitUntil: 'domcontentloaded' });
    await expect(page).toHaveURL((url) => new URL(url).pathname.endsWith('/dashboard'));
    await waitForOfflineRuntime(page);
}

async function waitForOfflineRuntime(page) {
    await expect.poll(
        () => page.evaluate(() => Boolean(window.ZazuOffline?.ready)),
        { message: 'offline runtime must be exposed before device-pairing assertions' },
    ).toBe(true);
    await page.evaluate(() => window.ZazuOffline.ready);
}

async function trustCurrentDevice(page) {
    await page.goto('/settings', { waitUntil: 'domcontentloaded' });
    const trustButton = page.getByRole('button', { name: 'Trust this device for offline sync' });
    await expect(trustButton).toBeVisible();
    await trustButton.click();
    await expect(page.getByRole('button', { name: 'This device is trusted' })).toBeVisible();
    await expect.poll(() => page.evaluate(() => window.ZazuOffline.isPaired())).toBe(true);
    // Leave the shared helper on the dashboard so callers start from a stable
    // authenticated route rather than inheriting the Settings page.
    await page.goto('/dashboard', { waitUntil: 'domcontentloaded' });
    await expect(page.getByRole('heading', { name: 'Dashboard' })).toBeVisible();
}

test.describe('Zazu installed PWA offline shell', () => {
    test('reports browser storage retention and requests persistence only after explicit action', async ({ page }, testInfo) => {
        test.skip(testInfo.project.name !== 'chromium', 'StorageManager permission contract runs once.');

        await login(page);
        await page.goto('/settings', { waitUntil: 'domcontentloaded' });

        const settingsPanel = page.locator('[data-zazu-offline-storage]');
        await expect(settingsPanel).toBeVisible();
        await page.evaluate(() => {
            Object.defineProperty(navigator, 'storage', {
                configurable: true,
                value: {
                    persisted: async () => false,
                    estimate: async () => ({ usage: 95 * 1024 * 1024, quota: 100 * 1024 * 1024 }),
                    persist: () => {
                        window.__zazuPersistRequestCount = (window.__zazuPersistRequestCount || 0) + 1;
                        return Promise.resolve(false);
                    },
                },
            });
            window.dispatchEvent(new CustomEvent('zazu:offline-state-updated'));
        });

        const status = settingsPanel.locator('[data-zazu-offline-storage-status]');
        const usage = settingsPanel.locator('[data-zazu-offline-storage-usage]');
        const request = settingsPanel.locator('[data-zazu-offline-storage-request]');

        await expect(status).toContainText('Persistent storage has not been granted');
        await expect(usage).toHaveText('Using 95.0 MB of an estimated 100 MB browser storage. Storage is nearing estimated capacity. Free device space without clearing Zazu site data before adding large attachments.');
        await expect(request).toBeVisible();

        await request.click();
        await expect(status).toContainText('The browser did not grant persistent storage');
        await expect.poll(() => page.evaluate(() => window.__zazuPersistRequestCount)).toBe(1);
        await expect(request).toBeEnabled();

        await page.evaluate(() => {
            Object.defineProperty(navigator, 'storage', {
                configurable: true,
                value: {
                    persisted: async () => true,
                    estimate: async () => ({ usage: 3 * 1024 * 1024, quota: 100 * 1024 * 1024 }),
                    persist: async () => true,
                },
            });
            window.dispatchEvent(new CustomEvent('zazu:offline-state-updated'));
        });
        await expect(status).toHaveText('Persistent storage is enabled for this browser.');
        await expect(usage).toContainText('Using 3.00 MB');
        await expect(request).toBeHidden();

        await page.evaluate(() => {
            Object.defineProperty(navigator, 'storage', {
                configurable: true,
                value: {
                    estimate: async () => ({ usage: 1024, quota: 2048 }),
                },
            });
            window.dispatchEvent(new CustomEvent('zazu:offline-state-updated'));
        });
        await expect(status).toHaveText('This browser does not support persistent storage requests.');
        await expect(request).toBeHidden();

        await page.goto('/dashboard', { waitUntil: 'domcontentloaded' });
        await page.evaluate(async () => {
            await window.ZazuOffline.ready;
            Object.defineProperty(navigator, 'storage', {
                configurable: true,
                value: {
                    estimate: async () => ({ usage: 1024, quota: 2048 }),
                },
            });
            const state = window.ZazuOffline.getState();
            state.local_dirty = true;
            await window.ZazuOffline.persist();
            window.dispatchEvent(new CustomEvent('zazu:offline-state-updated'));
        });
        const offlineWorkspace = page.locator('[data-zazu-offline-workspace]');
        await expect(offlineWorkspace).toBeVisible();
        await expect(offlineWorkspace.locator('[data-zazu-offline-storage-status]'))
            .toHaveText('This browser does not support persistent storage requests.');
    });

    test('surfaces quota failures without pretending the offline change was saved', async ({ page, context }, testInfo) => {
        test.skip(testInfo.project.name !== 'chromium', 'IndexedDB quota failure contract runs once.');

        await login(page);
        await context.setOffline(true);
        await expect(page.locator('body')).toHaveClass(/zazu-offline-mode/);
        await page.evaluate(async () => {
            await window.ZazuOffline.ready;
            const state = window.ZazuOffline.getState();
            state.local_dirty = true;
            await window.ZazuOffline.persist();

            const originalTransaction = IDBDatabase.prototype.transaction;
            let injected = false;
            IDBDatabase.prototype.transaction = function (storeNames, mode, options) {
                const names = Array.isArray(storeNames) ? storeNames : [storeNames];
                if (!injected && mode === 'readwrite'
                    && names.includes('state') && names.includes('mutations')) {
                    injected = true;
                    IDBDatabase.prototype.transaction = originalTransaction;
                    throw new DOMException('Injected browser storage quota failure.', 'QuotaExceededError');
                }
                return originalTransaction.call(this, storeNames, mode, options);
            };

            try {
                await window.ZazuOffline.queueMutation('customer', 'create', {
                    local_id: 'quota-boundary-test',
                    record: { name: 'Quota boundary test customer' },
                }, state);
                window.__zazuQuotaFailure = { injected, rejected: false };
            } catch (error) {
                window.__zazuQuotaFailure = {
                    injected,
                    rejected: true,
                    name: error.name,
                    message: error.message,
                };
            }
        });

        const outcome = await page.evaluate(() => window.__zazuQuotaFailure);
        expect(outcome.injected).toBe(true);
        expect(outcome.rejected).toBe(true);
        expect(outcome.name).toBe('QuotaExceededError');
        expect(outcome.message).toContain('Zazu could not save this change');

        const workspace = page.locator('[data-zazu-offline-workspace]');
        await expect(workspace).toBeVisible();
        const warning = workspace.locator('[data-zazu-offline-storage-error]');
        await expect(warning).toBeVisible();
        await expect(warning).toContainText('This device is low on browser storage');
        await expect(warning).toContainText('failed transaction was not committed');
    });

    test('refreshes the business-mix panel from the matching local profile while disconnected', async ({ page, context }, testInfo) => {
        test.skip(testInfo.project.name !== 'chromium', 'Local workspace profile presentation contract runs once.');

        await login(page);
        const businessId = Number(await page.locator('body').getAttribute('data-zazu-business-id'));
        const panel = page.locator('[data-zazu-workspace-profile]');
        await expect(panel).toBeVisible();

        await context.setOffline(true);
        await page.evaluate(async (id) => {
            await window.ZazuOffline.ready;
            const state = window.ZazuOffline.getState();
            state.business = { ...(state.business || {}), id };
            state.workspace_profile = {
                focus_labels: ['Sound & DJ'],
                catalogue_categories: ['Sound & entertainment', 'Audio & entertainment'],
                active_offerings: 3,
                active_rentals: 2,
            };
            await window.ZazuOffline.persist();
            if (window.ZazuOffline.getState().workspace_profile?.active_offerings !== 3) {
                throw new Error('The offline workspace profile was not durably updated.');
            }
            window.dispatchEvent(new CustomEvent('zazu:offline-state-updated'));
        }, businessId);

        await expect.poll(() => page.locator('[data-zazu-workspace-profile-count]').textContent())
            .toBe('3 active offerings');
        await expect(panel.locator('[data-zazu-workspace-profile-focuses]')).toHaveText('Sound & DJ');
        await expect(panel.locator('[data-zazu-workspace-profile-categories]')).toHaveText('Sound & entertainment · Audio & entertainment');

        // A profile belonging to another business must never replace this panel.
        await page.evaluate(async (id) => {
            const state = window.ZazuOffline.getState();
            state.business.id = id + 1;
            state.workspace_profile = {
                focus_labels: ['Foreign business profile'],
                catalogue_categories: ['Foreign category'],
                active_offerings: 99,
                active_rentals: 0,
            };
            await window.ZazuOffline.persist();
            window.dispatchEvent(new CustomEvent('zazu:offline-state-updated'));
        }, businessId);

        await expect(panel.locator('[data-zazu-workspace-profile-count]')).toHaveText('3 active offerings');
        await expect(panel.locator('[data-zazu-workspace-profile-focuses]')).toHaveText('Sound & DJ');
        await expect(panel.locator('[data-zazu-workspace-profile-categories]')).toHaveText('Sound & entertainment · Audio & entertainment');
    });

    test('local client becomes ready before an unresponsive initial sync', async ({ page }, testInfo) => {
        test.setTimeout(20_000);
        test.skip(testInfo.project.name !== 'chromium', 'Network-stall startup contract runs once; responsive viewports are covered separately.');

        await login(page);
        await page.evaluate(async () => {
            const state = window.ZazuOffline.getState();
            state.sync = { token: 'startup-readiness-test-token', device: { id: 999999 } };
            state.local_dirty = false;
            await window.ZazuOffline.persist({ allowSessionChange: true });
        });

        // Simulate a network request which never resolves instead of a fast 401/500.
        // A local-first boot must still resolve ready from IndexedDB.
        await page.addInitScript(() => {
            const originalFetch = window.fetch.bind(window);
            window.fetch = (input, init) => {
                const rawUrl = typeof input === 'string' ? input : input?.url;
                const url = new URL(rawUrl, window.location.href);
                if (url.pathname === '/api/sync/pull') return new Promise(() => {});
                return originalFetch(input, init);
            };
        });

        await page.reload({ waitUntil: 'domcontentloaded' });
        await expect(page.getByRole('heading', { name: 'Dashboard' })).toBeVisible();

        const readiness = await Promise.race([
            page.evaluate(() => window.ZazuOffline.ready.then(() => 'ready')),
            page.waitForTimeout(4_000).then(() => 'blocked'),
        ]);
        expect(readiness).toBe('ready');
    });

    test('detects a stopped Zazu host while Wi-Fi remains connected and exposes the local workspace', async ({ page }, testInfo) => {
        test.setTimeout(25_000);
        test.skip(!['chromium', 'chromium-mobile'].includes(testInfo.project.name), 'Origin-outage monitor contract runs on desktop and phone viewport.');

        let originAvailable = true;
        await page.route('**/up', async route => {
            await route.fulfill({
                status: originAvailable ? 200 : 503,
                contentType: 'text/plain',
                body: originAvailable ? 'ok' : 'unavailable',
            });
        });

        await login(page);
        expect(await page.evaluate(() => navigator.onLine)).toBe(true);

        const helperToggle = page.locator('[data-zazu-helper-toggle]');
        await helperToggle.click();
        const recommendation = page.locator('[data-zazu-helper-recommendation]');
        const attentionItem = page.locator('[data-zazu-helper-attention-item]').first();
        if (await attentionItem.count()) {
            await expect(recommendation).toBeVisible();
        } else {
            await expect(recommendation).toBeHidden();
        }
        await helperToggle.click();

        originAvailable = false;
        await page.evaluate(() => window.dispatchEvent(new Event('focus')));
        const notice = page.locator('[data-zazu-origin-offline-notice]');
        await expect(notice).toBeVisible({ timeout: 15_000 });
        await expect(notice).toContainText('Zazu server is unavailable');
        await expect(notice).toContainText('Only supported offline screens can be used');
        await expect(notice.getByRole('link', { name: 'Open offline workspace' })).toBeHidden();
        await expect(page.locator('[data-zazu-offline-workspace]')).toBeVisible();

        await page.locator('[data-zazu-helper-toggle]').click();
        const helperConnectivity = page.locator('[data-zazu-helper-connectivity]');
        await expect(helperConnectivity).toBeVisible();
        await expect(helperConnectivity).toContainText('Job recommendations are paused');
        await expect(page.locator('[data-zazu-helper-recommendation]')).toBeHidden();

        originAvailable = true;
        await page.evaluate(() => window.dispatchEvent(new Event('focus')));
        await expect(notice).toBeHidden({ timeout: 15_000 });
        await expect(helperConnectivity).toBeHidden({ timeout: 15_000 });
    });

    test('renders one route-specific offline view and never expands every collection into the dashboard', async ({ page }, testInfo) => {
        test.setTimeout(25_000);
        test.skip(!['chromium', 'chromium-mobile'].includes(testInfo.project.name), 'Offline route rendering contract runs on desktop and phone viewport.');

        await page.setViewportSize({ width: 390, height: 844 });
        await page.route('**/up', route => route.fulfill({
            status: 503,
            contentType: 'text/plain',
            body: 'unavailable',
        }));
        await login(page);

        const workspace = page.locator('[data-zazu-offline-workspace]');
        await expect(workspace).toBeVisible({ timeout: 15_000 });
        await expect(page.locator('.zazu-dash-hero')).toBeHidden();
        await expect(page.locator('.zazu-dash-overview')).toBeHidden();
        await expect(page.locator('.zazu-content > .zazu-dash-grid')).toBeHidden();

        const visitOfflinePath = async (path) => page.evaluate(pathname => {
            history.replaceState({}, '', pathname);
            window.dispatchEvent(new CustomEvent('zazu:offline-state-updated'));
        }, path);

        await visitOfflinePath('/dashboard');
        await expect(workspace).toHaveAttribute('data-zazu-offline-active-view', 'overview');
        await expect(workspace.locator('[data-zazu-offline-view]:visible')).toHaveCount(1);
        await expect(workspace.locator('[data-zazu-offline-view="overview"]')).toBeVisible();
        await expect(workspace.locator('[data-zazu-offline-overview-count="records"]')).toHaveText(/\d+/);
        const offlineNavigation = workspace.locator('[aria-label="Offline workspace navigation"]');
        await expect(offlineNavigation).toBeVisible();
        await expect(offlineNavigation.getByRole('link', { name: 'Open customers' })).toBeVisible();
        await expect(offlineNavigation.getByRole('link', { name: 'Open work' })).toBeVisible();
        await expect(offlineNavigation.getByRole('link', { name: 'Quotes', exact: true })).toBeVisible();

        const offlineHeaderGeometry = await workspace.locator('.zazu-offline-workspace-head').evaluate(header => {
            const intro = header.querySelector(':scope > .zazu-dash-panel-heading')?.getBoundingClientRect();
            const navigation = header.querySelector(':scope > [aria-label="Offline workspace navigation"]')?.getBoundingClientRect();
            return {
                intro: intro ? { x: intro.x, y: intro.y, width: intro.width, height: intro.height } : null,
                navigation: navigation ? { x: navigation.x, y: navigation.y, width: navigation.width, height: navigation.height } : null,
            };
        });
        expect(offlineHeaderGeometry.intro).not.toBeNull();
        expect(offlineHeaderGeometry.navigation).not.toBeNull();
        expect(offlineHeaderGeometry.navigation.y).toBeGreaterThanOrEqual(
            offlineHeaderGeometry.intro.y + offlineHeaderGeometry.intro.height
        );
        expect(Math.abs(offlineHeaderGeometry.navigation.x - offlineHeaderGeometry.intro.x)).toBeLessThanOrEqual(2);
        expect(Math.abs(offlineHeaderGeometry.navigation.width - offlineHeaderGeometry.intro.width)).toBeLessThanOrEqual(2);

        await expect(page.locator('body')).toHaveClass(/zazu-offline-mode/);
        await expect(page.locator('.zazu-page-title')).toHaveText('Offline workspace');
        await expect(page.locator('.zazu-command-search')).toBeHidden();
        await expect(page.locator('.zazu-nav-link[href="/calendar"]')).toBeHidden();
        await expect(page.locator('.zazu-section-tabs a[href="/calendar"]')).toBeHidden();
        const settingsLink = page.locator('.zazu-nav-link[href="/settings"]');
        await expect(settingsLink).toHaveCount(0);
        await expect(page.locator('[data-zazu-nav-area]:has(.zazu-nav-link[href="/settings"])')).toHaveCount(0);

        await visitOfflinePath('/customers');
        await expect(workspace).toHaveAttribute('data-zazu-offline-active-view', 'customers');
        await expect(workspace.locator('[data-zazu-offline-view]:visible')).toHaveCount(1);
        await expect(workspace.locator('[data-zazu-offline-view="customers"]')).toBeVisible();

        await visitOfflinePath('/capabilities');
        await expect(workspace.locator('[data-zazu-offline-view]:visible')).toHaveCount(1);
        await expect(workspace.locator('[data-zazu-offline-view="catalogue"]')).toBeVisible();

        await visitOfflinePath('/calendar');
        await expect(workspace.locator('[data-zazu-offline-view]:visible')).toHaveCount(0);
        await expect(workspace.locator('[data-zazu-offline-unavailable]')).toBeVisible();
        await expect(workspace.locator('[data-zazu-offline-unavailable]')).toContainText('This screen is not available offline yet');

        await visitOfflinePath('/settings');
        await expect(workspace).toHaveAttribute('data-zazu-offline-active-view', 'unavailable');
        await expect(workspace.locator('[data-zazu-offline-unavailable]')).toBeVisible();
        await expect(workspace.locator('[data-zazu-offline-unavailable]')).toContainText('This screen is not available offline yet');
    });

    test('reconciles exact delivered aggregates before acknowledging receiver sync', async ({ page }, testInfo) => {
        test.setTimeout(25_000);
        test.skip(testInfo.project.name !== 'chromium', 'Receiver delivery reconciliation contract runs once.');

        await login(page);
        await page.context().setOffline(true);
        await page.evaluate(async () => {
            const state = window.ZazuOffline.getState();
            state.sync = {
                token: 'receiver-delivery-reconciliation-token',
                device: { id: 998866, business_id: Number(document.body.dataset.zazuBusinessId) },
            };
            state.local_dirty = false;
            await window.ZazuOffline.persist({ allowSessionChange: true });
        });

        const bootstrapDeliverySequences = [];
        const acknowledgedSequences = [];
        const requestOrder = [];
        await page.route('**/api/sync/**', async route => {
            const url = new URL(route.request().url());
            if (url.pathname === '/api/sync/pull') {
                requestOrder.push('pull');
                const mutations = acknowledgedSequences.length === 0
                    ? [{ id: 'receiver-delivery-mutation', entity_type: 'supplier', entity_id: 'receiver-delivery-supplier', delivery_sequence: 17 }]
                    : [];
                await route.fulfill({
                    status: 200,
                    contentType: 'application/json',
                    body: JSON.stringify({ mutations }),
                });
                return;
            }
            if (url.pathname === '/api/sync/bootstrap') {
                const sequences = url.searchParams.getAll('delivery_sequences[]');
                bootstrapDeliverySequences.push(sequences);
                requestOrder.push('bootstrap:' + sequences.join(','));
                await route.fulfill({
                    status: 200,
                    contentType: 'application/json',
                    body: JSON.stringify({ version: 1 }),
                });
                return;
            }
            if (url.pathname === '/api/sync/acknowledge') {
                const sequence = Number(route.request().postDataJSON()?.sequence || 0);
                acknowledgedSequences.push(sequence);
                requestOrder.push('acknowledge:' + sequence);
                await route.fulfill({
                    status: 200,
                    contentType: 'application/json',
                    body: JSON.stringify({ cursor: { last_acknowledged_sequence: 17 } }),
                });
                return;
            }
            await route.fulfill({
                status: 200,
                contentType: 'application/json',
                body: JSON.stringify({ attachments: [] }),
            });
        });

        await page.context().setOffline(false);
        await page.evaluate(() => window.ZazuOffline.sync());
        await expect.poll(() => acknowledgedSequences.length).toBe(1);

        expect(bootstrapDeliverySequences[0]).toEqual(['17']);
        expect(acknowledgedSequences).toEqual([17]);
        expect(requestOrder.slice(0, 3)).toEqual(['pull', 'bootstrap:17', 'acknowledge:17']);
    });

    test('does not register a fresh browser device without explicit owner approval', async ({ page }, testInfo) => {
        test.setTimeout(20_000);
        test.skip(testInfo.project.name !== 'chromium', 'Primary device consent contract runs once.');

        let registrationAttempts = 0;
        await page.route('**/offline/device/register', async route => {
            registrationAttempts++;
            await route.fulfill({ status: 200, contentType: 'application/json', body: JSON.stringify({}) });
        });

        await login(page);
        await expect(page.getByRole('heading', { name: 'Dashboard' })).toBeVisible();
        await page.evaluate(async () => {
            await window.ZazuOffline.ready;
            window.dispatchEvent(new Event('focus'));
            window.dispatchEvent(new Event('online'));
        });
        await page.waitForTimeout(250);

        expect(registrationAttempts).toBe(0);
        const state = await page.evaluate(() => window.ZazuOffline.getState());
        expect(state.sync_registration_consent).toBe(false);
        expect(state.sync?.token || null).toBeNull();
    });

    test('owner can explicitly trust the current PC browser for offline sync', async ({ page }, testInfo) => {
        test.setTimeout(25_000);
        test.skip(testInfo.project.name !== 'chromium', 'Current-device trust contract runs once.');

        await login(page);
        const businessId = Number(await page.locator('body').getAttribute('data-zazu-business-id'));
        expect(businessId).toBeGreaterThan(0);

        let registrationAttempts = 0;
        await page.route('**/offline/device/register', async route => {
            registrationAttempts++;
            const body = route.request().postDataJSON();
            await route.fulfill({
                status: 200,
                contentType: 'application/json',
                body: JSON.stringify({
                    device: {
                        id: 999994,
                        installation_id: body.installation_id,
                        business_id: businessId,
                    },
                    token: 'explicit-current-device-trust-token',
                    bootstrap: {},
                }),
            });
        });

        await page.goto('/settings', { waitUntil: 'domcontentloaded' });
        const trustSection = page.locator('[data-zazu-current-device-trust]');
        const trustButton = page.getByRole('button', { name: 'Trust this device for offline sync' });
        await expect(trustSection).toContainText('anyone who can open that profile can use its offline workspace');
        await expect(trustSection).toContainText('staff permissions are not re-checked for each offline mutation');
        await expect(trustButton).toBeVisible();
        await trustButton.click();

        await expect(page.getByRole('button', { name: 'This device is trusted' })).toBeVisible();
        await expect.poll(() => registrationAttempts).toBe(1);
        const trustedState = await page.evaluate(() => ({
            consent: window.ZazuOffline.getState().sync_registration_consent,
            token: window.ZazuOffline.getState().sync?.token,
            paired: window.ZazuOffline.isPaired(),
        }));
        expect(trustedState).toEqual({
            consent: true,
            token: 'explicit-current-device-trust-token',
            paired: true,
        });
    });

    test('owner can generate a phone pairing code from Settings', async ({ page }, testInfo) => {
        test.skip(testInfo.project.name !== 'chromium', 'Owner phone-pairing UI contract runs once.');

        await login(page);
        await page.goto('/settings', { waitUntil: 'domcontentloaded' });

        const generateButton = page.getByRole('button', { name: 'Generate phone pairing code' });
        const result = page.locator('[data-zazu-pairing-result]');

        await expect(generateButton).toBeVisible();
        await generateButton.click();

        await expect(result).toContainText(/^12-digit code: \d{12} · expires /);
        await expect(generateButton).toBeEnabled();
    });

    test('retries primary device registration and drains queued customers after the host returns', async ({ page }, testInfo) => {
        test.setTimeout(25_000);
        test.skip(testInfo.project.name !== 'chromium', 'Primary device registration recovery contract runs once.');

        // Keep boot-time registration out of this test so the retry endpoint
        // is intercepted before the first registration attempt.
        await page.addInitScript(() => {
            window.__ZAZU_OFFLINE_DEFER_INITIAL_SYNC = true;
        });
        await login(page);
        const localCustomerIds = [
            'local:registration-retry-customer-one',
            'local:registration-retry-customer-two',
        ];
        await page.evaluate(async (ids) => {
            await window.ZazuOffline.ready;
            const state = window.ZazuOffline.getState();
            state.sync = null;
            state.sync_authorization_required = false;
            state.last_sync_error = null;
            state.customers = ids.map((local_id, index) => ({
                local_id,
                name: 'Customer saved before registration recovery ' + (index + 1),
            }));
            await window.ZazuOffline.persist({ allowSessionChange: true });
        }, localCustomerIds);

        const businessId = Number(await page.locator('body').getAttribute('data-zazu-business-id'));
        let registrationAttempts = 0;
        let pushed = 0;
        await page.route('**/offline/device/register', async route => {
            registrationAttempts += 1;
            if (registrationAttempts === 1) {
                await route.fulfill({
                    status: 503,
                    contentType: 'application/json',
                    body: JSON.stringify({ message: 'Local host is still starting.' }),
                });
                return;
            }
            const body = route.request().postDataJSON();
            await route.fulfill({
                status: 200,
                contentType: 'application/json',
                body: JSON.stringify({
                    device: {
                        id: 999995,
                        installation_id: body.installation_id,
                        business_id: businessId,
                    },
                    token: 'registration-retry-test-token',
                    bootstrap: {},
                }),
            });
        });
        await page.route('**/api/sync/**', async route => {
            const url = new URL(route.request().url());
            if (url.pathname === '/api/sync/push') {
                const body = route.request().postDataJSON();
                pushed += body.mutations.length;
                await route.fulfill({
                    status: 200,
                    contentType: 'application/json',
                    body: JSON.stringify({
                        recorded: body.mutations.map(mutation => ({
                            id: mutation.id,
                            status: 'applied',
                            identity: {
                                entity_type: mutation.entity_type,
                                local_id: mutation.payload.local_id,
                                server_id: mutation.payload.local_id === localCustomerIds[0] ? 880001 : 880002,
                            },
                        })),
                    }),
                });
                return;
            }
            if (url.pathname === '/api/sync/pull') {
                await route.fulfill({ status: 200, contentType: 'application/json', body: JSON.stringify({ mutations: [] }) });
                return;
            }
            if (url.pathname === '/api/sync/bootstrap') {
                await route.fulfill({ status: 200, contentType: 'application/json', body: JSON.stringify({}) });
                return;
            }
            if (url.pathname === '/api/sync/attachments') {
                await route.fulfill({ status: 200, contentType: 'application/json', body: JSON.stringify({ attachments: [] }) });
                return;
            }
            await route.fulfill({ status: 404, contentType: 'application/json', body: JSON.stringify({ message: 'Unexpected sync request' }) });
        });

        await page.evaluate(async (ids) => {
            await window.ZazuOffline.queueMutation('customer', 'create', {
                local_id: ids[0],
                record: { name: 'First customer saved before registration recovery' },
            });
            await window.ZazuOffline.queueMutation('customer', 'create', {
                local_id: ids[1],
                record: { name: 'Second customer saved before registration recovery' },
            });
        }, localCustomerIds);

        const firstRegistration = await page.evaluate(() =>
            window.ZazuOffline.registerPrimaryDevice({ explicitConsent: true })
        );
        expect(firstRegistration).toBe(false);

        await expect.poll(() => registrationAttempts, { timeout: 12_000 }).toBeGreaterThanOrEqual(2);
        await expect.poll(() => pushed, { timeout: 12_000 }).toBe(2);
        await expect.poll(async () => page.evaluate(async () => (await window.ZazuOffline.pending()).length)).toBe(0);
        const syncedCustomerIds = await page.evaluate(ids => {
            const customers = window.ZazuOffline.getState().customers;
            return ids.map(id => customers.find(customer => customer.local_id === id)?.server_id ?? null);
        }, localCustomerIds);
        expect(syncedCustomerIds).toEqual([880001, 880002]);
        expect(await page.evaluate(() => window.ZazuOffline.getState().sync?.token)).toBe('registration-retry-test-token');
    });

    test('offline workspace exposes locally saved supplier, service, stock, asset and finance records', async ({ page }, testInfo) => {
        test.skip(testInfo.project.name !== 'chromium', 'Local workspace collection rendering runs once.');
        await login(page);
        await page.context().setOffline(true);

        await page.evaluate(async () => {
            await window.ZazuOffline.ready;
            const state = window.ZazuOffline.getState();
            const identity = suffix => '00000000-0000-4000-8000-0000000000'+suffix;
            state.suppliers = [{
                id: 801, server_id: 801, local_id: identity('01'),
                name: 'Offline Supplier Proof', phone: '0123456789', email: 'supplier@example.test',
                address: 'Soshanguve',
            }];
            state.catalogue = [{
                id: 802, server_id: 802, local_id: identity('02'),
                name: 'Offline Catering Service', capability_type: 'service', is_active: false,
            }];
            state.inventory_items = [{
                id: 803, server_id: 803, local_id: identity('03'),
                name: 'Offline Stock Proof', sku: 'OFF-003', unit: 'box', on_hand: '12.00',
                reorder_level: '3.00', movements: [{ type: 'receipt', quantity: '12.00', movement_date: '2026-11-20' }],
            }];
            state.assets = [{
                id: 804, server_id: 804, local_id: identity('04'),
                name: 'Offline Marquee', asset_tag: 'OFF-A04', status: 'available', condition: 'good',
                allocations: [],
            }];
            state.purchase_orders = [{
                id: 805, server_id: 805, local_id: identity('05'),
                reference: 'PO-OFF-005', supplier_name: 'Offline Supplier Proof', status: 'part_received',
                total_amount: '1250.00', currency: 'ZAR', items: [{ description: 'Table linen', quantity: '10', received_quantity: '5', line_total: '1250.00' }],
            }];
            state.invoices = [{
                id: 806, server_id: 806, local_id: identity('06'),
                number: 'INV-OFF-006', status: 'part_paid', total: '2500.00', paid_amount: '1000.00',
                balance: '1500.00', currency: 'ZAR', payments: [{ reference: 'EFT-01', amount: '1000.00', paid_at: '2026-11-20' }],
            }];
            state.expenses = [{
                id: 807, server_id: 807, local_id: identity('07'),
                description: 'Offline fuel expense', amount: '350.00', currency: 'ZAR', status: 'recorded',
                expense_date: '2026-11-20',
            }];
            state.costs = [{
                id: 808, server_id: 808, local_id: identity('08'),
                description: 'Offline venue cost', category: 'Venue', status: 'incurred',
                projected_amount: '900.00', actual_amount: '950.00', currency: 'ZAR',
            }];
            await window.ZazuOffline.persist({ allowSessionChange: true });
            window.dispatchEvent(new CustomEvent('zazu:offline-state-updated'));
        });

        const workspace = page.locator('[data-zazu-offline-workspace]');
        await expect(workspace).toBeVisible();

        const routeExpectations = [
            ['/suppliers', 'suppliers', 'Offline Supplier Proof'],
            ['/capabilities', 'catalogue', 'Offline Catering Service'],
            ['/inventory', 'inventory_items', 'Offline Stock Proof'],
            ['/assets', 'assets', 'Offline Marquee'],
            ['/purchasing', 'purchase_orders', 'PO-OFF-005'],
            ['/finance/invoices', 'invoices', 'INV-OFF-006'],
            ['/finance/expenses', 'expenses', 'Offline fuel expense'],
            ['/work/730001/costs', 'costs', 'Offline venue cost'],
        ];

        for (const [path, view, label] of routeExpectations) {
            const response = await page.goto(path, { waitUntil: 'domcontentloaded' });
            expect(response?.ok(), path + ' must render through the cached offline shell').toBe(true);
            await expect(workspace).toHaveAttribute('data-zazu-offline-active-view', view);
            const section = workspace.locator('[data-zazu-offline-view="' + view + '"]');
            await expect(section).toBeVisible();
            await expect(section.getByText(label, { exact: false }).first()).toBeVisible();
        }

        await page.goto('/suppliers', { waitUntil: 'domcontentloaded' });
        const supplierSection = workspace.locator('[data-zazu-offline-view="suppliers"]');
        await supplierSection.getByRole('button', { name: 'View saved details' }).first().click();
        await expect(supplierSection.getByText('supplier@example.test', { exact: true })).toBeVisible();
        await expect(supplierSection.getByText('Soshanguve')).toBeVisible();

        await page.goto('/capabilities', { waitUntil: 'domcontentloaded' });
        const catalogueSection = workspace.locator('[data-zazu-offline-view="catalogue"]');
        await catalogueSection.getByRole('button', { name: 'View saved details' }).first().click();
        await expect(catalogueSection.getByText('Inactive', { exact: true })).toBeVisible();

        await page.goto('/inventory', { waitUntil: 'domcontentloaded' });
        const stockSection = workspace.locator('[data-zazu-offline-view="inventory_items"]');
        await stockSection.getByRole('button', { name: 'View saved details' }).click();
        await expect(stockSection.getByText('Recent stock movements')).toBeVisible();

        await page.goto('/finance/invoices', { waitUntil: 'domcontentloaded' });
        const invoiceSection = workspace.locator('[data-zazu-offline-view="invoices"]');
        await invoiceSection.getByRole('button', { name: 'View saved details' }).click();
        await expect(invoiceSection.getByText('EFT-01')).toBeVisible();
    });

    test('offline updates carry a base revision and preserve same-record queue order', async ({ page }) => {
        await login(page);
        await page.context().setOffline(true);

        const result = await page.evaluate(async () => {
            await window.ZazuOffline.ready;
            const localId = 'revision-queue-customer';
            const state = window.ZazuOffline.getState();
            state.customers = [{
                id: 420001,
                server_id: 420001,
                local_id: localId,
                revision: 7,
                name: 'Queue revision fixture',
            }];
            state.local_dirty = false;
            await window.ZazuOffline.persist({ allowSessionChange: true });

            const first = await window.ZazuOffline.queueMutation('customer', 'update', {
                local_id: localId,
                server_id: 420001,
                record: { name: 'First queued edit' },
            });
            const second = await window.ZazuOffline.queueMutation('customer', 'update', {
                local_id: localId,
                server_id: 420001,
                record: { name: 'Second queued edit' },
            });

            return {
                first,
                second,
                pending: await window.ZazuOffline.pending(),
            };
        });

        expect(result.first.payload.expected_revision).toBe(7);
        expect(result.first.queue_order).toBeLessThan(result.second.queue_order);
        expect(result.second.payload.predecessor_mutation_id).toBe(result.first.id);
        expect(result.pending.map(item => item.id)).toEqual([result.first.id, result.second.id]);
    });

    test('reconciles every related record revision for identity-less offline commands', async ({ page }, testInfo) => {
        test.setTimeout(25_000);
        test.skip(testInfo.project.name !== 'chromium', 'Related aggregate acknowledgement contract runs once.');

        await login(page);
        await page.context().setOffline(true);
        const queued = await page.evaluate(async () => {
            await window.ZazuOffline.ready;
            const businessId = Number(document.body.dataset.zazuBusinessId);
            const state = window.ZazuOffline.getState();
            state.sync = {
                token: 'related-aggregate-ack-token',
                device: { id: 998877, business_id: businessId },
            };
            state.jobs = [{
                id: 730001,
                server_id: 730001,
                local_id: 'related-ack-work',
                revision: 5,
                status: 'confirmed',
                name: 'Related acknowledgement Work',
            }];
            state.quotes = [{
                id: 730002,
                server_id: 730002,
                local_id: 'related-ack-quote',
                event_local_id: 'related-ack-work',
                revision: 4,
                status: 'accepted',
                latest_version: { status: 'accepted' },
            }];
            state.purchase_orders = [{
                id: 730003,
                server_id: 730003,
                local_id: 'related-ack-order',
                revision: 7,
                status: 'received',
                items: [],
            }];
            state.invoices = [{
                id: 730006,
                server_id: 730006,
                local_id: 'related-ack-invoice',
                revision: 1,
                status: 'issued',
                total: '100.00',
            }];
            state.assets = [{
                id: 730007,
                server_id: 730007,
                local_id: 'related-ack-asset',
                revision: 1,
                status: 'available',
                name: 'Related acknowledgement asset',
            }];
            state.inventory_items = [
                { id: 730004, server_id: 730004, local_id: 'related-ack-stock-a', revision: 2, on_hand: 4 },
                { id: 730005, server_id: 730005, local_id: 'related-ack-stock-b', revision: 10, on_hand: 8 },
            ];
            state.local_dirty = false;
            await window.ZazuOffline.persist({ allowSessionChange: true });

            const acceptance = await window.ZazuOffline.queueMutation('quote_acceptance', 'create', {
                local_id: 'related-ack-acceptance',
                quote_local_id: 'related-ack-quote',
                customer_name: 'Related acknowledgement customer',
            });
            const receipt = await window.ZazuOffline.queueMutation('purchase_receipt', 'create', {
                local_id: 'related-ack-receipt',
                purchase_order_local_id: 'related-ack-order',
                received_quantities: {
                    'related-ack-line-a': '1.00',
                },
            });
            const payment = await window.ZazuOffline.queueMutation('payment', 'create', {
                local_id: 'related-ack-payment',
                invoice_local_id: 'related-ack-invoice',
                amount: '25.00',
            });
            const movement = await window.ZazuOffline.queueMutation('inventory_movement', 'create', {
                local_id: 'related-ack-movement',
                inventory_item_local_id: 'related-ack-stock-a',
                quantity: '1.00',
            });
            const allocation = await window.ZazuOffline.queueMutation('asset_allocation', 'create', {
                local_id: 'related-ack-allocation',
                asset_local_id: 'related-ack-asset',
            });

            return {
                acceptanceId: acceptance.id,
                receiptId: receipt.id,
                paymentId: payment.id,
                movementId: movement.id,
                allocationId: allocation.id,
            };
        });

        const pushedTypes = [];
        await page.route('**/api/sync/**', async route => {
            const url = new URL(route.request().url());
            if (url.pathname === '/api/sync/push') {
                const body = route.request().postDataJSON();
                const recorded = body.mutations.map(mutation => {
                    pushedTypes.push(mutation.entity_type);
                    const relatedByType = {
                        quote_acceptance: [
                            { entity_type: 'quote', local_id: 'related-ack-quote', server_id: 730002, revision: 5 },
                            { entity_type: 'job', local_id: 'related-ack-work', server_id: 730001, revision: 6 },
                        ],
                        purchase_receipt: [
                            { entity_type: 'purchase_order', local_id: 'related-ack-order', server_id: 730003, revision: 8 },
                            // Only line A was received. Line B must not be advanced.
                            { entity_type: 'inventory_item', local_id: 'related-ack-stock-a', server_id: 730004, revision: 3 },
                        ],
                        payment: [
                            { entity_type: 'invoice', local_id: 'related-ack-invoice', server_id: 730006, revision: 2 },
                        ],
                        inventory_movement: [
                            { entity_type: 'inventory_item', local_id: 'related-ack-stock-a', server_id: 730004, revision: 4 },
                        ],
                        asset_allocation: [
                            { entity_type: 'asset', local_id: 'related-ack-asset', server_id: 730007, revision: 2 },
                        ],
                    };
                    const related = relatedByType[mutation.entity_type] || [];

                    return {
                        id: mutation.id,
                        status: 'applied',
                        applied: true,
                        identity: null,
                        related_identity: related[0],
                        related_identities: related,
                    };
                });
                await route.fulfill({
                    status: 200,
                    contentType: 'application/json',
                    body: JSON.stringify({ recorded, applied: recorded.map(item => item.id) }),
                });
                return;
            }
            if (url.pathname === '/api/sync/pull') {
                await route.fulfill({ status: 200, contentType: 'application/json', body: JSON.stringify({ mutations: [] }) });
                return;
            }
            if (url.pathname === '/api/sync/attachments') {
                await route.fulfill({ status: 200, contentType: 'application/json', body: JSON.stringify({ attachments: [] }) });
                return;
            }
            await route.fulfill({ status: 404, contentType: 'application/json', body: JSON.stringify({ message: 'Unexpected sync request' }) });
        });

        await page.context().setOffline(false);
        await page.evaluate(() => window.ZazuOffline.sync());
        await expect.poll(() => pushedTypes.length).toBe(5);
        // The online event may have started the first sync before the explicit
        // call above. Wait for the durable queue/ack transaction to finish before
        // inspecting the state that it reconciles.
        await expect.poll(
            () => page.evaluate(async () => (await window.ZazuOffline.pending()).length),
            { message: 'all command acknowledgements must be durably reconciled' },
        ).toBe(0);

        const state = await page.evaluate(async () => {
            const current = window.ZazuOffline.getState();
            return {
                workRevision: current.jobs.find(item => item.local_id === 'related-ack-work')?.revision,
                quoteRevision: current.quotes.find(item => item.local_id === 'related-ack-quote')?.revision,
                purchaseOrderRevision: current.purchase_orders.find(item => item.local_id === 'related-ack-order')?.revision,
                invoiceRevision: current.invoices.find(item => item.local_id === 'related-ack-invoice')?.revision,
                assetRevision: current.assets.find(item => item.local_id === 'related-ack-asset')?.revision,
                inventoryRevisions: current.inventory_items
                    .filter(item => item.local_id.startsWith('related-ack-stock-'))
                    .map(item => item.revision),
                pending: (await window.ZazuOffline.pending()).length,
            };
        });

        expect(state.workRevision).toBe(6);
        expect(state.quoteRevision).toBe(5);
        expect(state.purchaseOrderRevision).toBe(8);
        expect(state.invoiceRevision).toBe(2);
        expect(state.assetRevision).toBe(2);
        expect(state.inventoryRevisions).toEqual([4, 10]);
        expect(state.pending).toBe(0);
    });

    test('retries queued mutations when an installed app resumes after reconnect', async ({ page }, testInfo) => {
        test.setTimeout(25_000);
        test.skip(testInfo.project.name !== 'chromium', 'Installed-app resume reconnect contract runs once.');

        await login(page);
        await page.evaluate(async () => {
            await window.ZazuOffline.ready;
            const state = window.ZazuOffline.getState();
            state.sync = {
                token: 'resume-reconnect-test-token',
                device: { id: 999998, business_id: Number(document.body.dataset.zazuBusinessId) },
            };
            state.local_dirty = false;
            await window.ZazuOffline.persist({ allowSessionChange: true });
            await window.ZazuOffline.queueMutation('customer', 'create', {
                local_id: 'local:resume-reconnect-customer',
                record: { name: 'Resume reconnect customer' },
            });
        });

        let pushed = 0;
        await page.route('**/api/sync/**', async route => {
            const url = new URL(route.request().url());
            if (url.pathname === '/api/sync/push') {
                const body = route.request().postDataJSON();
                pushed += body.mutations.length;
                await route.fulfill({
                    status: 200,
                    contentType: 'application/json',
                    body: JSON.stringify({
                        recorded: body.mutations.map(mutation => ({ id: mutation.id, status: 'applied' })),
                    }),
                });
                return;
            }
            if (url.pathname === '/api/sync/pull') {
                await route.fulfill({ status: 200, contentType: 'application/json', body: JSON.stringify({ mutations: [] }) });
                return;
            }
            if (url.pathname === '/api/sync/bootstrap') {
                await route.fulfill({ status: 200, contentType: 'application/json', body: JSON.stringify({}) });
                return;
            }
            if (url.pathname === '/api/sync/attachments') {
                await route.fulfill({ status: 200, contentType: 'application/json', body: JSON.stringify({ attachments: [] }) });
                return;
            }
            await route.fulfill({ status: 404, contentType: 'application/json', body: JSON.stringify({ message: 'Unexpected sync request' }) });
        });

        // Simulate Android restoring the installed app after the network has
        // returned, without relying on the browser to replay its missed online event.
        await page.evaluate(() => window.dispatchEvent(new PageTransitionEvent('pageshow', { persisted: true })));

        await expect.poll(() => pushed).toBe(1);
        await expect.poll(async () => page.evaluate(async () => (await window.ZazuOffline.pending()).length)).toBe(0);
    });

    test('shows a retrying sync state instead of an ambiguous waiting label after a temporary server failure', async ({ page }, testInfo) => {
        test.setTimeout(20_000);
        test.skip(testInfo.project.name !== 'chromium', 'Sync status rendering contract runs once.');

        // This test supplies its own token and network responses; prevent the
        // dashboard's automatic first-device registration from racing setup.
        await page.addInitScript(() => {
            window.__ZAZU_OFFLINE_DEFER_INITIAL_SYNC = true;
        });
        await login(page);
        // The status chip is rendered on the Customer directory, not the dashboard.
        await page.goto('/customers', { waitUntil: 'domcontentloaded' });
        await waitForOfflineRuntime(page);
        await page.evaluate(() => window.ZazuOffline.ready);
        await page.evaluate(async () => {
            const state = window.ZazuOffline.getState();
            state.sync = {
                token: 'visible-sync-retry-token',
                device: { id: 999996, business_id: Number(document.body.dataset.zazuBusinessId) },
            };
            state.customers = [{
                local_id: 'local:visible-sync-customer',
                name: 'Visible sync customer',
                contact_name: 'Test Contact',
            }];
            state.local_dirty = false;
            await window.ZazuOffline.persist({ allowSessionChange: true });
        });

        await page.route('**/api/sync/push', route => route.fulfill({
            status: 503,
            contentType: 'application/json',
            body: JSON.stringify({ message: 'Local host is still starting.' }),
        }));
        await page.route('**/api/sync/pull', route => route.fulfill({
            status: 200,
            contentType: 'application/json',
            body: JSON.stringify({ mutations: [] }),
        }));
        await page.route('**/api/sync/bootstrap**', route => route.fulfill({
            status: 200,
            contentType: 'application/json',
            body: JSON.stringify({}),
        }));
        await page.route('**/api/sync/attachments', route => route.fulfill({
            status: 200,
            contentType: 'application/json',
            body: JSON.stringify({ attachments: [] }),
        }));
        await page.evaluate(async () => {
            await window.ZazuOffline.queueMutation('customer', 'create', {
                local_id: 'local:visible-sync-customer',
                record: { name: 'Visible sync customer' },
            });
            await window.ZazuOffline.sync();
        });

        const chip = page.locator('[data-zazu-local-record="customer"] .zazu-chip').filter({ hasText: 'Retrying sync' });
        await expect(chip).toBeVisible();
        await expect(chip).toHaveAttribute('title', /HTTP 503/);
    });

    test('replays the same server-accepted customer mutation after the first push response is lost and the page restarts', async ({ page, context }, testInfo) => {
        test.setTimeout(60_000);
        test.skip(testInfo.project.name !== 'chromium', 'Accepted-mutation lost-response recovery runs once against the real server.');

        await page.addInitScript(() => {
            window.__ZAZU_OFFLINE_DEFER_INITIAL_SYNC = true;
        });
        await login(page);
        await trustCurrentDevice(page);
        await context.setOffline(true);

        const customerName = 'Lost response replay ' + Date.now().toString(36);
        const localId = await page.evaluate(async ({ name }) => {
            await window.ZazuOffline.ready;
            const localId = crypto.randomUUID();
            const nextState = structuredClone(window.ZazuOffline.getState());
            nextState.customers.push({
                local_id: localId,
                server_id: null,
                name,
                contact_name: 'Replay Test Contact',
                phone: '0123456789',
                revision: 1,
                pending_sync: true,
            });
            await window.ZazuOffline.queueMutation('customer', 'create', {
                local_id: localId,
                record: {
                    name,
                    contact_name: 'Replay Test Contact',
                    phone: '0123456789',
                },
            }, nextState);
            return localId;
        }, { name: customerName });

        const queuedBeforePush = await page.evaluate(async () => window.ZazuOffline.pending());
        expect(queuedBeforePush).toHaveLength(1);
        const mutationId = queuedBeforePush[0].id;

        let pushAttempts = 0;
        let firstServerResponse = null;
        let firstResponseAborted = false;
        let retryServerResponse = null;
        await page.route('**/api/sync/push', async route => {
            pushAttempts += 1;
            const request = route.request().postDataJSON();
            const response = await route.fetch();
            const body = await response.body();
            const result = JSON.parse(body.toString('utf8'));
            const capture = {
                httpStatus: response.status(),
                requestMutationId: request.mutations?.[0]?.id,
                recorded: result.recorded || [],
            };

            if (pushAttempts === 1) {
                firstServerResponse = capture;
                // The real Laravel endpoint has already committed the mutation.
                // Abort only the browser-facing response to simulate a lost ACK.
                await route.abort('failed');
                firstResponseAborted = true;
                return;
            }

            retryServerResponse = capture;
            await route.fulfill({
                status: response.status(),
                headers: response.headers(),
                body,
            });
        });

        await context.setOffline(false);
        await expect.poll(() => pushAttempts, { timeout: 15_000 }).toBe(1);
        await expect.poll(() => firstServerResponse?.recorded?.[0]?.status, { timeout: 5_000 })
            .toBe('applied');
        expect(firstServerResponse.httpStatus).toBe(200);
        expect(firstServerResponse.requestMutationId).toBe(mutationId);
        expect(firstServerResponse.recorded[0].id).toBe(mutationId);
        await expect.poll(() => firstResponseAborted, { timeout: 5_000 }).toBe(true);
        // Stop the one-second automatic retry window immediately. The page
        // will restart offline, then reconnect only after the durable queue is
        // confirmed to still contain the original mutation.
        await context.setOffline(true);
        await expect.poll(() => page.evaluate(() => Boolean(window.ZazuOffline.getState().last_sync_error)))
            .toBe(true);

        // The browser did not receive the applied response, so the queue must
        // still contain the original mutation identity and payload.
        await expect.poll(async () => page.evaluate(async () => (await window.ZazuOffline.pending()).length))
            .toBe(1);
        await page.reload({ waitUntil: 'domcontentloaded' });
        await waitForOfflineRuntime(page);

        const afterRestart = await page.evaluate(async () => ({
            paired: window.ZazuOffline.isPaired(),
            pending: await window.ZazuOffline.pending(),
        }));
        expect(afterRestart.paired).toBe(true);
        expect(afterRestart.pending).toHaveLength(1);
        expect(afterRestart.pending[0].id).toBe(mutationId);
        expect(afterRestart.pending[0].payload.local_id).toBe(localId);

        // Reconnection causes the durable queue to replay the exact same
        // mutation. The server must return the already-applied result.
        await context.setOffline(false);
        await expect.poll(() => pushAttempts, { timeout: 15_000 }).toBe(2);
        await expect.poll(async () => page.evaluate(async () => (await window.ZazuOffline.pending()).length))
            .toBe(0);

        expect(retryServerResponse.httpStatus).toBe(200);
        expect(retryServerResponse.requestMutationId).toBe(mutationId);
        expect(retryServerResponse.recorded).toHaveLength(1);
        expect(retryServerResponse.recorded[0].id).toBe(mutationId);
        expect(retryServerResponse.recorded[0].status).toBe('applied');
        expect(retryServerResponse.recorded[0].sequence).toBe(firstServerResponse.recorded[0].sequence);
        expect(retryServerResponse.recorded[0].identity.server_id)
            .toBe(firstServerResponse.recorded[0].identity.server_id);

        const reconciledCustomer = await page.evaluate(({ id }) => {
            const record = window.ZazuOffline.getState().customers
                .find(customer => customer.local_id === id);
            return record ? {
                serverId: record.server_id ?? record.id ?? null,
                pendingSync: record.pending_sync === true,
            } : null;
        }, { id: localId });
        expect(reconciledCustomer).not.toBeNull();
        expect(reconciledCustomer.serverId).toBe(retryServerResponse.recorded[0].identity.server_id);
        expect(reconciledCustomer.pendingSync).toBe(false);
        expect(pushAttempts).toBe(2);
    });

    test('commits server identity, queue removal and clean local state in one acknowledgement transaction', async ({ page, context }, testInfo) => {
        test.setTimeout(30_000);
        test.skip(testInfo.project.name !== 'chromium', 'IndexedDB acknowledgement transaction contract runs once.');

        await login(page);
        await trustCurrentDevice(page);
        await context.setOffline(true);

        const localId = await page.evaluate(async () => {
            await window.ZazuOffline.ready;
            const localId = crypto.randomUUID();
            const nextState = structuredClone(window.ZazuOffline.getState());
            nextState.customers.push({
                local_id: localId,
                server_id: null,
                name: 'Atomic acknowledgement customer',
                contact_name: 'Atomic Test Contact',
                revision: 1,
                pending_sync: true,
            });
            await window.ZazuOffline.queueMutation('customer', 'create', {
                local_id: localId,
                record: {
                    name: 'Atomic acknowledgement customer',
                    contact_name: 'Atomic Test Contact',
                },
            }, nextState);
            return localId;
        });
        const queued = await page.evaluate(() => window.ZazuOffline.pending());
        expect(queued).toHaveLength(1);

        let pushAttempts = 0;
        await page.route('**/api/sync/push', async route => {
            pushAttempts += 1;
            const mutation = route.request().postDataJSON().mutations[0];
            // Arm only after the push reaches the server boundary. Earlier
            // setup or unrelated UI writes are outside this assertion.
            await page.evaluate(() => {
                window.__ZAZU_ACK_BOUNDARY.armed = true;
            });
            await route.fulfill({
                status: 200,
                contentType: 'application/json',
                body: JSON.stringify({
                    recorded: [{
                        id: mutation.id,
                        status: 'applied',
                        sequence: 1,
                        identity: {
                            entity_type: 'customer',
                            local_id: localId,
                            server_id: 990051,
                            revision: 1,
                        },
                    }],
                }),
            });
        });
        await page.route('**/api/sync/pull**', route => route.fulfill({
            status: 503,
            contentType: 'application/json',
            body: JSON.stringify({ message: 'No remote delivery required for this transaction test.' }),
        }));
        await page.route('**/api/sync/bootstrap', route => route.fulfill({
            status: 503,
            contentType: 'application/json',
            body: JSON.stringify({ message: 'No bootstrap required for this transaction test.' }),
        }));
        await page.route('**/api/sync/attachments', route => route.fulfill({
            status: 200,
            contentType: 'application/json',
            body: JSON.stringify({ attachments: [] }),
        }));

        await page.evaluate(() => {
            const boundary = { armed: false, blocked: false };
            window.__ZAZU_ACK_BOUNDARY = boundary;
            const originalTransaction = IDBDatabase.prototype.transaction;
            IDBDatabase.prototype.transaction = function (storeNames, mode, options) {
                const names = Array.isArray(storeNames) ? storeNames : [storeNames];
                if (boundary.armed && mode === 'readwrite'
                    && names.length === 1 && names[0] === 'state') {
                    boundary.armed = false;
                    boundary.blocked = true;
                    throw new DOMException('Injected failure for a separate post-acknowledgement state write.', 'QuotaExceededError');
                }
                return originalTransaction.call(this, storeNames, mode, options);
            };
        });

        await context.setOffline(false);
        await page.evaluate(() => window.ZazuOffline.sync());
        await expect.poll(() => pushAttempts).toBe(1);
        await expect.poll(async () => page.evaluate(async () => (await window.ZazuOffline.pending()).length))
            .toBe(0);

        const reconciled = await page.evaluate(async id => {
            const record = window.ZazuOffline.getState().customers.find(item => item.local_id === id);
            return {
                record: record ? {
                    serverId: record.server_id ?? record.id ?? null,
                    pendingSync: record.pending_sync === true,
                } : null,
                localDirty: window.ZazuOffline.getState().local_dirty === true,
                pending: await window.ZazuOffline.pending(),
                boundary: window.__ZAZU_ACK_BOUNDARY,
            };
        }, localId);

        expect(reconciled.record).not.toBeNull();
        expect(reconciled.record.serverId).toBe(990051);
        expect(reconciled.record.pendingSync).toBe(false);
        expect(reconciled.localDirty).toBe(false);
        expect(reconciled.pending).toHaveLength(0);
        expect(reconciled.boundary).toEqual({ armed: true, blocked: false });
    });

    test('automatically retries pending mutations when the host returns after the reconnect event', async ({ page }, testInfo) => {
        test.setTimeout(20_000);
        test.skip(testInfo.project.name !== 'chromium', 'Reconnect retry contract runs once.');

        await login(page);
        await page.evaluate(async () => {
            await window.ZazuOffline.ready;
            const state = window.ZazuOffline.getState();
            state.sync = {
                token: 'delayed-host-reconnect-token',
                device: { id: 999997, business_id: Number(document.body.dataset.zazuBusinessId) },
            };
            state.sync_authorization_required = false;
            await window.ZazuOffline.persist({ allowSessionChange: true });
            await window.ZazuOffline.queueMutation('customer', 'create', {
                local_id: 'local:delayed-host-customer',
                record: { name: 'Delayed host reconnect customer' },
            });
        });

        let pushAttempts = 0;
        await page.route('**/api/sync/**', async route => {
            const url = new URL(route.request().url());
            if (url.pathname === '/api/sync/push') {
                pushAttempts += 1;
                const body = route.request().postDataJSON();
                if (pushAttempts === 1) {
                    await route.fulfill({
                        status: 503,
                        contentType: 'application/json',
                        body: JSON.stringify({ message: 'Local host is still starting.' }),
                    });
                    return;
                }
                await route.fulfill({
                    status: 200,
                    contentType: 'application/json',
                    body: JSON.stringify({
                        recorded: body.mutations.map(mutation => ({ id: mutation.id, status: 'applied' })),
                    }),
                });
                return;
            }
            if (url.pathname === '/api/sync/pull') {
                await route.fulfill({ status: 200, contentType: 'application/json', body: JSON.stringify({ mutations: [] }) });
                return;
            }
            if (url.pathname === '/api/sync/bootstrap') {
                await route.fulfill({ status: 200, contentType: 'application/json', body: JSON.stringify({}) });
                return;
            }
            if (url.pathname === '/api/sync/attachments') {
                await route.fulfill({ status: 200, contentType: 'application/json', body: JSON.stringify({ attachments: [] }) });
                return;
            }
            await route.fulfill({ status: 404, contentType: 'application/json', body: JSON.stringify({ message: 'Unexpected sync request' }) });
        });

        await page.evaluate(() => window.ZazuOffline.sync());
        await expect.poll(() => pushAttempts, { timeout: 10_000 }).toBeGreaterThanOrEqual(2);
        await expect.poll(async () => page.evaluate(async () => (await window.ZazuOffline.pending()).length)).toBe(0);
    });

    test('queues supported changes when the Zazu origin is unreachable but the browser reports online', async ({ page }, testInfo) => {
        test.setTimeout(20_000);
        test.skip(testInfo.project.name !== 'chromium', 'Origin-reachability fallback contract runs once.');

        await login(page);
        await page.goto('/customers/create', { waitUntil: 'domcontentloaded' });
        await page.evaluate(() => window.ZazuOffline.ready);

        expect(await page.evaluate(() => navigator.onLine)).toBe(true);

        // Keep the browser online while making the application origin unavailable.
        await page.route('**/up', async route => {
            if (route.request().method() === 'HEAD') {
                await route.abort('failed');
                return;
            }
            await route.continue();
        });

        await page.locator('input[name="name"]').fill('Offline network customer');
        await page.locator('input[name="primary_contact_name"]').fill('Mandla Dlamini');
        await page.locator('input[name="primary_contact_phone"]').fill('0123456789');
        await page.locator('input[name="primary_contact_email"]').fill('mandla@example.test');
        await page.locator('input[name="legal_name"]').fill('Mandla Events');
        await page.locator('input[name="registration_number"]').fill('2026/12345');
        await page.locator('input[name="tax_number"]').fill('TAX-7788');
        await page.locator('input[name="vat_number"]').fill('VAT-1122');
        await page.locator('textarea[name="billing_address"]').fill('12 Market Street, Pretoria');
        await page.locator('input[name="day_contact_name"]').fill('Day Coordinator');
        await page.locator('input[name="day_contact_phone"]').fill('0123456788');
        await page.locator('input[name="day_contact_email"]').fill('day@example.test');
        // Night contact fields live inside a collapsed disclosure on the
        // real form; open it before filling rather than bypassing visibility.
        const nightContact = page.locator('details').filter({ has: page.locator('summary', { hasText: 'Night contact' }) });
        await nightContact.evaluate(element => { element.open = true; });
        await expect(page.locator('input[name="night_contact_name"]')).toBeVisible();
        await page.locator('input[name="night_contact_name"]').fill('Night Coordinator');
        await page.locator('input[name="night_contact_phone"]').fill('0123456787');
        await page.locator('input[name="night_contact_email"]').fill('night@example.test');
        await page.getByRole('button', { name: 'Save customer' }).click();

        await expect(page.getByText('Saved on this device. It will sync automatically when Zazu reconnects.')).toBeVisible();

        const queued = await page.evaluate(async () => window.ZazuOffline.pending());
        expect(queued).toHaveLength(1);
        expect(queued[0].entity_type).toBe('customer');
        expect(queued[0].payload.record.contact_name).toBe('Mandla Dlamini');
        expect(queued[0].payload.record.phone).toBe('0123456789');
        expect(queued[0].payload.record.email).toBe('mandla@example.test');
        expect(queued[0].payload.record.legal_name).toBe('Mandla Events');
        expect(queued[0].payload.record.registration_number).toBe('2026/12345');
        expect(queued[0].payload.record.tax_number).toBe('TAX-7788');
        expect(queued[0].payload.record.vat_number).toBe('VAT-1122');
        expect(queued[0].payload.record.billing_address).toBe('12 Market Street, Pretoria');
        expect(queued[0].payload.record.day_contact_name).toBe('Day Coordinator');
        expect(queued[0].payload.record.day_contact_phone).toBe('0123456788');
        expect(queued[0].payload.record.day_contact_email).toBe('day@example.test');
        expect(queued[0].payload.record.night_contact_name).toBe('Night Coordinator');
        expect(queued[0].payload.record.night_contact_phone).toBe('0123456787');
        expect(queued[0].payload.record.night_contact_email).toBe('night@example.test');

        await page.reload({ waitUntil: 'domcontentloaded' });
        await page.evaluate(() => window.ZazuOffline.ready);
        const restoredQueue = await page.evaluate(async () => window.ZazuOffline.pending());
        expect(restoredQueue).toHaveLength(1);
        expect(restoredQueue[0].id).toBe(queued[0].id);
        expect(restoredQueue[0].payload.record.day_contact_name).toBe('Day Coordinator');
        expect(restoredQueue[0].payload.record.night_contact_name).toBe('Night Coordinator');

        const restoredState = await page.evaluate(() => window.ZazuOffline.getState());
        expect(restoredState.customers.some(customer =>
            customer.local_id === queued[0].payload.local_id
            && customer.legal_name === 'Mandla Events'
            && customer.billing_address === '12 Market Street, Pretoria'
        )).toBe(true);
    });

    test('saves offline customer details while clearly deferring the profile photo change', async ({ page }, testInfo) => {
        test.setTimeout(20_000);
        test.skip(testInfo.project.name !== 'chromium', 'Offline customer photo boundary contract runs once.');

        await login(page);
        await page.goto('/customers/create', { waitUntil: 'domcontentloaded' });
        await page.evaluate(() => window.ZazuOffline.ready);
        await page.route('**/up', async route => {
            if (route.request().method() === 'HEAD') {
                await route.abort('failed');
                return;
            }
            await route.continue();
        });

        await page.locator('input[name="name"]').fill('Photo boundary customer');
        await page.locator('input[name="primary_contact_name"]').fill('Photo Contact');
        await page.locator('input[name="primary_contact_phone"]').fill('0123456789');
        await page.locator('input[name="profile_photo"]').setInputFiles({
            name: 'profile.png',
            mimeType: 'image/png',
            buffer: Buffer.from('offline-photo-fixture'),
        });

        await page.getByRole('button', { name: 'Save customer' }).click();

        const notice = page.locator('[data-zazu-offline-save-notice]');
        await expect(notice).toContainText('Customer details were saved, but the profile photo change was not saved');
        await expect(notice).toContainText('Update the photo when Zazu is online.');

        const queued = await page.evaluate(async () => window.ZazuOffline.pending());
        expect(queued).toHaveLength(1);
        expect(queued[0].entity_type).toBe('customer');
        expect(queued[0].payload.record.name).toBe('Photo boundary customer');
        expect(queued[0].payload.record.contact_name).toBe('Photo Contact');
        expect(queued[0].payload.record.phone).toBe('0123456789');
        expect(queued[0].payload.record).not.toHaveProperty('profile_photo');
        expect(queued[0].payload.record).not.toHaveProperty('remove_profile_photo');

        const localState = await page.evaluate(() => window.ZazuOffline.getState());
        expect(localState.customers.some(customer =>
            customer.name === 'Photo boundary customer'
            && customer.contact_name === 'Photo Contact'
            && customer.phone === '0123456789'
        )).toBe(true);
    });

    test('saves offline service details while clearly deferring its image', async ({ page }, testInfo) => {
        test.setTimeout(20_000);
        test.skip(testInfo.project.name !== 'chromium', 'Offline service image boundary contract runs once.');

        await login(page);
        await page.goto('/capabilities/create', { waitUntil: 'domcontentloaded' });
        await page.evaluate(() => window.ZazuOffline.ready);
        await page.route('**/up', async route => {
            if (route.request().method() === 'HEAD') {
                await route.abort('failed');
                return;
            }
            await route.continue();
        });

        await page.locator('input[name="name"]').fill('Offline service image boundary');
        await page.locator('input[name="category"]').first().check({ force: true });
        await page.locator('select[name="capability_type"]').selectOption('service');
        await page.locator('select[name="currency"]').selectOption('ZAR');
        await page.locator('select[name="pricing_basis"]').selectOption('custom');
        await page.locator('input[name="image"]').setInputFiles({
            name: 'service.png',
            mimeType: 'image/png',
            buffer: Buffer.from('offline-service-image-fixture'),
        });

        await page.getByRole('button', { name: 'Save service' }).click();

        const notice = page.locator('[data-zazu-offline-save-notice]');
        await expect(notice).toContainText('Service details were saved, but the image was not saved');
        await expect(notice).toContainText('Add the image when Zazu is online.');

        const queued = await page.evaluate(async () => window.ZazuOffline.pending());
        expect(queued).toHaveLength(1);
        expect(queued[0].entity_type).toBe('service');
        expect(queued[0].payload.record.name).toBe('Offline service image boundary');
        expect(queued[0].payload.record).not.toHaveProperty('image');

        const localState = await page.evaluate(() => window.ZazuOffline.getState());
        expect(localState.catalogue.some(service =>
            service.name === 'Offline service image boundary'
            && service.capability_type === 'service'
        )).toBe(true);
    });

    test('links a phone-local customer to an offline Work record', async ({ page }, testInfo) => {
        test.setTimeout(25_000);
        test.skip(testInfo.project.name !== 'chromium', 'Offline customer-to-Work contract runs once.');

        await login(page);
        // Prime both authenticated form shells before disconnecting. The
        // remainder of the journey must not allow a background sync to race
        // the local customer → local Work relationship.
        await page.goto('/work/create', { waitUntil: 'domcontentloaded' });
        await expect(page.locator('#new-job-form')).toBeVisible();
        await page.goto('/customers/create', { waitUntil: 'domcontentloaded' });
        await page.evaluate(() => window.ZazuOffline.ready);
        await page.locator('input[name="name"]').fill('Local only linked customer');
        await page.locator('input[name="primary_contact_name"]').fill('Local Customer Contact');
        await page.context().setOffline(true);
        await expect(page).toHaveURL(/\/customers\/create$/);
        await page.getByRole('button', { name: 'Save customer' }).click();
        await expect(page.getByText('Saved on this device. It will sync automatically when Zazu reconnects.')).toBeVisible();

        const customerLocalId = await page.evaluate(() =>
            window.ZazuOffline.getState().customers.find(item => item.name === 'Local only linked customer')?.local_id || null
        );
        expect(customerLocalId).toBeTruthy();

        await page.goto('/work/create', { waitUntil: 'domcontentloaded' });
        await page.evaluate(() => window.ZazuOffline.ready);
        const selector = page.locator('select[name="customer_id"]');
        await expect(selector.locator('option[value^="local:"]')).toHaveCount(1);
        await selector.selectOption({ label: 'Local only linked customer' });
        // Select the visible Wedding choice card, not its visually covered radio.
        const weddingChoice = page.locator('label.zazu-choice-card:has(input[name="event_type"][value="Wedding"])');
        await weddingChoice.click();
        await expect(weddingChoice.locator('input[name="event_type"]')).toBeChecked();
        await page.locator('input[name="event_date"]').fill('2026-11-15');
        await page.locator('input[name="event_address"]').fill('Pretoria');
        const cateringChoice = page.locator('label.zazu-service-choice:has(input[name="services[]"][value="Buffet Catering"])');
        await cateringChoice.click();
        await expect(cateringChoice.locator('input[name="services[]"]')).toBeChecked();
        let normalWorkPostAttempted = false;
        await page.route('**/work', async route => {
            if (route.request().method() === 'POST') {
                normalWorkPostAttempted = true;
                await route.abort('failed');
                return;
            }
            await route.continue();
        });
        await page.locator('#new-job-form input[name="name"]').fill('Offline job for local customer');
        await page.getByRole('button', { name: /Create job/i }).click();

        await expect(page.getByText('Saved on this device. It will sync automatically when Zazu reconnects.')).toBeVisible();
        const queued = await page.evaluate(async () => window.ZazuOffline.pending());
        const customerMutation = queued.find(item =>
            item.entity_type === 'customer' && item.payload?.local_id === customerLocalId
        );
        const jobMutation = queued.find(item => item.entity_type === 'job');
        expect(customerMutation).toBeTruthy();
        expect(jobMutation).toBeTruthy();
        expect(jobMutation.payload.record.customer_local_id).toBe(customerLocalId);
        expect(normalWorkPostAttempted).toBe(false);
        expect(jobMutation.payload.record.event_type).toBe('Wedding');
        expect(jobMutation.payload.record.event_date).toBe('2026-11-15');
        expect(jobMutation.payload.record.event_address).toBe('Pretoria');
        expect(jobMutation.payload.record.services).toEqual(['Buffet Catering']);
    });

    test('migrates legacy offline data only into its matching business workspace', async ({ page }) => {
        await login(page);
        await trustCurrentDevice(page);
        const businessId = await page.evaluate(() => Number(document.body.dataset.zazuBusinessId));
        expect(businessId).toBeGreaterThan(0);

        // Explicitly approve this browser before clearing its current workspace;
        // legacy migration must preserve the trusted business device identity.
        await page.evaluate(async activeBusinessId => {
            const db = await new Promise((resolve, reject) => {
                const request = indexedDB.open('zazu-client-business-' + activeBusinessId, 1);
                request.onsuccess = () => resolve(request.result);
                request.onerror = () => reject(request.error || new Error('Could not open current offline workspace'));
            });
            await new Promise((resolve, reject) => {
                const tx = db.transaction(['state', 'mutations'], 'readwrite');
                tx.objectStore('state').clear();
                tx.objectStore('mutations').clear();
                tx.oncomplete = resolve;
                tx.onerror = () => reject(tx.error || new Error('Could not clear current offline workspace'));
                tx.onabort = () => reject(tx.error || new Error('Current offline workspace cleanup aborted'));
            });
            db.close();
        }, businessId);

        const fixture = await page.evaluate(async (activeBusinessId) => {
            const localId = crypto.randomUUID();
            const mutationId = crypto.randomUUID();
            const db = await new Promise((resolve, reject) => {
                const request = indexedDB.open('zazu-client', 1);
                request.onupgradeneeded = () => {
                    const storeDb = request.result;
                    if (!storeDb.objectStoreNames.contains('state')) storeDb.createObjectStore('state');
                    if (!storeDb.objectStoreNames.contains('mutations')) {
                        const store = storeDb.createObjectStore('mutations', { keyPath: 'id' });
                        store.createIndex('status', 'status');
                        store.createIndex('created_at', 'created_at');
                    }
                };
                request.onsuccess = () => resolve(request.result);
                request.onerror = () => reject(request.error || new Error('Could not open legacy offline storage'));
            });

            const legacyState = {
                version: 'legacy-fixture',
                business: { id: activeBusinessId, name: 'Legacy migration business' },
                sync: { token: 'legacy-migration-token', device: { id: 12345, business_id: activeBusinessId } },
                customers: [{ local_id: localId, name: 'Preserved offline customer' }],
                local_dirty: true,
            };
            const mutation = {
                id: mutationId,
                entity_type: 'customer',
                entity_id: localId,
                operation: 'create',
                payload: { local_id: localId, name: 'Preserved offline customer' },
                created_at: new Date().toISOString(),
                status: 'pending',
            };

            await new Promise((resolve, reject) => {
                const tx = db.transaction(['state', 'mutations'], 'readwrite');
                tx.objectStore('state').put(legacyState, 'current');
                tx.objectStore('mutations').put(mutation);
                tx.oncomplete = resolve;
                tx.onerror = () => reject(tx.error || new Error('Could not seed legacy offline workspace'));
                tx.onabort = () => reject(tx.error || new Error('Legacy workspace setup aborted'));
            });
            db.close();
            return { localId, mutationId };
        }, businessId);

        await page.reload({ waitUntil: 'domcontentloaded' });
        await expect(page.getByRole('heading', { name: 'Dashboard' })).toBeVisible();
        await page.evaluate(() => window.ZazuOffline.ready);

        const migrated = await page.evaluate(async () => ({
            state: window.ZazuOffline.getState(),
            queue: await window.ZazuOffline.pending(),
        }));
        expect(migrated.state.business.id).toBe(businessId);
        expect(migrated.state.customers.some(customer => customer.name === 'Preserved offline customer')).toBe(true);
        expect(migrated.queue).toHaveLength(1);
        expect(migrated.queue[0].id).toBe(fixture.mutationId);

        const legacyCounts = await page.evaluate(() => new Promise((resolve, reject) => {
            const request = indexedDB.open('zazu-client', 1);
            request.onsuccess = () => {
                const db = request.result;
                const tx = db.transaction(['state', 'mutations'], 'readonly');
                const state = tx.objectStore('state').get('current');
                const queue = tx.objectStore('mutations').count();
                tx.oncomplete = () => {
                    resolve({ statePresent: Boolean(state.result), queueCount: queue.result });
                    db.close();
                };
                tx.onerror = () => reject(tx.error || new Error('Could not verify legacy cleanup'));
            };
            request.onerror = () => reject(request.error || new Error('Could not reopen legacy offline storage'));
        }));
        expect(legacyCounts).toEqual({ statePresent: false, queueCount: 0 });

        await page.evaluate(() => window.ZazuOffline.clearLocalSession());
        await page.reload({ waitUntil: 'domcontentloaded' });
        await page.evaluate(() => window.ZazuOffline.ready);
        expect(await page.evaluate(() => window.ZazuOffline.getState().business)).toBeNull();
        expect(await page.evaluate(() => window.ZazuOffline.pending())).toHaveLength(0);
    });

    test('refuses to import legacy offline data belonging to a different business', async ({ page }) => {
        await login(page);
        const businessId = await page.evaluate(() => Number(document.body.dataset.zazuBusinessId));
        const foreignBusinessId = businessId + 100000;

        await page.evaluate(async (foreignId) => {
            const db = await new Promise((resolve, reject) => {
                const request = indexedDB.open('zazu-client', 1);
                request.onupgradeneeded = () => {
                    const storeDb = request.result;
                    if (!storeDb.objectStoreNames.contains('state')) storeDb.createObjectStore('state');
                    if (!storeDb.objectStoreNames.contains('mutations')) {
                        const store = storeDb.createObjectStore('mutations', { keyPath: 'id' });
                        store.createIndex('status', 'status');
                        store.createIndex('created_at', 'created_at');
                    }
                };
                request.onsuccess = () => resolve(request.result);
                request.onerror = () => reject(request.error || new Error('Could not open legacy offline storage'));
            });

            await new Promise((resolve, reject) => {
                const tx = db.transaction(['state', 'mutations'], 'readwrite');
                tx.objectStore('state').put({
                    business: { id: foreignId, name: 'Another business private data' },
                    sync: { token: 'foreign-business-token', device: { id: 12346, business_id: foreignId } },
                    customers: [{ name: 'Must not be exposed' }],
                }, 'current');
                tx.objectStore('mutations').put({
                    id: crypto.randomUUID(),
                    entity_type: 'customer',
                    entity_id: 'foreign-customer',
                    operation: 'create',
                    payload: { name: 'Must not be exposed' },
                    created_at: new Date().toISOString(),
                    status: 'pending',
                });
                tx.oncomplete = resolve;
                tx.onerror = () => reject(tx.error || new Error('Could not seed foreign legacy workspace'));
            });
            db.close();
        }, foreignBusinessId);

        await page.reload({ waitUntil: 'domcontentloaded' });
        await expect(page.getByRole('heading', { name: 'Dashboard' })).toBeVisible();
        await page.evaluate(() => window.ZazuOffline.ready);

        const localState = await page.evaluate(async () => ({
            business: window.ZazuOffline.getState().business,
            customers: window.ZazuOffline.getState().customers,
            queue: await window.ZazuOffline.pending(),
        }));
        expect(localState.business).toBeNull();
        expect(localState.customers).toEqual([]);
        expect(localState.queue).toHaveLength(0);

        const legacyStillExists = await page.evaluate(() => new Promise((resolve, reject) => {
            const request = indexedDB.open('zazu-client', 1);
            request.onsuccess = () => {
                const db = request.result;
                const tx = db.transaction(['state', 'mutations'], 'readonly');
                const state = tx.objectStore('state').get('current');
                const queue = tx.objectStore('mutations').count();
                tx.oncomplete = () => {
                    resolve(Boolean(state.result) && queue.result === 1);
                    db.close();
                };
                tx.onerror = () => reject(tx.error || new Error('Could not inspect foreign legacy workspace'));
            };
            request.onerror = () => reject(request.error || new Error('Could not reopen legacy offline storage'));
        }));
        expect(legacyStillExists).toBe(true);
    });

    test('a rejected sync mutation and its recovery conflict persist together', async ({ page }, testInfo) => {
        test.setTimeout(30_000);
        test.skip(testInfo.project.name !== 'chromium', 'The failed-mutation persistence contract runs once in Chromium.');

        await login(page);
        // Initial sync is deliberately deferred above so the test controls the
        // offline transition. Explicitly trust the device and hydrate its local
        // workspace before selecting the server-backed job used by this test.
        await trustCurrentDevice(page);
        await page.evaluate(() => window.ZazuOffline.sync());
        await expect.poll(() => page.evaluate(() => (
            window.ZazuOffline.getState().jobs || []
        ).some(record => record.local_id && Number(record.id ?? record.server_id) > 0))).toBe(true);
        const businessId = await page.evaluate(() => Number(document.body.dataset.zazuBusinessId));
        expect(businessId).toBeGreaterThan(0);

        await page.evaluate(async (activeBusinessId) => {
            const state = window.ZazuOffline.getState();
            state.sync = {
                token: 'atomic-failed-mutation-token',
                device: { id: 987660, business_id: activeBusinessId },
            };
            state.invoices = [{
                local_id: 'atomic-failed-invoice',
                id: null,
                server_id: null,
                number: 'INV-LOCAL-001',
                status: 'draft',
                pending_sync: true,
            }];
            state.local_dirty = false;
            await window.ZazuOffline.persist({ allowSessionChange: true });
        }, businessId);

        // Keep the queue offline while installing deterministic server routes.
        // Otherwise the app's automatic online sync can race this test's mocked response.
        await page.context().setOffline(true);
        const mutation = await page.evaluate(() => window.ZazuOffline.queueMutation('invoice', 'create', {
            local_id: 'atomic-failed-invoice',
            quote_local_id: 'missing-local-quote',
            issued_at: '2026-11-20',
            due_at: '2026-11-27',
        }));

        await page.route('**/api/sync/push', route => route.fulfill({
            status: 200,
            contentType: 'application/json',
            body: JSON.stringify({
                recorded: [{
                    id: mutation.id,
                    status: 'pending',
                    sequence: 42,
                    conflict_id: 987661,
                    conflict_type: 'related_record_missing',
                    error: 'The related event is not available on the server.',
                }],
            }),
        }));
        await page.route('**/api/sync/pull**', route => route.fulfill({
            status: 200,
            contentType: 'application/json',
            body: JSON.stringify({ mutations: [] }),
        }));
        await page.route('**/api/sync/bootstrap**', route => route.fulfill({
            status: 200,
            contentType: 'application/json',
            body: JSON.stringify({
                business: { id: businessId, name: 'Test business' },
                catalogue: [], suppliers: [], purchase_orders: [], invoices: [],
                expenses: [], inventory_items: [], assets: [], costs: [], selected: [],
            }),
        }));

        // Reconnect only after every response is intercepted. sync() may coalesce
        // with an already-running pass, so assert the durable outcome rather than
        // assuming its returned promise represents the queued follow-up pass.
        await page.context().setOffline(false);
        await page.evaluate(() => window.ZazuOffline.sync());

        await expect.poll(async () => page.evaluate(async (mutationId) => {
            const db = await new Promise((resolve, reject) => {
                const request = indexedDB.open('zazu-client-business-' + document.body.dataset.zazuBusinessId, 1);
                request.onsuccess = () => resolve(request.result);
                request.onerror = () => reject(request.error || new Error('Could not open offline workspace'));
            });
            const status = await new Promise((resolve, reject) => {
                const tx = db.transaction('mutations', 'readonly');
                const request = tx.objectStore('mutations').get(mutationId);
                tx.oncomplete = () => resolve(request.result?.status || null);
                tx.onerror = () => reject(tx.error || new Error('Could not inspect mutation acknowledgement'));
            });
            db.close();
            return status;
        }, mutation.id), { timeout: 10000 }).toBe('failed');

        const persisted = await page.evaluate(async () => {
            const db = await new Promise((resolve, reject) => {
                const request = indexedDB.open('zazu-client-business-' + document.body.dataset.zazuBusinessId, 1);
                request.onsuccess = () => resolve(request.result);
                request.onerror = () => reject(request.error || new Error('Could not open offline workspace'));
            });
            const result = await new Promise((resolve, reject) => {
                const tx = db.transaction(['mutations', 'state'], 'readonly');
                const queueRequest = tx.objectStore('mutations').getAll();
                const stateRequest = tx.objectStore('state').get('current');
                tx.oncomplete = () => resolve({ queue: queueRequest.result || [], state: stateRequest.result });
                tx.onerror = () => reject(tx.error || new Error('Could not inspect failed mutation persistence'));
            });
            db.close();
            return result;
        });

        const failed = persisted.queue.find(item => item.id === mutation.id);
        expect(failed?.status).toBe('failed');
        expect(failed?.conflict_id).toBe(987661);
        expect(failed?.last_error).toContain('related event');
        expect(persisted.state.invoices).toEqual(expect.arrayContaining([
            expect.objectContaining({
                local_id: 'atomic-failed-invoice',
                number: 'INV-LOCAL-001',
                status: 'draft',
            }),
        ]));
        expect(persisted.state.conflicts).toEqual(expect.arrayContaining([
            expect.objectContaining({
                id: 987661,
                mutation_id: mutation.id,
                status: 'open',
                message: 'The related event is not available on the server.',
            }),
        ]));
        expect(persisted.state.local_dirty).toBe(false);
    });

    test('offline purchase receipts preserve partial quantities and match catalogue stock safely', async ({ page }, testInfo) => {
        test.setTimeout(45_000);
        test.skip(testInfo.project.name !== 'chromium', 'Purchase receipt reconciliation runs once in Chromium.');

        await login(page);
        await trustCurrentDevice(page);
        await page.goto('/purchasing', { waitUntil: 'domcontentloaded' });

        const orderLink = page.locator('a.zazu-list-item').filter({ hasText: 'PO-ZAZU-DEMO-002' }).first();
        await expect(orderLink).toBeVisible();

        const orderId = await orderLink.getAttribute('href').then(value => Number(value?.split('/').pop()));
        expect(orderId).toBeGreaterThan(0);
        await expect.poll(() => page.evaluate(async id => {
            await window.ZazuOffline.ready;
            return window.ZazuOffline.getState().purchase_orders.find(order => Number(order.server_id ?? order.id) === id)?.status ?? null;
        }, orderId)).toBe('ordered');

        await orderLink.click();
        const receiptForm = page.locator('form[data-zazu-offline-entity="purchase_receipt"]');
        await expect(receiptForm).toBeVisible();
        const businessId = await page.evaluate(() => Number(document.body.dataset.zazuBusinessId));
        const line = await page.evaluate(async id => {
            const order = window.ZazuOffline.getState().purchase_orders.find(item => Number(item.server_id ?? item.id) === id);
            const item = order?.items?.[0];
            return item ? {
                orderLocalId: order.local_id,
                orderRevision: Number(order.revision || 1),
                localId: item.local_id,
                serverId: Number(item.server_id ?? item.id),
                description: item.description,
                capabilityId: item.capability_id,
                ordered: item.quantity,
                received: item.received_quantity,
            } : null;
        }, orderId);
        expect(businessId).toBeGreaterThan(0);
        expect(line?.localId).toBeTruthy();
        expect(line?.serverId).toBeGreaterThan(0);
        expect(Number(line?.capabilityId)).toBeGreaterThan(0);
        expect(line?.ordered).toBe('1.00');
        expect(line?.received).toBe('0.00');

        await page.evaluate(async ({ description, capabilityId }) => {
            const state = window.ZazuOffline.getState();
            state.inventory_items = [
                {
                    local_id: 'wrong-catalogue-stock-item',
                    id: 99101,
                    server_id: 99101,
                    name: description,
                    capability_id: Number(capabilityId) + 999,
                    revision: 1,
                    on_hand: '10.00',
                    movements: [],
                },
                {
                    local_id: 'correct-catalogue-stock-item',
                    id: 99102,
                    server_id: 99102,
                    name: description,
                    capability_id: Number(capabilityId),
                    revision: 1,
                    on_hand: '2.10',
                    movements: [],
                },
                ...(state.inventory_items || []),
            ];
            await window.ZazuOffline.persist();
        }, { description: line.description, capabilityId: line.capabilityId });

        await page.context().setOffline(true);
        const quantityInput = receiptForm.locator(`input[name="received_quantity[${line.serverId}]"]`);
        await quantityInput.fill('0.10');
        await receiptForm.getByRole('button', { name: 'Record receipt' }).click();
        await expect(receiptForm.getByRole('status')).toContainText('Saved on this device');

        await quantityInput.fill('0.20');
        await receiptForm.getByRole('button', { name: 'Record receipt' }).click();
        await expect(receiptForm.getByRole('status')).toContainText('Saved on this device');

        let overReceiptMessage = null;
        page.once('dialog', async dialog => {
            overReceiptMessage = dialog.message();
            await dialog.dismiss();
        });
        await quantityInput.fill('0.80');
        await receiptForm.getByRole('button', { name: 'Record receipt' }).click();
        await expect.poll(() => overReceiptMessage).toContain('exceeds the remaining ordered quantity');

        const local = await page.evaluate(async orderServerId => {
            const state = window.ZazuOffline.getState();
            const order = state.purchase_orders.find(item => Number(item.server_id ?? item.id) === orderServerId);
            const wrongItem = state.inventory_items.find(item => item.local_id === 'wrong-catalogue-stock-item');
            const correctItem = state.inventory_items.find(item => item.local_id === 'correct-catalogue-stock-item');
            return {
                orderStatus: order?.status,
                orderPending: order?.pending_sync,
                receivedQuantity: order?.items?.[0]?.received_quantity,
                linePending: order?.items?.[0]?.pending_sync,
                wrongOnHand: wrongItem?.on_hand,
                correctOnHand: correctItem?.on_hand,
                correctMovementCount: correctItem?.movements?.length || 0,
                correctMovementsPending: (correctItem?.movements || []).every(item => item.pending_sync === true),
                receiptMutations: (await window.ZazuOffline.pending()).filter(item => item.entity_type === 'purchase_receipt').length,
            };
        }, orderId);

        expect(local).toEqual({
            orderStatus: 'ordered',
            orderPending: true,
            receivedQuantity: '0.30',
            linePending: true,
            wrongOnHand: '10.00',
            correctOnHand: 2.4,
            correctMovementCount: 2,
            correctMovementsPending: true,
            receiptMutations: 2,
        });

        // Reconnect both partial receipts. Each acknowledgement advances the
        // purchase-order and matched inventory revisions; parent and nested
        // optimistic markers clear only after every related receipt is settled.
        const pushedReceipts = [];
        await page.route('**/api/sync/**', async route => {
            const url = new URL(route.request().url());
            if (url.pathname === '/api/sync/push') {
                const body = route.request().postDataJSON();
                const recorded = body.mutations.map((mutation, index) => {
                    if (mutation.entity_type !== 'purchase_receipt') {
                        throw new Error('Unexpected queued mutation: ' + mutation.entity_type);
                    }
                    const payload = mutation.payload?.record || {};
                    pushedReceipts.push({
                        orderLocalId: payload.purchase_order_local_id,
                        quantities: Object.values(payload.received_quantities || {}),
                    });
                    return {
                        id: mutation.id,
                        status: 'applied',
                        applied: true,
                        identity: null,
                        related_identities: [
                            {
                                entity_type: 'purchase_order',
                                local_id: line.orderLocalId,
                                server_id: orderId,
                                revision: line.orderRevision + index + 1,
                            },
                            {
                                entity_type: 'inventory_item',
                                local_id: 'correct-catalogue-stock-item',
                                server_id: 99102,
                                revision: index + 2,
                            },
                        ],
                    };
                });
                await route.fulfill({
                    status: 200,
                    contentType: 'application/json',
                    body: JSON.stringify({ recorded, applied: recorded.map(item => item.id) }),
                });
                return;
            }
            if (url.pathname === '/api/sync/pull') {
                await route.fulfill({ status: 200, contentType: 'application/json', body: JSON.stringify({ mutations: [] }) });
                return;
            }
            if (url.pathname === '/api/sync/attachments') {
                await route.fulfill({ status: 200, contentType: 'application/json', body: JSON.stringify({ attachments: [] }) });
                return;
            }
            await route.fulfill({ status: 404, contentType: 'application/json', body: JSON.stringify({ message: 'Unexpected sync request' }) });
        });

        await page.context().setOffline(false);
        await page.evaluate(() => window.ZazuOffline.sync());
        await expect.poll(() => pushedReceipts.length).toBe(2);
        await expect(pushedReceipts).toEqual([
            { orderLocalId: line.orderLocalId, quantities: ['0.10'] },
            { orderLocalId: line.orderLocalId, quantities: ['0.20'] },
        ]);
        await expect.poll(
            () => page.evaluate(async () => (await window.ZazuOffline.pending()).length),
            { message: 'both purchase receipt acknowledgements must clear the durable queue' },
        ).toBe(0);

        const reconciled = await page.evaluate(orderServerId => {
            const state = window.ZazuOffline.getState();
            const order = state.purchase_orders.find(item => Number(item.server_id ?? item.id) === orderServerId);
            const correctItem = state.inventory_items.find(item => item.local_id === 'correct-catalogue-stock-item');
            return {
                orderRevision: order?.revision,
                orderPending: order?.pending_sync,
                receivedQuantity: order?.items?.[0]?.received_quantity,
                linePending: order?.items?.[0]?.pending_sync,
                inventoryRevision: correctItem?.revision,
                inventoryPending: correctItem?.pending_sync,
                onHand: correctItem?.on_hand,
                movementPending: (correctItem?.movements || []).some(item => item.pending_sync === true),
                localDirty: state.local_dirty,
            };
        }, orderId);

        expect(reconciled).toEqual({
            orderRevision: line.orderRevision + 2,
            orderPending: false,
            receivedQuantity: '0.30',
            linePending: false,
            inventoryRevision: 3,
            inventoryPending: false,
            onHand: 2.4,
            movementPending: false,
            localDirty: false,
        });
    });

    test('a disconnected stock receipt updates the local balance and movement history', async ({ page }, testInfo) => {
        test.setTimeout(45_000);
        test.skip(testInfo.project.name !== 'chromium', 'Inventory movement UI acceptance runs once in Chromium.');

        await page.addInitScript(() => {
            window.__ZAZU_OFFLINE_DEFER_INITIAL_SYNC = true;
        });
        await login(page);
        const itemName = 'Offline movement acceptance item';
        await page.goto('/inventory/create', { waitUntil: 'domcontentloaded' });
        await page.locator('input[name="name"]').fill(itemName);
        await page.locator('input[name="sku"]').fill('OFFLINE-MOVE-001');
        await page.locator('input[name="unit"]').fill('unit');
        await page.locator('input[name="reorder_level"]').fill('1.00');
        await page.getByRole('button', { name: 'Save item' }).click();
        await page.waitForURL('**/inventory');

        const itemRow = page.locator('.zazu-list-item').filter({ hasText: itemName });
        await expect(itemRow).toBeVisible();
        const movementForm = itemRow.locator('form[data-zazu-offline-entity="inventory_movement"]').first();
        const itemId = Number(await movementForm.getAttribute('data-zazu-offline-item-id'));
        const businessId = await page.evaluate(() => Number(document.body.dataset.zazuBusinessId));
        expect(itemId).toBeGreaterThan(0);
        expect(businessId).toBeGreaterThan(0);

        await page.evaluate(async ({ activeBusinessId, serverItemId, name }) => {
            await window.ZazuOffline.ready;
            const state = window.ZazuOffline.getState();
            state.sync = {
                token: 'offline-inventory-movement-token',
                device: { id: 987663, business_id: activeBusinessId },
            };
            state.inventory_items = [{
                local_id: 'offline-stock-item-acceptance',
                id: serverItemId,
                server_id: serverItemId,
                name,
                unit: 'unit',
                on_hand: 0,
                movements: [],
            }];
            state.assets = [{
                local_id: 'local-asset-identity',
                id: 98001,
                server_id: 98001,
                name: 'Offline event asset',
                status: 'allocated',
                allocations: [{
                    local_id: 'local-asset-allocation-identity',
                    id: null,
                    server_id: null,
                    status: 'allocated',
                    pending_sync: true,
                }],
            }];
            state.local_dirty = false;
            await window.ZazuOffline.persist({ allowSessionChange: true });
        }, { activeBusinessId: businessId, serverItemId: itemId, name: itemName });

        await page.context().setOffline(true);
        await movementForm.locator('input[name="quantity"]').fill('3.50');
        await movementForm.locator('input[name="unit_cost"]').fill('2.50');
        await movementForm.locator('input[name="movement_date"]').fill('2026-11-20');
        await movementForm.getByRole('button', { name: 'Receive' }).click();
        await expect(movementForm.getByRole('status')).toContainText('Saved on this device');

        const local = await page.evaluate(async serverItemId => {
            const item = window.ZazuOffline.getState().inventory_items.find(record => Number(record.server_id) === serverItemId);
            return {
                onHand: item?.on_hand,
                movements: item?.movements || [],
                pending: await window.ZazuOffline.pending(),
            };
        }, itemId);

        expect(local.onHand).toBe(3.5);
        expect(local.movements).toEqual(expect.arrayContaining([
            expect.objectContaining({
                type: 'receipt',
                quantity: '3.50',
                unit_cost: '2.50',
                pending_sync: true,
                server_id: null,
            }),
        ]));
        expect(local.pending).toHaveLength(1);
        expect(local.pending[0].entity_type).toBe('inventory_movement');

        const allocationMutation = await page.evaluate(() => window.ZazuOffline.queueMutation('asset_allocation', 'create', {
            local_id: 'local-asset-allocation-identity',
            asset_local_id: 'local-asset-identity',
            event_local_id: 'local-asset-job-identity',
            allocated_from: '2026-11-20',
            allocated_until: '2026-11-21',
            notes: 'Created while disconnected',
        }));
        const syncedTypes = [];
        await page.route('**/api/sync/push', async route => {
            const body = route.request().postDataJSON();
            const recorded = body.mutations.map((mutation, index) => {
                syncedTypes.push(mutation.entity_type);
                return {
                    id: mutation.id,
                    sequence: index + 1,
                    status: 'applied',
                    applied: true,
                    error: null,
                    retryable: false,
                    identity: {
                        entity_type: mutation.entity_type,
                        local_id: mutation.payload?.local_id || mutation.entity_id,
                        server_id: mutation.entity_type === 'inventory_movement' ? 96001 : 96002,
                    },
                };
            });
            await route.fulfill({
                status: 200,
                contentType: 'application/json',
                body: JSON.stringify({ recorded, applied: recorded.map(item => item.id) }),
            });
        });
        await page.route('**/api/sync/pull**', route => route.fulfill({
            status: 200,
            contentType: 'application/json',
            body: JSON.stringify({ mutations: [] }),
        }));
        await page.route('**/api/sync/bootstrap**', route => route.fulfill({
            status: 200,
            contentType: 'application/json',
            body: JSON.stringify({ business: { id: businessId, name: 'Offline movement business', currency: 'ZAR' } }),
        }));

        await page.context().setOffline(false);
        await page.evaluate(() => window.ZazuOffline.sync());
        await expect.poll(() => syncedTypes.length).toBe(2);
        await expect.poll(() => page.evaluate(() => {
            const item = window.ZazuOffline.getState().inventory_items.find(record => record.local_id === 'offline-stock-item-acceptance');
            const asset = window.ZazuOffline.getState().assets.find(record => record.local_id === 'local-asset-identity');
            const allocation = asset?.allocations?.find(record => record.local_id === 'local-asset-allocation-identity');
            return {
                movementId: item?.movements?.find(record => record.type === 'receipt')?.server_id ?? null,
                movementPending: item?.movements?.find(record => record.type === 'receipt')?.pending_sync ?? null,
                allocationId: allocation?.server_id ?? null,
                allocationPending: allocation?.pending_sync ?? null,
            };
        })).toEqual({
            movementId: 96001,
            movementPending: false,
            allocationId: 96002,
            allocationPending: false,
        });

        const finalQueue = await page.evaluate(() => window.ZazuOffline.pending());
        expect(finalQueue).toHaveLength(0);
        expect(allocationMutation.entity_type).toBe('asset_allocation');
    });

    test('event-cost mutations receive their canonical server identity after sync', async ({ page }, testInfo) => {
        test.setTimeout(30_000);
        test.skip(testInfo.project.name !== 'chromium', 'Event-cost identity reconciliation runs once in Chromium.');

        await page.addInitScript(() => {
            window.__ZAZU_OFFLINE_DEFER_INITIAL_SYNC = true;
        });
        await login(page);
        await page.goto('/settings', { waitUntil: 'domcontentloaded' });
        await page.evaluate(() => window.ZazuOffline.ready);
        const businessId = await page.evaluate(() => Number(document.body.dataset.zazuBusinessId));
        expect(businessId).toBeGreaterThan(0);

        await page.evaluate(async activeBusinessId => {
            const state = window.ZazuOffline.getState();
            state.sync = {
                token: 'event-cost-identity-token',
                device: { id: 987662, business_id: activeBusinessId },
            };
            state.costs = [{
                local_id: 'offline-event-cost-identity',
                id: null,
                server_id: null,
                description: 'Offline transport cost',
                pending_sync: true,
            }];
            state.local_dirty = false;
            await window.ZazuOffline.persist({ allowSessionChange: true });
        }, businessId);

        await page.context().setOffline(true);
        const mutation = await page.evaluate(() => window.ZazuOffline.queueMutation('event_cost', 'create', {
            local_id: 'offline-event-cost-identity',
            event_local_id: 'offline-event-cost-job',
            category: 'transport',
            description: 'Offline transport cost',
            currency: 'ZAR',
            projected_amount: '250.00',
            actual_amount: '250.00',
            status: 'incurred',
        }));

        let pushedEntityType = null;
        await page.route('**/api/sync/push', async route => {
            const body = route.request().postDataJSON();
            const item = body.mutations.find(candidate => candidate.id === mutation.id);
            pushedEntityType = item?.entity_type || null;
            await route.fulfill({
                status: 200,
                contentType: 'application/json',
                body: JSON.stringify({
                    recorded: [{
                        id: mutation.id,
                        sequence: 1,
                        status: 'applied',
                        applied: true,
                        error: null,
                        retryable: false,
                        identity: {
                            entity_type: 'event_cost',
                            local_id: 'offline-event-cost-identity',
                            server_id: 95001,
                        },
                    }],
                    applied: [mutation.id],
                }),
            });
        });
        await page.route('**/api/sync/pull**', route => route.fulfill({
            status: 200,
            contentType: 'application/json',
            body: JSON.stringify({ mutations: [] }),
        }));
        await page.route('**/api/sync/bootstrap**', route => route.fulfill({
            status: 200,
            contentType: 'application/json',
            body: JSON.stringify({
                business: { id: businessId, name: 'Event cost identity business', currency: 'ZAR' },
            }),
        }));

        await page.context().setOffline(false);
        await page.evaluate(() => window.ZazuOffline.sync());

        await expect.poll(() => pushedEntityType).toBe('event_cost');
        await expect.poll(() => page.evaluate(() => {
            const cost = window.ZazuOffline.getState().costs.find(item => item.local_id === 'offline-event-cost-identity');
            return { id: cost?.id ?? null, serverId: cost?.server_id ?? null, pendingSync: cost?.pending_sync ?? null };
        })).toEqual({ id: 95001, serverId: 95001, pendingSync: false });

        const reconciled = await page.evaluate(async () => ({
            cost: window.ZazuOffline.getState().costs.find(item => item.local_id === 'offline-event-cost-identity'),
            pending: await window.ZazuOffline.pending(),
        }));
        expect(reconciled.pending).toHaveLength(0);
        expect(reconciled.cost).toMatchObject({
            local_id: 'offline-event-cost-identity',
            id: 95001,
            server_id: 95001,
            pending_sync: false,
        });
    });

    test('event cost form saves locally and reconciles its job identity after reconnect', async ({ page, context }, testInfo) => {
        test.setTimeout(40_000);
        test.skip(testInfo.project.name !== 'chromium', 'Event-cost form reconciliation runs once in Chromium.');

        await page.addInitScript(() => {
            window.__ZAZU_OFFLINE_DEFER_INITIAL_SYNC = true;
        });
        await login(page);
        // Initial sync is intentionally deferred for this scenario. Explicitly
        // trust the device and hydrate its business workspace before selecting
        // the server-backed Work record used by the event-cost form.
        await trustCurrentDevice(page);
        await page.evaluate(() => window.ZazuOffline.sync());
        await expect.poll(() => page.evaluate(() => (
            window.ZazuOffline.getState().jobs || []
        ).some(record => record.local_id && Number(record.id ?? record.server_id) > 0))).toBe(true);

        const businessId = await page.evaluate(() => Number(document.body.dataset.zazuBusinessId));
        expect(businessId).toBeGreaterThan(0);

        const job = await page.evaluate(() => {
            const item = window.ZazuOffline.getState().jobs.find(record => record.local_id && Number(record.id ?? record.server_id) > 0);
            return item ? {
                id: Number(item.id ?? item.server_id),
                localId: item.local_id,
                name: item.name,
            } : null;
        });
        expect(job).not.toBeNull();

        await page.goto(`/work/${job.id}/costs/create`, { waitUntil: 'domcontentloaded' });
        const form = page.locator('form[data-zazu-offline-entity="event_cost"]');
        await expect(form).toBeVisible();
        await page.evaluate(() => window.ZazuOffline.ready);

        await context.setOffline(true);
        await form.locator('select[name="category"]').selectOption({ index: 1 });
        await form.locator('input[name="description"]').fill('Offline transport and delivery');
        await form.locator('select[name="currency"]').selectOption('ZAR');
        await form.locator('input[name="projected_amount"]').fill('320.00');
        await form.locator('input[name="actual_amount"]').fill('295.50');
        await form.locator('select[name="status"]').selectOption('incurred');
        await form.locator('textarea[name="notes"]').fill('Recorded on the disconnected device.');
        await form.getByRole('button', { name: 'Save cost' }).click();
        await expect(form.getByRole('status')).toContainText('Saved on this device');

        const local = await page.evaluate(async () => {
            const queue = await window.ZazuOffline.pending();
            const mutation = queue.find(item => item.entity_type === 'event_cost');
            const cost = window.ZazuOffline.getState().costs.find(item => item.local_id === mutation?.payload?.local_id);
            return {
                mutationId: mutation?.id ?? null,
                payload: mutation?.payload ?? null,
                cost: cost ? {
                    description: cost.description,
                    projectedAmount: cost.projected_amount,
                    actualAmount: cost.actual_amount,
                    status: cost.status,
                    pendingSync: cost.pending_sync,
                } : null,
            };
        });
        expect(local.mutationId).toEqual(expect.any(String));
        expect(local.payload).toMatchObject({
            local_id: expect.any(String),
            server_id: null,
        });
        expect(local.payload.record).toMatchObject({
            event_local_id: job.localId,
            category: expect.any(String),
            description: 'Offline transport and delivery',
            currency: 'ZAR',
            projected_amount: '320.00',
            actual_amount: '295.50',
            status: 'incurred',
            notes: 'Recorded on the disconnected device.',
        });
        expect(local.cost).toEqual({
            description: 'Offline transport and delivery',
            projectedAmount: '320.00',
            actualAmount: '295.50',
            status: 'incurred',
            pendingSync: true,
        });

        let pushed = null;
        await page.route('**/api/sync/push', async route => {
            const body = route.request().postDataJSON();
            const mutation = body.mutations.find(item => item.id === local.mutationId);
            pushed = mutation ? {
                entityType: mutation.entity_type,
                localId: mutation.payload?.local_id,
                eventLocalId: mutation.payload?.record?.event_local_id,
            } : null;
            await route.fulfill({
                status: 200,
                contentType: 'application/json',
                body: JSON.stringify({
                    recorded: [{
                        id: local.mutationId,
                        sequence: 1,
                        status: 'applied',
                        applied: true,
                        error: null,
                        retryable: false,
                        identity: {
                            entity_type: 'event_cost',
                            local_id: local.payload.local_id,
                            server_id: 95002,
                            revision: 1,
                        },
                    }],
                    applied: [local.mutationId],
                }),
            });
        });
        await page.route('**/api/sync/pull**', route => route.fulfill({
            status: 200,
            contentType: 'application/json',
            body: JSON.stringify({ mutations: [] }),
        }));
        await page.route('**/api/sync/bootstrap', route => route.fulfill({
            status: 200,
            contentType: 'application/json',
            body: JSON.stringify({
                business: { id: businessId, name: 'Event cost business', currency: 'ZAR' },
            }),
        }));

        await context.setOffline(false);
        await page.evaluate(() => window.ZazuOffline.sync());
        await expect.poll(() => pushed?.entityType).toBe('event_cost');
        expect(pushed).toEqual({
            entityType: 'event_cost',
            localId: local.payload.local_id,
            eventLocalId: job.localId,
        });

        await expect.poll(async () => page.evaluate(async localId => {
            const state = window.ZazuOffline.getState();
            const cost = state.costs.find(item => item.local_id === localId);
            return {
                serverId: cost?.server_id ?? null,
                id: cost?.id ?? null,
                pendingSync: cost?.pending_sync ?? null,
                pendingCount: (await window.ZazuOffline.pending()).length,
            };
        }, local.payload.local_id)).toEqual({
            serverId: 95002,
            id: 95002,
            pendingSync: false,
            pendingCount: 0,
        });
    });

    test('offline payment receives its canonical server identity inside the invoice', async ({ page }, testInfo) => {
        test.setTimeout(45_000);
        test.skip(testInfo.project.name !== 'chromium', 'Nested payment identity reconciliation runs once in Chromium.');

        await page.addInitScript(() => {
            window.__ZAZU_OFFLINE_DEFER_INITIAL_SYNC = true;
        });
        await login(page);
        await page.goto('/finance/payments/create', { waitUntil: 'domcontentloaded' });
        await page.evaluate(() => window.ZazuOffline.ready);
        const businessId = await page.evaluate(() => Number(document.body.dataset.zazuBusinessId));
        expect(businessId).toBeGreaterThan(0);

        await page.evaluate(async activeBusinessId => {
            const state = window.ZazuOffline.getState();
            state.sync = {
                token: 'offline-payment-identity-token',
                device: { id: 987664, business_id: activeBusinessId },
            };
            state.invoices = [{
                local_id: 'offline-payment-invoice-identity',
                id: 98401,
                server_id: 98401,
                revision: 1,
                number: 'INV-OFFLINE-PAY-001',
                status: 'issued',
                currency: 'ZAR',
                total: '250.00',
                paid_amount: '50.00',
                balance: '200.00',
                deposit_required: '75.00',
                deposit_paid_amount: '0.00',
                deposit_balance: '75.00',
                payments: [{
                    local_id: 'existing-invoice-payment',
                    id: 98499,
                    server_id: 98499,
                    type: 'payment',
                    amount: '50.00',
                    method: 'cash',
                    currency: 'ZAR',
                }],
            }];
            state.local_dirty = false;
            await window.ZazuOffline.persist({ allowSessionChange: true });
        }, businessId);

        const invoiceSelect = page.locator('select[name="invoice_id"]');
        await invoiceSelect.evaluate(select => {
            select.add(new Option('INV-OFFLINE-PAY-001 · ZAR 200.00 outstanding', '98401'));
            select.value = '98401';
        });

        await page.locator('select[name="type"]').selectOption('payment');
        await page.locator('select[name="method"]').selectOption('bank_transfer');
        await page.locator('input[name="paid_at"]').fill('2026-11-20');
        await page.context().setOffline(true);
        const paymentForm = page.locator('form[data-zazu-offline-entity="payment"]');

        let overpaymentMessage = null;
        page.once('dialog', async dialog => {
            overpaymentMessage = dialog.message();
            await dialog.dismiss();
        });
        await page.locator('input[name="amount"]').fill('200.01');
        await paymentForm.getByRole('button', { name: 'Record payment' }).click();
        await expect.poll(() => overpaymentMessage).toContain('Payment cannot exceed the outstanding invoice balance.');
        expect(await page.evaluate(() => window.ZazuOffline.pending())).toHaveLength(0);

        const unchanged = await page.evaluate(() => {
            const invoice = window.ZazuOffline.getState().invoices.find(item => item.local_id === 'offline-payment-invoice-identity');
            return { status: invoice?.status, paid: invoice?.paid_amount, balance: invoice?.balance, payments: invoice?.payments?.length };
        });
        expect(unchanged).toEqual({ status: 'issued', paid: '50.00', balance: '200.00', payments: 1 });

        await page.locator('input[name="amount"]').fill('200.00');
        await paymentForm.getByRole('button', { name: 'Record payment' }).click();
        await expect(paymentForm.getByRole('status')).toContainText('Saved on this device');

        const local = await page.evaluate(async () => {
            const invoice = window.ZazuOffline.getState().invoices.find(item => item.local_id === 'offline-payment-invoice-identity');
            return {
                invoice: {
                    status: invoice?.status,
                    paidAmount: invoice?.paid_amount,
                    balance: invoice?.balance,
                    payments: invoice?.payments || [],
                },
                pending: await window.ZazuOffline.pending(),
            };
        });
        expect(local.invoice.status).toBe('paid');
        expect(local.invoice.paidAmount).toBe('250.00');
        expect(local.invoice.balance).toBe('0.00');
        expect(local.invoice.payments).toHaveLength(2);
        expect(local.invoice.payments[1]).toMatchObject({
            invoice_id: 98401,
            amount: '200.00',
            method: 'bank_transfer',
            pending_sync: true,
            id: null,
            server_id: null,
        });
        expect(local.pending).toHaveLength(1);
        expect(local.pending[0].entity_type).toBe('payment');

        let paidInvoiceMessage = null;
        page.once('dialog', async dialog => {
            paidInvoiceMessage = dialog.message();
            await dialog.dismiss();
        });
        await page.locator('input[name="amount"]').fill('0.01');
        await paymentForm.getByRole('button', { name: 'Record payment' }).click();
        await expect.poll(() => paidInvoiceMessage).toContain('Only issued invoices can accept payments.');
        expect(await page.evaluate(() => window.ZazuOffline.pending())).toHaveLength(1);

        let pushedPaymentLocalId = null;
        await page.route('**/api/sync/push', async route => {
            const body = route.request().postDataJSON();
            const mutation = body.mutations.find(item => item.entity_type === 'payment');
            pushedPaymentLocalId = mutation?.payload?.local_id || null;
            await route.fulfill({
                status: 200,
                contentType: 'application/json',
                body: JSON.stringify({
                    recorded: [{
                        id: mutation.id,
                        sequence: 1,
                        status: 'applied',
                        applied: true,
                        error: null,
                        retryable: false,
                        identity: {
                            entity_type: 'payment',
                            local_id: pushedPaymentLocalId,
                            server_id: 98501,
                        },
                        related_identities: [{
                            entity_type: 'invoice',
                            local_id: 'offline-payment-invoice-identity',
                            server_id: 98401,
                            revision: 2,
                        }],
                    }],
                    applied: [mutation.id],
                }),
            });
        });
        await page.route('**/api/sync/pull**', route => route.fulfill({
            status: 200,
            contentType: 'application/json',
            body: JSON.stringify({ mutations: [] }),
        }));
        await page.route('**/api/sync/bootstrap**', route => route.fulfill({
            status: 200,
            contentType: 'application/json',
            body: JSON.stringify({
                business: { id: businessId, name: 'Offline payment identity business', currency: 'ZAR' },
            }),
        }));

        await page.context().setOffline(false);
        await page.evaluate(() => window.ZazuOffline.sync());
        await expect.poll(() => pushedPaymentLocalId).toBe(local.pending[0].payload.local_id);
        await expect.poll(() => page.evaluate(localId => {
            const invoice = window.ZazuOffline.getState().invoices.find(item => item.local_id === 'offline-payment-invoice-identity');
            const payment = invoice?.payments?.find(item => item.local_id === localId);
            return {
                id: payment?.id ?? null,
                serverId: payment?.server_id ?? null,
                pendingSync: payment?.pending_sync ?? null,
                invoiceRevision: invoice?.revision ?? null,
            };
        }, pushedPaymentLocalId)).toEqual({ id: 98501, serverId: 98501, pendingSync: false, invoiceRevision: 2 });

        expect(await page.evaluate(() => window.ZazuOffline.pending())).toHaveLength(0);
    });

    test('offline expense sync preserves job, supplier and purchase-order links', async ({ page, context }, testInfo) => {
        test.setTimeout(35_000);
        test.skip(testInfo.project.name !== 'chromium', 'Offline expense reconciliation runs once in Chromium.');

        const jobLocalId = 'a1a1a1a1-a1a1-41a1-81a1-a1a1a1a1a1a1';
        const supplierLocalId = 'b2b2b2b2-b2b2-42b2-82b2-b2b2b2b2b2b2';
        const orderLocalId = 'c3c3c3c3-c3c3-43c3-83c3-c3c3c3c3c3c3';

        await page.addInitScript(() => {
            window.__ZAZU_OFFLINE_DEFER_INITIAL_SYNC = true;
        });
        await login(page);
        await page.goto('/finance/expenses/create', { waitUntil: 'domcontentloaded' });
        const expenseForm = page.locator('form[data-zazu-offline-entity="expense"]');
        await expect(expenseForm).toBeVisible();
        await page.evaluate(() => window.ZazuOffline.ready);
        const businessId = await page.evaluate(() => Number(document.body.dataset.zazuBusinessId));
        expect(businessId).toBeGreaterThan(0);

        await page.evaluate(async ({ activeBusinessId, jobLocalId, supplierLocalId, orderLocalId }) => {
            const state = window.ZazuOffline.getState();
            state.sync = {
                token: 'offline-expense-reconciliation-token',
                device: { id: 987667, business_id: activeBusinessId },
            };
            state.jobs = [{
                id: 99101,
                server_id: 99101,
                local_id: jobLocalId,
                name: 'Offline expense job',
                status: 'confirmed',
            }];
            state.suppliers = [{
                id: 99102,
                server_id: 99102,
                local_id: supplierLocalId,
                name: 'Offline expense supplier',
            }];
            state.purchase_orders = [{
                id: 99103,
                server_id: 99103,
                local_id: orderLocalId,
                reference: 'PO-OFF-EXPENSE-001',
                status: 'received',
                supplier_id: 99102,
                event_id: 99101,
                supplier_local_id: supplierLocalId,
                event_local_id: jobLocalId,
                currency: 'ZAR',
                total_amount: '450.00',
            }];
            state.expenses = [];
            state.local_dirty = false;
            await window.ZazuOffline.persist({ allowSessionChange: true });
        }, { activeBusinessId: businessId, jobLocalId, supplierLocalId, orderLocalId });

        await page.locator('select[name="supplier_id"]').evaluate(select => {
            select.add(new Option('Offline expense supplier', '99102'));
            select.value = '99102';
        });
        await page.locator('select[name="event_id"]').evaluate(select => {
            select.add(new Option('Offline expense job', '99101'));
            select.value = '99101';
        });
        await page.locator('select[name="purchase_order_id"]').evaluate(select => {
            select.add(new Option('PO-OFF-EXPENSE-001', '99103'));
            select.value = '99103';
        });

        await context.setOffline(true);
        await expenseForm.locator('input[name="description"]').fill('Offline catering fuel');
        await expenseForm.locator('input[name="amount"]').fill('125.50');
        await expenseForm.locator('input[name="expense_date"]').fill('2026-11-21');
        await expenseForm.locator('select[name="status"]').selectOption('paid');
        await expenseForm.locator('input[name="reference"]').fill('FUEL-OFF-001');
        await expenseForm.locator('textarea[name="notes"]').fill('Captured while disconnected.');
        await expenseForm.getByRole('button', { name: 'Record expense' }).click();
        await expect(expenseForm.getByRole('status')).toContainText('Saved on this device');

        const local = await page.evaluate(async () => {
            const queued = await window.ZazuOffline.pending();
            const mutation = queued.find(item => item.entity_type === 'expense');
            const expense = window.ZazuOffline.getState().expenses.find(item =>
                item.local_id === mutation?.payload?.local_id
            );
            const record = mutation?.payload?.record || mutation?.payload || {};
            return {
                localId: mutation?.payload?.local_id || null,
                entityType: mutation?.entity_type || null,
                eventLocalId: record.event_local_id || null,
                supplierLocalId: record.supplier_local_id || null,
                orderLocalId: record.purchase_order_local_id || null,
                expense: expense ? {
                    description: expense.description,
                    amount: expense.amount,
                    status: expense.status,
                    pendingSync: expense?.pending_sync ?? null,
                } : null,
            };
        });
        expect(local).toEqual({
            localId: expect.any(String),
            entityType: 'expense',
            eventLocalId: jobLocalId,
            supplierLocalId,
            orderLocalId,
            expense: {
                description: 'Offline catering fuel',
                amount: '125.50',
                status: 'paid',
                pendingSync: null,
            },
        });

        let pushed = null;
        await page.route('**/api/sync/**', async route => {
            const url = new URL(route.request().url());
            if (url.pathname === '/api/sync/push') {
                const body = route.request().postDataJSON();
                const mutation = body.mutations.find(item => item.entity_type === 'expense');
                const record = mutation?.payload?.record || mutation?.payload || {};
                pushed = {
                    mutationId: mutation?.id || null,
                    localId: record.local_id || mutation?.payload?.local_id || null,
                    eventLocalId: record.event_local_id || null,
                    supplierLocalId: record.supplier_local_id || null,
                    orderLocalId: record.purchase_order_local_id || null,
                };
                await route.fulfill({
                    status: 200,
                    contentType: 'application/json',
                    body: JSON.stringify({
                        recorded: [{
                            id: mutation.id,
                            status: 'applied',
                            applied: true,
                            identity: {
                                entity_type: 'expense',
                                local_id: pushed.localId,
                                server_id: 99201,
                                revision: 1,
                            },
                            related_identities: [],
                        }],
                        applied: [mutation.id],
                    }),
                });
                return;
            }
            if (url.pathname === '/api/sync/pull') {
                await route.fulfill({ status: 200, contentType: 'application/json', body: JSON.stringify({ mutations: [] }) });
                return;
            }
            if (url.pathname === '/api/sync/attachments') {
                await route.fulfill({ status: 200, contentType: 'application/json', body: JSON.stringify({ attachments: [] }) });
                return;
            }
            if (url.pathname === '/api/sync/bootstrap') {
                await route.fulfill({
                    status: 200,
                    contentType: 'application/json',
                    body: JSON.stringify({ business: { id: businessId, name: 'Offline expense business', currency: 'ZAR' } }),
                });
                return;
            }
            await route.fulfill({ status: 404, contentType: 'application/json', body: JSON.stringify({ message: 'Unexpected sync request' }) });
        });

        await context.setOffline(false);
        await page.evaluate(() => window.ZazuOffline.sync());
        await expect.poll(() => pushed?.localId).toBe(local.localId);
        expect(pushed).toEqual({
            mutationId: expect.any(String),
            localId: local.localId,
            eventLocalId: jobLocalId,
            supplierLocalId,
            orderLocalId,
        });
        await expect.poll(
            () => page.evaluate(async () => (await window.ZazuOffline.pending()).length),
            { message: 'the acknowledged offline expense must leave the durable queue' },
        ).toBe(0);

        const reconciled = await page.evaluate(localId => {
            const state = window.ZazuOffline.getState();
            const expense = state.expenses.find(item => item.local_id === localId);
            return {
                id: expense?.id ?? null,
                serverId: expense?.server_id ?? null,
                pendingSync: expense?.pending_sync ?? null,
                eventLocalId: expense?.event_local_id ?? null,
                supplierLocalId: expense?.supplier_local_id ?? null,
                orderLocalId: expense?.purchase_order_local_id ?? null,
                localDirty: state.local_dirty,
            };
        }, local.localId);

        expect(reconciled).toEqual({
            id: 99201,
            serverId: 99201,
            pendingSync: null,
            eventLocalId: jobLocalId,
            supplierLocalId,
            orderLocalId,
            localDirty: false,
        });
    });

    test('offline deposits respect the quote deposit requirement', async ({ page }, testInfo) => {
        test.setTimeout(35_000);
        test.skip(testInfo.project.name !== 'chromium', 'Offline deposit limit runs once in Chromium.');

        await page.addInitScript(() => {
            window.__ZAZU_OFFLINE_DEFER_INITIAL_SYNC = true;
        });
        await login(page);
        await page.goto('/finance/payments/create', { waitUntil: 'domcontentloaded' });
        await page.evaluate(() => window.ZazuOffline.ready);
        const businessId = await page.evaluate(() => Number(document.body.dataset.zazuBusinessId));
        expect(businessId).toBeGreaterThan(0);

        await page.evaluate(async activeBusinessId => {
            const state = window.ZazuOffline.getState();
            state.sync = {
                token: 'offline-deposit-limit-token',
                device: { id: 987665, business_id: activeBusinessId },
            };
            state.invoices = [{
                local_id: 'offline-deposit-invoice',
                id: 98601,
                server_id: 98601,
                number: 'INV-OFFLINE-DEPOSIT-001',
                status: 'issued',
                currency: 'ZAR',
                total: '250.00',
                paid_amount: '0.00',
                balance: '250.00',
                deposit_required: '75.00',
                deposit_paid_amount: '0.00',
                deposit_balance: '75.00',
                payments: [],
            }];
            await window.ZazuOffline.persist({ allowSessionChange: true });
        }, businessId);

        const invoiceSelect = page.locator('select[name="invoice_id"]');
        await invoiceSelect.evaluate(select => {
            select.add(new Option('INV-OFFLINE-DEPOSIT-001 · ZAR 250.00 outstanding', '98601'));
            select.value = '98601';
        });
        await page.locator('select[name="type"]').selectOption('deposit');
        const paymentForm = page.locator('form[data-zazu-offline-entity="payment"]');
        await page.context().setOffline(true);

        let depositError = null;
        page.once('dialog', async dialog => {
            depositError = dialog.message();
            await dialog.dismiss();
        });
        await page.locator('input[name="amount"]').fill('75.01');
        await paymentForm.getByRole('button', { name: 'Record payment' }).click();
        await expect.poll(() => depositError).toContain('This deposit would exceed the required deposit amount.');
        expect(await page.evaluate(() => window.ZazuOffline.pending())).toHaveLength(0);

        await page.locator('input[name="amount"]').fill('75.00');
        await paymentForm.getByRole('button', { name: 'Record payment' }).click();
        await expect(paymentForm.getByRole('status')).toContainText('Saved on this device');

        const state = await page.evaluate(async () => {
            const invoice = window.ZazuOffline.getState().invoices.find(item => item.local_id === 'offline-deposit-invoice');
            return {
                status: invoice?.status,
                paidAmount: invoice?.paid_amount,
                balance: invoice?.balance,
                depositPaid: invoice?.deposit_paid_amount,
                depositBalance: invoice?.deposit_balance,
                payment: invoice?.payments?.[0] ?? null,
                pending: await window.ZazuOffline.pending(),
            };
        });

        expect(state.status).toBe('issued');
        expect(state.paidAmount).toBe('75.00');
        expect(state.balance).toBe('175.00');
        expect(state.depositPaid).toBe('75.00');
        expect(state.depositBalance).toBe('0.00');
        expect(state.payment).toMatchObject({
            type: 'deposit',
            amount: '75.00',
            pending_sync: true,
            id: null,
            server_id: null,
        });
        expect(state.pending).toHaveLength(1);
        expect(state.pending[0].entity_type).toBe('payment');
    });

    test('paired device can retry or discard a failed offline sync change', async ({ page }, testInfo) => {
        test.setTimeout(30_000);
        test.skip(testInfo.project.name !== 'chromium', 'Sync conflict controls run once; responsive layouts are covered by the settings suite.');

        await login(page);
        await page.goto('/settings', { waitUntil: 'domcontentloaded' });
        await page.evaluate(() => window.ZazuOffline.ready);

        const fixture = await page.evaluate(async () => {
            const offline = window.ZazuOffline;
            const initialState = offline.getState();
            initialState.sync = { token: 'sync-conflict-e2e-token', device: { id: 987650 } };
            await offline.persist({ allowSessionChange: true });

            const first = await offline.queueMutation('job', 'create', {
                local_id: crypto.randomUUID(),
                record: { name: 'Retry this failed Work change' },
            });
            const second = await offline.queueMutation('customer', 'create', {
                local_id: crypto.randomUUID(),
                record: { name: 'Discard this failed customer change' },
            });

            // queueMutation commits a replacement workspace object. Re-read the
            // current state after queue writes instead of mutating a stale reference.
            const currentState = offline.getState();
            currentState.conflicts = [
                {
                    id: 987651,
                    entity_type: 'job',
                    entity_id: first.entity_id,
                    conflict_type: 'mutation_identity_mismatch',
                    message: 'This edited change needs a new mutation identity before retry.',
                    status: 'open',
                },
                {
                    id: 987652,
                    entity_type: 'customer',
                    entity_id: second.entity_id,
                    message: 'This customer change needs review.',
                    status: 'open',
                },
            ];
            currentState.local_dirty = false;
            await offline.persist();

            const db = await new Promise((resolve, reject) => {
                const request = indexedDB.open('zazu-client-business-' + document.body.dataset.zazuBusinessId, 1);
                request.onsuccess = () => resolve(request.result);
                request.onerror = () => reject(request.error || new Error('Could not open offline storage'));
            });
            await new Promise((resolve, reject) => {
                const tx = db.transaction(['mutations'], 'readwrite');
                const store = tx.objectStore('mutations');
                store.put({ ...first, status: 'failed', conflict_id: 987651, last_error: 'Related customer missing' });
                store.put({ ...second, status: 'failed', conflict_id: 987652, last_error: 'Review required' });
                tx.oncomplete = resolve;
                tx.onerror = () => reject(tx.error || new Error('Could not stage failed sync records'));
                tx.onabort = () => reject(tx.error || new Error('Failed sync staging was aborted'));
            });
            db.close();
            window.dispatchEvent(new CustomEvent('zazu:offline-state-updated'));

            return { firstId: first.id, secondId: second.id };
        });

        const resolutions = [];
        await page.route('**/api/sync/conflicts/*/resolve', async route => {
            const requestUrl = new URL(route.request().url());
            const conflictId = Number(requestUrl.pathname.split('/').slice(-2, -1)[0]);
            const body = route.request().postDataJSON();
            resolutions.push({ conflictId, action: body.action });
            await route.fulfill({
                status: 200,
                contentType: 'application/json',
                body: JSON.stringify({
                    conflict: { id: conflictId, status: 'resolved' },
                    mutation_status: body.action === 'retry'
                        ? (conflictId === 987651 ? 'new_mutation_required' : 'pending')
                        : 'discarded',
                }),
            });
        });
        await page.route('**/api/sync/push', route => route.fulfill({
            status: 503,
            contentType: 'application/json',
            body: JSON.stringify({ message: 'Sync server unavailable for this test.' }),
        }));

        await expect(page.locator('[data-zazu-offline-conflicts]')).toBeVisible();
        await expect(page.locator('[data-zazu-offline-conflict-list] article')).toHaveCount(2);

        await page.locator('[data-zazu-offline-conflict-list] article[data-conflict-id="987651"]')
            .getByRole('button', { name: 'Retry with new ID' }).click();
        await expect.poll(() => resolutions.length).toBe(1);
        await expect.poll(() => page.locator('[data-zazu-offline-conflict-list] article').count()).toBe(1);

        const retryQueue = await page.evaluate(async () => window.ZazuOffline.pending());
        expect(retryQueue).toHaveLength(1);
        expect(retryQueue[0].id).not.toBe(fixture.firstId);
        expect(retryQueue[0].entity_id).not.toBe('');
        expect(retryQueue[0].payload.record.name).toBe('Retry this failed Work change');
        expect(retryQueue[0].status).toBe('pending');
        expect(retryQueue[0].conflict_id).toBeUndefined();

        page.once('dialog', dialog => dialog.accept());
        await page.locator('[data-zazu-offline-conflict-list] article[data-conflict-id="987652"]')
            .getByRole('button', { name: 'Discard change' }).click();
        await expect.poll(() => resolutions.length).toBe(2);
        // Keep the panel visible long enough for assistive technology and the
        // operator to receive the final resolution confirmation.
        await expect(page.locator('[data-zazu-offline-conflicts]')).toBeVisible();
        await expect(page.locator('[data-zazu-offline-conflict-status]')).toHaveText('Saved change discarded.');

        const remaining = await page.evaluate(async () => {
            const db = await new Promise((resolve, reject) => {
                const request = indexedDB.open('zazu-client-business-' + document.body.dataset.zazuBusinessId, 1);
                request.onsuccess = () => resolve(request.result);
                request.onerror = () => reject(request.error || new Error('Could not open offline storage'));
            });
            const records = await new Promise((resolve, reject) => {
                const tx = db.transaction(['mutations'], 'readonly');
                const request = tx.objectStore('mutations').getAll();
                tx.oncomplete = () => resolve(request.result || []);
                tx.onerror = () => reject(tx.error || new Error('Could not inspect offline queue'));
            });
            db.close();
            return records;
        });

        expect(resolutions).toEqual([
            { conflictId: 987651, action: 'retry' },
            { conflictId: 987652, action: 'discard' },
        ]);
        expect(remaining).toHaveLength(1);
        expect(remaining[0].id).not.toBe(fixture.firstId);
        expect(remaining[0].status).toBe('pending');
        expect(remaining.some(item => item.id === fixture.secondId)).toBe(false);
    });

    test('offline logout keeps local data when the server cannot end the web session', async ({ page, context }, testInfo) => {
        test.setTimeout(20_000);
        test.skip(testInfo.project.name !== 'chromium', 'Disconnected logout contract runs once; viewport behaviour is covered elsewhere.');

        await login(page);
        await page.waitForFunction(() => Boolean(window.ZazuOffline?.ready));
        await page.evaluate(async () => {
            await window.ZazuOffline.ready;
            const state = window.ZazuOffline.getState();
            state.business = { id: 987654, name: 'Offline logout safety fixture' };
            await window.ZazuOffline.persist();
            await window.ZazuOffline.queueMutation('customer', 'create', {
                local_id: crypto.randomUUID(),
                name: 'Keep this queued while the server is unavailable',
            });
        });

        await context.setOffline(true);
        const dialogs = [];
        page.on('dialog', async dialog => {
            dialogs.push({ type: dialog.type(), message: dialog.message() });
            if (dialog.type() === 'confirm') await dialog.accept();
            else await dialog.dismiss();
        });

        await page.evaluate(() => {
            const form = document.querySelector('form[action$="/logout"]');
            if (!form) throw new Error('Logout form was not found');
            form.requestSubmit();
        });

        await expect.poll(() => dialogs.length).toBe(2);
        expect(dialogs[0].type).toBe('confirm');
        expect(dialogs[0].message).toContain('Signing out ends this web session');
        expect(dialogs[1].type).toBe('alert');
        expect(dialogs[1].message).toContain('could not reach the server to end your session');
        expect(dialogs[1].message).toContain('offline workspace and queued work are safe');
        await expect.poll(() => new URL(page.url()).pathname).toBe('/dashboard');

        const localState = await page.evaluate(async () => ({
            businessId: window.ZazuOffline.getState()?.business?.id,
            pendingCount: (await window.ZazuOffline.pending()).length,
        }));
        expect(localState.businessId).toBe(987654);
        expect(localState.pendingCount).toBe(1);
    });

    test('logout preserves the private cache on network failure and blocks late HTML after server confirmation', async ({ page }, testInfo) => {
        test.setTimeout(20_000);
        test.skip(testInfo.project.name !== 'chromium', 'Service-worker cache race contract runs once.');

        await login(page);
        const logoutCacheResult = await page.evaluate(async () => {
            const workerSource = await fetch('/sw.js', { cache: 'no-store' }).then(response => response.text());
            const handlers = {};
            const cacheStores = new Map();
            const NativeRequest = window.Request;

            class WorkerRequest extends NativeRequest {
                constructor(input, init) {
                    const resolved = typeof input === 'string' && input.startsWith('/')
                        ? new URL(input, window.location.origin).href
                        : input;
                    super(resolved, init);
                }
            }

            const requestKey = request => typeof request === 'string'
                ? new URL(request, window.location.origin).href
                : request.url;

            const cachesApi = {
                async open(name) {
                    if (!cacheStores.has(name)) {
                        const records = new Map();
                        cacheStores.set(name, {
                            async match(request) {
                                const value = records.get(requestKey(request));
                                return value ? value.clone() : null;
                            },
                            async put(request, response) {
                                records.set(requestKey(request), response.clone());
                            },
                            async delete(request) {
                                return records.delete(requestKey(request));
                            },
                            async add(request) {
                                records.set(requestKey(request), new Response('cached'));
                            },
                        });
                    }
                    return cacheStores.get(name);
                },
                async keys() {
                    return [...cacheStores.keys()];
                },
                async delete(name) {
                    return cacheStores.delete(name);
                },
                async match(request) {
                    for (const cache of cacheStores.values()) {
                        const response = await cache.match(request);
                        if (response) return response;
                    }
                    return null;
                },
            };

            const mockSelf = {
                location: { origin: window.location.origin },
                addEventListener(type, callback) {
                    handlers[type] = callback;
                },
                skipWaiting: () => Promise.resolve(),
                clients: { claim: () => Promise.resolve() },
            };

            let resolveHtml;
            let markNetworkStarted;
            const networkStarted = new Promise(resolve => { markNetworkStarted = resolve; });
            const stalledHtml = new Promise(resolve => { resolveHtml = resolve; });
            const cachePrefix = workerSource.match(/const CACHE_NAME = '([^']+)'/)?.[1];
            if (!cachePrefix) throw new Error('Service-worker cache version was not found');
            const userCacheName = cachePrefix + '-user-123-business-456';
            await cachesApi.open(userCacheName);

            let logoutFails = true;
            const mockFetch = input => {
                const url = new URL(typeof input === 'string' ? input : input.url, window.location.origin);
                if (url.pathname === '/dashboard') {
                    markNetworkStarted();
                    return stalledHtml;
                }
                if (url.pathname === '/logout') {
                    return logoutFails
                        ? Promise.reject(new Error('Server is unreachable'))
                        : Promise.resolve({ ok: true, redirected: true });
                }
                return Promise.reject(new Error('Unexpected network request in cache-race test: ' + url.pathname));
            };

            // Execute the worker in an inline script instead of Function/eval:
            // the app's CSP intentionally disallows unsafe-eval.
            const workerScript = document.createElement('script');
            const trustedNonce = document.querySelector('script[nonce]')?.nonce;
            if (!trustedNonce) {
                throw new Error('CSP nonce was not available to initialize the service-worker test harness');
            }
            workerScript.nonce = trustedNonce;
            workerScript.textContent = `(function(self, caches, fetch, Request, Response, AbortController, setTimeout, clearTimeout, URL) {
                ${workerSource}
            })(window.__zazuMockSelf, window.__zazuMockCaches, window.__zazuMockFetch, window.__zazuMockRequest, window.Response, window.AbortController, window.setTimeout.bind(window), window.clearTimeout.bind(window), window.URL);`;
            window.__zazuMockSelf = mockSelf;
            window.__zazuMockCaches = cachesApi;
            window.__zazuMockFetch = mockFetch;
            window.__zazuMockRequest = WorkerRequest;
            document.head.appendChild(workerScript);
            workerScript.remove();
            delete window.__zazuMockSelf;
            delete window.__zazuMockCaches;
            delete window.__zazuMockFetch;
            delete window.__zazuMockRequest;

            const cacheWaits = [];
            let navigationResponse;
            const navigation = new WorkerRequest(new URL('/dashboard?late-cache-test=1', window.location.origin).href, {
                headers: { Accept: 'text/html' },
            });

            handlers.fetch({
                request: navigation,
                respondWith(promise) { navigationResponse = Promise.resolve(promise); },
                waitUntil(promise) { cacheWaits.push(promise); },
            });

            await networkStarted;

            let failedLogoutResponse;
            handlers.fetch({
                request: new WorkerRequest(new URL('/logout', window.location.origin).href, { method: 'POST' }),
                respondWith(promise) { failedLogoutResponse = Promise.resolve(promise); },
            });
            let failedLogoutRejected = false;
            try {
                await failedLogoutResponse;
            } catch {
                failedLogoutRejected = true;
            }
            const cachePreservedAfterFailure = (await cachesApi.keys()).includes(userCacheName);

            logoutFails = false;
            let successfulLogoutResponse;
            handlers.fetch({
                request: new WorkerRequest(new URL('/logout', window.location.origin).href, { method: 'POST' }),
                respondWith(promise) { successfulLogoutResponse = Promise.resolve(promise); },
            });
            await successfulLogoutResponse;

            resolveHtml(new window.Response(
                '<!doctype html><html><body data-zazu-user-id="123">stale authenticated page</body></html>',
                { status: 200, headers: { 'content-type': 'text/html; charset=utf-8' } }
            ));
            await navigationResponse;
            await Promise.all(cacheWaits);

            return {
                failedLogoutRejected,
                cachePreservedAfterFailure,
                userCaches: (await cachesApi.keys()).filter(name => name.includes('-user-')),
            };
        });

        expect(logoutCacheResult.failedLogoutRejected).toBe(true);
        expect(logoutCacheResult.cachePreservedAfterFailure).toBe(true);
        expect(logoutCacheResult.userCaches).toEqual([]);
    });

    test('logout preserves the business offline workspace and paired device across account sessions', async ({ page }) => {
        test.setTimeout(60_000);

        await login(page);
        await waitForOfflineRuntime(page);
        // Owner approval is explicit; then replace the real credential with a
        // deterministic fixture for the logout preservation test.
        await trustCurrentDevice(page);
        const databaseName = await page.evaluate(() => {
            const businessId = Number(document.body.dataset.zazuBusinessId);
            if (!Number.isSafeInteger(businessId) || businessId < 1) {
                throw new Error('Authenticated page did not expose its active business ID');
            }
            return 'zazu-client-business-' + businessId;
        });

        await page.evaluate(async name => {
            await window.ZazuOffline.ready;
            const state = window.ZazuOffline.getState();
            state.business = {
                id: Number(document.body.dataset.zazuBusinessId),
                name: 'Trusted business workspace fixture',
            };
            state.sync = {
                token: 'business-device-token-fixture',
                device: { id: 987650, business_id: Number(document.body.dataset.zazuBusinessId) },
            };
            await window.ZazuOffline.persist({ allowSessionChange: true });
            await window.ZazuOffline.queueMutation('customer', 'create', {
                local_id: crypto.randomUUID(),
                name: 'Keep this queued across account logout',
            });

            // A failed mutation keeps the fixture durable without relying on
            // network timing or an automatic sync during reload.
            const db = await new Promise((resolve, reject) => {
                const request = indexedDB.open(name, 1);
                request.onsuccess = () => resolve(request.result);
                request.onerror = () => reject(request.error || new Error('Could not open local offline storage'));
            });
            await new Promise((resolve, reject) => {
                const tx = db.transaction(['mutations'], 'readwrite');
                const store = tx.objectStore('mutations');
                const request = store.getAll();
                request.onsuccess = () => {
                    for (const record of request.result || []) store.put({ ...record, status: 'failed' });
                };
                tx.oncomplete = resolve;
                tx.onerror = () => reject(tx.error || new Error('Could not mark mutation as failed'));
                tx.onabort = () => reject(tx.error || new Error('Offline mutation transaction aborted'));
            });
            db.close();
        }, databaseName);

        let confirmLogout = false;
        const confirmMessages = [];
        page.on('dialog', async dialog => {
            if (dialog.type() !== 'confirm') {
                await dialog.dismiss();
                return;
            }
            confirmMessages.push(dialog.message());
            if (confirmLogout) await dialog.accept();
            else await dialog.dismiss();
        });

        const requestLogout = () => page.evaluate(() => {
            const form = document.querySelector('form[action$="/logout"]');
            if (!form) throw new Error('Logout form was not found');
            form.requestSubmit();
        });
        const inspectWorkspace = () => page.evaluate(name => new Promise((resolve, reject) => {
            const request = indexedDB.open(name, 1);
            request.onerror = () => reject(request.error || new Error('Could not inspect business offline storage'));
            request.onblocked = () => reject(new Error('Business offline storage inspection was blocked'));
            request.onsuccess = () => {
                const db = request.result;
                const tx = db.transaction(['state', 'mutations'], 'readonly');
                const stateRequest = tx.objectStore('state').get('current');
                const queueRequest = tx.objectStore('mutations').getAll();
                tx.oncomplete = () => {
                    resolve({
                        tokenPresent: Boolean(stateRequest.result?.sync?.token),
                        token: stateRequest.result?.sync?.token || null,
                        queueCount: (queueRequest.result || []).length,
                        workspacePresent: Boolean(stateRequest.result?.business),
                    });
                    db.close();
                };
                tx.onerror = () => reject(tx.error || new Error('Could not inspect business offline workspace'));
            };
        }), databaseName);

        await requestLogout();
        await expect.poll(() => new URL(page.url()).pathname).toBe('/dashboard');
        await expect.poll(() => confirmMessages.length).toBe(1);
        expect(confirmMessages[0]).toContain('business-scoped offline workspace');
        expect(confirmMessages[0]).toContain('will remain on this device');
        expect(await inspectWorkspace()).toMatchObject({
            tokenPresent: true,
            token: 'business-device-token-fixture',
            queueCount: 1,
            workspacePresent: true,
        });

        // The local business workspace survives a cold reload and a full
        // account logout. Only the user-scoped page cache is cleared.
        await page.reload({ waitUntil: 'domcontentloaded' });
        await expect(page.getByRole('heading', { name: 'Dashboard' })).toBeVisible();
        await page.evaluate(() => window.ZazuOffline.ready);
        expect(await inspectWorkspace()).toMatchObject({
            tokenPresent: true,
            token: 'business-device-token-fixture',
            queueCount: 1,
            workspacePresent: true,
        });

        confirmLogout = true;
        await Promise.all([
            page.waitForURL(url => url.pathname === '/'),
            requestLogout(),
        ]);
        expect(await inspectWorkspace()).toMatchObject({
            tokenPresent: true,
            token: 'business-device-token-fixture',
            queueCount: 1,
            workspacePresent: true,
        });

        // Re-authenticating into the same business reopens the same trusted
        // business workspace without requiring the owner to re-pair the phone.
        await login(page, MANAGER_EMAIL, ROLE_PASSWORD);
        await expect.poll(() => page.locator('body').evaluate(node => Number(node.dataset.zazuBusinessId))).toBe(Number(databaseName.slice('zazu-client-business-'.length)));
        await page.evaluate(() => window.ZazuOffline.ready);
        expect(await inspectWorkspace()).toMatchObject({
            tokenPresent: true,
            token: 'business-device-token-fixture',
            queueCount: 1,
            workspacePresent: true,
        });
    });

    test('late sync bootstrap from a previous device session cannot overwrite the active workspace', async ({ page }) => {
        test.setTimeout(30_000);

        await login(page);
        await page.evaluate(async () => {
            await window.ZazuOffline.ready;
            const state = window.ZazuOffline.getState();
            state.business = {
                id: Number(document.body.dataset.zazuBusinessId),
                name: 'Current trusted workspace',
            };
            state.sync = {
                token: 'previous-device-session-token',
                device: { id: 987651, business_id: Number(document.body.dataset.zazuBusinessId) },
            };
            state.local_dirty = false;
            await window.ZazuOffline.persist({ allowSessionChange: true });
        });

        let markRequestStarted;
        let releaseResponse;
        const requestStarted = new Promise(resolve => { markRequestStarted = resolve; });
        const heldResponse = new Promise(resolve => { releaseResponse = resolve; });

        await page.route('**/api/sync/bootstrap**', async route => {
            markRequestStarted();
            await heldResponse;
            await route.fulfill({
                status: 200,
                contentType: 'application/json',
                body: JSON.stringify({
                    business: {
                        id: Number(await page.locator('body').getAttribute('data-zazu-business-id')),
                        name: 'Stale response must not replace this workspace',
                    },
                    catalogue: [],
                    suppliers: [],
                    purchase_orders: [],
                    invoices: [],
                    expenses: [],
                    inventory_items: [],
                    assets: [],
                    costs: [],
                    selected: [],
                }),
            });
        });

        await page.evaluate(() => {
            window.__zazuStaleBootstrap = window.ZazuOffline.refreshBootstrap()
                .then(() => ({ ok: true }))
                .catch(error => ({ ok: false, message: error.message }));
        });
        await requestStarted;

        await page.evaluate(async () => {
            const state = window.ZazuOffline.getState();
            state.sync.token = 'replacement-device-session-token';
            await window.ZazuOffline.persist({ allowSessionChange: true });
        });
        releaseResponse();

        const outcome = await page.evaluate(() => window.__zazuStaleBootstrap);
        expect(outcome.ok).toBe(false);
        expect(outcome.message).toContain('device session changed');

        const state = await page.evaluate(() => window.ZazuOffline.getState());
        expect(state.sync.token).toBe('replacement-device-session-token');
        expect(state.business.name).toBe('Current trusted workspace');
    });

    test('a bootstrap response cannot overwrite an offline mutation queued while it is in flight', async ({ page }, testInfo) => {
        test.setTimeout(30_000);
        test.skip(testInfo.project.name !== 'chromium', 'The IndexedDB bootstrap race runs once in Chromium.');

        await login(page);
        await page.goto('/settings', { waitUntil: 'domcontentloaded' });
        await page.evaluate(async () => {
            await window.ZazuOffline.ready;
            const state = window.ZazuOffline.getState();
            state.business = {
                id: Number(document.body.dataset.zazuBusinessId),
                name: 'Current local workspace',
            };
            state.sync = {
                token: 'bootstrap-race-device-token',
                device: { id: 987659, business_id: Number(document.body.dataset.zazuBusinessId) },
            };
            state.local_dirty = false;
            await window.ZazuOffline.persist({ allowSessionChange: true });
        });

        let markRequestStarted;
        let releaseResponse;
        const requestStarted = new Promise(resolve => { markRequestStarted = resolve; });
        const heldResponse = new Promise(resolve => { releaseResponse = resolve; });

        await page.route('**/api/sync/bootstrap**', async route => {
            markRequestStarted();
            await heldResponse;
            await route.fulfill({
                status: 200,
                contentType: 'application/json',
                body: JSON.stringify({
                    business: {
                        id: Number(await page.locator('body').getAttribute('data-zazu-business-id')),
                        name: 'Stale remote workspace snapshot',
                    },
                    catalogue: [],
                    suppliers: [],
                    purchase_orders: [],
                    invoices: [],
                    expenses: [],
                    inventory_items: [],
                    assets: [],
                    costs: [],
                    selected: [],
                }),
            });
        });

        await page.evaluate(() => {
            window.__zazuQueuedBootstrapRace = window.ZazuOffline.refreshBootstrap();
        });
        await requestStarted;

        const mutation = await page.evaluate(() => window.ZazuOffline.queueMutation('customer', 'create', {
            local_id: 'bootstrap-race-local-customer',
            record: { name: 'Must survive bootstrap' },
        }));
        releaseResponse();
        await page.evaluate(() => window.__zazuQueuedBootstrapRace);

        const outcome = await page.evaluate(async mutationId => ({
            businessName: window.ZazuOffline.getState().business.name,
            localDirty: window.ZazuOffline.getState().local_dirty,
            pending: (await window.ZazuOffline.pending()).map(item => item.id),
        }), mutation.id);

        expect(outcome.businessName).toBe('Current local workspace');
        expect(outcome.localDirty).toBe(true);
        expect(outcome.pending).toContain(mutation.id);
    });

    test('expired or revoked sync credentials are surfaced without dropping queued work', async ({ page }) => {
        test.setTimeout(30_000);
        await login(page);
        await page.goto('/settings', { waitUntil: 'domcontentloaded' });
        await page.evaluate(() => window.ZazuOffline.ready);

        const mutationIds = await page.evaluate(async () => {
            const state = window.ZazuOffline.getState();
            state.invoices = [{
                id: null,
                local_id: 'expired-device-local-invoice',
                status: 'issued',
                total: '100.00',
                paid_amount: '0.00',
                balance: '100.00',
                payments: [],
                revision: 1,
            }];
            state.sync = { token: 'expired-device-token-fixture', device: { id: 987699 } };
            await window.ZazuOffline.persist({ allowSessionChange: true });
            const customerMutation = await window.ZazuOffline.queueMutation('customer', 'create', {
                local_id: 'expired-device-local-customer',
                record: { name: 'Preserve while re-pairing' },
            });
            const paymentMutation = await window.ZazuOffline.queueMutation('payment', 'create', {
                local_id: 'expired-device-local-payment',
                invoice_local_id: 'expired-device-local-invoice',
                amount: '25.00',
                type: 'payment',
            });
            return { customerMutationId: customerMutation.id, paymentMutationId: paymentMutation.id };
        });

        await page.route('**/api/sync/push', route => route.fulfill({
            status: 401,
            contentType: 'application/json',
            body: JSON.stringify({ message: 'The Zazu sync device token has expired.' }),
        }));

        await page.evaluate(() => window.ZazuOffline.sync());

        const outcome = await page.evaluate(async () => ({
            error: window.ZazuOffline.getState().last_sync_error,
            recoveryRequired: window.ZazuOffline.getState().sync_authorization_required === true,
            pending: (await window.ZazuOffline.pending()).map(item => item.id),
            tokenPresent: Boolean(window.ZazuOffline.getState().sync?.token),
        }));

        expect(outcome.error).toContain('no longer authorised to sync');
        expect(outcome.error).toContain('pair the device again');
        expect(outcome.recoveryRequired).toBe(true);
        expect(outcome.pending).toContain(mutationIds.customerMutationId);
        expect(outcome.pending).toContain(mutationIds.paymentMutationId);
        expect(outcome.tokenPresent).toBe(true);
        await expect(page.locator('[data-zazu-sync-recovery]')).toBeVisible();
        await expect(page.locator('[data-zazu-sync-recovery-message]')).toContainText('saved offline changes remain');
    });

    test('re-pairing preserves queued work and its local workspace until sync can reconcile it', async ({ page }) => {
        test.setTimeout(30_000);
        await login(page);
        await page.goto('/settings', { waitUntil: 'domcontentloaded' });
        await page.evaluate(() => window.ZazuOffline.ready);

        const mutationIds = await page.evaluate(async () => {
            const state = window.ZazuOffline.getState();
            state.business = { id: Number(document.body.dataset.zazuBusinessId), name: 'Locally preserved workspace' };
            state.customers = [{ id: null, local_id: 're-pair-local-customer', name: 'Keep this customer' }];
            state.invoices = [{
                id: null,
                local_id: 're-pair-local-invoice',
                status: 'issued',
                total: '100.00',
                paid_amount: '0.00',
                balance: '100.00',
                payments: [],
                revision: 1,
            }];
            state.sync = { token: 'revoked-old-token', device: { id: 987698 } };
            state.sync_authorization_required = true;
            await window.ZazuOffline.persist({ allowSessionChange: true });
            const customerMutation = await window.ZazuOffline.queueMutation('customer', 'create', {
                local_id: 're-pair-local-customer',
                record: { name: 'Keep this customer' },
            });
            const paymentMutation = await window.ZazuOffline.queueMutation('payment', 'create', {
                local_id: 're-pair-local-payment',
                invoice_local_id: 're-pair-local-invoice',
                amount: '25.00',
                type: 'payment',
            });
            return { customerMutationId: customerMutation.id, paymentMutationId: paymentMutation.id };
        });

        const businessId = await page.evaluate(() => Number(document.body.dataset.zazuBusinessId));
        await page.route('**/api/sync/provision', route => route.fulfill({
            status: 200,
            contentType: 'application/json',
            body: JSON.stringify({
                token: 'new-repair-token',
                device: { id: 987700, business_id: businessId },
                bootstrap: {
                    business: { id: businessId, name: 'Remote replacement snapshot' },
                    catalogue: [], suppliers: [], purchase_orders: [], invoices: [], expenses: [],
                    inventory_items: [], assets: [], costs: [], selected: [],
                },
            }),
        }));
        await page.route('**/api/sync/push', route => route.fulfill({
            status: 503,
            contentType: 'application/json',
            body: JSON.stringify({ message: 'Temporary sync outage.' }),
        }));

        await page.evaluate(() => window.ZazuOffline.pair('123456789012'));
        const outcome = await page.evaluate(async () => ({
            businessName: window.ZazuOffline.getState().business?.name,
            customerName: window.ZazuOffline.getState().customers?.[0]?.name,
            token: window.ZazuOffline.getState().sync?.token,
            recoveryRequired: window.ZazuOffline.getState().sync_authorization_required === true,
            pending: (await window.ZazuOffline.pending()).map(item => item.id),
            invoice: window.ZazuOffline.getState().invoices?.find(item => item.local_id === 're-pair-local-invoice') || null,
        }));

        expect(outcome.businessName).toBe('Locally preserved workspace');
        expect(outcome.customerName).toBe('Keep this customer');
        expect(outcome.token).toBe('new-repair-token');
        expect(outcome.recoveryRequired).toBe(false);
        expect(outcome.pending).toContain(mutationIds.customerMutationId);
        expect(outcome.pending).toContain(mutationIds.paymentMutationId);
        expect(outcome.invoice).toMatchObject({ status: 'issued', total: '100.00', paid_amount: '0.00', balance: '100.00', payments: [] });
    });

    test('a cross-business pairing response is revoked without changing local workspace data', async ({ page }) => {
        test.setTimeout(30_000);
        // Keep automatic primary-device registration out of this credential-boundary test.
        await page.addInitScript(() => { window.__ZAZU_OFFLINE_DEFER_INITIAL_SYNC = true; });
        await login(page);
        await page.goto('/settings', { waitUntil: 'domcontentloaded' });
        await page.evaluate(() => window.ZazuOffline.ready);

        const businessId = await page.evaluate(() => Number(document.body.dataset.zazuBusinessId));
        const mutationId = await page.evaluate(async () => {
            const state = window.ZazuOffline.getState();
            state.sync = null;
            state.sync_authorization_required = false;
            state.sync_registration_blocked = false;
            state.last_sync_error = null;
            await window.ZazuOffline.persist({ allowSessionChange: true });
            state.business = { id: Number(document.body.dataset.zazuBusinessId), name: 'Original business workspace' };
            state.customers = [{ id: null, local_id: 'cross-business-guard-customer', name: 'Do not lose this' }];
            await window.ZazuOffline.persist();
            return (await window.ZazuOffline.queueMutation('customer', 'create', {
                local_id: 'cross-business-guard-customer',
                record: { name: 'Do not lose this' },
            })).id;
        });

        await page.route('**/api/sync/provision', route => route.fulfill({
            status: 200,
            contentType: 'application/json',
            body: JSON.stringify({
                token: 'wrong-business-token',
                device: { id: 987701, business_id: businessId + 1 },
                bootstrap: { business: { id: businessId + 1, name: 'Wrong business' }, selected: [] },
            }),
        }));

        let revokedAuthorization = null;
        await page.route('**/api/sync/revoke', async route => {
            revokedAuthorization = route.request().headers()['authorization'] || null;
            await route.fulfill({ status: 200, contentType: 'application/json', body: JSON.stringify({ revoked: true }) });
        });

        const error = await page.evaluate(async () => {
            try {
                await window.ZazuOffline.pair('654321001234');
                return null;
            } catch (failure) {
                return failure.message;
            }
        });
        const outcome = await page.evaluate(async () => ({
            businessName: window.ZazuOffline.getState().business?.name,
            customerName: window.ZazuOffline.getState().customers?.[0]?.name,
            token: window.ZazuOffline.getState().sync?.token || null,
            pending: (await window.ZazuOffline.pending()).map(item => item.id),
        }));

        expect(error).toContain('different business workspace');
        expect(revokedAuthorization).toBe('Bearer wrong-business-token');
        expect(outcome.businessName).toBe('Original business workspace');
        expect(outcome.customerName).toBe('Do not lose this');
        expect(outcome.token).toBeNull();
        expect(outcome.pending).toContain(mutationId);
    });

    test('creates and retains a customer during a true browser-offline session', async ({ page, context }, testInfo) => {
        test.setTimeout(45_000);
        test.skip(testInfo.project.name !== 'chromium', 'True offline mutation acceptance runs once on desktop Chromium.');

        await login(page);
        await page.goto('/customers/create', { waitUntil: 'domcontentloaded' });
        await expect(page.locator('input[name="name"]')).toBeVisible();

        await page.evaluate(async () => {
            if (!('serviceWorker' in navigator)) throw new Error('Service workers are unavailable');
            await navigator.serviceWorker.ready;
        });
        await page.reload({ waitUntil: 'domcontentloaded' });
        await expect.poll(() => page.evaluate(() => Boolean(navigator.serviceWorker.controller))).toBe(true);

        await context.setOffline(true);
        const offlineResponse = await page.reload({ waitUntil: 'domcontentloaded' });
        expect(offlineResponse?.ok()).toBe(true);
        await expect(page.locator('input[name="name"]')).toBeVisible();

        await page.locator('input[name="name"]').fill('True offline acceptance customer');
        await page.locator('input[name="primary_contact_name"]').fill('Offline Contact');
        await page.locator('input[name="primary_contact_phone"]').fill('0123456789');
        await page.getByRole('button', { name: 'Save customer' }).click();
        await expect(page.getByText('Saved on this device. It will sync automatically when Zazu reconnects.')).toBeVisible();

        const queued = await page.evaluate(async () => window.ZazuOffline.pending());
        expect(queued).toHaveLength(1);
        expect(queued[0].entity_type).toBe('customer');
        expect(queued[0].payload.record.name).toBe('True offline acceptance customer');

        await page.reload({ waitUntil: 'domcontentloaded' });
        await page.evaluate(() => window.ZazuOffline.ready);
        const recovered = await page.evaluate(async () => ({
            pending: await window.ZazuOffline.pending(),
            customer: window.ZazuOffline.getState().customers.find(item =>
                item.name === 'True offline acceptance customer'
            ) || null,
        }));
        expect(recovered.pending.map(item => item.id)).toContain(queued[0].id);
        expect(recovered.customer).toBeTruthy();
    });

    test('authenticated dashboard survives a real offline navigation', async ({ page, context }) => {
        test.setTimeout(60_000);

        await login(page);
        await page.goto('/dashboard', { waitUntil: 'domcontentloaded' });
        await expect(page.getByRole('heading', { name: 'Dashboard' })).toBeVisible();

        await page.evaluate(async () => {
            if (!('serviceWorker' in navigator)) throw new Error('Service workers are unavailable');
            await navigator.serviceWorker.ready;
        });

        // The first registration is not necessarily controlling the page yet.
        // Reload once online so the installed shell is definitely under SW control.
        await page.reload({ waitUntil: 'domcontentloaded' });
        await expect(page.getByRole('heading', { name: 'Dashboard' })).toBeVisible();
        await expect.poll(() => page.evaluate(() => Boolean(navigator.serviceWorker.controller))).toBe(true);

        await context.setOffline(true);

        const offlineResponse = await page.goto('/dashboard', { waitUntil: 'domcontentloaded' });
        expect(offlineResponse?.ok()).toBe(true);
        await expect(page.locator('.zazu-page-title')).toHaveText('Offline workspace');
        await expect(page.locator('.zazu-error-shell')).toHaveCount(0);
        await expect(page.locator('[data-zazu-offline-workspace]')).toBeVisible();
        await expect(page.locator('.zazu-dash-hero')).toBeHidden();
        await expect(page.locator('.zazu-dash-overview')).toBeHidden();
    });
    test('primes Customers and Work routes for disconnected phone navigation', async ({ page, context }) => {
        test.setTimeout(90_000);

        await login(page);
        await page.goto('/dashboard', { waitUntil: 'domcontentloaded' });
        await expect(page.getByRole('heading', { name: 'Dashboard' })).toBeVisible();

        await page.evaluate(async () => {
            await navigator.serviceWorker.ready;
        });
        await page.reload({ waitUntil: 'domcontentloaded' });
        await expect.poll(() => page.evaluate(() => Boolean(navigator.serviceWorker.controller))).toBe(true);

        // A navigation/reload can boot the client while cache priming is already
        // running. Concurrent requests for the same business must join that task,
        // not cancel it and report a false offline-preparation failure.
        const primingResults = await page.evaluate(async () => {
            const registration = await navigator.serviceWorker.ready;
            const target = navigator.serviceWorker.controller || registration.active;
            if (!target) throw new Error('No active Zazu service worker is available');

            const routes = ['/dashboard', '/customers', '/work', '/customers/create', '/work/create'];
            const prime = () => new Promise(resolve => {
                const channel = new MessageChannel();
                channel.port1.onmessage = event => {
                    channel.port1.close();
                    resolve(event.data);
                };
                target.postMessage({
                    type: 'prime-pages',
                    userId: document.body.dataset.zazuUserId,
                    businessId: document.body.dataset.zazuBusinessId,
                    routes,
                }, [channel.port2]);
            });

            return Promise.all([prime(), prime(), prime()]);
        });
        expect(primingResults.map(result => result?.success)).toEqual([true, true, true]);
        await expect.poll(() => page.evaluate(() => document.documentElement.dataset.zazuOfflinePagesPrimed || 'pending'), {
            timeout: 45_000,
        }).toBe('true');
        await expect(page.locator('[data-zazu-offline-pages-notice]')).toHaveCount(0);

        await context.setOffline(true);

        try {
            for (const [path, heading] of [
                ['/customers', 'Customers'],
                ['/customers/create', 'New customer'],
                ['/work', 'Event Operations'],
                ['/work/create', 'New job'],
            ]) {
                const navigationResponse = page.waitForResponse(response =>
                    response.request().isNavigationRequest()
                    && new URL(response.url()).pathname === path
                );
                // Exercise a real same-tab link navigation. page.goto() creates
                // a browser-driven navigation without an initiating document
                // client and therefore models a cold launch, not a user click.
                await page.evaluate((destination) => {
                    const link = document.createElement('a');
                    link.href = destination;
                    link.textContent = 'Offline navigation test';
                    link.dataset.zazuNavigationFixture = 'true';
                    document.body.appendChild(link);
                    link.click();
                }, path);
                const response = await navigationResponse;
                expect(response.ok(), path + ' must be available from the authenticated offline cache').toBe(true);
                await page.waitForURL(url => url.pathname === path);
                await expect(page.locator('.zazu-page-title')).toHaveText(heading);
                await expect(page.locator('.zazu-error-shell')).toHaveCount(0);
            }

            // These routes are deliberately not primed. The service worker
            // must fall back to the cached Dashboard shell, and the client must
            // isolate the requested local view instead of showing the Dashboard
            // and every collection together.
            const supplierFallback = await page.goto('/suppliers', { waitUntil: 'domcontentloaded' });
            expect(supplierFallback?.ok()).toBe(true);
            await expect(page.locator('[data-zazu-offline-workspace]')).toBeVisible();
            await expect(page.locator('[data-zazu-offline-active-view="suppliers"]')).toBeVisible();
            await expect(page.locator('[data-zazu-offline-view="suppliers"]')).toBeVisible();
            await expect(page.locator('.zazu-dash-hero')).toBeHidden();
            await expect(page.locator('.zazu-dash-overview')).toBeHidden();
            await expect(page.locator('.zazu-content > .zazu-dash-grid')).toBeHidden();

            const unsupportedFallback = await page.goto('/calendar', { waitUntil: 'domcontentloaded' });
            expect(unsupportedFallback?.ok()).toBe(true);
            await expect(page.locator('[data-zazu-offline-unavailable]')).toBeVisible();
            await expect(page.locator('[data-zazu-offline-view]:visible')).toHaveCount(0);
            await expect(page.locator('.zazu-dash-hero')).toBeHidden();
        } finally {
            await context.setOffline(false);
        }
    });

    test('inspects a device-local quote version, invoice and payments while disconnected', async ({ page, context }, testInfo) => {
        test.setTimeout(30_000);
        test.skip(testInfo.project.name !== 'chromium', 'Local quote inspection contract runs once on desktop Chromium.');

        const jobLocalId = 'dddddddd-dddd-4ddd-8ddd-dddddddddddd';
        const customerLocalId = 'eeeeeeee-eeee-4eee-8eee-eeeeeeeeeeee';
        const quoteLocalId = 'ffffffff-ffff-4fff-8fff-ffffffffffff';
        const invoiceLocalId = 'abababab-abab-4bab-8bab-abababababab';
        const paymentLocalId = 'acacacac-acac-4cac-8cac-acacacacacac';
        const jobServerId = 88101;
        const quoteServerId = 88201;

        // Match the real SyncBootstrapPayloadBuilder envelope: selected records
        // and invoice payments are wrapped beneath "record", not flattened.
        await page.addInitScript(() => {
            window.__ZAZU_OFFLINE_DEFER_INITIAL_SYNC = true;
        });
        await login(page);
        await page.goto('/dashboard', { waitUntil: 'domcontentloaded' });
        await waitForOfflineRuntime(page);
        const businessId = await page.evaluate(() => Number(document.body.dataset.zazuBusinessId || 0));

        await page.route('**/api/sync/provision', route => route.fulfill({
            status: 200,
            contentType: 'application/json',
            body: JSON.stringify({
                device: {
                    id: 99001,
                    installation_id: 'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb',
                    business_id: businessId,
                },
                token: 'offline-quote-inspection-token',
                bootstrap: {
                    business: { id: businessId, name: 'Offline quote business', currency: 'ZAR' },
                    selected: [
                        {
                            type: 'customer',
                            local_id: customerLocalId,
                            server_id: 88301,
                            record: {
                                id: 88301,
                                name: 'Offline quote customer',
                                contact_name: 'Accounts contact',
                            },
                        },
                        {
                            type: 'job',
                            local_id: jobLocalId,
                            server_id: jobServerId,
                            record: {
                                id: jobServerId,
                                customer_local_id: customerLocalId,
                                customer_id: 88301,
                                name: 'Offline reception job',
                                reference: 'OFFLINE-JOB-QUOTE-001',
                                event_type: 'Wedding',
                                status: 'confirmed',
                            },
                        },
                        {
                            type: 'quote',
                            local_id: quoteLocalId,
                            server_id: quoteServerId,
                            record: {
                                id: quoteServerId,
                                event_local_id: jobLocalId,
                                event_id: jobServerId,
                                event_name: 'Offline reception job',
                                reference: 'OFFLINE-QUOTE-001',
                                status: 'sent',
                                currency: 'ZAR',
                                latest_version: {
                                    version: 2,
                                    status: 'sent',
                                    subtotal: '10000.00',
                                    tax_total: '0.00',
                                    total: '10000.00',
                                    deposit_percent: '50.00',
                                    deposit_amount: '5000.00',
                                    notes: 'Menu and service scope',
                                    items: [{
                                        description: 'Buffet package',
                                        quantity: '80.00',
                                        unit: 'guests',
                                        unit_price: '125.00',
                                        line_total: '10000.00',
                                    }],
                                },
                            },
                        },
                    ],
                    invoices: [{
                        local_id: invoiceLocalId,
                        server_id: 88401,
                        record: {
                            id: 88401,
                            quote_id: quoteServerId,
                            event_id: jobServerId,
                            number: 'INV-OFFLINE-001',
                            status: 'partially_paid',
                            currency: 'ZAR',
                            paid_amount: '5000.00',
                            balance: '5000.00',
                            payments: [{
                                local_id: paymentLocalId,
                                server_id: 88501,
                                record: {
                                    id: 88501,
                                    amount: '5000.00',
                                    method: 'EFT',
                                    paid_at: '2026-11-16',
                                    status: 'received',
                                },
                            }],
                        },
                    }],
                },
            }),
        }));

        await page.evaluate(async () => {
            await window.ZazuOffline.ready;
            await window.ZazuOffline.pair('123456789012');
            const invoice = window.ZazuOffline.getState().invoices.find(item => item.number === 'INV-OFFLINE-001');
            if (invoice?.payments?.[0]?.method !== 'EFT') {
                throw new Error('Pairing bootstrap did not preserve nested invoice payments.');
            }
        });

        // Cache the real invoice form while connected. After the quote is
        // accepted locally, the same form must discover that local acceptance.
        await page.goto('/finance/invoices/create', { waitUntil: 'domcontentloaded' });
        await expect(page.locator('form[data-zazu-offline-entity="invoice"]')).toBeVisible();
        // Return to the cached Dashboard while online. Do not visit Quotes
        // before disconnecting: that would cache the normal server-rendered page
        // and bypass the service-worker Dashboard fallback under test.
        await page.goto('/dashboard', { waitUntil: 'domcontentloaded' });
        await waitForOfflineRuntime(page);

        await context.setOffline(true);
        const quoteRoute = await page.goto('/quotes', { waitUntil: 'domcontentloaded' });
        expect(quoteRoute?.ok(), 'The unprimed Quotes route must load through the cached Dashboard fallback.').toBe(true);
        await waitForOfflineRuntime(page);
        await expect(page.locator('[data-zazu-offline-active-view="quotes"]')).toBeVisible();

        const quoteRow = page.locator('[data-zazu-local-record="quote"]').filter({ hasText: 'OFFLINE-QUOTE-001' });
        await expect(quoteRow).toBeVisible();
        await quoteRow.getByRole('button', { name: 'View offline' }).click();

        const detail = page.getByRole('region', { name: 'Offline quote details' });
        await expect(detail).toBeVisible();
        for (const value of [
            'OFFLINE-QUOTE-001',
            'Offline reception job',
            'Offline quote customer',
            'Menu and service scope',
            'Buffet package',
            'INV-OFFLINE-001',
            'EFT',
        ]) {
            await expect(detail.getByText(value, { exact: true })).toBeVisible();
        }
        await expect(detail.getByText('sent', { exact: true })).toBeVisible();
        await expect(detail.getByText('50.00%', { exact: true })).toBeVisible();
        await expect(detail.getByText('80.00 guests')).toBeVisible();
        await expect(detail.getByText(/Line total 10000\.00/)).toBeVisible();
        await expect(detail.getByText(/Balance ZAR 5000\.00/)).toBeVisible();

        await detail.getByRole('button', { name: 'Accept quote offline' }).click();
        await expect.poll(() => page.evaluate(localId =>
            window.ZazuOffline.getState().quotes.find(item => item.local_id === localId)?.status,
        quoteLocalId)).toBe('accepted');

        const acceptanceMutation = await page.evaluate(async localId => {
            const queued = await window.ZazuOffline.pending();
            const mutation = queued.find(item => item.entity_type === 'quote_acceptance');
            return mutation ? {
                quote_local_id: mutation.payload.record.quote_local_id,
                customer_name: mutation.payload.record.customer_name,
                status: mutation.status,
            } : null;
        }, quoteLocalId);
        expect(acceptanceMutation).toEqual({
            quote_local_id: quoteLocalId,
            customer_name: 'Offline quote customer',
            status: 'pending',
        });

        await expect(detail).toBeHidden();

        // Convert that newly accepted local quote through the existing invoice
        // form, without relying on the server-rendered quote list being current.
        await page.goto('/finance/invoices/create', { waitUntil: 'domcontentloaded' });
        const invoiceForm = page.locator('form[data-zazu-offline-entity="invoice"]');
        await expect(invoiceForm).toBeVisible();
        const localQuoteOption = invoiceForm.locator(`select[name="quote_id"] option[value="${quoteServerId}"]`);
        await expect(localQuoteOption).toHaveCount(1);
        await expect(localQuoteOption).toContainText('OFFLINE-QUOTE-001');
        await invoiceForm.locator('select[name="quote_id"]').selectOption(String(quoteServerId));
        await expect(invoiceForm.locator('[data-invoice-source-summary]')).toContainText('OFFLINE-QUOTE-001');
        await invoiceForm.getByRole('button', { name: 'Create invoice' }).click();
        await expect(invoiceForm.getByRole('status')).toContainText('Saved on this device');

        const queuedInvoice = await page.evaluate(async quoteId => {
            const state = window.ZazuOffline.getState();
            const queued = await window.ZazuOffline.pending();
            const mutation = queued.find(item =>
                item.entity_type === 'invoice'
                && item.payload?.record?.quote_local_id === quoteId
            );
            return {
                local_id: mutation?.payload?.record?.local_id || null,
                quote_local_id: mutation?.payload?.record?.quote_local_id || null,
                status: mutation?.status || null,
                local_invoice: state.invoices?.find(item =>
                    item.local_id === mutation?.payload?.record?.local_id
                )?.quote_local_id || null,
            };
        }, quoteLocalId);
        expect(queuedInvoice).toEqual({
            local_id: expect.any(String),
            quote_local_id: quoteLocalId,
            status: 'pending',
            local_invoice: quoteLocalId,
        });
        const createdInvoiceLocalId = queuedInvoice.local_id;

        // Reconnect both queued commands. The acceptance acknowledgement must
        // advance the quote/work revisions, and the invoice acknowledgement must
        // bind the local invoice to its server identity without losing the quote
        // relationship or leaving either command pending.
        const pushed = [];
        await page.route('**/api/sync/**', async route => {
            const url = new URL(route.request().url());
            if (url.pathname === '/api/sync/push') {
                const body = route.request().postDataJSON();
                const recorded = body.mutations.map(mutation => {
                    pushed.push(mutation.entity_type);
                    if (mutation.entity_type === 'quote_acceptance') {
                        return {
                            id: mutation.id,
                            status: 'applied',
                            applied: true,
                            identity: null,
                            related_identities: [
                                { entity_type: 'quote', local_id: quoteLocalId, server_id: quoteServerId, revision: 3 },
                                { entity_type: 'job', local_id: jobLocalId, server_id: jobServerId, revision: 4 },
                            ],
                        };
                    }
                    if (mutation.entity_type === 'invoice') {
                        return {
                            id: mutation.id,
                            status: 'applied',
                            applied: true,
                            identity: {
                                entity_type: 'invoice',
                                local_id: createdInvoiceLocalId,
                                server_id: 88601,
                                revision: 1,
                            },
                            related_identities: [],
                        };
                    }
                    throw new Error('Unexpected queued mutation: ' + mutation.entity_type);
                });
                await route.fulfill({
                    status: 200,
                    contentType: 'application/json',
                    body: JSON.stringify({ recorded, applied: recorded.map(item => item.id) }),
                });
                return;
            }
            if (url.pathname === '/api/sync/pull') {
                await route.fulfill({ status: 200, contentType: 'application/json', body: JSON.stringify({ mutations: [] }) });
                return;
            }
            if (url.pathname === '/api/sync/attachments') {
                await route.fulfill({ status: 200, contentType: 'application/json', body: JSON.stringify({ attachments: [] }) });
                return;
            }
            await route.fulfill({ status: 404, contentType: 'application/json', body: JSON.stringify({ message: 'Unexpected sync request' }) });
        });

        await context.setOffline(false);
        await page.evaluate(() => window.ZazuOffline.sync());
        await expect.poll(() => pushed.length).toBe(2);
        await expect.poll(
            () => page.evaluate(async () => (await window.ZazuOffline.pending()).length),
            { message: 'quote acceptance and invoice acknowledgements must both clear the durable queue' },
        ).toBe(0);

        const reconciled = await page.evaluate(({ quoteLocalId, jobLocalId, invoiceLocalId }) => {
            const state = window.ZazuOffline.getState();
            const quote = state.quotes.find(item => item.local_id === quoteLocalId);
            const job = state.jobs.find(item => item.local_id === jobLocalId);
            const invoice = state.invoices.find(item => item.local_id === invoiceLocalId);
            return {
                quoteStatus: quote?.status,
                quoteRevision: quote?.revision,
                jobStatus: job?.status,
                jobRevision: job?.revision,
                invoiceServerId: invoice?.server_id,
                invoiceRevision: invoice?.revision,
                invoiceQuoteLocalId: invoice?.quote_local_id,
                pending: state.local_dirty,
            };
        }, { quoteLocalId, jobLocalId, invoiceLocalId: createdInvoiceLocalId });

        expect(reconciled).toEqual({
            quoteStatus: 'accepted',
            quoteRevision: 3,
            jobStatus: 'confirmed',
            jobRevision: 4,
            invoiceServerId: 88601,
            invoiceRevision: 1,
            invoiceQuoteLocalId: quoteLocalId,
            pending: false,
        });
    });

    test('inspects a device-local Work record and its commercial chain while disconnected', async ({ page, context }, testInfo) => {
        test.setTimeout(30_000);
        test.skip(testInfo.project.name !== 'chromium', 'Local Work inspection contract runs once on desktop Chromium.');

        const jobLocalId = '44444444-4444-4444-8444-444444444444';
        const customerLocalId = '55555555-5555-4555-8555-555555555555';
        const jobServerId = 77777;

        await page.addInitScript(() => {
            window.__ZAZU_OFFLINE_DEFER_INITIAL_SYNC = true;
        });
        await login(page);
        await page.goto('/work', { waitUntil: 'domcontentloaded' });
        await waitForOfflineRuntime(page);
        await page.evaluate(async ({ jobLocalId, customerLocalId, jobServerId }) => {
            await window.ZazuOffline.ready;
            const state = window.ZazuOffline.getState();
            state.customers = [{
                local_id: customerLocalId,
                id: 99999,
                server_id: 99999,
                name: 'Offline Work customer',
                contact_name: 'Work day contact',
                phone: '0123456789',
                email: 'work@example.test',
            }];
            state.jobs = [{
                local_id: jobLocalId,
                id: jobServerId,
                server_id: jobServerId,
                customer_local_id: customerLocalId,
                customer_id: 99999,
                customer_name: 'Offline Work customer',
                name: 'Offline wedding reception',
                reference: 'OFFLINE-WORK-001',
                event_type: 'Wedding',
                event_date: '2026-12-12',
                event_address: 'Pretoria, Gauteng',
                status: 'confirmed',
                notes: 'Venue access from 08:00',
                requirements: [{
                    local_id: '66666666-6666-4666-8666-666666666666',
                    server_id: 90001,
                    record: {
                        description: 'Full catering service',
                        category: 'Catering',
                        quantity: 80,
                        unit: 'guests',
                        status: 'open',
                    },
                }],
            }];
            state.quotes = [{
                local_id: '88888888-8888-4888-8888-888888888888',
                id: 80001,
                server_id: 80001,
                event_local_id: jobLocalId,
                event_id: jobServerId,
                reference: 'OFFLINE-QUOTE-001',
                status: 'draft',
                currency: 'ZAR',
                latest_version: { status: 'draft', total: '12500.00', items: [] },
            }];
            state.preparations = [{
                local_id: '99999999-9999-4999-8999-999999999999',
                id: 90002,
                server_id: 90002,
                event_local_id: jobLocalId,
                event_id: jobServerId,
                title: 'Confirm final menu',
                status: 'open',
                due_date: '2026-12-01',
            }];
            state.costs = [{
                local_id: 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
                id: 91001,
                server_id: 91001,
                event_id: jobServerId,
                description: 'Food supplies',
                amount: '2500.00',
                currency: 'ZAR',
                status: 'actual',
            }];
            state.purchase_orders = [{
                local_id: 'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb',
                id: 92001,
                server_id: 92001,
                event_id: jobServerId,
                reference: 'PO-OFFLINE-001',
                status: 'received',
                items: [{ id: 1 }],
            }];
            state.invoices = [{
                local_id: 'cccccccc-cccc-4ccc-8ccc-cccccccccccc',
                id: 93001,
                server_id: 93001,
                event_id: jobServerId,
                number: 'INV-OFFLINE-001',
                status: 'partially_paid',
                currency: 'ZAR',
                paid_amount: '5000.00',
                balance: '7500.00',
            }];
            await window.ZazuOffline.persist();
            window.dispatchEvent(new CustomEvent('zazu:offline-state-updated'));
        }, { jobLocalId, customerLocalId, jobServerId });

        await context.setOffline(true);
        const jobRow = page.locator('[data-zazu-local-record="job"]').filter({ hasText: 'Offline wedding reception' });
        await expect(jobRow).toBeVisible();
        await jobRow.getByRole('button', { name: 'View offline' }).click();

        const detail = page.getByRole('region', { name: 'Offline job details' });
        await expect(detail).toBeVisible();
        for (const value of [
            'Offline wedding reception',
            'OFFLINE-WORK-001',
            'Work day contact',
            '2026-12-12',
            'Pretoria, Gauteng',
            'Venue access from 08:00',
            'Full catering service',
            'OFFLINE-QUOTE-001',
            '12500.00',
            'Confirm final menu',
            'Food supplies',
            'PO-OFFLINE-001',
            'INV-OFFLINE-001',
            'Balance ZAR 7500.00',
        ]) {
            await expect(detail.getByText(value).first()).toBeVisible();
        }
        await expect(detail.getByText('Wedding', { exact: true })).toBeVisible();

        await detail.getByRole('button', { name: 'Edit offline' }).click();
        const editForm = detail.locator('[data-zazu-job-offline-edit]');
        await expect(editForm).toBeVisible();
        // The customer is represented by the selected customer control in the
        // offline editor; its <option> is intentionally not visible page text.
        await expect(editForm.locator('select[name="customer_local_id"] option:checked')).toHaveText('Offline Work customer');
        await editForm.locator('[name="name"]').fill('Updated offline wedding reception');
        await editForm.locator('[name="status"]').selectOption('in_progress');
        await editForm.locator('[name="event_date"]').fill('2026-12-13');
        await editForm.locator('[name="event_address"]').fill('Centurion, Gauteng');
        await editForm.locator('[name="notes"]').fill('Updated offline venue instructions');
        await editForm.locator('[name="services"]').fill('Full catering service\nEquipment hire');
        await editForm.getByRole('button', { name: 'Save offline changes' }).click();

        await expect.poll(async () => page.evaluate(async () => (await window.ZazuOffline.pending()).length)).toBe(1);
        const queued = await page.evaluate(async () => window.ZazuOffline.pending());
        expect(queued[0].entity_type).toBe('job');
        expect(queued[0].operation).toBe('update');
        expect(queued[0].payload.record.name).toBe('Updated offline wedding reception');
        expect(queued[0].payload.record.status).toBe('in_progress');
        expect(queued[0].payload.record.event_date).toBe('2026-12-13');
        expect(queued[0].payload.record.event_address).toBe('Centurion, Gauteng');
        expect(queued[0].payload.record.services).toEqual(['Full catering service', 'Equipment hire']);

        const updatedRow = page.locator('[data-zazu-local-record="job"]').filter({ hasText: 'Updated offline wedding reception' });
        await expect(updatedRow).toBeVisible();
        await expect(updatedRow.getByText('Waiting to sync', { exact: true })).toBeVisible();
        await expect(updatedRow.locator('.zazu-ops-row-main .zazu-chip')).toHaveText('In progress');
        await updatedRow.getByRole('button', { name: 'View offline' }).click();
        const updatedDetail = page.getByRole('region', { name: 'Offline job details' });
        await expect(updatedDetail.getByText('Updated offline wedding reception')).toBeVisible();
        await expect(updatedDetail.getByText('2026-12-13')).toBeVisible();
        await expect(updatedDetail.getByText('Centurion, Gauteng')).toBeVisible();
        await expect(updatedDetail.getByText('Updated offline venue instructions')).toBeVisible();
        await expect(updatedDetail.getByText('Equipment hire')).toBeVisible();
        await updatedDetail.getByRole('button', { name: 'Close' }).click();
        await expect(updatedDetail).toBeHidden();
    });

    test('reassigns a device-local Work record to another customer while disconnected', async ({ page, context }, testInfo) => {
        test.setTimeout(25_000);
        test.skip(testInfo.project.name !== 'chromium', 'Offline Work reassignment contract runs once on desktop Chromium.');

        const jobLocalId = 'abababab-abab-4bab-8bab-abababababab';
        const firstCustomerId = 'acacacac-acac-4cac-8cac-acacacacacac';
        const nextCustomerId = 'adadadad-adad-4dad-8dad-adadadadadad';

        await page.addInitScript(() => {
            window.__ZAZU_OFFLINE_DEFER_INITIAL_SYNC = true;
        });
        await login(page);
        await page.goto('/work', { waitUntil: 'domcontentloaded' });
        await waitForOfflineRuntime(page);
        await page.evaluate(async ({ jobLocalId, firstCustomerId, nextCustomerId }) => {
            const state = window.ZazuOffline.getState();
            state.customers = [
                { local_id: firstCustomerId, name: 'Original offline customer' },
                { local_id: nextCustomerId, name: 'Replacement offline customer' },
            ];
            state.jobs = [{
                local_id: jobLocalId,
                name: 'Offline reassignment job',
                customer_local_id: firstCustomerId,
                customer_name: 'Original offline customer',
                event_type: 'Other',
                status: 'draft',
                requirements: [],
                services: [],
            }];
            state.local_dirty = false;
            await window.ZazuOffline.persist();
            window.dispatchEvent(new CustomEvent('zazu:offline-state-updated'));
        }, { jobLocalId, firstCustomerId, nextCustomerId });

        await context.setOffline(true);
        const row = page.locator('[data-zazu-local-record="job"]').filter({ hasText: 'Offline reassignment job' });
        await expect(row).toBeVisible();
        await row.getByRole('button', { name: 'View offline' }).click();
        const detail = page.getByRole('region', { name: 'Offline job details' });
        await detail.getByRole('button', { name: 'Edit offline' }).click();
        const form = detail.locator('[data-zazu-job-offline-edit]');
        await form.locator('[name="customer_local_id"]').selectOption(nextCustomerId);
        await form.getByRole('button', { name: 'Save offline changes' }).click();

        const queued = await page.evaluate(async () => window.ZazuOffline.pending());
        expect(queued).toHaveLength(1);
        expect(queued[0].payload.record.customer_local_id).toBe(nextCustomerId);
        const updated = await page.evaluate(id => window.ZazuOffline.getState().jobs.find(job => job.local_id === id), jobLocalId);
        expect(updated.customer_local_id).toBe(nextCustomerId);
        expect(updated.customer_name).toBe('Replacement offline customer');
    });

    test('reads and edits a device-local customer relationship while disconnected', async ({ page, context }, testInfo) => {
        test.setTimeout(30_000);
        test.skip(testInfo.project.name !== 'chromium', 'Customer local read/edit contract runs once on desktop Chromium.');

        const customerLocalId = '11111111-1111-4111-8111-111111111111';
        const jobLocalId = '22222222-2222-4222-8222-222222222222';
        const invoiceLocalId = '44444444-4444-4444-8444-444444444444';
        const customerName = 'Offline relationship customer';

        await page.addInitScript(() => {
            window.__ZAZU_OFFLINE_DEFER_INITIAL_SYNC = true;
        });
        await login(page);
        await page.goto('/customers', { waitUntil: 'domcontentloaded' });
        await waitForOfflineRuntime(page);
        await page.evaluate(async ({ customerLocalId, jobLocalId, invoiceLocalId, customerName }) => {
            await window.ZazuOffline.ready;
            const state = window.ZazuOffline.getState();
            state.customers = [{
                local_id: customerLocalId,
                name: customerName,
                contact_name: 'Offline primary contact',
                phone: '0123456789',
                email: 'offline@example.test',
                billing_address: 'Pretoria, Gauteng',
                notes: 'Customer notes stored on this device',
            }];
            state.jobs = [{
                local_id: jobLocalId,
                customer_local_id: customerLocalId,
                name: 'Offline wedding job',
                reference: 'LOCAL-JOB-001',
                event_date: '2026-12-12',
                status: 'confirmed',
            }];
            state.quotes = [{
                local_id: '33333333-3333-4333-8333-333333333333',
                event_local_id: jobLocalId,
                reference: 'LOCAL-QUOTE-001',
                status: 'sent',
            }];
            state.invoices = [{
                local_id: invoiceLocalId,
                number: 'INV-OFFLINE-TEMP-LOCAL',
                status: 'issued',
                currency: 'ZAR',
                total: '100.00',
                paid_amount: '0.00',
                balance: '100.00',
                payments: [],
            }];
            await window.ZazuOffline.persist();
            window.dispatchEvent(new CustomEvent('zazu:offline-state-updated'));
        }, { customerLocalId, jobLocalId, invoiceLocalId, customerName });

        await context.setOffline(true);
        const customerRow = page.locator('[data-zazu-local-record="customer"]').filter({ hasText: customerName });
        await expect(customerRow).toBeVisible();
        await customerRow.getByRole('button', { name: 'View offline' }).click();

        let detail = page.getByRole('region', { name: 'Offline customer details' });
        await expect(detail).toBeVisible();
        await expect(detail.getByText(customerName)).toBeVisible();
        await expect(detail.getByText('Offline primary contact')).toBeVisible();
        await expect(detail.getByText('Pretoria, Gauteng')).toBeVisible();
        await expect(detail.getByText('Customer notes stored on this device')).toBeVisible();
        await expect(detail.getByText('Offline wedding job')).toBeVisible();
        await expect(detail.getByText('LOCAL-JOB-001')).toBeVisible();
        await expect(detail.getByText('LOCAL-QUOTE-001')).toBeVisible();

        await detail.getByRole('button', { name: 'Edit offline' }).click();
        const editForm = detail.locator('[data-zazu-customer-offline-edit]');
        await expect(editForm).toBeVisible();
        await editForm.locator('[name="name"]').fill('Updated offline relationship customer');
        await editForm.locator('[name="contact_name"]').fill('Updated offline contact');
        await editForm.locator('[name="phone"]').fill('0115550199');
        await editForm.getByRole('button', { name: 'Save offline changes' }).click();

        await expect.poll(async () => page.evaluate(async () => (await window.ZazuOffline.pending()).length)).toBe(1);
        const queued = await page.evaluate(async () => window.ZazuOffline.pending());
        expect(queued[0].entity_type).toBe('customer');
        expect(queued[0].operation).toBe('update');
        expect(queued[0].payload.record.name).toBe('Updated offline relationship customer');
        expect(queued[0].payload.record.phone).toBe('0115550199');

        const updatedRow = page.locator('[data-zazu-local-record="customer"]').filter({ hasText: 'Updated offline relationship customer' });
        await expect(updatedRow).toBeVisible();
        await expect(updatedRow.locator('.zazu-chip')).toHaveText('Waiting to sync');
        await updatedRow.getByRole('button', { name: 'View offline' }).click();

        detail = page.getByRole('region', { name: 'Offline customer details' });
        await expect(detail.getByText('Updated offline relationship customer')).toBeVisible();
        await expect(detail.getByText('Updated offline contact')).toBeVisible();
        await expect(detail.getByText('0115550199')).toBeVisible();
        await expect(detail.getByText('Offline wedding job')).toBeVisible();
        await expect(detail.getByText('LOCAL-QUOTE-001')).toBeVisible();

        await page.route('**/api/sync/**', async route => {
            const url = new URL(route.request().url());
            if (url.pathname === '/api/sync/push') {
                const body = route.request().postDataJSON();
                await route.fulfill({
                    status: 200,
                    contentType: 'application/json',
                    body: JSON.stringify({
                        recorded: body.mutations.map(mutation => ({
                            id: mutation.id,
                            status: 'applied',
                            identity: {
                                entity_type: mutation.entity_type,
                                local_id: mutation.payload.local_id,
                                server_id: 7654321,
                            },
                        })),
                    }),
                });
                return;
            }
            if (url.pathname === '/api/sync/pull') {
                await route.fulfill({ status: 200, contentType: 'application/json', body: JSON.stringify({ mutations: [] }) });
                return;
            }
            if (url.pathname === '/api/sync/bootstrap') {
                await route.fulfill({
                    status: 200,
                    contentType: 'application/json',
                    body: JSON.stringify({
                        invoices: [{
                            local_id: invoiceLocalId,
                            server_id: 7654322,
                            record: {
                                id: 7654322,
                                number: 'INV-202611-000001',
                                status: 'issued',
                                currency: 'ZAR',
                                total: '100.00',
                                paid_amount: '0.00',
                                balance: '100.00',
                            },
                        }],
                    }),
                });
                return;
            }
            await route.fulfill({ status: 200, contentType: 'application/json', body: JSON.stringify({ attachments: [] }) });
        });
        await page.evaluate(async () => {
            const state = window.ZazuOffline.getState();
            state.sync = {
                token: 'customer-edit-reconnect-token',
                device: { id: 999993, business_id: Number(document.body.dataset.zazuBusinessId) },
            };
            await window.ZazuOffline.persist({ allowSessionChange: true });
        });
        await context.setOffline(false);
        await page.evaluate(() => window.ZazuOffline.sync());
        await expect.poll(async () => page.evaluate(async () => (await window.ZazuOffline.pending()).length)).toBe(0);
        await expect.poll(async () => page.evaluate(() => {
            const customer = window.ZazuOffline.getState().customers[0];
            return { serverId: customer.server_id, pendingSync: customer.pending_sync };
        })).toEqual({ serverId: 7654321, pendingSync: false });
        await expect(updatedRow.locator('.zazu-chip')).toHaveText('Synced');
        await expect.poll(async () => page.evaluate(invoiceLocalId =>
            window.ZazuOffline.getState().invoices.find(invoice => invoice.local_id === invoiceLocalId)?.number,
            invoiceLocalId,
        )).toBe('INV-202611-000001');
        // The state update rebuilds the pending local row after server identity is assigned.
        await expect(detail).toBeHidden();
    });

    test('opens the local quote register and queues acceptance from the dashboard shell while disconnected', async ({ page, context }, testInfo) => {
        test.setTimeout(30_000);
        test.skip(testInfo.project.name !== 'chromium', 'Local quote acceptance contract runs once on desktop Chromium.');

        const quoteLocalId = '55555555-5555-4555-8555-555555555555';
        const jobLocalId = '66666666-6666-4666-8666-666666666666';
        const customerLocalId = '77777777-7777-4777-8777-777777777777';

        await page.addInitScript(() => {
            window.__ZAZU_OFFLINE_DEFER_INITIAL_SYNC = true;
        });
        await login(page);
        await page.goto('/dashboard', { waitUntil: 'domcontentloaded' });
        await waitForOfflineRuntime(page);
        await page.evaluate(async ({ quoteLocalId, jobLocalId, customerLocalId }) => {
            await window.ZazuOffline.ready;
            const state = window.ZazuOffline.getState();
            state.customers = [{
                local_id: customerLocalId,
                name: 'Offline quote acceptance customer',
            }];
            state.jobs = [{
                local_id: jobLocalId,
                customer_local_id: customerLocalId,
                name: 'Offline quote acceptance event',
                status: 'draft',
            }];
            state.quotes = [{
                local_id: quoteLocalId,
                reference: 'LOCAL-QUOTE-ACCEPT-001',
                event_local_id: jobLocalId,
                status: 'sent',
                currency: 'ZAR',
                latest_version: {
                    version: 1,
                    status: 'sent',
                    subtotal: '1000.00',
                    tax_total: '150.00',
                    total: '1150.00',
                    items: [{
                        description: 'Catering package',
                        quantity: '10',
                        unit: 'guests',
                        unit_price: '100.00',
                        line_total: '1000.00',
                    }],
                },
            }];
            await window.ZazuOffline.persist();
            window.dispatchEvent(new CustomEvent('zazu:offline-state-updated'));
        }, { quoteLocalId, jobLocalId, customerLocalId });

        await context.setOffline(true);
        await expect(page.locator('body')).toHaveClass(/zazu-offline-mode/);
        const offlineWorkspace = page.locator('[data-zazu-offline-workspace]');
        const quotesLink = offlineWorkspace.getByRole('link', { name: 'Quotes', exact: true });
        await expect(quotesLink).toBeVisible();
        await quotesLink.click();
        const quoteRow = page.locator('[data-zazu-local-record="quote"]').filter({ hasText: 'LOCAL-QUOTE-ACCEPT-001' });
        await expect(quoteRow).toBeVisible();
        await quoteRow.getByRole('button', { name: 'View offline' }).click();

        const detail = page.getByRole('region', { name: 'Offline quote details' });
        await expect(detail).toBeVisible();
        await expect(detail.getByText('Offline quote acceptance event')).toBeVisible();
        await expect(detail.getByText('Offline quote acceptance customer')).toBeVisible();
        await expect(detail.getByText('Catering package')).toBeVisible();
        await expect(detail.getByText('1150.00')).toBeVisible();

        await detail.getByRole('button', { name: 'Accept quote offline' }).click();
        // queueMutation emits zazu:offline-state-updated, which rebuilds the local
        // quote register and its detail panel. Assert the durable post-render state,
        // not transient DOM that is intentionally replaced by that render.
        await expect(quoteRow).toContainText('accepted');
        await expect.poll(async () => page.evaluate(async () => (await window.ZazuOffline.pending()).length)).toBe(1);

        const result = await page.evaluate(async ({ quoteLocalId, jobLocalId }) => {
            const quote = window.ZazuOffline.getState().quotes.find(item => item.local_id === quoteLocalId);
            const job = window.ZazuOffline.getState().jobs.find(item => item.local_id === jobLocalId);
            const [mutation] = await window.ZazuOffline.pending();
            return {
                quoteStatus: quote?.status,
                versionStatus: quote?.latest_version?.status,
                jobStatus: job?.status,
                entityType: mutation?.entity_type,
                operation: mutation?.operation,
                quoteLocalId: mutation?.payload?.record?.quote_local_id,
            };
        }, { quoteLocalId, jobLocalId });

        expect(result).toEqual({
            quoteStatus: 'accepted',
            versionStatus: 'accepted',
            jobStatus: 'confirmed',
            entityType: 'quote_acceptance',
            operation: 'create',
            quoteLocalId,
        });
    });

    test('cold installed-app launch opens the local workspace from a cached Work detail URL', async ({ page, context }, testInfo) => {
        test.setTimeout(30_000);
        test.skip(!['chromium', 'chromium-mobile'].includes(testInfo.project.name), 'Cold-launch navigation contract runs on desktop and phone viewport.');

        await login(page);
        await page.goto('/work', { waitUntil: 'domcontentloaded' });
        // The Work register exposes each server-backed row by its event ID.
        // Its primary action is a button that opens an inspector, not a /work/{id}
        // anchor, so selecting an assumed detail link makes this test depend on
        // incidental UI markup rather than the seeded record contract.
        const workRow = page.locator('[data-zazu-server-job-id]').first();
        await expect(workRow).toBeVisible();
        const eventId = await workRow.getAttribute('data-zazu-server-job-id');
        expect(eventId).toMatch(/^[0-9]+$/);
        const workUrl = `/work/${eventId}`;

        await page.goto(workUrl, { waitUntil: 'domcontentloaded' });
        await expect(page).toHaveURL(new URL(workUrl, page.url()).href);
        const cachedWorkTitle = await page.locator('.zazu-page-title').textContent();
        expect(cachedWorkTitle?.trim()).toBeTruthy();
        await expect.poll(() => page.evaluate(() => Boolean(navigator.serviceWorker.controller))).toBe(true);
        await expect.poll(() => page.evaluate(async (path) => {
            for (const name of await caches.keys()) {
                const cache = await caches.open(name);
                if (await cache.match(new URL(path, window.location.origin).href)) return true;
            }
            return false;
        }, workUrl)).toBe(true);

        await context.setOffline(true);
        const coldLaunch = await context.newPage();
        await coldLaunch.goto(new URL(workUrl, page.url()).href, { waitUntil: 'domcontentloaded' });
        // A cold launch on a cached Work deep link must keep that exact route.
        await expect(coldLaunch).toHaveURL(new URL(workUrl, page.url()).href);
        await expect(coldLaunch.locator('.zazu-page-title')).toHaveText(cachedWorkTitle);
        await expect(coldLaunch.locator('[data-zazu-offline-workspace]')).toBeVisible();

    });

});
