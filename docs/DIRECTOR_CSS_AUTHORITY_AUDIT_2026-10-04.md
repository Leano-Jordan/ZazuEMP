# Director CSS Authority Audit — 2026-10-04

## Target

Make Zazu's existing CSS deterministic before further visual refinement. This operation intentionally avoids adding a new visual layer.

## Authority map

| Surface | Canonical owner | Boundary |
| --- | --- | --- |
| Design tokens / themes | resources/css/app.css | Light + dark root tokens; shared colours, surfaces, typography aliases, dimensions |
| Shared components | resources/css/app.css | Shell, cards, panels, forms, buttons, lists, shared state visuals |
| Shared responsive structure | resources/css/zazu-responsive-theme.css | Footer, shared responsive layout, tabs, job context, form/grid behaviour |
| Mobile shell/navigation | resources/css/zazu-mobile-refinement.css | Mobile menu, topbar grid, mobile navigation, touch sizing |
| Component-specific extensions | resources/css/zazu-final-visual-sweep.css | Only selectors whose component class is not owned by the three authorities above; no token root and no generic shared restyling |

The former zazu-final-visual-sweep.css token/component system is no longer a visual authority.

## Findings

### Critical — competing root token systems

Before cleanup, zazu-final-visual-sweep.css contained multiple independent :root systems, including a late indigo/purple theme. Because it loaded after app.css, it could silently replace the intended Zazu blue token values.

Disposition: removed. The extension stylesheet now has zero root token blocks. app.css contains the only light/dark token roots.

### Critical — shared selector cascade collisions

The former final sweep duplicated major shared selectors such as:
- .zazu-content
- .zazu-topbar
- .zazu-topbar-inner
- .zazu-sidebar
- .zazu-nav-link
- .zazu-nav-link.active
- .zazu-page-title
- .zazu-command-band
- .zazu-card
- .zazu-panel
- .zazu-form-section
- .zazu-btn
- .zazu-metric-card
- .zazu-list-item

Disposition: the extension layer was reconstructed so it no longer contains exact shared selectors. Remaining extension selectors are component-specific combinations and therefore cannot become a second base authority.

### High — obsolete hard-coded visual layers inside app.css

An older contrast/form refinement block used hard-coded pale-blue surfaces plus !important, including input/textarea borders and backgrounds. A later token-driven UX refinement existed below it, so the older layer was redundant and capable of defeating intended values through specificity.

Disposition: removed. This deleted 433 lines across the obsolete contrast/form layer and reduced app.css from 49 to 33 !important declarations.

A separate obsolete compact-navigation block was also removed:
- legacy 36px navigation treatment;
- repeated 4px radius normalization;
- redundant no-shadow rules;
- !important active-nav shadow rules.

Disposition: removed 37 lines and a further 4 !important declarations, leaving app.css at 29.

### Medium — mobile override duplication

zazu-mobile-refinement.css contained a stale max-width:520px page-title size that was immediately contradicted later by the mobile readability block.

Disposition: removed the stale declaration so <=520px now inherits the later intentional mobile title size.

## Current stylesheet metrics

- app.css: 7878 lines, 29 !important declarations.
- zazu-responsive-theme.css: 523 lines, 3 !important declarations.
- zazu-mobile-refinement.css: 735 lines, 7 !important declarations.
- zazu-final-visual-sweep.css: 3978 lines, 7 !important declarations, 0 root token blocks.
- Exact selector duplication between the extension stylesheet and the other three stylesheet files: 0 after cleanup.

Remaining !important declarations are predominantly accessibility, reduced-motion, print/mobile state, or bounded interaction protections. They are not evidence that another colour/token authority exists.

## Dead / obsolete visual systems removed

1. Final-sweep root token systems and theme palettes.
2. Final-sweep generic shared component restyling.
3. App-level obsolete contrast/form hard-coded colour layer.
4. App-level obsolete compact-navigation override layer.
5. Stale mobile title override.
6. Six CSS-only dead component selectors from the former extension layer.

## Blade selector discipline

The extension layer is now constrained by component-specific class ownership rather than generic selectors. This reduces the risk that a selector intended for one specialized surface silently becomes a global application rule.

Source checks also identified six selectors that existed only in the former extension CSS: zazu-asset-manifest, zazu-dashboard-telemetry, zazu-report-grid, zazu-supplier-network, zazu-travel-register and zazu-workspace-online. Their rules were removed.

A complete semantic selector-vs-Blade inventory remains a source-analysis task; rendered browser inspection is still required before declaring visual acceptance.

## Verification boundary

Source-level verification completed:
- changed files re-fetched from GitHub after writes;
- final extension has no token roots;
- no exact cross-file selector duplication remains between the extension stylesheet and the other three stylesheets;
- obsolete !important layers were removed;
- stylesheet authority comments now identify app.css as the canonical visual authority.

Runtime limitation:
- the local Zazu browser/runtime is not exposed through the repository connector in this environment;
- therefore this operation is IMPLEMENTED / SOURCE-VERIFIED, not VISUALLY VERIFIED.

## Director next gate

Rebuild Vite assets, load the actual authenticated dashboard/work/finance/calendar views in the real browser, and compare desktop + mobile + light + dark against the recorded reference baseline before any new visual styling is added.
