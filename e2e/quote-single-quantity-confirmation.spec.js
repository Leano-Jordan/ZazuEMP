import { test, expect } from '@playwright/test';
import { randomUUID } from 'node:crypto';

const DEMO_EMAIL = process.env.ZAZU_DEMO_EMAIL || 'demo@zazu.local';
const DEMO_PASSWORD = process.env.ZAZU_DEMO_PASSWORD || 'password';

async function login(page) {
    await page.goto('/?auth=login', { waitUntil: 'domcontentloaded' });
    await expect(page.locator('[data-auth-modal]')).toBeVisible();
    await page.getByLabel('Username or email').fill(DEMO_EMAIL);
    await page.locator('#auth_password').fill(DEMO_PASSWORD);
    await page.locator('[data-auth-submit]').click();
    await expect(page).toHaveURL(/\/dashboard$/);
}

test('quote builder blocks an unconfirmed quantity of one and recalculates after correction', async ({ page }) => {
    test.setTimeout(60_000);

    const runId = randomUUID().slice(0, 8);
    await login(page);

    await page.goto('/work/create?start=quote', { waitUntil: 'domcontentloaded' });
    const form = page.locator('#new-job-form');
    await expect(form).toBeVisible();
    const customers = await page.locator('#customer_id option').evaluateAll(options =>
        options.filter(option => option.value !== '').map(option => option.value)
    );
    expect(customers.length, 'seeded demo customer is available').toBeGreaterThan(0);
    await page.locator('#customer_id').selectOption(customers[0]);

    const eventTypeChoice = page.locator('label.zazu-choice-card:has(input[name="event_type"])').first();
    await expect(eventTypeChoice).toBeVisible();
    await eventTypeChoice.click();
    await expect(eventTypeChoice.locator('input[name="event_type"]')).toBeChecked();

    const serviceChoice = page.locator('label.zazu-service-choice:has(input[name="services[]"][value="Buffet Catering"])');
    await expect(serviceChoice).toBeVisible();
    await serviceChoice.click();
    await expect(serviceChoice.locator('input[name="services[]"]')).toBeChecked();
    const selectedService = (await serviceChoice.innerText()).trim();

    await form.locator('input[name="name"]').fill('Quantity confirmation acceptance ' + runId);
    const eventDate = new Date(Date.now() + 35 * 24 * 60 * 60 * 1000).toISOString().slice(0, 10);
    await form.locator('input[name="event_date"]').fill(eventDate);
    await form.getByRole('button', { name: 'Create job & continue to quote' }).click();

    await expect(page).toHaveURL(/\/work\/\d+\/quotes\/create$/);
    // This test isolates quantity confirmation; tax behaviour has separate coverage.
    await page.locator('select[name="tax_rate_id"]').selectOption('');
    const line = page.locator('[data-quote-line]').first();
    await expect(line).toBeVisible();

    const quantity = line.locator('[data-quote-quantity]');
    const unitPrice = line.locator('[data-quote-price]');
    const confirmation = line.locator('[data-quote-single-confirm]');
    const reviewStatus = page.locator('[data-quote-review-status]');
    const subtotal = page.locator('[data-quote-subtotal]');
    const lineTotal = line.locator('[data-quote-line-total]');

    await expect(quantity).toHaveValue('');
    await expect(confirmation).toBeHidden();
    await quantity.fill('1');
    await unitPrice.fill('123.45');

    await expect(confirmation).toBeVisible();
    await expect(confirmation).toHaveJSProperty('required', true);
    await expect(reviewStatus).toHaveText('1 single-unit line(s) need explicit confirmation.');
    await expect(lineTotal).toHaveText('Confirm quantity of 1');
    await expect(subtotal).toContainText('0.00');

    await confirmation.check();
    await expect(reviewStatus).toHaveText('All line quantities and prices are entered.');
    await expect(lineTotal).toContainText('123.45');
    await expect(subtotal).toContainText('123.45');

    await quantity.fill('2');
    await expect(confirmation).toBeHidden();
    await expect(confirmation).not.toBeChecked();
    await expect(confirmation).toHaveJSProperty('required', false);
    await expect(lineTotal).toContainText('246.90');
    await expect(subtotal).toContainText('246.90');

    await quantity.fill('1');
    await expect(confirmation).toBeVisible();
    await expect(confirmation).not.toBeChecked();
    await expect(reviewStatus).toHaveText('1 single-unit line(s) need explicit confirmation.');
    await confirmation.check();

    await page.getByRole('button', { name: 'Save quote v1' }).click();
    await expect(page).toHaveURL(/\/quotes\/\d+$/);

    const quoteHeading = page.getByRole('heading', { name: /^QUO-/ });
    await expect(quoteHeading).toBeVisible();
    const savedLines = page.locator('.zazu-quote-lines');
    await expect(savedLines).toContainText(selectedService);
    await expect(savedLines).toContainText('1.00 guest');
    await expect(savedLines).toContainText('ZAR 123.45');
    await expect(page.locator('.zazu-total-row')).toContainText('ZAR 123.45');
});
