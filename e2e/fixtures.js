import { test as base, expect } from '@playwright/test';

// Isolate browser-test client IPs so unrelated E2E cases do not consume one
// another's IP-wide login throttle. This is confined to the local test server;
// production login throttles and authentication behaviour remain unchanged.
export const test = base.extend({
    page: async ({ page }, use, testInfo) => {
        let hash = 2166136261;
        for (const character of `${testInfo.testId}:${testInfo.retry}`) {
            hash ^= character.charCodeAt(0);
            hash = Math.imul(hash, 16777619);
        }

        const secondOctet = (hash >>> 8) & 0xff;
        const thirdOctet = hash & 0xff;
        const clientIp = `198.18.${secondOctet}.${thirdOctet || 1}`;

        await page.context().setExtraHTTPHeaders({
            'X-Forwarded-For': clientIp,
        });
        await use(page);
    },
});

export { expect };
