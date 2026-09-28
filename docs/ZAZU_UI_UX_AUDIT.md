# Zazu EMP UI/UX Audit and Hardening Record

Date: 2026-09-26

## Scope

This audit covers the shared application shell and the currently implemented Zazu EMP views in resources/views, with attention to visual hierarchy, interaction consistency, responsive behaviour, forms, content clarity, colour relationships, keyboard access, assistive technology cues, and suitability for South African small-business users.

## Design direction applied

The supplied visual direction was translated into a Zazu-specific system rather than copied literally:

- Warm cream and white surfaces with a deep forest-green navigation shell in light mode.
- Deep emerald surfaces with bright mint primary actions in dark mode.
- Distinct cards and sections using spacing, elevation and restrained top accents.
- One coherent visual system across dashboard, Work, Customers, Requirements, Quotes, Calendar, Travel, Catalogue and Settings.
- Rounded controls and cards with stronger separation between adjacent information groups.
- Mobile layouts preserve the same hierarchy and allow dense calendar content to scroll instead of collapsing into unreadable cells.

## Accessibility and interaction hardening

Implemented:

- Skip-to-content link.
- Focus-visible indicators with high-contrast outlines.
- Minimum 44px interactive controls for core buttons, fields and theme control.
- Required-field visual markers normalised at runtime.
- Inline error association using aria-invalid and aria-describedby.
- Form error summary with links back to affected fields.
- Native telephone input type and telephone autocomplete on contact forms.
- Email input mode and autocomplete handling.
- Decimal input mode for numeric fields.
- Larger calendar event targets and full weekday names exposed through labels.
- Reduced-motion support.
- Forced-colour support.
- Higher-contrast mode support.
- Mobile calendar horizontal scrolling for dense month layouts.
- Content wrapping for long customer names, references, addresses and descriptions.
- Theme metadata follows light/dark mode.

## Content and South African usability

Customer-facing copy was simplified to use common words and short, task-focused descriptions. Developer terms such as skeleton, engine not connected, and similar implementation language were removed from visible product surfaces and replaced with honest user-facing status language.

The interface avoids assuming specialist English vocabulary. Labels describe the business task first, while deeper operational concepts remain in supporting copy. Examples continue to use familiar South African contexts such as weddings, funerals, catering, hires and phone numbers in local format.

The UI remains English-first while keeping text simple enough to translate later. No false language selector was introduced before translation content exists.

## Commercial UX maturity

Improved:

- Clear primary actions.
- Consistent secondary and tertiary actions.
- Distinct page heading and page content hierarchy.
- Predictable placement of navigation, context, actions and records.
- Clear empty states.
- Honest planned-module states rather than fake live metrics.
- Consistent historical-record language around quotes and removed records.
- Better separation between catalogue definitions, Work requirements and commercial quoting.
- Responsive behaviour for desktop, tablet and mobile.
- Persistent theme choice.
- Project metadata and CI PHP runtime alignment.

## Remaining product-level maturity boundaries

These are not hidden by the UI:

- Inventory is planned, not represented as live data.
- Supplier management is planned, not represented as live data.
- Asset management is planned, not represented as live data.
- Reporting is planned, not represented as live data.
- Authentication, active business context, server-side business isolation and role permissions remain production architecture work rather than UI decoration.

## Verification basis

The hardening was checked against WCAG 2.2 interaction and visual guidance and current South African GCIS web guidance on accessibility, plain language, mobile-first design, information architecture and navigation.

Colour contrast was checked for the principal foreground/background relationships in both themes. Primary text and controls exceed common WCAG AA contrast thresholds, and stronger border values were selected so component boundaries remain visible rather than depending only on shadow.

## CI verification

The repository workflow was aligned to PHP 8.4 because the current dependency lock contains Symfony 8.1 packages requiring PHP 8.4.1 or newer while the project itself permits PHP 8.3 and above.

A pre-hardening CI failure was confirmed at Composer installation because the workflow was running PHP 8.3 against the current lock set. The workflow was corrected to PHP 8.4. That CI result is historical evidence from the earlier hardening cycle; the latest execution cycle remains locally unverified.


## 2026-09-26 implementation cycle: typography, branding and remaining pages

