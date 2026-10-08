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
