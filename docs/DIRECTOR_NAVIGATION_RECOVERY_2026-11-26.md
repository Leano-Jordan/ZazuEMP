# Director Incident Record — Collapsed Navigation Falling Back to Dashboard

**Date:** 2026-11-26  
**Status:** SOURCE-LEVEL ROOT CAUSE IDENTIFIED / FIX COMMITTED / AUTOMATED ACCEPTANCE PENDING  
**Repository:** `Leano-Jordan/ZazuEMP`  
**Write target:** `main` only

## User-observed failure

When the desktop sidebar was collapsed, selecting navigation destinations could appear to return to the Dashboard with the Work group expanded instead of opening the selected page. The Founder reports this across pages. Treat this as a navigation correctness defect, not a cosmetic issue.

## Root cause identified

The sidebar Blade destinations were correctly generated with their named Laravel routes, and the client navigation handlers did not intentionally rewrite destination links to Dashboard.

The failure path was in the service worker:

1. `public/sw.js` intercepted authenticated page navigations and used `fetchWithTimeout(request, 1800)`.
2. The application-side origin health probe in `resources/js/app.js` allows 3,000 ms for the lightweight `/up` check.
3. If an ordinary Blade destination took longer than 1.8 seconds while the origin itself remained healthy, the service worker aborted the requested route and substituted the cached Dashboard shell.
4. The new page's health probe could then report the origin healthy, so offline-route presentation correctly stayed disabled and the Dashboard shell remained visible at the requested navigation URL. The server-rendered Dashboard shell also naturally marked Work as the active/open navigation group.

This explains the reported visual symptom without inventing a broken Laravel route or a permission redirect. The source-level timing race is confirmed; the precise production request latency that triggered it has not yet been captured from the Founder device.

## Correction

- Increased the service-worker authenticated navigation timeout from **1,800 ms to 5,000 ms**, giving ordinary authenticated page responses a realistic window before the offline fallback is selected.
- Advanced the shared service-worker cache from **v63 to v64**, so installed clients can install the corrected worker.
- Kept the existing security boundary: 401/403 responses are not replaced with stale authenticated cache content; offline and 5xx fallback behaviour remains scoped to the active user/business cache.
- Added a regression that simulates a healthy `/quotes` destination taking 2.2 seconds and requires the actual Quotes response rather than the cached Dashboard.
- Updated the disconnected-host fallback test to enforce a bounded return (<6.5 seconds).
- Added a desktop Playwright regression that collapses the sidebar, opens the Sales flyout, selects Quotes, and verifies the destination URL, active group and persisted collapsed state after full-page navigation.
- Added a small stacking-layer correction so an open collapsed-rail flyout paints above adjacent navigation groups and its links retain pointer interaction.

## Verification boundary

The source and tests are committed. GitHub Actions has been triggered for the current `main` head, but no PASS is claimed until the Laravel, Zazu Quality, browser and relevant worker tests complete on the same head. Physical Founder-device reproduction remains required after the corrected build is installed.

## Operational notes

- No branch was created. All changes are on `main`.
- No offline business data, cache records or queued mutations were cleared as part of this correction.
- The cache version bump is a static-client update; it must preserve authenticated user/business page caches through the existing migration path.
- If the Founder still sees the fallback after the updated worker is installed and CI passes, capture the requested URL, final browser URL, online/offline state and whether the 5-second wait elapsed. Do not ask for repeated generic smoke tests.

## Changed files

- `public/sw.js`
- `tests/js/service-worker-auth-cache.test.js`
- `resources/css/app.css`
- `e2e/navigation-responsive.spec.js`
- `tests/Feature/SidebarNavigationPresentationTest.php`
