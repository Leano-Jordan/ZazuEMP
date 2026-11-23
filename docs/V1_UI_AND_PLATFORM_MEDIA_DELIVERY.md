# V1 UI and Platform Media Delivery

**Date:** 2026-11-23  
**Repository:** `Leano-Jordan/ZazuEMP`  
**Write target:** `main`

This record captures the targeted UI and platform-media implementation completed for the current V1 hardening pass. It is not a declaration that the full V1 acceptance gates are complete.

## Function sheet / Job inspector

The Job-register function sheet uses a fixed-position drawer and backdrop. They are moved to the document body before interaction so transformed or contained application layout ancestors cannot redefine the fixed-position containing block.

The drawer is exposed as a modal dialog. Opening it moves focus into the drawer; Tab navigation remains inside it; Escape closes it; closing returns focus to the triggering control.

Browser coverage checks that the drawer is a child of `document.body`, remains fixed-positioned and stays within the viewport after opening.

## Customer directory and customer pages

The customer directory row now has an explicit three-region model:

- customer identity and contact summary;
- linked work count;
- two distinct action controls.

Desktop aligns these regions in a stable grid. Compact layouts reflow the identity above workload and actions, remove desktop-only separators and preserve two readable action targets instead of allowing generic action-group rules to collapse them. Customer-detail header actions wrap with a consistent gap.

Playwright coverage checks actual action geometry at desktop (1280px) and phone (390px) widths, then navigates through the customer detail and edit surfaces and checks the sticky form actions remain within the viewport.

## Platform landing image workshop

The public landing page uses six image positions: hero, operations, resources, commercial control, venue and decor. The platform settings page previously exposed only four of them. The settings model and controller now expose all six.

The selectable image library now contains only bundled raster photography and validated admin uploads. The locally drawn SVG illustration fallbacks were removed from all six default/library mappings so they cannot be selected as landing artwork. Each position has a dimension-specific crop preview (hero 16:9; supporting positions 4:3). The library is directly clickable: choose a position, then click or keyboard-activate a photograph to assign it and update that position's preview. Upload previews remain local until saved into the library.

Upload rules:
- JPEG, PNG and WebP only; SVG and arbitrary markup are not accepted.
- Up to 8 files per save, 5 MB per file.
- Dimensions between 600 × 400 and 6,000 × 6,000 pixels.
- Server-side MIME/image/dimension validation.
- UUID filenames; original filenames are used only as a sanitized display label.
- A dedicated media record stores label, path, MIME type and byte size.
- Uploads first join the library; they can then be assigned to one or more of the six positions on the next edit. Existing public assignments do not change until the form is saved.

The default media directory is `public/images/landing/uploads`. Production installation must ensure it is writable by the application. Where public webroot writes are not permitted, set `ZAZU_LANDING_MEDIA_UPLOAD_DIRECTORY` to an application-managed writable directory and ensure the configured URL path is served by the installation. Do not point this setting to a user-controlled arbitrary filesystem location.

These settings are platform-level and do not change individual business branding, customer profile images or event attachments.

## Automated coverage added

- Function sheet document-layer and viewport geometry.
- Customer actions at desktop and phone widths and navigation through customer detail/edit.
- All six landing settings and the complete bundled image library.
- Safe raster upload, storage metadata, library reuse and assignment to a public image position.
- Rejection of SVG upload and denial of upload to a non-platform administrator.
- Multi-capability Helper guidance, Job-specific component guidance, and operational attention priority.

## Release evidence and remaining checks

All changes were committed directly to `main`; no feature branch was created. At the time of the first check for the current code batch, Laravel, quality, PHPMD and Director health workflows had been created by GitHub but were still queued. Their completed result must be checked against the latest main commit before describing the work as passing.

Automated browser checks do not replace visual review on the actual installed PC and phone. Physical offline acceptance, reconnect/restart acceptance, business recovery drill, commercial workflow acceptance and legal/brand clearance remain separate V1 gates.


## Refinement pass — 2026-11-23

A second review found that the first inspector implementation still used catering- and equipment-hire-specific tab names. That was inconsistent with the adaptive business model. The visible labels now use business-neutral event language: **Scope & preparation**, **Resources & setup**, and **Commercial position**. Internal tab keys remain stable to avoid breaking existing content wiring.

