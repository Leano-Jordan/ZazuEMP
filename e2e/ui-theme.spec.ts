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
        await expect(page.locator('a', { hasText: 'Launch Rosco ICT Workspace' }).first()).toBeVisible();

        await page.locator('a', { hasText: 'Launch Rosco ICT Workspace' }).first().click();
        await expect(page).toHaveURL(/\/login$/);

        await page.goto('/work');
        await expect(page).toHaveURL(/\/login$/);
        expect(responses).toEqual([]);
    });
});
