import { test, expect } from '@playwright/test';

const DEMO_EMAIL = process.env.ZAZU_DEMO_EMAIL || 'demo@zazu.local';
const DEMO_PASSWORD = process.env.ZAZU_DEMO_PASSWORD || 'password';
const MANAGER_EMAIL = process.env.ZAZU_MANAGER_EMAIL || 'operations@zazu.local';
const STAFF_EMAIL = process.env.ZAZU_STAFF_EMAIL || 'finance@zazu.local';
const ROLE_PASSWORD = process.env.ZAZU_ROLE_PASSWORD || 'password';

async function login(page, email, password) {
    await page.goto('/?auth=login', { waitUntil: 'domcontentloaded' });
    await expect(page.locator('[data-auth-modal]')).toBeVisible();
    await page.getByLabel('Username or email').fill(email);
    await page.locator('#auth_password').fill(password);
    await page.locator('[data-auth-submit]').click();
    await expect(page).toHaveURL(/\/dashboard$/);
}

async function expectNoServerFailures(page, responses, errors) {
    const errorShells = page.locator('.zazu-error-shell');
    const errorTexts = await errorShells.allTextContents();
    if (errorTexts.length > 0) {
        console.error('Zazu error shells detected:', errorTexts);
    }

    expect(responses, 'HTTP 5xx responses').toEqual([]);
    expect(errors, 'browser page errors').toEqual([]);
    await expect(errorShells).toHaveCount(0);
}

