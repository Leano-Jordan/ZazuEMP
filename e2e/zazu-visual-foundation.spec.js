import { test, expect } from '@playwright/test';

const DEMO_EMAIL = process.env.ZAZU_DEMO_EMAIL || 'demo@zazu.local';
const DEMO_PASSWORD = process.env.ZAZU_DEMO_PASSWORD || 'password';

test('dashboard uses shared Zazu colours while retaining its branded visual depth', async ({ page }) => {
    const failedStylesheets = [];

    page.on('requestfailed', request => {
        if (request.resourceType() === 'stylesheet') failedStylesheets.push(request.url());
    });
    page.on('response', response => {
        if (response.request().resourceType() === 'stylesheet' && response.status() >= 400) {
            failedStylesheets.push(response.url() + ' (' + response.status() + ')');
        }
    });

    await page.goto('/?auth=login', { waitUntil: 'domcontentloaded' });
    await expect(page.locator('[data-auth-modal]')).toBeVisible();
    await page.getByLabel('Username or email').fill(DEMO_EMAIL);
    await page.locator('#auth_password').fill(DEMO_PASSWORD);
    await page.locator('[data-auth-submit]').click();
    await expect(page).toHaveURL(/\\/dashboard$/);
    await expect(page.locator('body.zazu-dashboard-page')).toBeVisible();
    await expect(page.locator('.zazu-dash-hero')).toBeVisible();

    const captureTheme = theme => page.evaluate(nextTheme => {
        document.documentElement.dataset.theme = nextTheme;

        const body = getComputedStyle(document.body);
        const sidebar = document.querySelector('.zazu-sidebar');
        const hero = document.querySelector('.zazu-dash-hero');
        const heroSide = document.querySelector('.zazu-dash-hero-side');
        const metric = document.querySelector('.zazu-dash-metric');
        const probe = document.createElement('span');

        probe.style.backgroundColor = 'var(--zazu-sidebar)';
        document.body.append(probe);
        const sharedSidebarBackground = getComputedStyle(probe).backgroundColor;
        probe.style.backgroundColor = 'var(--zazu-surface)';
        const sharedSurfaceBackground = getComputedStyle(probe).backgroundColor;
        probe.remove();

        return {
            dashboardCanvasToken: body.getPropertyValue('--dash-canvas').trim(),
            sharedCanvasToken: body.getPropertyValue('--zazu-page').trim(),
            sidebarBackground: sidebar ? getComputedStyle(sidebar).backgroundColor : '',
            sharedSidebarBackground,
            metricBackground: metric ? getComputedStyle(metric).backgroundColor : '',
            sharedSurfaceBackground,
            heroBackground: hero ? getComputedStyle(hero).backgroundImage : '',
            heroSideBackground: heroSide ? getComputedStyle(heroSide).backgroundImage : '',
            metricShadow: metric ? getComputedStyle(metric).boxShadow : 'none',
            bodyFont: body.fontFamily,
        };
    }, theme);

    const light = await captureTheme('light');
    const dark = await captureTheme('dark');

    expect(failedStylesheets).toEqual([]);
    for (const visual of [light, dark]) {
        expect(visual.dashboardCanvasToken).toBe(visual.sharedCanvasToken);
        expect(visual.sidebarBackground).toBe(visual.sharedSidebarBackground);
        expect(visual.metricBackground).toBe(visual.sharedSurfaceBackground);
        expect(visual.heroBackground).toContain('radial-gradient');
        expect(visual.heroSideBackground).toContain('linear-gradient');
        expect(visual.metricShadow).not.toBe('none');
        expect(visual.bodyFont).toContain('Manrope');
    }
});
