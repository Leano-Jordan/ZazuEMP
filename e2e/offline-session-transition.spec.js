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
    await expect(page.getByRole('heading', { name: 'Dashboard' })).toBeVisible();
}

async function waitForOfflineRuntime(page) {
    await expect.poll(
        () => page.evaluate(() => Boolean(window.ZazuOffline?.ready)),
        { message: 'offline runtime must be exposed before the session-preservation journey continues' },
    ).toBe(true);
}

async function openPublicNavigation(page) {
    const toggle = page.getByRole('button', { name: 'Open navigation' });
    if (await toggle.isVisible()) await toggle.click();
}

test('unreachable logout host preserves the signed-in session and queued business work', async ({ page }, testInfo) => {
    test.setTimeout(45_000);
    test.skip(testInfo.project.name !== 'chromium', 'Session transition acceptance runs once; responsive navigation is covered separately.');

    await login(page);
    await waitForOfflineRuntime(page);

    const mutationId = await page.evaluate(async () => {
        await window.ZazuOffline.ready;
        const state = window.ZazuOffline.getState();
        state.sync = {
            token: 'offline-session-transition-device-token',
            device: {
                id: 987650,
                business_id: Number(document.body.dataset.zazuBusinessId),
            },
        };
        state.local_dirty = false;
        await window.ZazuOffline.persist({ allowSessionChange: true });

        const mutation = await window.ZazuOffline.queueMutation('customer', 'create', {
            local_id: 'offline-session-' + crypto.randomUUID(),
            name: 'Offline session preservation proof',
        });
        return mutation.id;
    });

    await page.goto('/');
    await expect(page.locator('nav[aria-label="Public navigation"]').getByText("You’re signed in", { exact: true })).toBeVisible();

    const dialogs = [];
    page.on('dialog', async dialog => {
        dialogs.push({ type: dialog.type(), message: dialog.message() });
        if (dialog.type() === 'alert') await dialog.dismiss();
        else await dialog.accept();
    });

    await page.route('**/up', route => route.abort());
    await openPublicNavigation(page);
    await page.locator('nav[aria-label="Public navigation"]').getByRole('button', { name: 'Sign out', exact: true }).click();

    await expect.poll(() => dialogs.length).toBe(1);
    expect(dialogs[0].type).toBe('alert');
    expect(dialogs[0].message).toContain('Your business offline workspace, pending changes and device token were left untouched');
    await expect(page).toHaveURL(/\/$/);
    await expect(page.locator('nav[aria-label="Public navigation"]').getByText("You’re signed in", { exact: true })).toBeVisible();

    await page.unroute('**/up');
    await page.goto('/dashboard', { waitUntil: 'domcontentloaded' });
    await expect(page.getByRole('heading', { name: 'Dashboard' })).toBeVisible();
    await waitForOfflineRuntime(page);

    const preservedBeforeLogout = await page.evaluate(async id => {
        await window.ZazuOffline.ready;
        const queue = await window.ZazuOffline.pending();
        return {
            paired: window.ZazuOffline.isPaired(),
            mutationPresent: queue.some(item => item.id === id),
        };
    }, mutationId);
    expect(preservedBeforeLogout).toEqual({ paired: true, mutationPresent: true });

    await page.goto('/');
    await openPublicNavigation(page);
    await page.locator('nav[aria-label="Public navigation"]').getByRole('button', { name: 'Sign out', exact: true }).click();
    await expect.poll(() => dialogs.length).toBe(2);
    expect(dialogs[1].type).toBe('confirm');
    expect(dialogs[1].message).toContain('business-scoped offline workspace and any queued changes or attachments will remain on this device');
    await expect(page).toHaveURL(/\/$/);
    await expect(page.locator('nav[aria-label="Public navigation"]').getByRole('button', { name: 'Log in', exact: true })).toBeVisible();

    await login(page);
    await waitForOfflineRuntime(page);
    const preservedAfterRelogin = await page.evaluate(async id => {
        await window.ZazuOffline.ready;
        const queue = await window.ZazuOffline.pending();
        return {
            paired: window.ZazuOffline.isPaired(),
            mutationPresent: queue.some(item => item.id === id),
        };
    }, mutationId);
    expect(preservedAfterRelogin).toEqual({ paired: true, mutationPresent: true });
});
