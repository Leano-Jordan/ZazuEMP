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


## Director UI/UX polish cycle — 2026-10-04

### Rendered evidence inspected

The supplied desktop screenshots were reviewed as current visual evidence. They exposed:
- navigation/flyout behaviour that could be obscured by the application content;
- a flyout hover dead-zone caused by physical separation from its trigger;
- dashboard content losing grouping/visual hierarchy and reading too much like a text stream;
- an oversized/over-emphasised hero treatment relative to operational content;
- legacy light-mode colour treatment leaking indigo/purple into otherwise blue/teal Zazu surfaces.

### Corrections executed

1. **Desktop hierarchical navigation**
   - desktop nav overflow is now visible so the nested flyout can cross the sidebar edge;
   - mobile keeps the bounded nav scroll behaviour because mobile flyouts are inline;
   - flyout is physically attached to the trigger (`left: calc(100% - 1px)`, `top: 0`) rather than separated by a 7px gap;
   - flyout no longer animates through a temporary horizontal gap;
   - hidden flyouts have `pointer-events:none`; visible flyouts regain pointer interaction;
   - sidebar remains above the main application content via the existing z-index contract.

2. **Dashboard visual authority cleanup**
   - removed a 141-line obsolete hard-coded dashboard visual override block from the extension stylesheet;
   - removed the legacy indigo/white dashboard treatment and a mobile table rule that disabled horizontal overflow;
   - dashboard components now inherit the canonical Zazu token system instead of the obsolete colour layer.

3. **Remaining component colour normalization**
   - account menu, document, form-control and semantic-chip exceptions that still used legacy indigo/purple hard-coded colours were converted to canonical semantic tokens.

### Verification

Source verification after implementation confirms:
- desktop nav no longer has the `overflow-x:hidden` clipping rule;
- mobile nav retains bounded scrolling;
- flyout has no physical hover gap;
- final visual extension remains free of root token blocks;
- dashboard legacy purple/white override block was removed;
- no new stylesheet was introduced.

### Visual state

**IMPLEMENTED / VISUAL UNVERIFIED**

The supplied screenshots are pre-change rendered evidence. This environment cannot render the user's local Zazu runtime after the change, so the final visual state must still be checked in the actual browser at desktop/mobile and light/dark widths.

Next gate: rebuild assets → render Dashboard, Work, Forms and Calendar → inspect navigation hover/click/keyboard → compare against the approved references → then accept or repair.


## Director commercial UI harmonization pass — 2026-10-04

Objective: harmonize typography, colour, sizing, spacing and component relationships against the repository reference collection and current commercial SaaS conventions, while preserving Zazu's established blue corporate direction.

Reference evidence applied:
- The complete `reference ui ux images/` collection remains the design evidence set, including `blue pallette.jpg`, `NAVBAR INSPIRATION.png`, `booking calendar example.jpg`, `Background-ZAZU.jpg` and the broader dashboard/application examples.
- The recorded reference language is now treated as an application system rather than page decoration: dark navigation rail, pale-blue canvas, elevated light surfaces, restrained blue actions, dense operational tables/calendars, readable hierarchy and mobile-first composition.

External design-system research applied:
- Atlassian: one token source for colour/spacing/typography and an 8px spacing foundation.
- Carbon: productive UI uses a 14px base type size, spacing creates perceived relationships, and sizing/spacing scales should remain constrained.
- Current SaaS dashboard guidance: high information density without clutter, restrained accents, tabular numeric alignment and responsive prioritisation.

Changes executed on main:
1. `resources/css/app.css`
   - added canonical UI font, mono font, type, spacing and shape tokens;
   - aligned the Tailwind `@theme` colour/font aliases with the same Zazu token family instead of the stale blue palette;
   - unified the application body font stack;
   - established a 14px body / 11px metadata / 13px nav / 24px page-title hierarchy;
   - established 4/8/12/16/20/24/32/40px spacing steps;
   - established 6/8/10px application radii;
   - aligned shell geometry around a 240px navigation rail, 1320px content measure and a 64px top bar;
   - removed the last sub-9px shared operational labels from the core stylesheet.

