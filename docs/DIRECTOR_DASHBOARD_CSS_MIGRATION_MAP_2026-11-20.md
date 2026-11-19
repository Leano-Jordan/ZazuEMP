# Director Dashboard CSS Migration Map — 2026-11-20

## Scope

This is the next-step engineering record for the dashboard CSS authority cleanup. It supplements `docs/DIRECTOR_CSS_AUTHORITY_AUDIT_2026-10-04.md`; it does not replace that historical audit.

The reference UI/UX image directory remains reference-only. It is not runtime media and is not in scope for asset cleanup.

## Repository and change controls

- Repository: `Leano-Jordan/ZazuEMP`
- Target branch: `main` only
- No branch creation, switching, or branch cleanup is part of this task.
- Preserve the six loaded stylesheets until their responsibilities are reconciled.
- Do not add a styleguide or new shared Blade components during this pass.
- Do not claim runtime, test, or visual acceptance without actually running the relevant checks.

## Newly verified cascade evidence

The authenticated layout is `resources/views/components/app-layout.blade.php`. Its Vite declaration loads the relevant stylesheets in this order:

1. `resources/css/zazu-dashboard-authority.css`
2. `resources/css/zazu-product-authority.css`

The product authority therefore loads after the dashboard authority and can override dashboard declarations when selector specificity and importance permit. This is a concrete reason not to move or delete dashboard-looking rules based on selector names alone.

The Vite build input list in `vite.config.js` also includes both files, with the product authority after the dashboard authority.

## Test coupling discovered

The following source-level test dependencies must be reviewed before any ownership migration:

- `tests/Feature/UiSharedComponentAuthorityTest.php` reads `zazu-product-authority.css` and currently expects dashboard selectors including sidebar, topbar, hero and dashboard surface headings to exist there. This encodes the current overlap as a test contract.
- `tests/Feature/DashboardVisualRefinementTest.php` reads both dashboard and product authority files and asserts dashboard-specific event styling in the product file.
- `tests/Feature/UiDashboardInteractionTest.php` reads the dashboard authority and protects dashboard layout, responsive and theme rules.
- `tests/Feature/ViteAssetDeliveryTest.php` verifies both stylesheet build entries.
- `e2e/zazu-visual-foundation.spec.js` checks that the dashboard and hero render and captures theme states. This is useful runtime coverage, but source inspection does not mean it has passed on the current head.

A CSS-only move would therefore create test failures or silently weaken regression protection unless the assertions are migrated with the declarations.

## Dashboard hero / supporting panel: migration decision

**Decision: do not consolidate or move the hero blocks yet.**

The dashboard authority contains repeated hero and hero-side selectors, but later declarations are not necessarily redundant: the file has accumulated different layout, theme, image, responsive, and refinement states. The product authority is also loaded later and contains dashboard-scoped overrides. The effective hero is consequently a cross-file cascade, not a self-contained repeated block.

The next source pass must extract, in source order, every matching block from both authority files and classify each declaration by:

- selector and source order;
- property/value and `!important` status;
- enclosing media query;
- theme/state selector;
- whether the declaration affects geometry, surface, typography, image/overlay, interaction, or accessibility;
- which later declaration wins in the current load order;
- test assertions that depend on its location or exact text.

Do not merge declarations across different breakpoint or theme/state contexts. Do not remove an earlier declaration solely because a later block repeats the selector.

## Safe migration sequence

1. Complete the hero + hero-side declaration matrix across both files, including the final dark-theme and responsive blocks.
2. Identify the smallest group whose effective declarations are provably equivalent and whose removal does not change the computed cascade.
3. Move dashboard-only declarations out of the product authority only where their purpose is genuinely dashboard-specific. Keep reusable authenticated component rules in the product authority.
4. Update `UiSharedComponentAuthorityTest` and `DashboardVisualRefinementTest` in the same commit so they assert ownership in the correct file, rather than preserving the old overlap.
5. Preserve and extend dashboard responsive/theme assertions in `UiDashboardInteractionTest` and browser-level visual checks.
6. Re-fetch changed files from `main`; run the focused PHPUnit tests, Vite build and the visual browser suite in the actual application environment.
7. Record runtime and rendered acceptance separately for desktop, tablet and phone in light and dark themes.

## Current verification status

- Repository branch inventory checked: `main` and an existing `deploy/render-staging` branch were returned. No branch was created or modified. The staging branch is outside this task.
- Current source files inspected: dashboard authority, product authority, shared-component authority test, authenticated app layout, Vite configuration and relevant dashboard test/search references.
- Confirmed: dashboard authority precedes product authority in the authenticated layout.
- Confirmed: tests currently encode dashboard selectors in the product authority.
- Not yet verified: complete property-level hero/hero-side matrix, PHP test results, Vite build, browser-rendered acceptance, and physical mobile behaviour.
- No CSS or application behaviour changed in this pass.

## Next execution target

Finish the property-level hero and hero-side matrix from the exact current `main` files. Only then make the first CSS consolidation and its paired regression-test update.
