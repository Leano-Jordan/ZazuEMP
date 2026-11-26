import { test, expect } from '@playwright/test';

const DEMO_EMAIL = process.env.ZAZU_DEMO_EMAIL || 'demo@zazu.local';
const DEMO_PASSWORD = process.env.ZAZU_DEMO_PASSWORD || 'password';
const MANAGER_EMAIL = process.env.ZAZU_MANAGER_EMAIL || 'operations@zazu.local';
const ROLE_PASSWORD = process.env.ZAZU_ROLE_PASSWORD || 'password';

async function login(page, email = DEMO_EMAIL, password = DEMO_PASSWORD) {
    await page.goto('/?auth=login', { waitUntil: 'domcontentloaded' });
    await expect(page.locator('[data-auth-modal]')).toBeVisible();
    await page.getByLabel('Username or email').fill(email);
    await page.locator('#auth_password').fill(password);
    await page.locator('[data-auth-submit]').click();
    await page.waitForURL(url => url.pathname !== '/' && url.pathname !== '/login');
    await page.goto('/dashboard', { waitUntil: 'domcontentloaded' });
    await expect(page).toHaveURL(/\/dashboard$/);
    await expect(page.getByRole('heading', { name: 'Dashboard' })).toBeVisible();
}

async function waitForOfflineRuntime(page) {
    await expect.poll(
        () => page.evaluate(() => Boolean(window.ZazuOffline?.ready)),
        { message: 'offline runtime must be exposed before the session-preservation journey continues' },
    ).toBe(true);
}

async function waitForOfflinePagesPrimed(page) {
    await expect.poll(
        () => page.locator('html').getAttribute('data-zazu-offline-pages-primed'),
        { message: 'the authenticated page cache must be primed before changing business context' },
    ).toBe('true');
}

async function openPublicNavigation(page) {
    const toggle = page.getByRole('button', { name: 'Open navigation' });
    if (await toggle.isVisible()) await toggle.click();
}

test('offline logout retries trusted-device revocation after the next owner login without storing its token', async ({ page }, testInfo) => {
    test.setTimeout(45_000);
    test.skip(testInfo.project.name !== 'chromium', 'Offline logout revocation recovery runs once.');

    await login(page);
    await waitForOfflineRuntime(page);
    const businessId = Number(await page.locator('body').getAttribute('data-zazu-business-id'));
    expect(businessId).toBeGreaterThan(0);

    const installationId = '6f96fa7a-7d39-4bc1-a0df-8b9b98d73f61';
    await page.evaluate(async ({ id, business }) => {
        await window.ZazuOffline.ready;
        const state = window.ZazuOffline.getState();
        state.installation_id = id;
        state.sync = {
            token: 'logout-revocation-secret-token',
            device: { id: 987649, business_id: business },
        };
        state.local_dirty = false;
        await window.ZazuOffline.persist({ allowSessionChange: true });
    }, { id: installationId, business: businessId });

    await page.route('**/api/sync/revoke', route => route.abort());
    const logout = await page.evaluate(() => window.ZazuOffline.clearLocalSession());
    expect(logout).toEqual({
        ok: false,
        reason: 'unreachable',
        queued_for_retry: true,
    });

    const storedMarker = await page.evaluate(business => {
        const raw = localStorage.getItem('zazu-pending-device-revocation:' + business);
        return raw ? JSON.parse(raw) : null;
    }, businessId);
    expect(storedMarker.installation_id).toBe(installationId);
    expect(storedMarker.business_id).toBe(businessId);
    expect(storedMarker).not.toHaveProperty('token');
    expect(JSON.stringify(storedMarker)).not.toContain('logout-revocation-secret-token');

    const exposedCredentialSurface = await page.evaluate(() => ({
        localStorage: Array.from({ length: localStorage.length }, (_, index) => {
            const key = localStorage.key(index);
            return [key, localStorage.getItem(key)];
        }),
        sessionStorage: Array.from({ length: sessionStorage.length }, (_, index) => {
            const key = sessionStorage.key(index);
            return [key, sessionStorage.getItem(key)];
        }),
        url: window.location.href,
    }));
    expect(JSON.stringify(exposedCredentialSurface)).not.toContain('logout-revocation-secret-token');

    let retryAttempts = 0;
    await page.route('**/settings/sync/devices/revoke-installation', async route => {
        retryAttempts++;
        expect(route.request().method()).toBe('POST');
        expect(route.request().postDataJSON()).toEqual({ installation_id: installationId });
        await route.fulfill({
            status: 200,
            contentType: 'application/json',
            body: JSON.stringify({ revoked: true, device_id: 987649 }),
        });
    });

    // The server-side web session ends independently of the local offline clear.
    await page.context().clearCookies();
    await page.goto('/');
    await login(page);
    await expect.poll(() => retryAttempts).toBe(1);

    const recovered = await page.evaluate(business => ({
        marker: localStorage.getItem('zazu-pending-device-revocation:' + business),
        installationId: window.ZazuOffline.getState().installation_id,
        token: window.ZazuOffline.getState().sync?.token || null,
        consent: window.ZazuOffline.getState().sync_registration_consent,
    }), businessId);
    expect(recovered).toEqual({
        marker: null,
        installationId,
        token: null,
        consent: false,
    });
});

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

    // Simulate a genuinely disconnected installed client. A page-level route
    // abort can miss the POST when the service worker owns the fetch event.
    await page.context().setOffline(true);
    // Exercise the actual logout form directly; the menu's responsive visibility
    // is covered elsewhere and must not make this session-safety assertion flaky.
    await page.evaluate(() => {
        const form = document.querySelector('form[action$="/logout"]');
        if (!form) throw new Error('Logout form was not found');
        form.requestSubmit();
    });

    await expect.poll(() => dialogs.length).toBe(2);
    expect(dialogs[0].type).toBe('confirm');
    expect(dialogs[0].message).toContain('Signing out ends this web session');
    expect(dialogs[1].type).toBe('alert');
    expect(dialogs[1].message).toContain('could not reach the server to end your session');
    await expect(page).toHaveURL(/\/$/);
    await expect(page.locator('nav[aria-label="Public navigation"]').getByText("You’re signed in", { exact: true })).toBeVisible();

    await page.context().setOffline(false);
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
    await expect.poll(() => dialogs.length).toBe(3);
    expect(dialogs[2].type).toBe('confirm');
    expect(dialogs[2].message).toContain('business-scoped offline workspace and any queued changes or attachments will remain on this device');
    expect(dialogs[2].message).toContain('offline changes are attributed to the approving owner');
    await expect(page).toHaveURL(/\/$/);
    await expect(page.locator('nav[aria-label="Public navigation"]').getByRole('button', { name: 'Log in', exact: true })).toBeVisible();

    await login(page, MANAGER_EMAIL, ROLE_PASSWORD);
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

