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

    await page.goto('/work/create', { waitUntil: 'domcontentloaded' });
    await expect(page.locator('#new-job-form')).toBeVisible();

    const customer = page.locator('#customer_id option:not([value=""])').first();
    await expect(customer).toHaveCount(1);
    await page.locator('#customer_id').selectOption({ index: 1 });
    await page.locator('label.zazu-choice-card:has(input[name="event_type"])').first().click();
    await page.locator('#new-job-form input[name="name"]').fill('Offline catering job');
    // Keep native required-field validation realistic: offline saves still require a job date.
    await page.locator('#new-job-form input[name="event_date"]').fill('2030-01-15');

    await page.context().setOffline(true);
    await page.locator('#new-job-form').getByRole('button', { name: /Create job/ }).click();

    await expect(page.getByText('Saved on this device. It will sync automatically when Zazu reconnects.')).toBeVisible();
    await expect(page.locator('#new-job-form button[type="submit"]')).toHaveText('Saved locally');

    const state = await page.evaluate(() => window.ZazuOffline?.getState()?.jobs || []);
    expect(state.some((job) => job.name === 'Offline catering job' && job.local_id)).toBe(true);
});
