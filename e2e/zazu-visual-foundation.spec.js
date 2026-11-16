import { test, expect } from '@playwright/test';

const DEMO_EMAIL = process.env.ZAZU_DEMO_EMAIL || 'demo@zazu.local';
const DEMO_PASSWORD = process.env.ZAZU_DEMO_PASSWORD || 'password';

test('dashboard loads the branded visual system instead of unstyled HTML', async ({ page }) => {
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
    await expect(page).toHaveURL(/\/dashboard$/);
    await expect(page.locator('body.zazu-dashboard-page')).toBeVisible();
    await expect(page.locator('.zazu-dash-hero')).toBeVisible();

    const visual = await page.evaluate(() => {
        const sidebar = document.querySelector('.zazu-sidebar');
        const hero = document.querySelector('.zazu-dash-hero');
        const heroSide = document.querySelector('.zazu-dash-hero-side');
        const metric = document.querySelector('.zazu-dash-metric');

        return {
            sidebarBackground: sidebar ? getComputedStyle(sidebar).backgroundImage : '',
            heroBackground: hero ? getComputedStyle(hero).backgroundImage : '',
            heroSideBackground: heroSide ? getComputedStyle(heroSide).backgroundImage : '',
            metricShadow: metric ? getComputedStyle(metric).boxShadow : 'none',
            bodyFont: getComputedStyle(document.body).fontFamily,
        };
    });

    expect(failedStylesheets).toEqual([]);
    expect(visual.sidebarBackground).toContain('linear-gradient');
    expect(visual.heroBackground).toContain('radial-gradient');
    expect(visual.heroSideBackground).toContain('linear-gradient');
    expect(visual.metricShadow).not.toBe('none');
    expect(visual.bodyFont).toContain('Manrope');
});