2. `resources/css/zazu-final-visual-sweep.css`
   - removed remaining legacy semantic colour fragments in dashboard, status, account and calendar components;
   - moved dashboard KPIs/statuses away from JetBrains Mono; mono remains reserved for operational references/data identifiers;
   - raised dashboard/calendar/table supporting text into the shared readable metadata range;
   - reduced dashboard hero dominance and tightened the main dashboard grid relationships;
   - aligned component status colours to the canonical success/warning/danger/info tokens;
   - kept the image-overlay white treatment only where it is intentionally required for image contrast.

3. `resources/css/zazu-responsive-theme.css`
   - aligned footer/container width to the shared 1320px content measure;
   - raised footer/context microtype to the metadata scale;
   - brought section tabs onto the shared 32px desktop / 40px mobile rhythm;
   - kept mobile navigation scroll ownership intact.

4. `resources/css/zazu-mobile-refinement.css`
   - changed the mobile header grid to prevent action crowding;
   - restored a 20px mobile page-title hierarchy;
   - increased mobile content/form breathing room without returning to excessive whitespace;
   - preserved 44px touch-safe action targets;
   - raised mobile calendar/supporting text to readable sizes;
   - kept the helper/status indicator on semantic colour tokens.

Source-level verification after implementation:
- core stylesheets now report no <=9px font declarations;
- `zazu-final-visual-sweep.css` still contains no root token system;
- JetBrains Mono remains in only two operational reference/data contexts in the final sweep;
- no new stylesheet was introduced;
- remaining literal hex values in `app.css` are primarily the canonical token definitions themselves, not competing visual roots.

Verification boundary:
- this remains SOURCE-VERIFIED / VISUAL-UNVERIFIED;
- the repository connector does not expose the user's live local browser runtime, so post-change Dashboard/Work/Forms/Calendar renders could not be captured here;
- the next acceptance gate remains an actual Vite rebuild and browser comparison across desktop/tablet/mobile plus light/dark before another broad styling pass.


## Director shell readability correction — 2026-10-04

User-reported defects were treated as usability failures, not as justification for removing interface capability.

Observed/identified causes:
- the late visual refinement layer had changed the sidebar to a light surface while hierarchical navigation still used dark-shell assumptions;
- the hierarchical trigger and flyout therefore did not share one reliable foreground/background relationship;
- the account popover was positioned below the sidebar footer, allowing it to extend beyond the viewport and hide the Sign out action;
- the public landing page's dark authentication panel still contained several light-theme label/input colours, producing the same dark-text-on-dark-surface failure.

Correction on main:
- restored the sidebar as a consistently dark shell using sidebar-specific foreground tokens;
- made the topbar use the same dark shell so page title, section tabs and header controls remain readable;
- made desktop nav flyouts use the same dark shell family as the navigation rather than a light surface;
- removed rounding from hierarchical nav triggers and flyout links so the navigation and attached popover read as one symmetrical system;
- kept the flyout physically attached to its trigger;
- moved the account popover above the footer trigger, constrained its height to the viewport and made its surface/foreground explicitly dark-shell readable;
- retained a readable, semantic Sign out state;
- made mobile hierarchical flyouts use the same dark shell surface while preserving inline behaviour;
- harmonized the public landing header/navigation and authentication modal with the same shell font/foreground relationships;
- no interface capability or navigation section was removed.

Research applied:
- Carbon UI shell guidance treats header/left navigation as a coordinated shell and stresses consistent interaction across the shell. citeturn0search5turn0search11
- Carbon popover guidance requires the layer to sit above page content, stay connected to its trigger when using a tab-tip relationship, and avoid clipped overflow. citeturn0search1turn0search4
- Carbon accessibility guidance requires keyboard-reachable header/nav controls and predictable nested navigation. citeturn0search2turn0search9
- Atlassian specifically warns that dark-mode overlay/background combinations must be checked independently for contrast and stacking. citeturn0search8

