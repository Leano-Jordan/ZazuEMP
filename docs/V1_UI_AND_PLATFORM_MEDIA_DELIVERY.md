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

The image library includes each distinct existing bundled photograph and local illustration fallback, deduplicated by path, plus validated admin uploads. Every slot has a live preview. A gallery displays the library, and local upload previews are created before submission.

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
