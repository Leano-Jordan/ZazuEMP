# Director Incident Record — Collapsed Navigation Falling Back to Dashboard

**Date:** 2026-11-26  
**Status:** SHARED OFFLINE WORKSPACE SHELL IMPLEMENTED / EXACT-HEAD ACCEPTANCE PENDING  
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

3. **Cold-launch detection is not reliable enough to drive route selection:** the empty `FetchEvent.clientId`, same-origin referrer and live-window enumeration attempts did not reliably distinguish an installed-app launch from a same-tab navigation in the browser acceptance environment. The 5ec4799 and subsequent referrer-based runs demonstrated false classification in both directions. The worker must not make exact-route selection depend on these signals.
4. **Offline workspace markup was Dashboard-only:** after exact-route selection was corrected, a cold launch on a cached Work detail URL loaded the correct event page, but the offline workspace indicator/runtime had no `data-zazu-offline-workspace` host element to reveal. That shell existed only in `dashboard.blade.php`, so every other authenticated route lacked the offline workspace container. The failure was a real layout ownership defect, not a service-worker route fallback regression.

## Correction committed

- Keep the 5,000 ms authenticated navigation timeout and static cache v64.
- Remove the cold-launch Dashboard preference entirely. Every navigation first requests its exact URL from the active user/business cache, regardless of `clientId`, referrer or whether the browser is opening a new installed-app window.
- Dashboard is a safe fallback only when the requested URL has no exact cached entry. A cold launch on a supported cached deep link therefore opens that destination; the installed app's configured start URL continues to open Dashboard normally.
- Keep `respondWith()` synchronous in the fetch event. No asynchronous client enumeration or browser launch heuristic is required.
- Preserve the security boundary: 401/403 and other client responses are not replaced with stale authenticated cache content. Authenticated HTML remains isolated to the active user/business cache.
- Worker regressions now prove exact-route selection for cold launch and in-app navigation, with Dashboard fallback only for an uncached route/query variant.
- Extracted the offline workspace shell from the Dashboard view into `resources/views/components/offline-workspace.blade.php` and mount it from the authenticated app layout whenever an active business exists. This makes the local workspace available from cached Work detail, customer, quote and other authenticated pages rather than only from Dashboard. The partial uses the layout's existing current-business permission closure for its navigation links.
- Browser acceptance now uses actual same-tab anchor navigation rather than `page.goto()` to model a user click. The offline customer edit journey uses the same real navigation path.
- Keep the slow-but-healthy /quotes regression, hanging-host timeout coverage, collapsed desktop rail and responsive navigation click-through checks.
- Keep the cross-tab logout E2E fixture accepting the intentional production confirmation dialog; production logout behaviour was not weakened.

## Verification boundary

The referrer-based attempt passed Laravel, coverage, PostgreSQL and Zazu Quality, but offline browser acceptance remained 78 passed / 2 failed. The exact-route worker run then reached 79 passed / 1 failed: the cached Work detail page was selected correctly but had no offline workspace container because that markup lived only in Dashboard. The shared-shell correction is committed across partial `de8b334`, Dashboard extraction `f2fe975`, authenticated layout mount `6f62622`, feature contract `df32c6e` plus assertion quoting fixes `47bf2e6`, `9044bf9` and `13c8e38`. Exact-head Laravel, browser and package workflows for the shared-shell correction are pending; no pass is claimed. Earlier exact-route worker commits: `2332115`, worker unit contract `880b6b6`, Laravel PWA contract `c90f256`, offline route-click acceptance `1e87892`, customer edit navigation `d6d0ee3`. Physical Founder-device verification remains required after the corrected worker and shared shell are installed.

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