test.describe('Zazu populated runtime challenge', () => {
    test('owner can traverse the seeded commercial and operational chain', async ({ page }) => {
        test.setTimeout(90_000);

        const responses = [];
        const errors = [];
        page.on('response', response => {
            if (response.status() >= 500) {
                responses.push({ status: response.status(), url: response.url() });
            }
        });
        page.on('pageerror', error => errors.push(error.message));

        await login(page, DEMO_EMAIL, DEMO_PASSWORD);

        const dashboard = await page.goto('/dashboard', { waitUntil: 'domcontentloaded' });
        expect(dashboard?.status()).toBe(200);
        await expect(page.getByRole('heading', { name: 'Command Centre' })).toBeVisible();
        await expect(page.getByText('Zazu Demo Catering', { exact: true }).first()).toBeVisible();

        const work = await page.goto('/work', { waitUntil: 'domcontentloaded' });
        expect(work?.status()).toBe(200);
        const row = page.locator('[data-zazu-job-row]').filter({ hasText: 'Mokoena Family Celebration' }).first();
        await expect(row).toBeVisible();
        await expect(row).toContainText('ZAZU-DEMO-001');
        await expect(row).toContainText('80 guests');

        const inspector = page.locator('[data-zazu-inspector-panel]').first();
        await expect(inspector).toHaveAttribute('aria-hidden', 'true');
        await expect(inspector).toHaveCSS('visibility', 'hidden');
        await row.locator('[data-zazu-inspector-open]').first().click();
        await expect(inspector).toBeVisible();
        await expect(inspector).toHaveAttribute('aria-hidden', 'false');
        await expect(inspector).toContainText('Mokoena Family Celebration');
        await expect(inspector).toContainText('Buffet catering');
        await expect(inspector).toContainText('Confirm buffet quantities');

        await inspector.getByRole('tab', { name: 'Equipment Hire Manifest' }).click();
        const equipmentPanel = inspector.locator('[data-zazu-tab-panel="equipment"]');
        await expect(equipmentPanel).toBeVisible();
        await expect(equipmentPanel).toContainText('6m x 12m stretch tent');

        await inspector.getByRole('tab', { name: 'Financial Ledger' }).click();
        const financePanel = inspector.locator('[data-zazu-tab-panel="finance"]');
        await expect(financePanel).toBeVisible();
        await expect(financePanel).toContainText('11,500.00');
        await expect(inspector.getByRole('link', { name: 'Open job record' })).toBeVisible();

        await inspector.getByRole('link', { name: 'Open job record' }).click();
        await expect(page).toHaveURL(/\/work\/\d+$/);
        const workUrl = new URL(page.url());
        const workPath = workUrl.pathname;
        await expect(page.getByRole('heading', { name: 'Mokoena Family Celebration' })).toBeVisible();

        await page.goto(workPath + '/requirements', { waitUntil: 'domcontentloaded' });
        expect(page.url()).toMatch(/\/requirements$/);
        await expect(page.getByText('Buffet catering', { exact: true })).toBeVisible();
        await expect(page.getByText('80.00 guests', { exact: true })).toBeVisible();

        await page.goto(workPath + '/preparation', { waitUntil: 'domcontentloaded' });
        expect(page.url()).toMatch(/\/preparation$/);
        await expect(page.getByText('Confirm buffet quantities', { exact: true })).toBeVisible();
        await expect(page.getByText('Prepare tables and chairs', { exact: true })).toBeVisible();

        await page.goto(page.url().replace(/\/preparation$/, ''), { waitUntil: 'domcontentloaded' });
        const purchasingLink = page.locator('a.zazu-quick-link').filter({ hasText: 'Purchasing' }).first();
        await expect(purchasingLink).toBeVisible();
        await purchasingLink.click();
        await expect(page).toHaveURL(/\/purchasing\?event_id=\d+$/);
        await expect(page.getByText('PO-ZAZU-DEMO-001', { exact: true })).toBeVisible();
        await expect(page.getByText('PO-ZAZU-DEMO-002', { exact: true })).toHaveCount(0);

        await page.locator('a.zazu-list-item').filter({ hasText: 'PO-ZAZU-DEMO-001' }).click();
        await expect(page).toHaveURL(/\/purchasing\/\d+$/);
        await expect(page.getByText('Status: Received', { exact: true })).toBeVisible();
        await expect(page.getByText('Job: Mokoena Family Celebration', { exact: true })).toBeVisible();
        await expect(page.getByText('Receive goods', { exact: true })).toHaveCount(0);

        await page.goto('/inventory', { waitUntil: 'domcontentloaded' });
        await expect(page.getByText('Stainless chafing dish', { exact: true })).toBeVisible();
        await expect(page.getByText('White linen napkin', { exact: true })).toBeVisible();

        await page.goto('/assets', { waitUntil: 'domcontentloaded' });
        await expect(page.getByText('AST-TENT-001', { exact: true })).toBeVisible();

        await page.goto('/finance', { waitUntil: 'domcontentloaded' });
        const invoiceRow = page.locator('.zazu-list-item').filter({ hasText: 'INV-ZAZU-DEMO-001' }).first();
        await expect(invoiceRow).toBeVisible();
        await expect(invoiceRow.locator('.zazu-side-primary')).toHaveText('ZAR 11500.00');
        const invoiceLink = invoiceRow.getByRole('link', { name: 'Open', exact: true });
        await expect(invoiceLink).toBeVisible({ timeout: 10_000 });
        await invoiceLink.click();
        await expect(page).toHaveURL(/\/finance\/invoices\/\d+$/);
        await expect(page.getByText('Payment history', { exact: true })).toBeVisible();
        await expect(page.getByText('ZAR 11500.00 received · ZAR 0.00 outstanding', { exact: true })).toBeVisible();
        await expect(page.locator('.zazu-list-item').filter({ hasText: 'DEP-ZAZU-DEMO-001' })).toBeVisible();
        const balancePayment = page.locator('.zazu-list-item').filter({ hasText: 'BAL-ZAZU-DEMO-001' });
        await expect(balancePayment.locator('.zazu-side-primary')).toHaveText('ZAR 8050.00');

        await page.goto('/reports', { waitUntil: 'domcontentloaded' });
        await expect(page.getByRole('heading', { name: 'Reports' })).toBeVisible();
        await expect(page.getByText('ZAR 11,500.00', { exact: true })).toBeVisible();

        await expectNoServerFailures(page, responses, errors);
    });

    test('owner financial reconciliation rejects invalid replay and overpayment states', async ({ page }) => {
        test.setTimeout(60_000);

        const responses = [];
        const errors = [];
        page.on('response', response => {
            if (response.status() >= 500) {
                responses.push({ status: response.status(), url: response.url() });
            }
        });
        page.on('pageerror', error => errors.push(error.message));

        await login(page, DEMO_EMAIL, DEMO_PASSWORD);

        const finance = await page.goto('/finance', { waitUntil: 'domcontentloaded' });
        expect(finance?.status()).toBe(200);

        const invoiceRow = page.locator('.zazu-list-item').filter({ hasText: 'INV-ZAZU-DEMO-001' }).first();
        await expect(invoiceRow).toBeVisible();
        const invoiceLink = invoiceRow.getByRole('link', { name: 'Open', exact: true });
        await expect(invoiceLink).toBeVisible({ timeout: 10_000 });
        const invoiceHref = await invoiceLink.getAttribute('href');
        expect(invoiceHref).toMatch(/\/finance\/invoices\/\d+$/);
        const invoiceId = invoiceHref.match(/\/(\d+)$/)?.[1];
        expect(invoiceId).toBeTruthy();

        await page.goto('/finance/payments/create', { waitUntil: 'domcontentloaded' });
        const csrf = await page.locator('input[name="_token"]').first().inputValue();

        const replay = await page.request.post('/finance/payments', {
            form: {
                _token: csrf,
                idempotency_key: '00000000-0000-4000-8000-000000000002',
                type: 'payment',
                invoice_id: invoiceId,
                amount: '8050.00',
                method: 'bank_transfer',
                paid_at: new Date().toISOString().slice(0, 10),
                reference: 'DIRECTOR-REPLAY-001',
            },
            maxRedirects: 0,
        });
        expect(replay.status(), 'same payment idempotency key must replay safely').toBe(302);
        expect(replay.headers()['location']).toMatch(/\/finance$/);

        const overpayment = await page.request.post('/finance/payments', {
            form: {
                _token: csrf,
                idempotency_key: '00000000-0000-4000-8000-000000000003',
                type: 'payment',
                invoice_id: invoiceId,
                amount: '0.01',
                method: 'bank_transfer',
                paid_at: new Date().toISOString().slice(0, 10),
                reference: 'DIRECTOR-OVERPAYMENT-001',
            },
            maxRedirects: 0,
        });
        expect(overpayment.status(), 'paid invoice must reject a new payment').toBe(422);

        await expectNoServerFailures(page, responses, errors);
    });

    test('manager has operational access but cannot enter owner-only finance mutation or settings', async ({ page }) => {
        test.setTimeout(60_000);

        const responses = [];
        const errors = [];
        page.on('response', response => {
            if (response.status() >= 500) {
                responses.push({ status: response.status(), url: response.url() });
            }
        });
        page.on('pageerror', error => errors.push(error.message));

        await login(page, MANAGER_EMAIL, ROLE_PASSWORD);

        for (const path of ['/dashboard', '/work', '/quotes', '/purchasing', '/inventory', '/assets', '/reports']) {
            const response = await page.goto(path, { waitUntil: 'domcontentloaded' });
            expect(response?.status(), 'manager ' + path).toBe(200);
            await expect(page.locator('.zazu-error-shell')).toHaveCount(0);
        }

        const settings = await page.goto('/settings', { waitUntil: 'domcontentloaded' });
        expect(settings?.status(), 'manager settings denial').toBe(403);
        await expect(page.locator('.zazu-error-shell')).toContainText('AUTHZ-001');

        const paymentCreate = await page.goto('/finance/payments/create', { waitUntil: 'domcontentloaded' });
        expect(paymentCreate?.status(), 'manager payment mutation denial').toBe(403);
        await expect(page.locator('.zazu-error-shell')).toContainText('AUTHZ-001');

        const dashboard = await page.goto('/dashboard', { waitUntil: 'domcontentloaded' });
        expect(dashboard?.status(), 'manager dashboard after denied requests').toBe(200);
        await expect(page.locator('.zazu-error-shell')).toHaveCount(0);
        await expectNoServerFailures(page, responses, errors);
    });

    test('finance staff can view finance but is denied owner-only finance mutation', async ({ page }) => {
        test.setTimeout(60_000);

        const responses = [];
        const errors = [];
        page.on('response', response => {
            if (response.status() >= 500) {
                responses.push({ status: response.status(), url: response.url() });
            }
        });
        page.on('pageerror', error => errors.push(error.message));

        await login(page, STAFF_EMAIL, ROLE_PASSWORD);

        const finance = await page.goto('/finance', { waitUntil: 'domcontentloaded' });
        expect(finance?.status(), 'staff finance view').toBe(200);
        await expect(page.locator('.zazu-error-shell')).toHaveCount(0);

        const paymentCreate = await page.goto('/finance/payments/create', { waitUntil: 'domcontentloaded' });
        expect(paymentCreate?.status(), 'staff payment mutation denial').toBe(403);
        await expect(page.locator('.zazu-error-shell')).toContainText('AUTHZ-001');

        const settings = await page.goto('/settings', { waitUntil: 'domcontentloaded' });
        expect(settings?.status(), 'staff settings denial').toBe(403);
        await expect(page.locator('.zazu-error-shell')).toContainText('AUTHZ-001');

        const financeRecovery = await page.goto('/finance', { waitUntil: 'domcontentloaded' });
        expect(financeRecovery?.status(), 'staff finance after denied requests').toBe(200);
        await expect(page.locator('.zazu-error-shell')).toHaveCount(0);
        await expectNoServerFailures(page, responses, errors);
    });
});