Verification:
- modified files were re-read from `main`;
- source brace/parenthesis checks are balanced;
- no navigation capability was deleted;
- rendered browser acceptance remains pending because the live local Zazu runtime is not exposed through this connector.


## 2026-10-04 current refinement / defect hardening

### FIXED — account navigation placement
The authenticated account popover is now aligned to the same outward navigation relationship as the Work/System flyouts on desktop, while mobile places it above the account trigger. Light-theme account links are explicitly scoped to the dark navigation tokens so they cannot inherit page text colours.

### FIXED — Helper/navigation collision
The Helper is now below navigation flyouts in the visual stack. A small runtime collision check moves it horizontally when an open desktop flyout intersects it; the mobile shell suppresses Helper interaction while the navigation drawer is open.

### FIXED — wallpaper regression
The shell no longer paints an opaque page colour over the body wallpaper when a business wallpaper is configured. The wallpaper readability treatment was recalibrated to a translucent blue overlay rather than an almost-opaque page layer.

### REFINED — dashboard/title surfaces
Dashboard keeps all existing operational content, but the top hero now clearly reads as the Dashboard command/title band. Shared command/title rectangles are blue-tinted and distinct from the canvas rather than presenting as near-white slabs.

### REFINED — calendar
Calendar remains a multi-view operational surface. Holiday names are now surfaced in Month, 3 Months, Year and Agenda representations; Agenda also keeps dates that contain a holiday even when no job is scheduled. View controls use the same rectangular relationship as the navigation language.

Verification boundary:
- changed source files were fetched again from main;
- CSS/JS brace and parenthesis balance passed;
- Blade conditional counts balanced for changed Blade views;
- rendered desktop/tablet/mobile visual acceptance remains pending an actual runtime/browser render.


## Deep contrast/layout re-audit — 2026-10-04

This pass treated the reported failures as cascade and regression defects rather than isolated styling preferences.

### Verified source causes
- Asset/inventory register rules only constrained min-width; the base list item remained flex-based and the action cluster was right-biased, so useful content could collapse toward the far edge.
- The shared command band was still a pale gradient surface, unlike the dashboard workspace-state hero.
- The landing page contained a broad dark-shell block followed by a light public block. The later block did not reset every earlier foreground/background pairing. Notable bad pairs included light ZAZU text on the light header and a near-white comparison surface carrying white text.
- Business logo sizing was split across several rules without one explicit identity-zone fit contract.
- Landing bento placement relied partly on auto-placement, so Plan/Delivery had no protected 50/50 invariant.

### Corrections
- Resource registers now use explicit grid regions with bounded forms and deterministic responsive collapse.
- Command banners now use the dashboard image variable with a dark readability overlay and explicit light foregrounds.
- Brand presentation reserves a 40px identity slot in a 72px shell zone and contains the supplied artwork.
- Public navigation uses a common 40px control rhythm.
- Plan/Delivery occupy 6 columns + 6 columns on the final desktop row.
- Public landing foreground/background pairings are explicitly normalized for light surfaces, dark comparison surfaces and the intentionally dark operational-flow strip.

### Acceptance invariants
1. Resource register content must not be forced into a right-edge-only flex cluster.
2. A single asset or inventory item must retain compact row height and distribute record/state/action content across the available width.
3. Every shared page command banner must inherit the workspace-state image/readability treatment.
4. Business logos are contained, not cropped or stretched, inside the dedicated identity slot.
5. Public navigation links and actions share one 40px baseline.
6. Plan and Delivery remain 50/50 on the final desktop bento row.
7. Light landing surfaces use dark foregrounds; dark or image-backed surfaces use light foregrounds.
8. Hover/focus states preserve the same foreground/surface relationship rather than swapping to an unreadable token.

### Runtime acceptance boundary
Source acceptance is implemented. GitHub Actions for the resulting commit must still be observed before this cycle is marked fully accepted.