test('workspace switching submits the selected business and preserves each business offline queue', async ({ page }, testInfo) => {
    test.setTimeout(45_000);
    test.skip(testInfo.project.name !== 'chromium', 'Workspace identity transition runs once on desktop Chromium.');

    await login(page);
    await waitForOfflineRuntime(page);
    await waitForOfflinePagesPrimed(page);

    const firstBusinessId = Number(await page.locator('body').getAttribute('data-zazu-business-id'));
    expect(firstBusinessId).toBeGreaterThan(0);

    const secondBusinessId = Number(await page.locator('[data-business-switch] option').filter({
        hasText: 'Zazu Demo Secondary Workspace',
    }).getAttribute('value'));
    expect(secondBusinessId).toBeGreaterThan(0);
    expect(secondBusinessId).not.toBe(firstBusinessId);

    const userId = Number(await page.locator('body').getAttribute('data-zazu-user-id'));
    const firstBusinessCache = await page.evaluate(({ user, business }) => caches.keys().then(keys =>
        keys.filter(name => name.includes('-user-' + user + '-business-' + business))
    ), { user: userId, business: firstBusinessId });
    expect(firstBusinessCache.length).toBeGreaterThan(0);

    await page.context().setOffline(true);

    const firstMutationId = await page.evaluate(async businessId => {
        await window.ZazuOffline.ready;
        const state = window.ZazuOffline.getState();
        state.sync = {
            token: 'first-business-switch-token',
            device: { id: 987680, business_id: businessId },
        };
        state.local_dirty = false;
        await window.ZazuOffline.persist({ allowSessionChange: true });

        const mutation = await window.ZazuOffline.queueMutation('customer', 'create', {
            local_id: 'switch-boundary-' + crypto.randomUUID(),
            name: 'First business queued customer',
        });
        return mutation.id;
    }, firstBusinessId);

    // Keep the mutation pending after reconnect so the only operation under
    // test is the real business-context transition, not a sync retry.
    await page.route('**/api/sync/**', route => route.abort());
    await page.context().setOffline(false);

    const switchWorkspace = async (businessId) => {
        await page.locator('[data-user-trigger]').click();
        await expect(page.locator('[data-business-switch]')).toBeVisible();
        await page.locator('[data-business-switch]').selectOption(String(businessId));
        await expect.poll(
            () => page.locator('body').getAttribute('data-zazu-business-id'),
            { message: 'the workspace selector must submit the selected business context' },
        ).toBe(String(businessId));
        await waitForOfflineRuntime(page);
        await waitForOfflinePagesPrimed(page);
    };

    await switchWorkspace(secondBusinessId);

    const secondWorkspace = await page.evaluate(async () => ({
        businessId: Number(document.body.dataset.zazuBusinessId),
        pending: await window.ZazuOffline.pending(),
        paired: window.ZazuOffline.isPaired(),
    }));
    expect(secondWorkspace).toEqual({
        businessId: secondBusinessId,
        pending: [],
        paired: false,
    });

    const secondBusinessCache = await page.evaluate(({ user, business }) => caches.keys().then(keys =>
        keys.filter(name => name.includes('-user-' + user + '-business-' + business))
    ), { user: userId, business: secondBusinessId });
    expect(secondBusinessCache.length).toBeGreaterThan(0);

    const oldBusinessCache = await page.evaluate(({ user, business }) => caches.keys().then(keys =>
        keys.filter(name => name.includes('-user-' + user + '-business-' + business))
    ), { user: userId, business: firstBusinessId });
    expect(oldBusinessCache).toHaveLength(0);

    await switchWorkspace(firstBusinessId);

    const restoredFirstWorkspace = await page.evaluate(async () => ({
        businessId: Number(document.body.dataset.zazuBusinessId),
        pending: (await window.ZazuOffline.pending()).map(item => item.id),
        paired: window.ZazuOffline.isPaired(),
    }), firstMutationId);
    expect(restoredFirstWorkspace.businessId).toBe(firstBusinessId);
    expect(restoredFirstWorkspace.pending).toContain(firstMutationId);
    expect(restoredFirstWorkspace.paired).toBe(true);

    const restoredFirstCache = await page.evaluate(({ user, business }) => caches.keys().then(keys =>
        keys.filter(name => name.includes('-user-' + user + '-business-' + business))
    ), { user: userId, business: firstBusinessId });
    expect(restoredFirstCache.length).toBeGreaterThan(0);
});

