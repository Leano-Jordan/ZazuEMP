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


## Property-level hero cascade findings — 2026-11-20

The exact current source was re-fetched from `main` before this pass:

- `zazu-dashboard-authority.css`: 2,134 lines, blob `e0cb0ad634effe480113bc9b89f5ac2356f0ffa7`
- `zazu-product-authority.css`: 1,885 lines, blob `6173878b926561eba9f644c621d8a3ae63ce7b6c`

### Effective source-order map

| Concern | Dashboard authority | Product authority | Cascade implication |
|---|---|---|---|
| Hero geometry | Initial block around line 61; later visual passes around 594, 1,354 and final restore around 1,988 | Hero block around 1,328 adds its own minimum height, overflow and radius | Product loads later; its `min-height:190px` can override dashboard's final `min-height:184px` because both declarations are important and selectors have equal specificity |
| Hero surface | Final restore around 1,988 defines layered slate/blue/accent gradient and shadow | Final token palette around 1,704 sets border, background-color, background-image and shadow | Product's later important declarations win for those same properties; the rendered hero surface is not solely controlled by the dashboard file |
| Hero text | Dashboard final restore sets lead/support presentation; dark block around 2,087 sets dark text | Product token block around 1,712 sets hero text to `var(--zazu-ink)`; dark token block around 1,858 also sets theme text | Product authority supplies the later theme-aware text colour |
| Supporting panel | Dashboard final restore around 2,022 sets padding, border, gradient and layout; dark rules around 2,097 set dark surface | Product block around 1,375 sets margin, padding, border, background image and shadow; token block around 1,720 reasserts border, background image and shadow | Product owns several final panel surface properties today, despite the panel being dashboard-only |
| Responsive composition | Dashboard media queries around 2,117-2,134 control collapse and mobile heading size; earlier responsive blocks also exist | Product media queries around 1,528-1,534 adjust hero and panel dimensions | Product's later responsive declarations can win when media conditions overlap |
| Dark theme | Dashboard dark rules around 2,087 onward | Product dark rules around 1,827 onward | Product's later theme rules currently win for overlapping important properties |

Line numbers are source locations at the blob SHAs above; they must be refreshed after any edit.

### Confirmed redundant-looking but unsafe candidates

- The dashboard file repeats the hero and hero-side selectors across multiple historical refinement blocks, but their declarations differ in geometry, copy visibility, image treatment, and breakpoints. They are not safe to collapse as selector duplicates.
- The product file contains an older image-led hero block around line 1,328 and a later token-driven dashboard palette block around line 1,704. These are distinct visual generations; removing the earlier block without checking all properties it uniquely supplies would be unsafe.
- The dashboard file's final visual restore is not the final cascade authority because product CSS loads after it. A simple append-to-dashboard migration would not preserve all existing outcomes.
- The product file's token-driven dashboard rules include shared theme tokens, but their selectors are dashboard-scoped. Their migration must preserve the later dark-theme and responsive override order as one coherent group.

### Decision after property comparison

No CSS declaration was moved or deleted in this pass. There is not yet a proven zero-difference subset of the hero/supporting-panel rules that can be safely consolidated without changing the current cascade. The apparent repetition is partly historical design evolution and partly a cross-file authority overlap.

The first code migration should therefore be a coordinated relocation of a fully mapped dashboard-only token/theme group, not a partial hero rule cleanup. Before that change, map the exact start/end of the product authority's dashboard palette section and all responsive/theme blocks it owns; then update the source-ownership tests in the same commit. Do not move only the light-theme declarations.


## Full product-file dashboard selector inventory — 2026-11-20

A full-file selector scan of `zazu-product-authority.css` (blob `6173878b926561eba9f644c621d8a3ae63ce7b6c`, 1,885 lines) found **260 lines containing dashboard-scoped selectors**. They are not confined to the final Director token section.

The first dashboard-scoped selector occurs at line 496. Further groups appear around lines 536-545, 786-789, 882-894, 1,038-1,110, 1,300 onward, and the final palette block at 1,680-1,885. The file therefore contains multiple generations of dashboard-specific rules interleaved with cross-module product rules.

### Corrected migration boundary

The final Director palette block is not a safe standalone move. Earlier product-file dashboard rules occur before it and include shell, hero, surface, responsive, and dark-theme declarations. Moving only the final block into the dashboard file would place it before those earlier product-file rules in the actual load order and could reverse existing winners.

**Decision: do not relocate the final block alone.** The earlier recommendation to map that block as a self-contained migration unit is superseded by this full-file inventory.

### Next required analysis

1. Extract complete CSS rule blocks (not just selector lines) for all 260 dashboard-scoped selector lines.
2. Preserve their original source order and record mixed-selector groups that also target non-dashboard pages.
3. Separate pure dashboard-only blocks from mixed shared-shell blocks.
4. For each pure dashboard block, determine whether it can be moved to dashboard authority while preserving its order relative to every other dashboard rule.
5. For mixed selector lists, split selectors only when declarations and media/theme context remain identical; otherwise leave the shared rule in product authority.
6. Update tests only alongside a verified move. In particular, preserve the product-level shared shell assertions and move dashboard-specific ownership assertions to dashboard CSS.

### Status correction

- Confirmed: dashboard-specific rules are distributed throughout product authority, not only in the tail section.
- Confirmed: a tail-only relocation would risk changing cascade order.
- No CSS or test source changed in this pass.
- The migration boundary is now the complete ordered set of dashboard-scoped rules, not a single contiguous block.
