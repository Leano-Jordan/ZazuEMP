import { test, expect } from '@playwright/test';

const DEMO_EMAIL = process.env.ZAZU_DEMO_EMAIL || 'demo@zazu.local';
const DEMO_PASSWORD = process.env.ZAZU_DEMO_PASSWORD || 'password';

async function login(page) {
    await page.goto('/?auth=login', { waitUntil: 'domcontentloaded' });
    await expect(page.locator('[data-auth-modal]')).toBeVisible();
    await page.getByLabel('Username or email').fill(DEMO_EMAIL);
    await page.locator('#auth_password').fill(DEMO_PASSWORD);
    await page.locator('[data-auth-submit]').click();
    await expect(page).toHaveURL(/\/dashboard$/);
}

test.describe('Zazu installed PWA offline shell', () => {
    test('local client becomes ready before an unresponsive initial sync', async ({ page }, testInfo) => {
        test.setTimeout(20_000);
        test.skip(testInfo.project.name !== 'chromium', 'Network-stall startup contract runs once; responsive viewports are covered separately.');

        await login(page);
        await page.evaluate(async () => {
            await window.ZazuOffline.ready;
            const state = window.ZazuOffline.getState();
            state.sync = { token: 'startup-readiness-test-token', device: { id: 999999 } };
            state.local_dirty = false;
            await window.ZazuOffline.persist();
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

    test('queues supported changes when the Zazu origin is unreachable but the browser reports online', async ({ page }, testInfo) => {
        test.setTimeout(20_000);
        test.skip(testInfo.project.name !== 'chromium', 'Origin-reachability fallback contract runs once.');

        await login(page);
        await page.goto('/customers/create', { waitUntil: 'domcontentloaded' });
        await page.evaluate(() => window.ZazuOffline.ready);

        expect(await page.evaluate(() => navigator.onLine)).toBe(true);

        // Keep the browser online while making the application origin unavailable.
        await page.route('**/customers/create*', async route => {
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
        await page.locator('input[name="night_contact_name"]').fill('Night Coordinator');
        await page.locator('input[name="night_contact_phone"]').fill('0123456787');
        await page.locator('input[name="night_contact_email"]').fill('night@example.test');
        await page.getByRole('button', { name: 'Save customer' }).click();

        await expect(page.getByText('Saved on this device. It will sync automatically when Zazu reconnects.')).toBeVisible();

        const queued = await page.evaluate(async () => window.ZazuOffline.pending());
        expect(queued).toHaveLength(1);
        expect(queued[0].entity_type).toBe('customer');
        expect(queued[0].payload.contact_name).toBe('Mandla Dlamini');
        expect(queued[0].payload.phone).toBe('0123456789');
        expect(queued[0].payload.email).toBe('mandla@example.test');
        expect(queued[0].payload.legal_name).toBe('Mandla Events');
        expect(queued[0].payload.registration_number).toBe('2026/12345');
        expect(queued[0].payload.tax_number).toBe('TAX-7788');
        expect(queued[0].payload.vat_number).toBe('VAT-1122');
        expect(queued[0].payload.billing_address).toBe('12 Market Street, Pretoria');
        expect(queued[0].payload.day_contact_name).toBe('Day Coordinator');
        expect(queued[0].payload.day_contact_phone).toBe('0123456788');
        expect(queued[0].payload.day_contact_email).toBe('day@example.test');
        expect(queued[0].payload.night_contact_name).toBe('Night Coordinator');
        expect(queued[0].payload.night_contact_phone).toBe('0123456787');
        expect(queued[0].payload.night_contact_email).toBe('night@example.test');

        await page.reload({ waitUntil: 'domcontentloaded' });
        await page.evaluate(() => window.ZazuOffline.ready);
        const restoredQueue = await page.evaluate(async () => window.ZazuOffline.pending());
        expect(restoredQueue).toHaveLength(1);
        expect(restoredQueue[0].id).toBe(queued[0].id);
        expect(restoredQueue[0].payload.day_contact_name).toBe('Day Coordinator');
        expect(restoredQueue[0].payload.night_contact_name).toBe('Night Coordinator');

        const restoredState = await page.evaluate(() => window.ZazuOffline.getState());
        expect(restoredState.customers.some(customer =>
            customer.local_id === queued[0].payload.local_id
            && customer.legal_name === 'Mandla Events'
            && customer.billing_address === '12 Market Street, Pretoria'
        )).toBe(true);
    });

    test('does not claim an offline customer photo was saved when only metadata can be queued', async ({ page }, testInfo) => {
        test.setTimeout(20_000);
        test.skip(testInfo.project.name !== 'chromium', 'Offline file boundary contract runs once.');

        await login(page);
        await page.goto('/customers/create', { waitUntil: 'domcontentloaded' });
        await page.evaluate(() => window.ZazuOffline.ready);
        await page.route('**/customers/create*', async route => {
            if (route.request().method() === 'HEAD') {
                await route.abort('failed');
                return;
            }
            await route.continue();
        });

        await page.locator('input[name="name"]').fill('Photo boundary customer');
        await page.locator('input[name="primary_contact_name"]').fill('Photo Contact');
        await page.locator('input[name="profile_photo"]').setInputFiles({
            name: 'profile.png',
            mimeType: 'image/png',
            buffer: Buffer.from('offline-photo-fixture'),
        });

        const dialog = page.waitForEvent('dialog');
        await page.getByRole('button', { name: 'Save customer' }).click();
        const alert = await dialog;
        expect(alert.message()).toContain('Customer profile photos need a connection');
        await alert.accept();

        await expect(page.getByRole('button', { name: 'Save customer' })).toBeEnabled();
        expect(await page.evaluate(async () => window.ZazuOffline.pending())).toHaveLength(0);
    });

    test('links a phone-local customer to an offline Work record', async ({ page }, testInfo) => {
        test.setTimeout(25_000);
        test.skip(testInfo.project.name !== 'chromium', 'Offline customer-to-Work contract runs once.');

        await login(page);
        await page.goto('/customers/create', { waitUntil: 'domcontentloaded' });
        await page.evaluate(() => window.ZazuOffline.ready);
        await page.route('**/customers/create*', async route => {
            if (route.request().method() === 'HEAD') await route.abort('failed');
            else await route.continue();
        });
        await page.locator('input[name="name"]').fill('Local only linked customer');
        await page.locator('input[name="primary_contact_name"]').fill('Local Customer Contact');
        await page.getByRole('button', { name: 'Save customer' }).click();
        await expect(page.getByText('Saved on this device. It will sync automatically when Zazu reconnects.')).toBeVisible();

        await page.goto('/work/create', { waitUntil: 'domcontentloaded' });
        await page.evaluate(() => window.ZazuOffline.ready);
        const selector = page.locator('select[name="customer_id"]');
        await expect(selector.locator('option[value^="local:"]')).toHaveCount(1);
        await selector.selectOption({ label: 'Local only linked customer' });
        await page.locator('input[name="event_type"][value="Wedding"]').check();
        await page.locator('input[name="event_date"]').fill('2026-11-15');
        await page.locator('input[name="event_address"]').fill('Pretoria');
        await page.locator('input[name="services[]"][value="Catering"]').check();
        let normalWorkPostAttempted = false;
        await page.route('**/work', async route => {
            if (route.request().method() === 'POST') {
                normalWorkPostAttempted = true;
                await route.abort('failed');
                return;
            }
            await route.continue();
        });
        await page.locator('input[name="name"]').fill('Offline job for local customer');
        await page.getByRole('button', { name: /Create job/i }).click();

        await expect(page.getByText('Saved on this device. It will sync automatically when Zazu reconnects.')).toBeVisible();
        const queued = await page.evaluate(async () => window.ZazuOffline.pending());
        const customerMutation = queued.find(item => item.entity_type === 'customer');
        const jobMutation = queued.find(item => item.entity_type === 'job');
        expect(customerMutation).toBeTruthy();
        expect(jobMutation).toBeTruthy();
        expect(jobMutation.payload.customer_local_id).toBe(customerMutation.payload.local_id);
        expect(normalWorkPostAttempted).toBe(false);
        expect(jobMutation.payload.event_type).toBe('Wedding');
        expect(jobMutation.payload.event_date).toBe('2026-11-15');
        expect(jobMutation.payload.event_address).toBe('Pretoria');
        expect(jobMutation.payload.services).toEqual(['Catering']);
    });

    test('offline logout keeps local data when the server cannot end the web session', async ({ page, context }, testInfo) => {
        test.setTimeout(20_000);
        test.skip(testInfo.project.name !== 'chromium', 'Disconnected logout contract runs once; viewport behaviour is covered elsewhere.');

        await login(page);
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
        expect(dialogs[1].type).toBe('alert');
        expect(dialogs[1].message).toContain('Your local offline data and device token were left untouched');
        await expect.poll(() => new URL(page.url()).pathname).toBe('/dashboard');

        const localState = await page.evaluate(async () => ({
            businessId: window.ZazuOffline.getState()?.business?.id,
            pendingCount: (await window.ZazuOffline.pending()).length,
        }));
        expect(localState.businessId).toBe(987654);
        expect(localState.pendingCount).toBe(1);
    });

    test('late authenticated HTML cannot repopulate the private cache after logout starts', async ({ page }, testInfo) => {
        test.setTimeout(20_000);
        test.skip(testInfo.project.name !== 'chromium', 'Service-worker cache race contract runs once.');

        await login(page);
        const userCaches = await page.evaluate(async () => {
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
            const mockFetch = input => {
                const url = new URL(typeof input === 'string' ? input : input.url, window.location.origin);
                if (url.pathname === '/dashboard') {
                    markNetworkStarted();
                    return stalledHtml;
                }
                return Promise.reject(new Error('Unexpected network request in cache-race test: ' + url.pathname));
            };

            // Execute the worker in an inline script instead of Function/eval:
            // the app's CSP intentionally disallows unsafe-eval.
            const workerScript = document.createElement('script');
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

            const logoutWaits = [];
            handlers.fetch({
                request: new WorkerRequest(new URL('/logout', window.location.origin).href, { method: 'POST' }),
                waitUntil(promise) { logoutWaits.push(promise); },
            });
            await Promise.all(logoutWaits);

            resolveHtml(new window.Response(
                '<!doctype html><html><body data-zazu-user-id="123">stale authenticated page</body></html>',
                { status: 200, headers: { 'content-type': 'text/html; charset=utf-8' } }
            ));
            await navigationResponse;
            await Promise.all(cacheWaits);

            return (await cachesApi.keys()).filter(name => name.includes('-user-'));
        });

        expect(userCaches).toEqual([]);
    });

    test('logout protects queued offline work and clears the local session after explicit discard', async ({ page }) => {
        test.setTimeout(60_000);

        await login(page);
        await page.evaluate(async () => {
            await window.ZazuOffline.ready;
            await window.ZazuOffline.queueMutation('customer', 'create', {
                local_id: crypto.randomUUID(),
                name: 'Logout guard test fixture',
            });

            // Use a failed mutation for a deterministic persistence test. A
            // pending mutation would legitimately auto-sync during the reload
            // and make the fixture depend on server timing.
            const db = await new Promise((resolve, reject) => {
                const request = indexedDB.open('zazu-client', 1);
                request.onsuccess = () => resolve(request.result);
                request.onerror = () => reject(request.error || new Error('Could not open local offline storage'));
            });
            await new Promise((resolve, reject) => {
                const tx = db.transaction(['mutations'], 'readwrite');
                const store = tx.objectStore('mutations');
                const request = store.getAll();
                request.onsuccess = () => {
                    for (const record of request.result || []) {
                        store.put({ ...record, status: 'failed' });
                    }
                };
                tx.oncomplete = resolve;
                tx.onerror = () => reject(tx.error || new Error('Could not mark mutation as failed'));
                tx.onabort = () => reject(tx.error || new Error('Offline mutation transaction aborted'));
            });
            db.close();
        });

        let confirmDiscard = false;
        const confirmMessages = [];
        page.on('dialog', async dialog => {
            if (dialog.type() !== 'confirm') {
                await dialog.dismiss();
                return;
            }

            confirmMessages.push(dialog.message());
            if (confirmDiscard) await dialog.accept();
            else await dialog.dismiss();
        });

        await page.evaluate(() => {
            const form = document.querySelector('form[action$="/logout"]');
            if (!form) throw new Error('Logout form was not found');
            form.requestSubmit();
        });

        await expect.poll(() => new URL(page.url()).pathname).toBe('/dashboard');
        await expect.poll(() => confirmMessages.length).toBe(1);
        expect(confirmMessages[0]).toContain('rejected/failed change(s)');
        const queueCount = () => page.evaluate(() => new Promise((resolve, reject) => {
            const request = indexedDB.open('zazu-client', 1);
            request.onerror = () => reject(request.error || new Error('Could not inspect offline queue'));
            request.onsuccess = () => {
                const db = request.result;
                const tx = db.transaction(['mutations'], 'readonly');
                const read = tx.objectStore('mutations').getAll();
                tx.oncomplete = () => {
                    resolve((read.result || []).length);
                    db.close();
                };
                tx.onerror = () => reject(tx.error || new Error('Could not inspect offline queue'));
            };
        }));
        expect(await queueCount()).toBe(1);

        // A cancelled sign-out must survive a cold page reload without losing queued work.
        await page.reload({ waitUntil: 'domcontentloaded' });
        await expect(page.getByRole('heading', { name: 'Dashboard' })).toBeVisible();
        await page.evaluate(async () => window.ZazuOffline.ready);
        await expect.poll(queueCount).toBe(1);

        confirmDiscard = true;
        await Promise.all([
            page.waitForURL(url => !url.pathname.endsWith('/dashboard')),
            page.evaluate(() => document.querySelector('form[action$="/logout"]').requestSubmit()),
        ]);

        const localData = await page.evaluate(() => new Promise((resolve, reject) => {
            const request = indexedDB.open('zazu-client', 1);
            request.onerror = () => reject(request.error || new Error('Could not inspect local offline storage'));
            request.onsuccess = () => {
                const db = request.result;
                const tx = db.transaction(['state', 'mutations'], 'readonly');
                const stateRequest = tx.objectStore('state').get('current');
                const queueRequest = tx.objectStore('mutations').getAll();
                tx.oncomplete = () => {
                    resolve({
                        tokenPresent: Boolean(stateRequest.result?.sync?.token),
                        queueCount: (queueRequest.result || []).length,
                        workspacePresent: Boolean(stateRequest.result?.business),
                    });
                    db.close();
                };
                tx.onerror = () => reject(tx.error || new Error('Could not inspect local offline state'));
            };
        }));

        expect(localData.tokenPresent).toBe(false);
        expect(localData.queueCount).toBe(0);
        expect(localData.workspacePresent).toBe(false);
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
        await expect(page.getByRole('heading', { name: 'Dashboard' })).toBeVisible();
        await expect(page.locator('.zazu-error-shell')).toHaveCount(0);
    });
});
