# Director Incident Record — Collapsed Navigation Falling Back to Dashboard

**Date:** 2026-11-26  
**Status:** NAVIGATION FALLBACK REWORKED / EXACT-HEAD ACCEPTANCE PENDING  
**Repository:** Leano-Jordan/ZazuEMP  
**Write target:** main only

## User-observed failure

When the desktop sidebar was collapsed, selecting navigation destinations could return to the Dashboard with the Work group expanded instead of opening the selected page. The Founder reports this across pages. Treat this as a navigation correctness defect, not a cosmetic issue.

## Root causes identified

The sidebar Blade destinations use their named Laravel routes. The client-side hierarchical navigation handler treats group buttons as accordion controls and does not rewrite destination anchors.

Two service-worker fallback defects combined to produce the symptom:

1. **Timing race (first correction):** authenticated navigation originally used a 1,800 ms service-worker timeout while the app's /up origin-health probe allowed 3,000 ms. A slow healthy destination could be aborted and replaced with Dashboard. The timeout was increased to 5,000 ms and the static cache advanced to v64.
2. **Over-broad dashboard preference (second correction):** shouldPreferOfflineShell returned true for every in-app navigation except a short allowlist of forms and Work detail routes. When the browser reported offline or a navigation fetch timed out/failed, cachedAppPage prioritised the Dashboard shell over the exact cached destination for most pages. This deliberately discarded navigation intent and made ordinary destinations appear to link back to Dashboard. The allowlist was too narrow for the actual navigation architecture.

The second defect is the more direct explanation for a broad class of in-app navigation failures. The precise network state/latency observed on the Founder device has not been captured, so do not claim the exact trigger for every occurrence.

3. **Cold-launch detection assumption (third correction):** the first policy correction used an empty `FetchEvent.clientId` as the cold-launch signal. That is not reliable for top-level navigation requests. A follow-up attempt used `clients.matchAll()` to look for any same-origin window, but that also failed: the previous page may no longer be discoverable during a same-tab navigation, while an unrelated existing tab can make a genuinely new installed-app launch look like an in-app route change. The 5ec4799 browser run exposed both directions of this error.

## Correction committed

- Keep the 5,000 ms authenticated navigation timeout and static cache v64.
- When `clientId` is empty, use the navigation request's referrer as the discriminant: a same-origin referrer is an in-app route transition and preserves the exact cached destination; an absent, `about:client`, malformed or non-local referrer is treated as a cold launch and prefers Dashboard. The app's `strict-origin-when-cross-origin` policy preserves full same-origin referrers.
- Keep `respondWith()` synchronous in the fetch event. The referrer decision is synchronous and does not require asynchronous client enumeration.
- Update browser acceptance to pass the current same-origin URL as `referer` for programmatic navigation that is intended to model an in-app transition. The true cold-launch test deliberately omits a referrer.
- For in-app navigation, prefer the exact route from the active user/business cache when the host is offline, returns a transient 5xx, or the request times out. Use Dashboard only when the requested route has no exact cached entry.
- Preserve the security boundary: 401/403 and other client responses are not replaced with stale authenticated cache content. Authenticated HTML remains isolated to the active user/business cache.
- Update worker regressions so a failed in-app /customers navigation returns its exact cached destination, a cached /work directory remains on /work, query variants without an exact cache entry fall back safely, and a true cold launch still prefers Dashboard.
- Model browser navigation correctly in the worker harness: an empty `clientId` with an existing same-origin window must preserve the exact cached destination; an empty client set is a cold launch.
- Fix the cross-tab logout E2E fixture to accept the production confirmation dialog. The previous test auto-dismissed the intentional `window.confirm`, so it never actually signed out; production logout behavior was not weakened.
- Retain the slow-but-healthy /quotes regression and the collapsed desktop rail Playwright test.

## Verification boundary

The source and worker regressions are committed. The first exact-head run after the live-client attempt (`5ec4799`) passed Laravel, coverage, PostgreSQL, Zazu Quality and Windows packaging, but offline browser acceptance remained 77 passed / 3 failed. The failures were the offline customer edit, priming Customers/Work routes, and cold installed-app launch. They showed the global-client heuristic was not reliable. The current referrer-based correction is committed across worker `a2b8957`, worker tests `102b6fa`, Laravel feature contract `0871121`, PWA route acceptance `b769a63`, and customer edit acceptance `651ac45`. Exact-head workflows for this correction set are queued; no pass is claimed. Physical Founder-device reproduction remains required after the corrected worker is installed.

## Operational notes

- No branch was created. All changes remain on main.
- No offline business data, cache records or queued mutations were cleared.
- Existing v64 caches are migrated by the existing worker activation path; no cache-version bump is needed for this policy-only correction.
- If the Founder still sees the fallback after the updated worker is installed and CI passes, capture the requested URL, final browser URL, browser online/offline state and whether the host returned a response or timed out. Do not repeat generic smoke tests.

## Changed files

- public/sw.js
- tests/js/service-worker-auth-cache.test.js
- tests/Feature/PwaOfflineFoundationTest.php
- e2e/offline-session-transition.spec.js
- resources/css/app.css
- e2e/navigation-responsive.spec.js
- tests/Feature/SidebarNavigationPresentationTest.php
