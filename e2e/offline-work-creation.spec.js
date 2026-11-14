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

test('offline workspace exposes locally stored quotes with their sync state', async ({ page }) => {
    const runId = randomUUID().slice(0, 8);
    const jobLocalId = randomUUID();
    const quoteLocalId = randomUUID();
    const jobName = 'Offline quote parent ' + runId;

    await login(page);
    await page.evaluate(async ({ jobLocalId, quoteLocalId, jobName }) => {
        await window.ZazuOffline.ready;
        const state = window.ZazuOffline.getState();
        state.jobs.push({
            local_id: jobLocalId,
            server_id: 7654321,
            id: 7654321,
            reference: 'ZAZ-JOB-' + jobLocalId.slice(0, 6),
            name: jobName,
            event_type: 'Catering',
            status: 'confirmed',
        });
        state.quotes.push({
            local_id: quoteLocalId,
            server_id: null,
            event_local_id: jobLocalId,
            reference: null,
            status: 'draft',
            currency: 'ZAR',
            items: [{ description: 'Catering package', quantity: 1, unit_price: '2500.00' }],
        });
        await window.ZazuOffline.persist();
        window.dispatchEvent(new CustomEvent('zazu:offline-state-updated'));
    }, { jobLocalId, quoteLocalId, jobName });

    const workspace = page.locator('[data-zazu-offline-workspace]');
    await expect(workspace).toBeVisible();
    const quoteRow = workspace.locator('[data-zazu-local-record="quote"]').filter({ hasText: jobName });
    await expect(quoteRow).toHaveCount(1);
    await expect(quoteRow.locator('.zazu-list-title')).toHaveText('Draft quote');
    await expect(quoteRow.locator('.zazu-list-meta')).toContainText(jobName);
    await expect(quoteRow.locator('.zazu-list-meta')).toContainText('draft');
    await expect(quoteRow.locator('.zazu-list-meta')).toContainText('ZAR');
    await expect(quoteRow).toContainText('Waiting to sync');
});

test('offline workspace distinguishes synced local jobs from pending jobs', async ({ page }) => {
    const runId = randomUUID().slice(0, 8);
    const syncedReference = 'ZAZ-SYNC-' + runId;
    const syncedName = 'Previously synced local job ' + runId;
    const pendingCustomerName = 'Pending workspace customer ' + runId;

    await login(page);
    await page.evaluate(async ({ syncedReference, syncedName, pendingCustomerName }) => {
        await window.ZazuOffline.ready;
        const state = window.ZazuOffline.getState();
        state.customers.push({
            local_id: crypto.randomUUID(),
            server_id: null,
            name: pendingCustomerName,
        });
        state.jobs.push({
            local_id: crypto.randomUUID(),
            server_id: 987654321,
            id: 987654321,
            reference: syncedReference,
            name: syncedName,
            customer_name: 'Existing customer',
            event_type: 'Catering',
            event_date: '2026-11-20',
            status: 'confirmed',
        });
        await window.ZazuOffline.persist();
        window.dispatchEvent(new CustomEvent('zazu:offline-state-updated'));
    }, { syncedReference, syncedName, pendingCustomerName });

    const workspace = page.locator('[data-zazu-offline-workspace]');
    await expect(workspace).toBeVisible();
    const syncedRow = workspace.locator('[data-zazu-local-record="job"]').filter({ hasText: syncedName });
    await expect(syncedRow).toBeVisible();
    await expect(syncedRow.locator('.zazu-ops-ref-label')).toHaveText('Job reference');
    await expect(syncedRow.locator('.zazu-ops-ref strong')).toHaveText(syncedReference);
    await expect(syncedRow).toContainText('Synced');
    await expect(syncedRow).not.toContainText('Pending sync');
});

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
    // Cache entries can appear before the service worker has finished warming
    // their dependent assets. Wait for its explicit completion acknowledgement
    // before simulating a disconnected launch.
    await page.waitForFunction(
        () => document.documentElement.dataset.zazuOfflinePagesPrimed === 'true',
        { timeout: 20_000 },
    );
    // Create the customer through Zazu's real customer form while offline.
    // This exercises the same form handler and durable queue used by operators.
    await page.context().setOffline(true);
    const offlineCustomerResponse = await page.goto('/customers/create', { waitUntil: 'domcontentloaded' });
    const customerForm = page.locator('form[data-zazu-offline-entity="customer"]');
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


