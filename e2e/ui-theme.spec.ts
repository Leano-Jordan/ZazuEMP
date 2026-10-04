import { test, expect } from '@playwright/test';

const username = process.env.ZAZU_E2E_USERNAME || process.env.ZAZU_DEMO_EMAIL || 'demo@zazu.local';
const password = process.env.ZAZU_E2E_PASSWORD || process.env.ZAZU_DEMO_PASSWORD || 'password';

async function login(page) {
    await page.goto('/?auth=login');
    await page.getByLabel('Username or email').fill(username);
    await page.locator('#auth_password').fill(password);
    await page.locator('[data-auth-submit]').click();
    await expect(page).toHaveURL(/\/dashboard$/);
}

async function expectNoViewportOverflow(page) {
    const dimensions = await page.evaluate(() => ({
        scrollWidth: document.documentElement.scrollWidth,
        clientWidth: document.documentElement.clientWidth,
    }));

    expect(dimensions.scrollWidth).toBeLessThanOrEqual(dimensions.clientWidth);
}

test.describe('Zazu UI theme and navigation', () => {
    test('asserts Signature Blue tokens in light and dark mode', async ({ browser }) => {
        const context = await browser.newContext();
        const page = await context.newPage();

        await login(page);
        await expectNoViewportOverflow(page);

        const lightTokens = await page.evaluate(() => {
            const styles = getComputedStyle(document.documentElement);
            return {
                canvas: styles.getPropertyValue('--zazu-surface-canvas').trim(),
                blue: styles.getPropertyValue('--zazu-blue-primary').trim(),
            };
        });

        expect(lightTokens.canvas.toLowerCase()).toBe('#f1f5fb');
        expect(lightTokens.blue.toLowerCase()).toBe('#3949e8');

        const toggle = page.locator('[data-theme-toggle]').first();
        await expect(toggle).toBeVisible();
        await toggle.click();

        const darkTokens = await page.evaluate(() => {
            const styles = getComputedStyle(document.documentElement);
            return {
                canvas: styles.getPropertyValue('--zazu-surface-canvas').trim(),
                blue: styles.getPropertyValue('--zazu-blue-primary').trim(),
            };
        });

        expect(darkTokens.canvas.toLowerCase()).toBe('#0b0e14');
        expect(darkTokens.blue.toLowerCase()).toBe('#7a78ff');

        await context.close();
    });

    test('navigates landing to login and workspace operations without server errors', async ({ page }) => {
        const responses: number[] = [];
        page.on('response', (response) => {
            if (response.status() >= 500) responses.push(response.status());
        });

        await page.goto('/');
        const publicNavigation = page.locator('nav[aria-label="Public navigation"]');
        const mobileNavigationToggle = page.getByRole('button', { name: 'Open navigation' });
        if (await mobileNavigationToggle.isVisible()) {
            await mobileNavigationToggle.click();
        }
        await expect(publicNavigation.getByRole('button', { name: 'Get started', exact: true })).toBeVisible();
        await expect(publicNavigation.getByRole('button', { name: 'Log in', exact: true })).toBeVisible();

        await publicNavigation.getByRole('button', { name: 'Log in', exact: true }).click();
        await expect(page.locator('[data-auth-modal]')).toBeVisible();
        await expect(page.getByRole('heading', { name: 'Welcome back.' })).toBeVisible();

        await page.getByLabel('Username or email').fill(username);
        await page.locator('#auth_password').fill(password);
        await page.locator('[data-auth-submit]').click();

        await expect(page).toHaveURL(/\/dashboard$/);
        await page.goto('/work');
        await expect(page).toHaveURL(/\/work/);
        await expectNoViewportOverflow(page);
        await expect(page.locator('[data-zazu-inspector-open]').first()).toBeVisible();
        await page.locator('[data-zazu-inspector-open]').first().click();
        await expect(page.locator('[data-zazu-inspector-panel]').first()).toBeVisible();

        expect(responses).toEqual([]);
    });
});
