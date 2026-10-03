import { test, expect } from '@playwright/test';

test('registration flows through onboarding into the dashboard', async ({ page }, testInfo) => {
    test.setTimeout(60_000);
    test.skip(testInfo.project.name !== 'chromium', 'The complete registration flow runs once to avoid shared-IP registration throttling; responsive entry surfaces are covered separately.');
    const unique = globalThis.crypto.randomUUID()
        .replace(/[^a-zA-Z0-9]/g, '')
        .slice(-10);
    const username = 'browserowner' + unique;
    const businessName = 'Browser Catering ' + unique;

    const landingResponse = await page.goto('/', { waitUntil: 'domcontentloaded' });
    expect(landingResponse?.status(), 'public landing').toBe(200);
    await expect(page.locator('html')).toHaveAttribute('lang', /.+/);
    await expect(page.locator('nav[aria-label="Public navigation"]')).toBeVisible();
    await expect(page.locator('.zazu-error-shell')).toHaveCount(0);
    await expect(page.getByRole('heading', { name: 'Run every event. Stay ahead of the business.' })).toBeVisible();
    await expect(page.locator('nav[aria-label="Public navigation"]').getByRole('button', { name: 'Register', exact: true })).toBeVisible();
    await expect(page.locator('nav[aria-label="Public navigation"]').getByRole('button', { name: 'Log in', exact: true })).toBeVisible();
    await expect(page.locator('[data-auth-modal]')).toHaveCount(1);
    await page.locator('nav[aria-label="Public navigation"]').getByRole('button', { name: 'Register', exact: true }).click();
    await expect(page.locator('[data-auth-modal]')).toBeVisible();
    await expect(page.getByRole('heading', { name: 'Set up your Zazu workspace.' })).toBeVisible();

    await page.getByLabel('Your name').fill('Browser Owner');
    await page.locator('#auth_username').fill(username);
    await page.getByLabel('Business name').fill(businessName);
    await page.getByLabel('Email address').fill(username + '@example.com');
    await page.locator('#auth_register_password').fill('password123');
    await page.locator('#auth_password_confirmation').fill('password123');
    await page.getByRole('button', { name: 'Create workspace' }).click();

    await expect(page).toHaveURL(/\/setup\/catalogue$/);
    await expect(page.locator('.zazu-error-shell')).toHaveCount(0);
    await expect(page.getByRole('heading', { name: 'Set up what you offer' })).toBeVisible();

    await page.getByLabel('What do you offer?').fill('Wedding catering');
    await page.getByRole('button', { name: 'Continue' }).click();

    await expect(page).toHaveURL(/\/setup\/experience$/);
    await expect(page.locator('.zazu-error-shell')).toHaveCount(0);
    await expect(page.getByRole('heading', { name: 'Workspace focus' })).toBeVisible();
    await expect(page.getByText('Sound & DJ', { exact: true })).toBeVisible();

    await page.getByLabel('Catering & baking', { exact: true }).check();
    await page.getByLabel('Intermediate').check();
    await page.getByRole('button', { name: 'Continue setup' }).click();

    await expect(page).toHaveURL(/\/setup\/business$/);
    await expect(page.locator('.zazu-error-shell')).toHaveCount(0);
    await expect(page.getByRole('heading', { name: 'Tell Zazu about your business' })).toBeVisible();

    await page.getByRole('button', { name: 'Finish setup' }).click();

    await expect(page).toHaveURL(/\/dashboard$/);
    await expect(page.locator('.zazu-error-shell')).toHaveCount(0);
    await expect(page.locator('main#main-content')).toBeVisible();
    await expect(page.locator('a.zazu-skip-link')).toHaveCount(1);
    if (testInfo.project.name !== 'chromium') {
        await expect(page.getByRole('button', { name: 'Open navigation' })).toBeVisible();
    }
    await expect(page.getByRole('heading', { name: 'Command Centre' })).toBeVisible();
    await expect(page.locator('.zazu-footer-workspace strong')).toHaveText(businessName);

    const signedInLandingResponse = await page.goto('/', { waitUntil: 'domcontentloaded' });
    expect(signedInLandingResponse?.status(), 'authenticated landing').toBe(200);
    await expect(page.locator('.zazu-error-shell')).toHaveCount(0);
    await expect(page.locator('nav[aria-label="Public navigation"]').getByText("You’re signed in", { exact: true })).toBeVisible();

    await page.locator('nav[aria-label="Public navigation"]').getByRole('button', { name: 'Sign out', exact: true }).click();
    await expect(page).toHaveURL(/\/$/);
    await expect(page.locator('.zazu-error-shell')).toHaveCount(0);

    await page.locator('nav[aria-label="Public navigation"]').getByRole('button', { name: 'Log in', exact: true }).click();
    await expect(page.locator('[data-auth-modal]')).toBeVisible();
    await expect(page.getByRole('heading', { name: 'Welcome back.' })).toBeVisible();

    const loginIdentifier = username;
    await page.getByLabel(/Username or email/i).fill(loginIdentifier);
    await page.locator('#auth_password').fill('password123');
    await page.locator('[data-auth-submit]').click();

    await expect(page).toHaveURL(/\/dashboard$/);
    await expect(page.locator('.zazu-error-shell')).toHaveCount(0);
    await expect(page.getByRole('heading', { name: 'Command Centre' })).toBeVisible();
});