test('an existing customer can be edited locally while disconnected and synced on reconnect', async ({ page }) => {
    const runId = randomUUID().slice(0, 8);
    const customerName = 'Offline edit fixture ' + runId;
    await login(page);

    // Create this test's own server-backed record instead of depending on
    // whichever demo customer happens to be present in the seeded database.
    await page.goto('/customers/create', { waitUntil: 'domcontentloaded' });
    await page.evaluate(() => window.ZazuOffline.ready);
    const createForm = page.locator('form[data-zazu-offline-entity="customer"]');
    await expect(createForm).toBeVisible();
    expect(await page.evaluate(() => navigator.onLine)).toBe(true);
    const reachableStatus = await page.evaluate(async () => {
        const response = await fetch('/up', {
            method: 'HEAD',
            credentials: 'same-origin',
            cache: 'no-store',
        });
        return response.status;
    });
    expect(reachableStatus).toBe(200);
    await createForm.locator('input[name="name"]').fill(customerName);
    await createForm.locator('input[name="primary_contact_name"]').fill('Offline edit contact');
    await createForm.locator('input[name="primary_contact_phone"]').fill('0123456789');

    // This test requires a server-backed customer as the starting point for
    // the subsequent disconnected edit. Record both sides of the online save
    // contract so a false offline classification cannot look like success.
    const customerCreateResponse = page.waitForResponse(response => {
        const request = response.request();
        return request.method() === 'POST' && new URL(response.url()).pathname === '/customers';
    });

    await createForm.getByRole('button', { name: 'Save customer' }).click();
    const createResponse = await customerCreateResponse;
    expect(createResponse.status(), 'the fixture must be saved by Laravel before the offline edit begins').toBe(302);
    await expect(page).toHaveURL(/\/customers$/);

    // Confirm that Laravel persisted the server-backed fixture independently
    // of the service-worker-cached directory HTML. The new customer URL is unique
    // and therefore cannot be mistaken for a previous cached directory snapshot.
    const serverDirectory = await page.request.get('/customers');
    expect(serverDirectory.status()).toBe(200);
    const directoryHtml = await serverDirectory.text();
    const customerPosition = directoryHtml.indexOf(customerName);
    expect(customerPosition, 'Laravel must return the newly created customer in its directory').toBeGreaterThan(-1);
    const rowMarkers = [...directoryHtml.slice(0, customerPosition).matchAll(/data-zazu-server-customer-id="(\d+)"/g)];
    const customerId = Number(rowMarkers.at(-1)?.[1] || 0);
    expect(customerId, 'the directory row must expose a server customer ID').toBeGreaterThan(0);

    // Refresh the paired device snapshot after creating the fixture so the edit
    // path has the server record in IndexedDB before the connection is removed.
    await page.evaluate(() => window.ZazuOffline.refreshBootstrap());
    const localFixture = await page.evaluate(name =>
        window.ZazuOffline.getState().customers.find(customer => customer.name === name) || null,
        customerName
    );
    expect(localFixture?.local_id, 'the customer must be present in the paired local workspace').toBeTruthy();
    expect(Number(localFixture?.server_id ?? localFixture?.id)).toBe(customerId);

    await page.goto('/customers/' + customerId, { waitUntil: 'domcontentloaded' });
    await page.getByRole('link', { name: 'Edit customer' }).click();

    const form = page.locator('form[data-zazu-offline-entity="customer"]');
    await expect(form).toBeVisible();

    const nameInput = form.locator('input[name="name"]');
    const updatedName = (await nameInput.inputValue()) + ' offline edit ' + runId;
    await nameInput.fill(updatedName);
    await form.locator('input[name="primary_contact_phone"]').fill('0123456789');

    await page.context().setOffline(true);
    await form.getByRole('button', { name: 'Save changes' }).click();

    await expect(form.getByText('Saved on this device. It will sync automatically when Zazu reconnects.')).toBeVisible();
    const queued = await page.evaluate(async () => window.ZazuOffline.pending());
    const update = queued.find(item => item.entity_type === 'customer' && item.operation === 'update' && item.payload?.record?.name === updatedName);
    expect(update).toBeTruthy();

    const localCustomer = await page.evaluate((id) => window.ZazuOffline.getState()?.customers?.find(item => Number(item.id ?? item.server_id) === id) || null, customerId);
    expect(localCustomer?.name).toBe(updatedName);

    // The cached Customers directory must render the local override rather
    // than showing the older server-rendered snapshot while disconnected.
    await page.goto('/customers', { waitUntil: 'domcontentloaded' });
    const cachedCustomerRow = page.locator('[data-zazu-server-customer-id="' + customerId + '"]');
    await expect(cachedCustomerRow.locator('.zazu-list-title')).toHaveText(updatedName);
    await expect(cachedCustomerRow.locator('.zazu-list-meta')).toContainText('0123456789');

    await page.context().setOffline(false);
    await expect.poll(async () => page.evaluate(async () => (await window.ZazuOffline.pending()).length), {
        timeout: 30_000,
        message: 'The existing customer update should sync after reconnecting.',
    }).toBe(0);

    const customerResponse = await page.request.get('/customers/' + customerId);
    expect(customerResponse.ok()).toBe(true);
    expect(await customerResponse.text()).toContain(updatedName);
});