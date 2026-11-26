import { test, expect } from '@playwright/test';

const DEMO_EMAIL = process.env.ZAZU_DEMO_EMAIL || 'demo@zazu.local';
const DEMO_PASSWORD = process.env.ZAZU_DEMO_PASSWORD || 'password';

async function login(page) {
    await page.goto('/?auth=login', { waitUntil: 'domcontentloaded' });
    await expect(page.locator('[data-auth-modal]')).toBeVisible();
    await page.getByLabel('Username or email').fill(DEMO_EMAIL);
    await page.locator('#auth_password').fill(DEMO_PASSWORD);
    await page.locator('[data-auth-submit]').click();
    await page.waitForURL(url => url.pathname === '/dashboard');
    await expect(page.getByRole('heading', { name: 'Dashboard' })).toBeVisible();
}

test('desktop navigation groups open without navigating; destinations navigate directly', async ({ page }) => {
    await page.setViewportSize({ width: 1440, height: 1000 });
    await login(page);

    const work = page.locator('[data-zazu-nav-area]').filter({ has: page.getByRole('button', { name: /Work/ }) });
    const workTrigger = work.getByRole('button', { name: /Work/ });
    await workTrigger.click();
    await expect(workTrigger).toHaveAttribute('aria-expanded', 'true');
    await expect(work.getByRole('link', { name: 'Jobs' })).toBeVisible();
    await expect(page).toHaveURL(/\/dashboard$/);

    await work.getByRole('link', { name: 'Jobs' }).click();
    await expect(page).toHaveURL(/\/work$/);
    await expect(page.locator('main')).toBeVisible();
});

test('mobile and tablet navigation use tap-controlled groups and preserve destination intent', async ({ page }) => {
    await login(page);

    for (const viewport of [{ width: 390, height: 844 }, { width: 810, height: 1080 }]) {
        await page.setViewportSize(viewport);
        const menu = page.locator('[data-mobile-sidebar-toggle]');
        await menu.click();
        await expect(page.locator('body')).toHaveClass(/zazu-mobile-menu-open/);

        const sales = page.locator('[data-zazu-nav-area]').filter({ has: page.getByRole('button', { name: /Sales/ }) });
        const salesTrigger = sales.getByRole('button', { name: /Sales/ });
        await salesTrigger.click();
        await expect(salesTrigger).toHaveAttribute('aria-expanded', 'true');
        await expect(sales.getByRole('link', { name: 'Quotes' })).toBeVisible();
        await expect(page).toHaveURL(/\/dashboard$/);

        await salesTrigger.click();
        await expect(salesTrigger).toHaveAttribute('aria-expanded', 'false');
        await expect(page).toHaveURL(/\/dashboard$/);

        await salesTrigger.click();
        await sales.getByRole('link', { name: 'Quotes' }).click();
        await expect(page).toHaveURL(/\/quotes$/);
        await expect(page.locator('body')).not.toHaveClass(/zazu-mobile-menu-open/);
        await expect(page.locator('main')).toBeVisible();
    }
});