The inspector's summary fields now read as distinct cards, the tab strip has a stable three-column layout with readable wrapping, the heading remains visible while scrolling, and the content area uses contained scrolling. On phone widths the inspector occupies the viewport and its Job action controls become a deliberate two-column grid rather than a squeezed inline row.

Tab panels now update both the native `hidden` state and `aria-hidden`, retain labelled tab/panel relationships and support arrow/Home/End keyboard navigation. Browser coverage checks the three neutral labels, tab switching, panel visibility and arrow-key navigation.

The Helper now visibly identifies active business services as compact chips. On a Job, saved requirement components appear as chips instead of a generic bullet dump. The recommended next action has a clearer visual hierarchy, a reason label and a distinct action link. This is still driven by existing authoritative Job state and permission-filtered attention, not speculative AI recommendations.

## Quality gate correction — 2026-11-23

The failure logs were retrieved and the root causes were addressed rather than suppressing the gates:

- Laravel suite: stale test expectations used the retired inspector labels “Services & preparation” and “Financial Ledger”; the landing-media test counted attribute text in the inline script instead of counting rendered preview elements; RouteIntegrity misclassified `request()->route('event')` as a named-route helper; Helper context tests expected relation counts that the view had not loaded.
- Helper: permitted event relationship counts are now explicitly loaded, business-scoped and permission-filtered before rendering.
- Browser E2E: both populated-runtime and offline-attachment suites now select the current “Commercial position” tab label.
- PHP syntax/formatting: the DemoScenarioSeeder test class closure and unused import were corrected.
- PHPMD: `LandingMediaService::library()` complexity was decomposed into smaller library methods; test classes exceeding the public-method threshold were split into focused classes.

**Evidence:** PHPMD completed successfully on implementation/test refactor head `f64d143c1f137261c356fe3258901dbbdcbc89c6`. Further catalogue controller/test changes followed and triggered fresh exact-head PHPMD, Laravel and Zazu Quality runs; those latest runs must be read against the current `main` head before declaring the batch green. SonarCloud remains skipped and is not counted as a pass.

Physical PC/phone visual review, real installation offline acceptance and restart/reconnect acceptance remain separate founder-operated release checks.


## Founder-requested media correction — 2026-11-23

- Removed the four bundled SVG illustration fallbacks from the landing-image configuration and therefore from the selectable media library. Public defaults now resolve to the bundled photographic assets; no blank default is introduced.
- Added direct, keyboard-accessible image-card assignment to the currently focused landing position while retaining the per-position dropdowns.
- Added per-slot crop frames: hero 16:9, supporting sections 4:3, with cover cropping so the administrator can judge composition before publishing.
- Added a save guard that requires all six slots to have a selected library entry and all six previews to have loaded successfully. Server-side LandingMediaService::save() continues to verify each submitted key exists in the safe library and that the referenced file exists before storing a path.
- Corrected workspace wallpaper rendering by using a valid gradient plus wallpaper image layer and allowing the application shell/content canvas to remain transparent. This addresses the invalid previous background-image composition where a surface colour token was supplied as an image layer.
- Restored the business-selected dashboard picture in the dashboard capability panel. The previous dashboard authority explicitly hid .zazu-dash-hero-image; the new presentation shows the image at useful opacity with a restrained lower text gradient instead of a darkened full-panel treatment.
- Added feature assertions for direct-pick controls, crop-preview hooks and removal of the SVG illustration fallbacks from the rendered media library.

**Verification boundary:** code and tests are committed to main; this record does not claim tests or visual acceptance have passed. Check exact-head GitHub Actions, then visually inspect the six public crops, settings wallpaper and dashboard picture on PC and phone.

## Regression repair and adaptive catalogue hardening — 2026-11-23

- Fixed the failing Laravel assertions and route-helper integrity false positive without weakening the checks.
- Corrected Helper event-record loading to fetch only permitted relation counts, constrained to the active business.
- Updated stale browser selectors to the current neutral inspector tab label.
- Added a dedicated catalogue regression suite for mixed service/rental businesses, service-only businesses and staff without asset-allocation permission.
- Split high-method-count test classes and decomposed the landing-media library builder after PHPMD findings.
- Disabled catalogue entries remain visible to owners with a HIDDEN status so they can be restored; operational staff see active offerings only. No capability or historical event record is deleted.
