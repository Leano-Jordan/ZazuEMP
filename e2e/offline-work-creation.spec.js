import { test, expect } from '@playwright/test';
import { randomUUID } from 'node:crypto';

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

test('real Zazu job form can create a job locally while disconnected', async ({ page }) => {
    const runId = randomUUID().slice(0, 8);
    const customerName = `Offline test customer ${runId}`;
    const jobName = `Offline catering job ${runId}`;
    await login(page);
    await expect(page.getByRole('heading', { name: 'Dashboard' })).toBeVisible();

    // The installed app must prime the supported local creation routes from
    // its first authenticated dashboard launch. Do not visit them online here:
    // doing so would hide a broken automatic-prime path.
    await page.waitForFunction(() => Boolean(navigator.serviceWorker?.controller));
    let primedCacheSnapshot = null;
    await expect.poll(async () => {
        primedCacheSnapshot = await page.evaluate(async () => {
            const names = await caches.keys();
            const staticName = names.find(name => name.startsWith('zazu-static-v') && !name.includes('-user-'));
            if (!staticName) return { ready: false, reason: 'static-cache-missing' };
            const contextResponse = await (await caches.open(staticName)).match('/__zazu-active-context__');
            if (!contextResponse) return { ready: false, reason: 'active-context-missing' };
            const context = await contextResponse.json();
            const pageCacheName = staticName + '-user-' + context.user_id + '-business-' + context.business_id;
            if (!names.includes(pageCacheName)) return { ready: false, reason: 'business-cache-missing', context };
            const cache = await caches.open(pageCacheName);
            const requiredRoutes = ['/customers', '/customers/create', '/work', '/work/create'];
            const cachedRoutes = await Promise.all(requiredRoutes.map(path =>
                cache.match(new URL(path, window.location.origin))
            ));
            if (!cachedRoutes.every(Boolean)) {
                return {
                    ready: false,
                    reason: 'routes-not-cached',
                    context,
                    pageCacheName,
                    cachedRoutes: cachedRoutes.map(Boolean),
                };
            }
            const [customerCreateHtml, workCreateHtml] = await Promise.all([
                cachedRoutes[1].clone().text(),
                cachedRoutes[3].clone().text(),
            ]);
            const registration = await navigator.serviceWorker.getRegistration('/');
            return {
                ready: cachedRoutes.every(Boolean)
                    && customerCreateHtml.includes('data-zazu-offline-entity="customer"')
                    && workCreateHtml.includes('data-zazu-offline-entity="job"')
                    && navigator.serviceWorker.controller === registration?.active
                    && !registration?.waiting
                    && !registration?.installing,
                context,
                pageCacheName,
                cachedRoutes: cachedRoutes.map(Boolean),
                customerForm: customerCreateHtml.includes('data-zazu-offline-entity="customer"'),
                workForm: workCreateHtml.includes('data-zazu-offline-entity="job"'),
                controllerState: navigator.serviceWorker.controller?.state || null,
                controllerMatchesActive: navigator.serviceWorker.controller === registration?.active,
                activeState: registration?.active?.state || null,
                waitingState: registration?.waiting?.state || null,
                installingState: registration?.installing?.state || null,
            };
        });
        return primedCacheSnapshot?.ready === true;
    }, { timeout: 20_000 }).toBe(true);
    // Create the customer through Zazu's real customer form while offline.
    // This exercises the same form handler and durable queue used by operators.
    await page.context().setOffline(true);
    const offlineCustomerResponse = await page.goto('/customers/create', { waitUntil: 'domcontentloaded' });
    const customerForm = page.locator('form[data-zazu-offline-entity="customer"]');
    if (await customerForm.count() === 0) {
        const diagnostic = await page.evaluate(async () => ({
            url: window.location.href,
            route: document.body?.dataset.zazuRoute || null,
            title: document.title,
            hasDashboard: Boolean(document.querySelector('[data-zazu-offline-workspace]')),
            bodyText: (document.body?.innerText || '').slice(0, 700),
            cacheState: await (async () => {
                const names = await caches.keys();
                const staticName = names.find(name => /^zazu-static-v/.test(name) && !name.includes('-user-'));
                let activeContext = null;
                if (staticName) {
                    const response = await (await caches.open(staticName)).match('/__zazu-active-context__');
                    activeContext = response ? await response.json() : null;
                }
                const userCaches = await Promise.all(names.filter(name => name.includes('-user-')).map(async name => {
                    const cache = await caches.open(name);
                    const response = await cache.match(new URL('/customers/create', window.location.origin));
                    const html = response ? await response.clone().text() : '';
                    return {
                        name,
                        hasCustomerCreate: Boolean(response),
                        hasCustomerForm: html.includes('data-zazu-offline-entity="customer"'),
                    };
                }));
                return { activeContext, userCaches };
            })(),
        }));
        console.log('OFFLINE_CUSTOMER_ROUTE_DIAGNOSTIC', JSON.stringify({
            responseStatus: offlineCustomerResponse?.status() ?? null,
            ...diagnostic,
        }));
    }
    await expect(customerForm).toBeVisible();
    await customerForm.locator('input[name="name"]').fill(customerName);
    await customerForm.locator('input[name="primary_contact_name"]').fill('Offline test contact');
    await customerForm.locator('input[name="primary_contact_phone"]').fill('0123456789');
    await customerForm.getByRole('button', { name: 'Save customer' }).click();

    await expect(customerForm.getByText('Saved on this device. It will sync automatically when Zazu reconnects.')).toBeVisible();
    await expect(customerForm.getByRole('button', { name: 'Saved locally' })).toBeDisabled();

    const customerLocalId = await page.evaluate((name) => {
        const customer = window.ZazuOffline?.getState()?.customers?.find(item => item.name === name);
        return customer?.local_id || null;
    }, customerName);
    expect(customerLocalId).toBeTruthy();
    const customerQueue = await page.evaluate(async () => await window.ZazuOffline?.pending());
    expect(customerQueue.some((mutation) => mutation.entity_type === 'customer' && mutation.payload?.record?.name === customerName)).toBe(true);

    await page.goto('/customers', { waitUntil: 'domcontentloaded' });
    await expect(page.locator('[data-zazu-local-record="customer"]').filter({ hasText: customerName })).toBeVisible();

    await page.goto('/work/create', { waitUntil: 'domcontentloaded' });
    await expect(page.locator('#new-job-form')).toBeVisible();
    const customerOption = page.locator('#customer_id option').filter({ hasText: customerName });
    await expect(customerOption).toHaveCount(1);
    await page.locator('#customer_id').selectOption('local:' + customerLocalId);

    // Use the visible choice cards exactly as an operator would.
    const eventTypeChoice = page.locator('#new-job-form label.zazu-choice-card:has(input[name="event_type"])').first();
    const cateringChoice = page.locator('#new-job-form label.zazu-service-choice:has(input[name="services[]"][value="Catering"])');
    await eventTypeChoice.click();
    await cateringChoice.click();
    await expect(eventTypeChoice.locator('input[name="event_type"]')).toBeChecked();
    await expect(cateringChoice.locator('input[name="services[]"]')).toBeChecked();
    await page.locator('#new-job-form input[name="name"]').fill(jobName);
    // The production form requires a job date; without it the browser blocks
    // submission before the offline handler can run.
    await page.locator('#new-job-form input[name="event_date"]').fill('2026-11-15');

    await page.context().setOffline(true);
    await page.locator('#new-job-form').getByRole('button', { name: /Create job/ }).click();

    await expect(page.getByText('Saved on this device. It will sync automatically when Zazu reconnects.')).toBeVisible();
    await expect(page.locator('#new-job-form button[type="submit"]')).toHaveText('Saved locally');

    const state = await page.evaluate(() => window.ZazuOffline?.getState()?.jobs || []);
    expect(state.some((job) => job.name === jobName && job.local_id)).toBe(true);
    const queued = await page.evaluate(async () => await window.ZazuOffline?.pending());
    expect(queued.some((mutation) => mutation.entity_type === 'job' && mutation.payload?.record?.name === jobName)).toBe(true);

    // A reload while disconnected must recover the same locally persisted job.
    await page.reload({ waitUntil: 'domcontentloaded' });
    await page.waitForFunction(() => Boolean(window.ZazuOffline?.ready));
    await page.evaluate(() => window.ZazuOffline.ready);
    const persisted = await page.evaluate(() => window.ZazuOffline?.getState()?.jobs || []);
    expect(persisted.some((job) => job.name === jobName && job.local_id)).toBe(true);
    const persistedQueue = await page.evaluate(async () => await window.ZazuOffline?.pending());
    expect(persistedQueue.some((mutation) => mutation.entity_type === 'job' && mutation.payload?.record?.name === jobName)).toBe(true);
    const persistedCustomer = await page.evaluate((name) => window.ZazuOffline?.getState()?.customers?.find(customer => customer.name === name) || null, customerName);
    expect(persistedCustomer).toBeTruthy();

    // Offline records must be visible in the normal operating directories,
    // not merely present in IndexedDB.
    await page.goto('/work', { waitUntil: 'domcontentloaded' });
    await expect(page.locator('[data-zazu-local-record="job"]').filter({ hasText: jobName })).toHaveCount(1);
    await expect(page.locator('[data-zazu-local-record="job"]').filter({ hasText: jobName })).toBeVisible();
    // The local directory renderer must retain pending records in the All Work view.
    await page.evaluate(() => {
        const url = new URL(window.location.href);
        url.searchParams.set('filter', 'all');
        history.replaceState({}, '', url);
        window.dispatchEvent(new CustomEvent('zazu:offline-state-updated'));
    });
    await expect(page.locator('[data-zazu-local-record="job"]').filter({ hasText: jobName })).toHaveCount(1);

    await expect(page.locator('[data-zazu-local-record="job"]').filter({ hasText: customerName })).toHaveCount(1);

    await page.goto('/customers', { waitUntil: 'domcontentloaded' });
    await expect(page.locator('[data-zazu-local-record="customer"]').filter({ hasText: customerName })).toHaveCount(1);
    await expect(page.locator('[data-zazu-local-record="customer"]').filter({ hasText: customerName })).toBeVisible();

    // The cached dashboard is the fallback for unprimed routes. It must expose
    // the IndexedDB working set instead of leaving the operator on a stale snapshot.
    await page.goto('/dashboard', { waitUntil: 'domcontentloaded' });
    await expect(page.locator('[data-zazu-offline-workspace]')).toBeVisible();
    await expect(page.locator('[data-zazu-offline-workspace] [data-zazu-local-record="customer"]').filter({ hasText: customerName })).toBeVisible();
    await expect(page.locator('[data-zazu-offline-workspace] [data-zazu-local-record="job"]').filter({ hasText: jobName })).toBeVisible();

    // The device-local workspace must navigate into the cached customer and
    // Work directories and render the same IndexedDB records there.
    await page.getByRole('link', { name: 'Open customers' }).click();
    await expect(page).toHaveURL(/customers$/);
    await expect(page.locator('[data-zazu-local-record="customer"]').filter({ hasText: customerName })).toBeVisible();
    await page.goBack({ waitUntil: 'domcontentloaded' });
    await expect(page.locator('[data-zazu-offline-workspace]')).toBeVisible();
    await page.getByRole('link', { name: 'Open work' }).click();
    await expect(page).toHaveURL(/work$/);
    await expect(page.locator('[data-zazu-local-record="job"]').filter({ hasText: jobName })).toBeVisible();

    // Reconnect must provision this signed-in phone automatically. No
    // desktop settings screen or manually generated pairing code is involved.
    await page.context().setOffline(false);
    await expect.poll(async () => page.evaluate(() => Boolean(window.ZazuOffline?.isPaired())), {
        timeout: 20_000,
        message: 'The signed-in primary device should provision itself after reconnecting.',
    }).toBe(true);
    await expect.poll(async () => page.evaluate(async () => (await window.ZazuOffline.pending()).length), {
        timeout: 30_000,
        message: 'The phone should sync queued work after automatic device registration.',
    }).toBe(0);

    const syncedState = await page.evaluate(({ customerLocalId, jobName }) => {
        const state = window.ZazuOffline?.getState();
        return {
            customer: state?.customers?.find(item => item.local_id === customerLocalId) || null,
            job: state?.jobs?.find(item => item.name === jobName) || null,
            conflicts: state?.conflicts || [],
        };
    }, { customerLocalId, jobName: jobName });

    expect(syncedState.customer?.server_id).toBeGreaterThan(0);
    expect(syncedState.customer?.id).toBe(syncedState.customer?.server_id);
    expect(syncedState.job?.server_id).toBeGreaterThan(0);
    expect(syncedState.job?.id).toBe(syncedState.job?.server_id);
    expect(syncedState.conflicts.some(conflict =>
        conflict.entity_id === customerLocalId
        || conflict.entity_id === syncedState.job?.local_id
    )).toBe(false);

    const serverWork = await page.request.get('/work/' + syncedState.job.server_id);
    expect(serverWork.ok()).toBe(true);
    expect(await serverWork.text()).toContain(jobName);

    const serverCustomers = await page.request.get('/customers');
    expect(serverCustomers.ok()).toBe(true);
    expect(await serverCustomers.text()).toContain(customerName);
});
