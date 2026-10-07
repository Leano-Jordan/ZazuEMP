import { test, expect } from '@playwright/test';

test('real Zazu job form can create a job locally while disconnected', async ({ page }) => {
    await page.goto('/dashboard', { waitUntil: 'domcontentloaded' });
    await expect(page.getByRole('heading', { name: 'Dashboard' })).toBeVisible();

    await page.goto('/work/create', { waitUntil: 'domcontentloaded' });
    await expect(page.locator('#new-job-form')).toBeVisible();

    await page.getByRole('button', { name: 'Add customer', exact: true }).click();
    await expect(page.locator('#quick-customer-dialog')).toBeVisible();
    await page.getByLabel('Customer name').fill('Offline Browser Customer');
    await page.getByLabel('Contact name').fill('Offline Browser Contact');
    await page.getByRole('button', { name: 'Save and use customer', exact: true }).click();
    await expect(page.locator('#quick-customer-dialog')).toBeHidden();
    await expect(page.locator('#customer_id')).not.toHaveValue('');
    await page.locator('input[name="event_type"]').first().check();
    await page.locator('input[name="name"]').fill('Offline catering job');

    await page.context().setOffline(true);
    await page.locator('#new-job-form').getByRole('button', { name: /Create job/ }).click();

    await expect(page.getByText('Saved on this device. It will sync automatically when Zazu reconnects.')).toBeVisible();
    await expect(page.locator('#new-job-form button[type="submit"]')).toHaveText('Saved locally');

    const state = await page.evaluate(() => window.ZazuOffline?.getState()?.jobs || []);
    expect(state.some((job) => job.name === 'Offline catering job' && job.local_id)).toBe(true);
});
