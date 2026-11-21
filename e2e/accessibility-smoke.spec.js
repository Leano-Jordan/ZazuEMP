import { test, expect } from '@playwright/test';
import AxeBuilder from '@axe-core/playwright';

async function expectNoSeriousAccessibilityViolations(page, surface) {
    // Axe must inspect settled colors, not intermediate frames of intentional CSS transitions.
    await page.waitForTimeout(300);
    const results = await new AxeBuilder({ page })
        .withTags(['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa'])
        .analyze();
    const blocking = results.violations.filter(item => ['critical', 'serious'].includes(item.impact));
    for (const violation of blocking) {
        console.error(`[${surface}] ${violation.id}: ${violation.help} (${violation.nodes.length} affected nodes)`);
        for (const node of violation.nodes.slice(0, 5)) {
            console.error(`  ${node.target.join(', ')} — ${node.failureSummary || node.html}`);
        }
    }
    expect(blocking, `${surface} must not contain critical or serious WCAG 2.1 A/AA violations`).toEqual([]);
}

test('public landing and authentication surfaces meet serious WCAG accessibility checks', async ({ page }) => {
    const response = await page.goto('/', { waitUntil: 'domcontentloaded' });
    expect(response?.status()).toBe(200);
    await expect(page.locator('main')).toBeVisible();
    await expectNoSeriousAccessibilityViolations(page, 'public landing');

    const navigation = page.locator('nav[aria-label="Public navigation"]');
    await navigation.getByRole('button', { name: 'Get started', exact: true }).click();
    await expect(page.locator('[data-auth-modal]')).toBeVisible();
    await expectNoSeriousAccessibilityViolations(page, 'registration modal');

    await page.locator('[data-auth-modal-switch="login"]').click();
    await expect(page.locator('[data-auth-modal]')).toBeVisible();
    await expectNoSeriousAccessibilityViolations(page, 'login modal');
});