- [x] Local-first typography stack: no external font dependency; uses installed system fonts with accessible fallbacks.
- [x] Business logo upload and application-shell branding.
- [x] Configurable dashboard artwork.
- [x] Configurable workspace wallpaper with readability overlay in light/dark mode.
- [x] Dashboard metrics are actionable links to the information they describe.
- [x] Resource and reporting foundation pages now distinguish live navigation from planned functionality and avoid fake data.
- [x] Settings page now provides a real business appearance workflow.
- [x] Business context is created for an authenticated account that has no assigned business, establishing an explicit owner relationship.
- [x] Image uploads are constrained to raster formats and size limits.
- [x] CI verification passed on current HEAD after the cycle.

Design rule retained: branding is business-owned, not user-owned. Operational records should remain separate from presentation artwork.

## 2026-09-26 execution cycle: hierarchy, guided choices and workload hardening

Findings:
- Repeated generic white table structures made column labels visually indistinguishable from records.
- Several structured values, especially currency, categories and units, were exposed as free-text inputs even though the product already knows valid choices.
- Workload visibility was fragmented; users had to infer urgency from records rather than navigate directly to actionable views.
- Business ownership existed in the schema but was not consistently enforced at every model/write/query boundary.
- Work lifecycle rules were distributed in controllers rather than represented as reusable domain rules.

Implemented:
- Added explicit record-table headers and stronger separation between page/section headers, columns, records, side metadata and row actions.
- Replaced avoidable currency/category/unit free-text inputs with guided controls backed by central Zazu configuration.
- Added business default currency storage and reused it in commercial/planning forms.
- Removed the editable routing-provider field from travel; the current source is a fixed manual-calculation boundary.
- Added clickable Workload quick views including overdue preparation.
- Centralized active business resolution through CurrentBusiness.
- Added model-level business ownership protection for direct business-owned records.
- Scoped affected Customers, Work, Dashboard, Calendar, Capabilities, Requirements, Contacts, Costs, Travel, Preparation and Quotes operations to the active business context.
- Added closed-work protection and centralized valid Work status transitions.
- Hardened quote arithmetic around integer cents and quantity hundredths.
- Added regression coverage for business isolation, workload isolation and lifecycle controls.

Verification:
- Current GitHub source was re-inspected after implementation.
- Critical PHP files were re-fetched and confirmed structurally present after automated writes.
- Local PHPUnit, migrations, Blade compilation and browser rendering remain unverified because this environment cannot reach GitHub or execute the project checkout.

Security boundary:
- Business isolation is now enforced in the touched domain/query/write paths.
- Full authentication, explicit active-business selection, role/permission authorization and authenticated media delivery remain production gates.


## 2026-09-26 commercial hardening cycle

Implemented:
- [x] Header theme control reduced to an icon-only 42px control with accessible labelling and persistent theme state.
- [x] Signed-in identity changed from a passive status chip to a compact account control with name, account context, email and secure POST sign-out action.
- [x] Account menu closes on outside interaction and Escape.
- [x] Removed internal/developer-facing wording from recently added customer, Work, travel and quote surfaces where users were being told about implementation/source mechanics.
- [x] Service selection no longer forces a system-selected description into a read-only field; selected service details remain editable by the operator.
- [x] Removed placeholder authentication artwork text.
- [x] Image upload controls use constrained file-input sizing rather than inheriting full-width field treatment.
- [x] Business identity controls use constrained widths appropriate to their data.
- [x] Added UI regression assertions for icon-only theme and account controls.

Commercial interaction discipline:
- Icon-only controls retain accessible names.
- Actions are visually differentiated by hierarchy rather than oversized controls.
- Field width is constrained according to content type where a full-width treatment adds no value.
- Static explanatory copy does not expose implementation terminology.
- Existing workflow routes and server-side business-scope checks were left intact.

Verification boundary:
- Current GitHub source was re-read after the changes.
- Source-level checks confirmed the removed theme label, read-only service description behaviour and placeholder authentication copy are absent from the changed files.
- Runtime browser traversal, Blade compilation, local PHPUnit and fresh migration execution remain unverified in this environment.


## 2026-09-28 operational premium UI pass