test('sibling browser tabs follow the active authenticated identity', async ({ page }, testInfo) => {
    test.setTimeout(60_000);
    test.skip(testInfo.project.name !== 'chromium', 'Cross-tab session transition runs once.');

    await login(page);
    const firstUser = Number(await page.locator('body').getAttribute('data-zazu-user-id'));
    expect(firstUser).toBeGreaterThan(0);

    const sibling = await page.context().newPage();
    await sibling.goto('/dashboard', { waitUntil: 'load' });
    await expect(sibling.getByRole('heading', { name: 'Dashboard' })).toBeVisible();
    await expect(sibling.locator('body')).toHaveAttribute('data-zazu-user-id', String(firstUser));

    await page.goto('/');
    await page.evaluate(() => {
        const form = document.querySelector('form[action$="/logout"]');
        if (!form) throw new Error('Logout form was not found');
        form.requestSubmit();
    });
    await expect(page.locator('body')).not.toHaveAttribute('data-zazu-user-id', String(firstUser));

    await login(page, MANAGER_EMAIL, ROLE_PASSWORD);
    const secondUser = Number(await page.locator('body').getAttribute('data-zazu-user-id'));
    expect(secondUser).toBeGreaterThan(0);
    expect(secondUser).not.toBe(firstUser);

    await expect.poll(() => sibling.locator('body').getAttribute('data-zazu-user-id')).toBe(String(secondUser));
    await expect(sibling.locator('body')).toContainText('You’re signed in');
    await sibling.close();
});

test('expired web session requires sign-in without erasing the trusted offline workspace', async ({ page }, testInfo) => {
    test.setTimeout(45_000);
    test.skip(testInfo.project.name !== 'chromium', 'Expired-session preservation runs once; responsive navigation is covered separately.');

    await login(page);
    await waitForOfflineRuntime(page);
    const businessId = Number(await page.locator('body').getAttribute('data-zazu-business-id'));
    expect(businessId).toBeGreaterThan(0);

    // A real expired session must not make the already trusted business
    // workspace or its pending mutations disappear from device storage.
    await page.route('**/api/sync/**', route => route.abort());

    const mutationId = await page.evaluate(async () => {
        await window.ZazuOffline.ready;
        const state = window.ZazuOffline.getState();
        state.sync = {
            token: 'expired-web-session-device-token',
            device: {
                id: 987663,
                business_id: Number(document.body.dataset.zazuBusinessId),
            },
        };
        await window.ZazuOffline.persist({ allowSessionChange: true });
        const mutation = await window.ZazuOffline.queueMutation('customer', 'create', {
            local_id: 'local:expired-web-session-customer',
            name: 'Customer saved before web session expiry',
        });
        return mutation.id;
    });

    // Remove the authenticated web-session cookie without touching IndexedDB.
    await page.context().clearCookies();
    await page.goto('/dashboard', { waitUntil: 'domcontentloaded' });
    await expect(page.locator('[data-auth-modal]')).toBeVisible();

    await login(page);
    await waitForOfflineRuntime(page);

    const recovered = await page.evaluate(async id => {
        await window.ZazuOffline.ready;
        const queue = await window.ZazuOffline.pending();
        return {
            businessId: Number(document.body.dataset.zazuBusinessId),
            paired: window.ZazuOffline.isPaired(),
            mutationPresent: queue.some(item => item.id === id),
        };
    }, mutationId);

    expect(recovered).toEqual({
        businessId,
        paired: true,
        mutationPresent: true,
    });
});
