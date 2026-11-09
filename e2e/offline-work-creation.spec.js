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

test('real Zazu job form can create a job locally while disconnected', async ({ page }) => {
    await login(page);
    await page.goto('/dashboard', { waitUntil: 'domcontentloaded' });
    await expect(page.getByRole('heading', { name: 'Dashboard' })).toBeVisible();

    // Prime the authenticated directory routes before disconnecting. The
    // test then verifies their cached shells can render the IndexedDB records.
    await page.goto('/customers', { waitUntil: 'domcontentloaded' });
    await expect(page.getByRole('heading', { name: 'Customers' })).toBeVisible();
    await page.goto('/work', { waitUntil: 'domcontentloaded' });
    await expect(page.getByRole('heading', { name: 'Event Operations' })).toBeVisible();

    await page.goto('/work/create', { waitUntil: 'domcontentloaded' });
    await expect(page.locator('#new-job-form')).toBeVisible();

    // A job may only reference a customer already present in this device's
    // offline workspace. Seed a realistic phone-local customer rather than
    // relying on a server-rendered option that may not have been synced.
    await page.evaluate(async () => {
        await window.ZazuOffline.ready;
        const state = structuredClone(window.ZazuOffline.getState());
        const localId = crypto.randomUUID();
        const customer = {
            local_id: localId,
            server_id: null,
            id: null,
            name: 'Offline test customer',
            contact_name: 'Offline test contact',
            phone: '0123456789',
            email: 'offline@example.test',
        };
        state.customers = [...(state.customers || []), customer];
        await window.ZazuOffline.queueMutation('customer', 'create', customer, state);
        const select = document.querySelector('#customer_id');
        const option = new Option(customer.name, 'local:' + localId);
        option.dataset.zazuLocalId = localId;
        select.add(option);
        select.value = option.value;
    });
    // Use the visible choice cards exactly as an operator would.
    const eventTypeChoice = page.locator('#new-job-form label.zazu-choice-card:has(input[name="event_type"])').first();
    const cateringChoice = page.locator('#new-job-form label.zazu-service-choice:has(input[name="services[]"][value="Catering"])');
    await eventTypeChoice.click();
    await cateringChoice.click();
    await expect(eventTypeChoice.locator('input[name="event_type"]')).toBeChecked();
    await expect(cateringChoice.locator('input[name="services[]"]')).toBeChecked();
    await page.locator('#new-job-form input[name="name"]').fill('Offline catering job');
    // The production form requires a job date; without it the browser blocks
    // submission before the offline handler can run.
    await page.locator('#new-job-form input[name="event_date"]').fill('2026-11-15');

    await page.context().setOffline(true);
    await page.locator('#new-job-form').getByRole('button', { name: /Create job/ }).click();

    await expect(page.getByText('Saved on this device. It will sync automatically when Zazu reconnects.')).toBeVisible();
    await expect(page.locator('#new-job-form button[type="submit"]')).toHaveText('Saved locally');

    const state = await page.evaluate(() => window.ZazuOffline?.getState()?.jobs || []);
    expect(state.some((job) => job.name === 'Offline catering job' && job.local_id)).toBe(true);
    const queued = await page.evaluate(async () => await window.ZazuOffline?.pending());
    expect(queued.some((mutation) => mutation.entity_type === 'job' && mutation.payload?.name === 'Offline catering job')).toBe(true);

    // A reload while disconnected must recover the same locally persisted job.
    await page.reload({ waitUntil: 'domcontentloaded' });
    await page.waitForFunction(() => Boolean(window.ZazuOffline?.ready));
    await page.evaluate(() => window.ZazuOffline.ready);
    const persisted = await page.evaluate(() => window.ZazuOffline?.getState()?.jobs || []);
    expect(persisted.some((job) => job.name === 'Offline catering job' && job.local_id)).toBe(true);
    const persistedQueue = await page.evaluate(async () => await window.ZazuOffline?.pending());
    expect(persistedQueue.some((mutation) => mutation.entity_type === 'job' && mutation.payload?.name === 'Offline catering job')).toBe(true);

    // Offline records must be visible in the normal operating directories,
    // not merely present in IndexedDB.
    await page.goto('/work', { waitUntil: 'domcontentloaded' });
    await expect(page.locator('[data-zazu-local-record="job"]').filter({ hasText: 'Offline catering job' })).toBeVisible();
    await expect(page.locator('[data-zazu-local-record="job"]').filter({ hasText: 'Offline test customer' })).toBeVisible();

    await page.goto('/customers', { waitUntil: 'domcontentloaded' });
    await expect(page.locator('[data-zazu-local-record="customer"]').filter({ hasText: 'Offline test customer' })).toBeVisible();
});