Implemented on `main`:

- Reworked the shared visual token layer toward the accepted neutral workbench / deep-green Zazu direction in both light and dark themes.
- Reduced operational surface radii to the 2–6px default range and removed decorative elevation from routine cards, navigation and data surfaces.
- Tightened shell, navigation, headings, panels, lists, forms and controls to improve scan density without reducing core touch-target sizing.
- Applied tabular numerics to dashboard metrics and other numeric operational values.
- Preserved reduced-motion handling and existing focus-visible/accessibility rules.
- Simplified the Dashboard into an operational command centre: attention prompt → compact metrics → upcoming work → contextual quick access.
- Removed redundant dashboard module-directory cards and configurable dashboard artwork from the operational dashboard surface so the screen prioritises work over decoration.
- Replaced repeated quick-access cards with a dense contextual list that preserves navigation while reducing visual repetition.
- Preserved existing routes, metrics, permissions and business logic.

Fresh-eyes review:

- Remaining generic SaaS signals were identified in shared styling, especially oversized radii, decorative shadows and blue-first visual hierarchy, and corrected in the final normalization pass.
- No asymmetric Bento structure was forced into transaction-heavy areas. Dashboard asymmetry remains limited to the overview composition where it improves hierarchy.
- Runtime browser rendering, responsive visual inspection and local Blade compilation remain environment verification boundaries and must be performed in the owner's checkout/CI before treating the visual pass as fully rendered-verified.


## 2026-09-28 blue reference + fresh-eyes UI pass

Implemented on `main` after auditing the current shared shell and Dashboard:

- Rebased the shared light/dark visual tokens on the repository reference asset `images/blue pallette.jpg`.
- Canonical brand anchors are Primary `#5592FC`, Secondary `#F2F7FF`, Shadow `#C2DCFF`, Pale `#9EC8FF`, Light `#6FA4FF`, and Deep `#416AD7`.
- Preserved distinct semantic success, warning and danger colours so operational status does not collapse into the brand colour.
- Consolidated the palette in the shared token layer rather than introducing screen-specific colours.
- Completed another micro-radius sweep; rectangular operational surfaces now stay within the 2–6px default range. Semantic circular avatars and true pill/status controls remain exceptions.
- Expanded component-level responsiveness with CSS Container Queries for reusable Zazu surfaces and retained viewport media queries only where shell/device behaviour requires them.
- Removed a redundant Dashboard overview band. The Dashboard now establishes hierarchy directly through setup state, operational attention, compact metrics, upcoming work and contextual access.
- Preserved existing routes, metrics, permissions and business logic.

Fresh-eyes review:
- Removed duplicate hierarchy that made the Dashboard feel like two competing overview headers.
- Kept asymmetry limited to the command-centre composition instead of forcing Bento into transactional surfaces.
- Kept routine elevation restrained and used borders/surface contrast as the primary separation mechanism.
- Confirmed CSS brace balance, removal of superseded green/teal token values from the current shared stylesheet, and presence of Container Query primitives.

Verification boundary:
- Source-level re-read and structural checks were performed after the changes.
- Browser-rendered desktop/tablet/mobile inspection, Blade compilation and full application tests were not run in this environment and are not claimed as verified.


## 2026-09-28 iterative commercial hardening loop

Implemented during the continued Director loop:

- Permission-aware Dashboard discovery, metrics, CTAs and record links.
- Shared navigation now reuses the loaded current workspace membership role rather than re-querying permission state for each navigation item.
- Branded confirmation dialogs replaced remaining browser confirmation handlers; focus returns to the initiating control on Escape or cancel.
- Workspace switching moved out of inline JavaScript into the shared UI initializer.
- Workflow helper semantics corrected from dialog to non-modal region.
- Finance summaries are grouped by currency and monetary display no longer round-trips through floating point.
- Invoice paid and balance calculations use integer cents.
- Purchase Order and Quote status transitions are centralized in their domain models and reused by locked controller checks and status UI.
- Purchasing currency entry now follows the active business currency instead of allowing an invalid server-rejected value.
- Money multiplication and decimal parsing now reject negative or overflowing inputs with regression coverage.
- Mixed-currency payment history is rejected rather than silently contaminating invoice balances.
- Remaining monetary float formatting was removed from Purchase Orders, Travel, Quotes, Catalogue and capability surfaces.
- Legacy theme overrides were consolidated so the blue reference palette has a single canonical root token system.
- Browser smoke identity generation was made parallel-safe and its Dashboard expectation updated to the current operational hierarchy.
- Branding preview validation now matches the server upload allowlist.
- Reduced-motion handling now applies to toast dismissal as well as persistent UI transitions.
- Shared UI DOM initialization is centralized through one DOM-ready lifecycle.

