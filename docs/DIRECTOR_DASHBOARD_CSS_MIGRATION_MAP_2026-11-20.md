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


### Selector-group classification

A source-order pass over the complete product stylesheet identified eight mixed selector groups that combine dashboard selectors with non-dashboard selectors:

- Shared sidebar shell: `body:not(.zazu-dashboard-page) .zazu-sidebar` paired with the dashboard sidebar selector.
- Shared topbar shell: the equivalent paired topbar selector.
- Shared dark topbar shell: paired dark-theme topbar selectors.
- Shared section-heading surfaces: generic card/panel/form headings combined with the dashboard surface heading.
- Shared heading typography: generic heading selectors combined with dashboard surface heading selectors.
- Shared solid-surface reset: generic card/panel/command/KPI selectors combined with dashboard hero and surface-heading selectors.
- Shared dark heading surface: generic dark-theme headings combined with the dashboard surface heading.
- Final dashboard token block: the scanner reports a mixed group at its opening because the leading comment and the first selector are part of the same extracted prelude; this must be manually parsed before any edit.

The first seven are genuine shared/mixed ownership cases and must remain in product authority unless split into selector-equivalent rules with identical declarations and unchanged media/theme context. The final item is a parser-boundary artifact and requires manual source parsing, not an assumption that the rule itself is mixed.

The scan confirms that a bulk move by line range is not safe. The migration must be performed rule-by-rule, preserving source order and splitting only demonstrably equivalent selector lists.


## Rule-level follow-up — dashboard quote pipeline candidate

The quote-pipeline group around product-authority lines 775–795 was inspected against dashboard-authority source and repository search results.

| Product rule | Ownership | Result |
|---|---|---|
| `.zazu-dash-commercial .zazu-dash-quote-stages` | Dashboard-only | Dashboard authority already has a more specific `body.zazu-dashboard-page .zazu-dash-commercial .zazu-dash-quote-stages` rule. The product rule's `gap` does not win against that more-specific important declaration. Its padding remains a distinct declaration and needs full downstream property tracing. |
| `.zazu-dash-commercial .zazu-dash-quote-stage` | Dashboard-only | Dashboard authority already contains a more-specific stage rule. The product block's minimum-height and padding cannot be treated as an independent ownership move without checking the dashboard rule's declarations and later product overrides. |
| `body.zazu-dashboard-page .zazu-dash-panel-head` | Dashboard-only | Separate rule, but panel-head structure is also styled in dashboard authority and in the final visual sweep stylesheet. Not part of the safe candidate. |
| `body.zazu-dashboard-page .zazu-dash-hero-side` | Dashboard-only | Separate rule, with multiple earlier/later hero-side declarations in both authority files. Not part of the safe candidate. |

The product quote-pipeline block is not a clean first migration unit: its declarations are interleaved with unrelated product rules, overlap more-specific dashboard rules, and have test assertions tied to the current product-file location (`UiVisualSystemRegressionTest`). The dashboard interaction test also asserts the dashboard-owned quote-pipeline selector. A blind move would either leave dead declarations behind or alter the cascade while giving misleading ownership signals.

### Current decision

No CSS relocation is approved by the evidence gathered so far. This is deliberate: the candidate inspected is not a safe atomic group. The next useful pass is a complete rule/declaration matrix for the dashboard-only blocks in product authority, including their selector specificity and all later declarations in both authority files. Only a block with a proven unchanged computed winner and paired test update should be moved.

### Verification boundary

- Re-fetched current `main` product authority, dashboard authority, shared-component test and migration document before this analysis.
- Used repository search to locate the corresponding dashboard-authority rules and source-level test coupling.
- No CSS or test source changed.
- PHPUnit, Vite and browser visual tests were not run in this environment; no pass is claimed.


## Brace-aware rule inventory and cascade correction — 2026-11-20

A second pass parsed complete rule blocks from the exact current product stylesheet (blob `6173878b926561eba9f644c621d8a3ae63ce7b6c`), tracking comments, quoted strings, braces, and enclosing at-rule context. This is a source-order inventory, not a browser CSSOM/computed-style test.

- **154 rule blocks** contain dashboard-related selectors or dashboard component names.
- **7 selector-list blocks** mix dashboard-scoped selectors with selectors also used outside the dashboard. These are the shared shell and shared heading/reset groups at lines 495, 502, 508, 1035, 1045, 1107 and 1144.
- The 154 count is lower than the earlier 161-rule exploratory scan because the earlier pass did not handle all selector-list and at-rule boundaries consistently. The earlier 260 figure counted matching source lines, not rules; it must not be interpreted as 260 independent CSS rules.

### Quote-pipeline declaration trace

The product quote block around lines 778–797 has the following trace:

- The desktop `.zazu-dash-commercial .zazu-dash-quote-stages` rule sets padding and gap; its gap is later affected by the more-specific dashboard rule in dashboard authority. The padding needs to be evaluated against the subsequent product layout passes.
- The stage rule sets min-height and padding, with a later mobile override at max-width 620px. It is not safe to remove based on selector overlap alone.
- The adjacent dashboard panel-head and hero-side border rules at 786 and 789 are separate overrides; they are not part of the same quote-stage ownership unit.
- Existing source assertions in `UiVisualSystemRegressionTest` and `DashboardVisualRefinementTest` encode current product-file locations/values and must be deliberately rewritten if ownership changes.

### Mixed selector groups — confirmed boundary

The seven mixed blocks are:
1. Sidebar shell (495)
2. Topbar shell (502)
3. Dark topbar shell (508)
4. Shared card/panel/form/dashboard heading surface (1035)
5. Shared heading typography (1045)
6. Shared solid-surface background-image reset (1107)
7. Dark shared heading surface (1144)

These should remain in product authority unless split into selector-equivalent rules with identical declarations and identical theme context. The pure dashboard blocks are candidates for further cascade mapping, not automatic migration candidates.

### Current engineering decision

No CSS source change is made in this pass. The parser provides a more reliable ordered inventory, but it does not prove unchanged computed styles. The next code change must be based on a property-by-property comparison of one pure dashboard block against all earlier/later declarations in both stylesheets, then paired test changes. Browser computed-style validation is still required before calling the migration visually safe.


## Load-order verification and migration constraint — 2026-11-20

The current authenticated layout was re-fetched from `main` (blob `af8367b2c23b61ea8ccb1bd1efd05f932c191bcd`). Its Vite entry order is:

1. `zazu-responsive-theme.css`
2. `zazu-mobile-refinement.css`
3. `zazu-final-visual-sweep.css`
4. `zazu-dashboard-authority.css`
5. `zazu-product-authority.css`

This confirms that product authority is loaded after dashboard authority. A dashboard-only selector in product authority is therefore not automatically safe to relocate: doing so changes its position relative to all product rules and can change the winning declaration, including theme and responsive overrides.

The current product and dashboard authority blobs remain `6173878b926561eba9f644c621d8a3ae63ce7b6c` and `e0cb0ad634effe480113bc9b89f5ac2356f0ffa7`, respectively. No stylesheet or test file changed in this pass.

### Next migration gate

Before moving any block, build a property-level trace for a single candidate: selector specificity, declaration importance, source order, enclosing media/theme context, and every later matching declaration across the remaining loaded stylesheets. The candidate must have a provably unchanged winner at desktop, tablet, mobile, light and dark contexts. If the trace cannot establish that, retain the rule in product authority and continue the audit. Do not use source-string assertions as a substitute for computed-style validation.
