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
    test('asserts current theme tokens and readable navigation in light and dark mode', async ({ browser }) => {
        const context = await browser.newContext();
        const page = await context.newPage();

        await page.addInitScript(() => window.localStorage.setItem('zazu-theme', 'light'));
        await login(page);
        await expectNoViewportOverflow(page);

        const lightTokens = await page.evaluate(() => {
            const styles = getComputedStyle(document.documentElement);
            const active = document.querySelector('.zazu-sidebar .zazu-nav-link.active');
            const workspace = document.querySelector('.zazu-sidebar .zazu-footer-workspace');
            const workspaceLabel = document.querySelector('.zazu-sidebar .zazu-footer-workspace-label');
            return {
                surface: styles.getPropertyValue('--zazu-surface').trim(),
                primary: styles.getPropertyValue('--zazu-primary').trim(),
                activeBackground: active ? getComputedStyle(active).backgroundColor : '',
                activeColor: active ? getComputedStyle(active).color : '',
                workspaceBackground: workspace ? getComputedStyle(workspace).backgroundColor : '',
                workspaceColor: workspace ? getComputedStyle(workspace).color : '',
                workspaceLabelColor: workspaceLabel ? getComputedStyle(workspaceLabel).color : '',
            };
        });

        expect(lightTokens.surface.toLowerCase()).toMatch(/^#(?:fff|ffffff)$/);
        expect(lightTokens.primary.toLowerCase()).toBe('#426987');
        expect(lightTokens.activeBackground).toBe('rgb(180, 200, 215)');
        expect(lightTokens.activeColor).toBe('rgb(35, 57, 77)');
        expect(lightTokens.workspaceColor).toBe('rgb(35, 57, 77)');
        expect(lightTokens.workspaceLabelColor).toBe('rgb(83, 107, 127)');

        const toggle = page.locator('[data-theme-toggle]').first();
        await expect(toggle).toBeVisible();
        await toggle.click();

        const darkTokens = await page.evaluate(() => {
            const styles = getComputedStyle(document.documentElement);
            const active = document.querySelector('.zazu-sidebar .zazu-nav-link.active');
            const workspace = document.querySelector('.zazu-sidebar .zazu-footer-workspace');
            const workspaceLabel = document.querySelector('.zazu-sidebar .zazu-footer-workspace-label');
            return {
                surface: styles.getPropertyValue('--zazu-surface').trim(),
                primary: styles.getPropertyValue('--zazu-primary').trim(),
                activeBackground: active ? getComputedStyle(active).backgroundColor : '',
                activeColor: active ? getComputedStyle(active).color : '',
                workspaceBackground: workspace ? getComputedStyle(workspace).backgroundColor : '',
                workspaceColor: workspace ? getComputedStyle(workspace).color : '',
                workspaceLabelColor: workspaceLabel ? getComputedStyle(workspaceLabel).color : '',
                theme: document.documentElement.dataset.theme,
            };
        });

        expect(darkTokens.surface.toLowerCase()).toBe('#122235');
        expect(darkTokens.primary.toLowerCase()).toBe('#6fa4ff');
        expect(darkTokens.activeBackground).toBe('rgb(72, 97, 116)');
        expect(darkTokens.activeColor).toBe('rgb(243, 246, 248)');
        expect(darkTokens.workspaceColor).toBe('rgb(229, 235, 240)');
        expect(darkTokens.workspaceLabelColor).toBe('rgb(184, 199, 210)');
        expect(darkTokens.theme).toBe('dark');

        await context.close();
    });

    test('keeps all primary form controls dark in dark mode', async ({ page }) => {
        await login(page);
        await page.goto('/settings');
        await page.locator('[data-theme-toggle]').first().click();

        const result = await page.evaluate(() => {
            const controls = Array.from(document.querySelectorAll('input:not([type="hidden"]):not([type="checkbox"]):not([type="radio"]):not([type="button"]):not([type="submit"]):not([type="reset"]):not([type="file"]):not([type="image"]):not([type="color"]):not([type="range"]), select, textarea'));
            return controls.map((control) => {
                const style = getComputedStyle(control);
                return {
                    tag: control.tagName,
                    type: control.getAttribute('type') || '',
                    background: style.backgroundColor,
                    color: style.color,
                };
            }).filter((item) => item.background !== 'rgba(0, 0, 0, 0)');
        });

        expect(result.length).toBeGreaterThan(0);
        for (const control of result) {
            expect(control.background).not.toMatch(/rgb\(255,\s*255,\s*255\)|rgba\(255,\s*255,\s*255,/);
            expect(control.background).not.toMatch(/rgb\(248,\s*250,\s*252\)|rgba\(248,\s*250,\s*252,/);
        }
    });

    test('keeps calendar reachable from the active Work navigation group', async ({ page }) => {
        await login(page);
        await page.goto('/calendar');
        await expect(page).toHaveURL(/\/calendar/);

        const workArea = page.locator('[data-zazu-nav-area].is-active').filter({ hasText: 'Work' }).first();
        const calendarLink = workArea.locator('a.zazu-nav-link').filter({ hasText: /^Calendar$/ });

        await expect(workArea).toHaveCount(1);
        await expect(workArea).toHaveClass(/is-active/);
        await expect(calendarLink).toHaveAttribute('aria-current', 'page');

        const mobileNavigationToggle = page.getByRole('button', { name: 'Open navigation' });
        if (await mobileNavigationToggle.isVisible()) {
            await mobileNavigationToggle.click();
            const workTrigger = workArea.getByRole('button', { name: 'Work', exact: true });
            if (await workTrigger.isVisible()) {
                await workTrigger.click();
            }
            await expect(workArea).toHaveClass(/is-open/);
        } else {
            await workArea.getByRole('button', { name: 'Work', exact: true }).click();
        }

        await expect(calendarLink).toBeVisible();
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