Automated evidence:
- Laravel: green on commit 613f0f0.
- Psalm Security Scan: green on commit 613f0f0.
- PHPMD: green on commit 613f0f0.
- Browser smoke: still running on commit 613f0f0 at the time of this record.

Verification boundary:
- Browser smoke successfully built the frontend, installed Chromium and started the application; its regression suite had not completed at record time.
- Local manual browser inspection is unavailable in this environment.


## 2026-09-28 deep-dive UI/UX cycle

Scope: every form surface, unified authentication, Calendar, navigation simplification, dead-end reduction and mobile hardening.

Implemented:
- Registration and normal sign-in now share the same dual auth composition with route-preserving in-place switching and animation.
- Owner sign-in remains semantically separate from workspace registration.
- Shared password visibility handling is centralized in the application UI initializer.
- Form rhythm is standardized through the shared `.zazu-form` system so labels, controls, helper text and validation align as one vertical unit, with mobile single-column fallback.
- Calendar now has stronger month hierarchy, current-day emphasis, event limits, overflow indication and a mobile agenda representation.
- Primary navigation was reduced to compact workspace destinations; lower-level destinations now appear as contextual section tabs inside the page-title area.
- Mobile navigation exposes only the current section's relevant destinations and no longer depends on an internally scrolling navigation rail.
- Finance contextual actions are permission-gated individually.
- Mobile form grids collapse to one column, controls receive mobile-safe minimum heights, action groups can fill available width, and document/form surfaces reduce padding safely.

Findings addressed: duplicate auth compositions; missing mobile navigation toggle initialization; excessive sidebar density; lower-level routes competing with primary navigation; weak Calendar hierarchy; inconsistent form rhythm; and finance action links that could be shown without their specific permission.

Verification boundary:
- Source-level structural checks after this batch were clean for CSS and JS brace balance; auth pages no longer contain page-local script blocks.
- Connected GitHub status surface did not expose CI status for the latest UI commits at audit time.
- Browser-rendered desktop/tablet/mobile QA and local Blade compilation were not executed in this environment, so rendered verification is not claimed.


## 2026-09-28 comparative product-pattern cycle

Benchmark sources reviewed: HoneyBook event-planner/venue workflows, Tripleseat event and catering workflows, and Event Temple hotel/event workflows. These products consistently emphasize one connected event/project record, a visible next step, fast access to documents/payments, client-facing document/payment experiences, and mobile access. citeturn0search2turn0search4turn0search7

Adapted into Zazu without importing unrelated enterprise complexity:
- Preserved and strengthened the Job workspace as the single operational thread linking customer, services, quote, preparation, travel and costs.
- Converted the final non-actionable “Complete” lifecycle step into a real path to the job status editor, removing a dead-end control.
- Added a compact “At a glance” operational block to the Job workspace so the core event context stays visible while working through secondary panels.
- Removed duplicate Dashboard quick-access entries found during the fresh-eyes pass.
- Normalized customer contact cards away from one-off utility styling into the shared surface language.
- Hardened action-heavy page headers for narrow screens so multiple controls wrap deliberately instead of competing for one line.

The benchmark lesson was treated as workflow design rather than feature copying: Zazu already has several of the strongest structural ideas, particularly the event workspace, next-action treatment, quote customer view and preparation workflow. HoneyBook emphasizes centralized project information and client portals, while Tripleseat and Event Temple emphasize the event record as the operational source of truth and connected documents/payments. citeturn0search12turn0search0turn0search1

Verification boundary: source-level checks only for this batch; browser-rendered QA and Blade/runtime execution remain unverified.
