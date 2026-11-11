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
    await page.goto('/dashboard', { waitUntil: 'domcontentloaded' });
    await expect(page.getByRole('heading', { name: 'Dashboard' })).toBeVisible();

    // Prime the authenticated directory routes before disconnecting. The
    // test then verifies their cached shells can render the IndexedDB records.
    await page.goto('/customers/create', { waitUntil: 'domcontentloaded' });
    await expect(page.locator('form[data-zazu-offline-entity="customer"]')).toBeVisible();
    await page.goto('/customers', { waitUntil: 'domcontentloaded' });
    await expect(page.getByRole('heading', { name: 'Customers' })).toBeVisible();
    await page.goto('/work', { waitUntil: 'domcontentloaded' });
    await expect(page.getByRole('heading', { name: 'Event Operations' })).toBeVisible();

    await page.goto('/work/create', { waitUntil: 'domcontentloaded' });
    await expect(page.locator('#new-job-form')).toBeVisible();

    // Create the customer through Zazu's real customer form while offline.
    // This exercises the same form handler and durable queue used by operators.
    await page.context().setOffline(true);
    await page.goto('/customers/create', { waitUntil: 'domcontentloaded' });
    const customerForm = page.locator('form[data-zazu-offline-entity="customer"]');
    await expect(customerForm).toBeVisible();
    await customerForm.locator('input[name="name"]').fill(customerName);
    await customerForm.locator('input[name="primary_contact_name"]').fill('Offline test contact');
    await customerForm.locator('input[name="primary_contact_phone"]').fill('0123456789');
    await customerForm.getByRole('button', { name: 'Save customer' }).click();

    await expect(customerForm.getByText('Saved on this device. It will sync automatically when Zazu reconnects.')).toBeVisible();
    await expect(customerForm.getByRole('button', { name: 'Saved locally' })).toBeDisabled();

    const customerLocalId = await page.evaluate(() => {
        const customer = window.ZazuOffline?.getState()?.customers?.find(item => item.name === customerName);
        return customer?.local_id || null;
    });
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
    const persistedCustomer = await page.evaluate(() => window.ZazuOffline?.getState()?.customers?.find(customer => customer.name === customerName) || null);
    expect(persistedCustomer).toBeTruthy();

    // Offline records must be visible in the normal operating directories,
    // not merely present in IndexedDB.
    await page.goto('/work', { waitUntil: 'domcontentloaded' });
    await expect(page.locator('[data-zazu-local-record="job"]').filter({ hasText: jobName })).toHaveCount(1);
    await expect(page.locator('[data-zazu-local-record="job"]').filter({ hasText: jobName })).toBeVisible();
    await expect(page.locator('[data-zazu-local-record="job"]').filter({ hasText: customerName })).toHaveCount(1);

    await page.goto('/customers', { waitUntil: 'domcontentloaded' });
    await expect(page.locator('[data-zazu-local-record="customer"]').filter({ hasText: customerName })).toHaveCount(1);
    await expect(page.locator('[data-zazu-local-record="customer"]').filter({ hasText: customerName })).toBeVisible();

    // Prove the queued customer and job reach the real Laravel database after
    // reconnect, not merely that the browser can retain them in IndexedDB.
    await page.context().setOffline(false);
    await page.goto('/settings', { waitUntil: 'domcontentloaded' });
    const pairingButton = page.getByRole('button', { name: 'Generate phone pairing code' });
    await pairingButton.click();
    const pairingResult = page.locator('[data-zazu-pairing-result]');
    await expect(pairingResult).toContainText(/Code: \d{6}/);
    const pairingCode = (await pairingResult.textContent()).match(/\d{6}/)?.[0];
    expect(pairingCode).toMatch(/^\d{6}$/);

    await page.evaluate(code => window.ZazuOffline.pair(code), pairingCode);
    await expect.poll(async () => page.evaluate(async () => (await window.ZazuOffline.pending()).length), {
        timeout: 20_000,
        message: 'The real sync endpoint should apply the locally queued customer and job after pairing.',
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
