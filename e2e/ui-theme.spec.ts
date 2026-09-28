import { test, expect } from '@playwright/test';

const storageState = process.env.ZAZU_E2E_STORAGE_STATE;

test.describe('Zazu UI theme and navigation', () => {
    test.beforeEach(async ({}, testInfo) => {
        testInfo.skip(
            !storageState,
            'Set ZAZU_E2E_STORAGE_STATE to an authenticated Playwright storage state for workspace UI verification.'
        );
    });

    test('asserts Signature Blue tokens in light and dark mode', async ({ browser }) => {
        const context = await browser.newContext({
            storageState,
        });
        const page = await context.newPage();

        await page.goto('/dashboard');
        await expect(page).toHaveURL(/\/dashboard$/);

        const lightTokens = await page.evaluate(() => {
            const styles = getComputedStyle(document.documentElement);
            return {
                canvas: styles.getPropertyValue('--zazu-surface-canvas').trim(),
                blue: styles.getPropertyValue('--zazu-blue-primary').trim(),
            };
        });

        expect(lightTokens.canvas).toBe('#F2F7FF');
        expect(lightTokens.blue).toBe('#416AD7');

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

        expect(darkTokens.canvas).toBe('#0B0E14');
        expect(darkTokens.blue).toBe('#416AD7');

        await context.close();
    });

    test('navigates landing to login and workspace operations without server errors', async ({ page }) => {
        const responses: number[] = [];
        page.on('response', (response) => {
            if (response.status() >= 500) responses.push(response.status());
        });

        await page.goto('/');
        await expect(page.getByRole('link', { name: 'Register' })).toBeVisible();
        await expect(page.getByRole('link', { name: 'Log in' })).toBeVisible();

        await page.getByRole('link', { name: 'Log in' }).click();
        await expect(page).toHaveURL(/\/login$/);

        const username = process.env.ZAZU_E2E_USERNAME;
        const password = process.env.ZAZU_E2E_PASSWORD;

        if (!username || !password) {
            expect(responses).toEqual([]);
            return;
        }

        await page.locator('#identifier').fill(username);
        await page.locator('#password').fill(password);
        await page.getByRole('button', { name: /sign in/i }).click();

        await expect(page).toHaveURL(/\/dashboard$/);
        await page.goto('/work');
        await expect(page).toHaveURL(/\/work/);
        await expect(page.locator('[data-zazu-inspector-open]').first()).toBeVisible();
        await page.locator('[data-zazu-inspector-open]').first().click();
        await expect(page.locator('[data-zazu-inspector-panel]').first()).toBeVisible();

        expect(responses).toEqual([]);
    });
});
