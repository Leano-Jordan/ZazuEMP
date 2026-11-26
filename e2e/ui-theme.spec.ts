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
        await expect(page.locator('html')).toHaveAttribute('data-theme', 'dark');
        await expect.poll(async () => page.locator('.zazu-sidebar .zazu-nav-link.active').evaluate((element) => getComputedStyle(element).backgroundColor)).toBe('rgb(72, 97, 116)');

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
        expect(darkTokens.primary.toLowerCase()).toBe('#78a9e8');
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
        await expect(page.locator('html')).toHaveAttribute('data-theme', 'dark');

        const controlSelector = 'input:not([type="hidden"]):not([type="checkbox"]):not([type="radio"]):not([type="button"]):not([type="submit"]):not([type="reset"]):not([type="file"]):not([type="image"]):not([type="color"]):not([type="range"]), select, textarea';
        await expect.poll(async () => page.locator(controlSelector).evaluateAll((controls) => {
            const visibleBackgrounds = controls
                .map((control) => getComputedStyle(control).backgroundColor)
                .filter((background) => background !== 'rgba(0, 0, 0, 0)');

            return visibleBackgrounds.length > 0 && visibleBackgrounds.every((background) =>
                !/rgb\(255,\s*255,\s*255\)|rgba\(255,\s*255,\s*255,/.test(background) &&
                !/rgb\(248,\s*250,\s*252\)|rgba\(248,\s*250,\s*252,/.test(background)
            );
        })).toBe(true);

        const result = await page.locator(controlSelector).evaluateAll((controls) => controls.map((control) => {
            const style = getComputedStyle(control);
            return {
                tag: control.tagName,
                type: control.getAttribute('type') || '',
                background: style.backgroundColor,
                color: style.color,
            };
        }).filter((item) => item.background !== 'rgba(0, 0, 0, 0)'));

        expect(result.length).toBeGreaterThan(0);
        for (const control of result) {
            expect(control.background, `${control.tag} type=${control.type}`).not.toMatch(/rgb\(255,\s*255,\s*255\)|rgba\(255,\s*255,\s*255,/);
            expect(control.background, `${control.tag} type=${control.type}`).not.toMatch(/rgb\(248,\s*250,\s*252\)|rgba\(248,\s*250,\s*252,/);
        }
    });

    test('opens and follows links from the active Work navigation group on desktop and mobile', async ({ page }) => {
        await login(page);
        await page.goto('/dashboard');

        const workArea = page.locator('[data-zazu-nav-area].is-active').filter({ hasText: 'Work' }).first();
        const workTrigger = workArea.getByRole('button', { name: 'Work', exact: true });
        const calendarLink = workArea.locator('a.zazu-nav-link').filter({ hasText: /^Calendar$/ });

        await expect(workArea).toHaveCount(1);
        await expect(workArea).toHaveClass(/is-active/);

        const mobileNavigationToggle = page.getByRole('button', { name: 'Open navigation' });
        if (await mobileNavigationToggle.isVisible()) {
            await mobileNavigationToggle.click();
        }
        // The active desktop group is expanded on first render; clicking its
        // trigger would correctly collapse it and invalidate this assertion.
        await expect(workArea).toHaveClass(/is-open/);
        await expect(workTrigger).toHaveAttribute('aria-expanded', 'true');

        await expect(calendarLink).toBeVisible();
        await expect(calendarLink).toHaveAttribute('href', /\/calendar$/);
        await calendarLink.click();
        await expect(page).toHaveURL(/\/calendar$/);
        await expect(calendarLink).toHaveAttribute('aria-current', 'page');
    });

    test('collapsed desktop rail opens anchored contextual navigation without navigating on parent click', async ({ page }) => {
        await page.setViewportSize({ width: 1440, height: 900 });
        await login(page);

        const sidebar = page.locator('[data-mobile-sidebar]');
        const collapse = page.getByRole('button', { name: 'Collapse sidebar' });
        const work = sidebar.locator('[data-zazu-nav-area]').filter({ hasText: 'Work' }).first();
        const trigger = work.getByRole('button', { name: 'Work', exact: true });
        const panel = work.locator('[data-zazu-nav-panel]');
        const heading = panel.locator('.zazu-nav-flyout-heading');
        const calendar = panel.locator('a').filter({ hasText: /^Calendar$/ });

        await expect(collapse).toBeVisible();
        await collapse.click();
        await expect(page.locator('body')).toHaveClass(/zazu-sidebar-collapsed/);
        await expect(sidebar).toHaveCSS('width', '76px');
        await expect(heading).toBeVisible();
        await expect(work).toHaveCSS('position', 'relative');
        await expect(panel).toHaveCSS('position', 'absolute');
        await expect.poll(() => panel.evaluate((node) => getComputedStyle(node).overflowY)).toBe('visible');
        await expect.poll(() => panel.evaluate((node) => getComputedStyle(node).maxHeight)).toBe('none');
        const panelAnchorGap = await panel.evaluate((node) => {
            const panelRect = node.getBoundingClientRect();
            const parentRect = node.parentElement.getBoundingClientRect();
            return panelRect.left - parentRect.right;
        });
        expect(panelAnchorGap).toBeGreaterThanOrEqual(0);

        // The active Work group starts open. Close and reopen it to prove that
        // the rail trigger controls its contextual panel without changing route.
        await trigger.click();
        await expect(work).not.toHaveClass(/is-open/);
        await expect(trigger).toHaveAttribute('aria-expanded', 'false');
        await expect(panel).toBeHidden();
        await trigger.click();
        await expect(work).toHaveClass(/is-open/);
        await expect(panel).toBeVisible();
        await expect(trigger).toHaveAttribute('aria-expanded', 'true');
        await expect(calendar).toBeVisible();
        await expect(page).toHaveURL(/\/dashboard$/);

        await calendar.focus();
        await page.keyboard.press('Escape');
        await expect(panel).toBeHidden();
        await expect(trigger).toHaveAttribute('aria-expanded', 'false');
        await expect(trigger).toBeFocused();

        await trigger.click();
        await expect(panel).toBeVisible();
        await calendar.click();
        await expect(page).toHaveURL(/\/calendar$/);
        await expect(calendar).toHaveAttribute('aria-current', 'page');
    });

    test('mobile drawer parent controls expand in place without accidental navigation', async ({ page }) => {
        await page.setViewportSize({ width: 390, height: 844 });
        await login(page);

        const drawerToggle = page.getByRole('button', { name: 'Open navigation' });
        await expect(drawerToggle).toBeVisible();
        await drawerToggle.click();

        const sidebar = page.locator('[data-mobile-sidebar]');
        const sales = sidebar.locator('[data-zazu-nav-area]').filter({ hasText: 'Sales' }).first();
        const trigger = sales.getByRole('button', { name: 'Sales', exact: true });
        const quotes = sales.locator('[data-zazu-nav-panel] a').filter({ hasText: /^Quotes$/ });

        await trigger.click();
        await expect(sales).toHaveClass(/is-open/);
        await expect(trigger).toHaveAttribute('aria-expanded', 'true');
        await expect(quotes).toBeVisible();
        await expect(page).toHaveURL(/\/dashboard$/);

        await quotes.click();
        await expect(page).toHaveURL(/\/quotes$/);
        await expect(quotes).toHaveAttribute('aria-current', 'page');
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
        const inspector = page.locator('[data-zazu-inspector-panel]').first();
        await expect(inspector).toBeVisible();

        const inspectorGeometry = await inspector.evaluate((element) => {
            const rect = element.getBoundingClientRect();
            return {
                isDocumentLayer: element.parentElement === document.body,
                top: rect.top,
                bottom: rect.bottom,
                viewportHeight: window.innerHeight,
                position: getComputedStyle(element).position,
            };
        });
        expect(inspectorGeometry.isDocumentLayer).toBe(true);
        expect(inspectorGeometry.position).toBe('fixed');
        expect(inspectorGeometry.top).toBeGreaterThanOrEqual(0);
        expect(inspectorGeometry.bottom).toBeLessThanOrEqual(inspectorGeometry.viewportHeight + 1);

        const scopeTab = inspector.getByRole('tab', { name: 'Scope & preparation' });
        const resourcesTab = inspector.getByRole('tab', { name: 'Resources & setup' });
        const commercialTab = inspector.getByRole('tab', { name: 'Commercial position' });
        await expect(scopeTab).toHaveAttribute('aria-selected', 'true');
        await resourcesTab.click();
        await expect(resourcesTab).toHaveAttribute('aria-selected', 'true');
        await expect(inspector.locator('[data-zazu-tab-panel="equipment"]')).toBeVisible();
        await commercialTab.click();
        await expect(commercialTab).toHaveAttribute('aria-selected', 'true');
        await expect(inspector.locator('[data-zazu-tab-panel="finance"]')).toBeVisible();
        await scopeTab.focus();
        await page.keyboard.press('ArrowRight');
        await expect(resourcesTab).toBeFocused();

        await expect(responses).toEqual([]);
    });

    test('keeps sidebar navigation expanded without hover flyout overlap and preserves the full mobile month grid', async ({ page, browser }) => {
        const desktop = page;
        await desktop.setViewportSize({ width: 1365, height: 900 });
        await login(desktop);
        const workArea = desktop.locator('[data-zazu-nav-area]').filter({ has: desktop.getByRole('button', { name: 'Work', exact: true }) });
        const desktopNav = desktop.locator('.zazu-sidebar .zazu-nav');
        const workPanel = workArea.locator('.zazu-nav-flyout');
        await expect(workArea).toHaveClass(/is-open/);
        await expect(workPanel).toBeVisible();
        await expect.poll(() => desktopNav.evaluate((node) => getComputedStyle(node).overflowY)).toBe('visible');
        await expect.poll(() => workPanel.evaluate((node) => getComputedStyle(node).overflowY)).toBe('visible');
        const navIcon = workArea.locator('.zazu-nav-icon');
        await expect.poll(() => navIcon.evaluate((node) => Math.round(node.getBoundingClientRect().width))).toBeGreaterThanOrEqual(22);
        await desktop.getByRole('button', { name: 'Sales' }).hover();
        await expect(workArea.locator('.zazu-nav-flyout')).toBeVisible();
        await expect(desktop.locator('[data-zazu-nav-area].is-open')).toHaveCount(1);
        const mobileContext = await browser.newContext({
            baseURL: process.env.ZAZU_BASE_URL || 'http://127.0.0.1:8000',
            viewport: { width: 390, height: 844 },
            isMobile: true,
            hasTouch: true,
        });
        const mobile = await mobileContext.newPage();
        await login(mobile);
        const mobileNav = mobile.locator('.zazu-sidebar .zazu-nav');
        const mobileSidebar = mobile.locator('.zazu-sidebar');
        await expect.poll(() => mobileNav.evaluate((node) => getComputedStyle(node).overflowY)).toBe('visible');
        await expect.poll(() => mobileSidebar.evaluate((node) => getComputedStyle(node).overflowY)).toBe('auto');
        await mobile.goto('/calendar');
        const monthGrid = mobile.locator('.zazu-calendar-grid-scroll > .zazu-calendar-grid');
        await expect(monthGrid).toBeVisible();
        await expect(monthGrid.locator('.zazu-calendar-label')).toHaveCount(7);
        expect(await monthGrid.locator('.zazu-calendar-cell').count()).toBeGreaterThanOrEqual(28);
        const dimensions = await mobile.locator('.zazu-calendar-grid-scroll').evaluate((element) => ({
            clientWidth: element.clientWidth,
            scrollWidth: element.scrollWidth,
            gridWidth: element.querySelector('.zazu-calendar-grid').getBoundingClientRect().width,
        }));
        expect(dimensions.scrollWidth).toBeGreaterThan(dimensions.clientWidth);
        expect(dimensions.gridWidth).toBeGreaterThanOrEqual(700);
        await expectNoViewportOverflow(mobile);
        await mobileContext.close();
    });
});
