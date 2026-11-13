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
    await expect(page).toHaveURL(/\/dashboard$/);
}

async function waitForOfflineRuntime(page) {
    await expect.poll(
        () => page.evaluate(() => Boolean(window.ZazuOffline?.ready)),
        { message: 'offline runtime must be exposed before device-pairing assertions' },
    ).toBe(true);
    await page.evaluate(() => window.ZazuOffline.ready);
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

    test('retries primary device registration and drains queued customers after the host returns', async ({ page }, testInfo) => {
        test.setTimeout(25_000);
        test.skip(testInfo.project.name !== 'chromium', 'Primary device registration recovery contract runs once.');

        // Keep boot-time registration out of this test so the retry endpoint
        // is intercepted before the first registration attempt.
        await page.addInitScript(() => {
            window.__ZAZU_OFFLINE_DEFER_INITIAL_SYNC = true;
        });
        await login(page);
        await page.evaluate(async () => {
            await window.ZazuOffline.ready;
            const state = window.ZazuOffline.getState();
            state.sync = null;
            state.sync_authorization_required = false;
            state.last_sync_error = null;
            await window.ZazuOffline.persist({ allowSessionChange: true });
        });

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
                                server_id: 880001,
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

        await page.evaluate(async () => {
            await window.ZazuOffline.queueMutation('customer', 'create', {
                local_id: crypto.randomUUID(),
                name: 'Customer saved before device registration recovered',
            });
        });

        await expect.poll(() => registrationAttempts, { timeout: 12_000 }).toBeGreaterThanOrEqual(2);
        await expect.poll(() => pushed, { timeout: 12_000 }).toBe(1);
        await expect.poll(async () => page.evaluate(async () => (await window.ZazuOffline.pending()).length)).toBe(0);
        expect(await page.evaluate(() => window.ZazuOffline.getState().sync?.token)).toBe('registration-retry-test-token');
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
        await page.evaluate(async () => {
            await window.ZazuOffline.ready;
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
        await page.route('**/api/sync/bootstrap', route => route.fulfill({
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
        const cateringChoice = page.locator('label.zazu-service-choice:has(input[name="services[]"][value="Catering"])');
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
        expect(jobMutation.payload.record.services).toEqual(['Catering']);
    });

    test('migrates legacy offline data only into its matching business workspace', async ({ page }) => {
        await login(page);
        await expect.poll(() => page.evaluate(() => window.ZazuOffline.isPaired())).toBe(true);
        const businessId = await page.evaluate(() => Number(document.body.dataset.zazuBusinessId));
        expect(businessId).toBeGreaterThan(0);

        // The authenticated first load auto-registers this installation. Clear
        // that current workspace so this test represents a genuine legacy-only upgrade.
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
        await page.goto('/settings', { waitUntil: 'domcontentloaded' });
        await page.evaluate(() => window.ZazuOffline.ready);
        const businessId = await page.evaluate(() => Number(document.body.dataset.zazuBusinessId));
        expect(businessId).toBeGreaterThan(0);

        await page.evaluate(async (activeBusinessId) => {
            const state = window.ZazuOffline.getState();
            state.sync = {
                token: 'atomic-failed-mutation-token',
                device: { id: 987660, business_id: activeBusinessId },
            };
            state.local_dirty = false;
            await window.ZazuOffline.persist({ allowSessionChange: true });
        }, businessId);

        const mutation = await page.evaluate(() => window.ZazuOffline.queueMutation('customer', 'create', {
            local_id: 'atomic-failed-customer',
            record: { name: 'Customer requiring operator review' },
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
        await page.route('**/api/sync/bootstrap', route => route.fulfill({
            status: 200,
            contentType: 'application/json',
            body: JSON.stringify({
                business: { id: businessId, name: 'Test business' },
                catalogue: [], suppliers: [], purchase_orders: [], invoices: [],
                expenses: [], inventory_items: [], assets: [], costs: [], selected: [],
            }),
        }));

        await page.evaluate(() => window.ZazuOffline.sync());

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

        await page.getByRole('button', { name: 'Retry sync' }).first().click();
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
        await page.getByRole('button', { name: 'Discard change' }).click();
        await expect.poll(() => resolutions.length).toBe(2);
        await expect.poll(() => page.locator('[data-zazu-offline-conflicts]').isVisible()).toBe(false);

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

        await expect.poll(() => dialogs.length).toBe(1);
        expect(dialogs[0].type).toBe('alert');
        expect(dialogs[0].message).toContain('Your business offline workspace, pending changes and device token were left untouched');
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

    test('logout preserves the business offline workspace and paired device across account sessions', async ({ page }) => {
        test.setTimeout(60_000);

        await login(page);
        await waitForOfflineRuntime(page);
        // Wait for the best-effort first-load device registration before replacing
        // its token with a deterministic fixture for the logout preservation test.
        await expect.poll(() => page.evaluate(() => window.ZazuOffline.isPaired())).toBe(true);
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

        await page.route('**/api/sync/bootstrap', async route => {
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

        await page.route('**/api/sync/bootstrap', async route => {
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

        const mutationId = await page.evaluate(async () => {
            const state = window.ZazuOffline.getState();
            state.sync = { token: 'expired-device-token-fixture', device: { id: 987699 } };
            await window.ZazuOffline.persist({ allowSessionChange: true });
            const mutation = await window.ZazuOffline.queueMutation('customer', 'create', {
                local_id: 'expired-device-local-customer',
                record: { name: 'Preserve while re-pairing' },
            });
            return mutation.id;
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
        expect(outcome.pending).toContain(mutationId);
        expect(outcome.tokenPresent).toBe(true);
        await expect(page.locator('[data-zazu-sync-recovery]')).toBeVisible();
        await expect(page.locator('[data-zazu-sync-recovery-message]')).toContainText('saved offline changes remain');
    });

    test('re-pairing preserves queued work and its local workspace until sync can reconcile it', async ({ page }) => {
        test.setTimeout(30_000);
        await login(page);
        await page.goto('/settings', { waitUntil: 'domcontentloaded' });
        await page.evaluate(() => window.ZazuOffline.ready);

        const mutationId = await page.evaluate(async () => {
            const state = window.ZazuOffline.getState();
            state.business = { id: Number(document.body.dataset.zazuBusinessId), name: 'Locally preserved workspace' };
            state.customers = [{ id: null, local_id: 're-pair-local-customer', name: 'Keep this customer' }];
            state.sync = { token: 'revoked-old-token', device: { id: 987698 } };
            state.sync_authorization_required = true;
            await window.ZazuOffline.persist({ allowSessionChange: true });
            const mutation = await window.ZazuOffline.queueMutation('customer', 'create', {
                local_id: 're-pair-local-customer',
                record: { name: 'Keep this customer' },
            });
            return mutation.id;
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

        await page.evaluate(() => window.ZazuOffline.pair('123456'));
        const outcome = await page.evaluate(async () => ({
            businessName: window.ZazuOffline.getState().business?.name,
            customerName: window.ZazuOffline.getState().customers?.[0]?.name,
            token: window.ZazuOffline.getState().sync?.token,
            recoveryRequired: window.ZazuOffline.getState().sync_authorization_required === true,
            pending: (await window.ZazuOffline.pending()).map(item => item.id),
        }));

        expect(outcome.businessName).toBe('Locally preserved workspace');
        expect(outcome.customerName).toBe('Keep this customer');
        expect(outcome.token).toBe('new-repair-token');
        expect(outcome.recoveryRequired).toBe(false);
        expect(outcome.pending).toContain(mutationId);
    });

    test('a cross-business pairing response is revoked without changing local workspace data', async ({ page }) => {
        test.setTimeout(30_000);
        await login(page);
        await page.goto('/settings', { waitUntil: 'domcontentloaded' });
        await page.evaluate(() => window.ZazuOffline.ready);

        const businessId = await page.evaluate(() => Number(document.body.dataset.zazuBusinessId));
        const mutationId = await page.evaluate(async () => {
            const state = window.ZazuOffline.getState();
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
                await window.ZazuOffline.pair('654321');
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
        await expect(page.getByRole('heading', { name: 'Dashboard' })).toBeVisible();
        await expect(page.locator('.zazu-error-shell')).toHaveCount(0);
    });
});