test('public entry and login surfaces render without Zazu error pages', async ({ page }) => {
    const landingResponse = await page.goto('/', { waitUntil: 'domcontentloaded' });
    expect(landingResponse?.status(), 'public landing').toBe(200);
    await expect(page.locator('.zazu-error-shell')).toHaveCount(0);
    await expect(page.locator('nav[aria-label="Public navigation"]')).toBeVisible();

    const loginResponse = await page.goto('/login', { waitUntil: 'domcontentloaded' });
    expect(loginResponse?.status(), 'legacy login entry').toBe(200);
    await expect(page).toHaveURL(/\/?(?:\?auth=login)?$/);
    await expect(page.locator('.zazu-error-shell')).toHaveCount(0);
    await expect(page.locator('[data-auth-modal]')).toBeVisible();
    await expect(page.getByRole('heading', { name: 'Welcome back.' })).toBeVisible();
    await expect(page.getByLabel('Username or email')).toBeVisible();
});

test('landing authentication modal is keyboard-safe and switches between login and registration', async ({ page }) => {
    await page.goto('/', { waitUntil: 'domcontentloaded' });
    const loginTrigger = page.getByRole('button', { name: 'Log in', exact: true }).first();
    await loginTrigger.click();
    const modal = page.locator('[data-auth-modal]');
    await expect(modal).toBeVisible();
    await expect(page.getByRole('heading', { name: 'Welcome back.' })).toBeVisible();
    await expect(page.getByLabel('Username or email')).toBeFocused();

    await page.getByRole('button', { name: 'Create your workspace', exact: true }).click();
    await expect(page.getByRole('heading', { name: 'Set up your Zazu workspace.' })).toBeVisible();
    await expect(page.getByLabel('Your name')).toBeFocused();

    const loginSwitch = page.locator('[data-auth-modal-switch="login"]');
    await expect(loginSwitch).toBeVisible();
    await loginSwitch.click();
    await expect(page.getByRole('heading', { name: 'Welcome back.' })).toBeVisible();
    const ownerAccessSwitch = page.locator('[data-auth-access-switch="owner"]');
    await expect(ownerAccessSwitch).toHaveAttribute('aria-selected', 'false');
    await ownerAccessSwitch.click();
    await expect(page.getByRole('heading', { name: 'Welcome back, owner.' })).toBeVisible();
    await expect(page.getByRole('button', { name: 'Open owner area', exact: false })).toBeVisible();
    await expect(page.locator('input[name="owner_access"]')).toHaveValue('1');
    await page.locator('[data-auth-access-switch="login"]').click();
    await expect(page.getByRole('heading', { name: 'Welcome back.' })).toBeVisible();
    await expect(page.locator('input[name="owner_access"]')).toHaveValue('0');

    await page.keyboard.press('Escape');
    await expect(modal).toBeHidden();
    await expect(loginTrigger).toBeFocused();
});
