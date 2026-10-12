# Zazu EMP pre-launch site

This directory is a standalone static marketing site intended for GitHub Pages. It is deliberately separate from the Laravel/Vite application so marketing deployment does not replace the product runtime or PWA assets.

## Contents

- `index.html` — landing page and early-access/demo enquiry form
- `styles.css` — responsive styles, using Zazu's existing blue/navy/mint palette
- `script.js` — small progressive-enhancement interactions
- `privacy.html` — enquiry privacy notice
- `thanks.html` — form redirect destination
- `favicon.svg` — simple site-specific mark

The hero uses a clearly labelled conceptual CSS dashboard illustration, not a screenshot of a live customer account. The audience section uses three bundled, licensed temporary photographs as labelled cards—not as full-section wallpaper. The Pages workflow copies those images into the deployed artifact, so the page does not request images from third-party hosts at runtime. Their provenance and temporary-use limitations are recorded in `docs/ZAZU_LANDING_MEDIA_SOURCES.md`. No remote font dependency is loaded.

## Publish

The workflow at `.github/workflows/deploy-prelaunch.yml` deploys only this directory to GitHub Pages when files under `prelaunch/` or the workflow itself change on `main`.

1. In repository **Settings → Pages**, set the build/deployment source to **GitHub Actions** if it is not already selected.
2. Ensure GitHub Actions are permitted for this repository.
3. Check the **Actions → Deploy Zazu pre-launch site** run.
4. The expected project-page URL is `https://leano-jordan.github.io/ZazuEMP/`; confirm the actual URL in the workflow deployment output and repository Pages settings.

## Lead capture — activation required

The form currently posts to FormSubmit and is addressed to `rosscorelabs@outlook.com`. FormSubmit requires recipient verification/activation before first use. This has **not** been verified by this repository change.

Before public promotion:
1. Confirm the recipient mailbox is correct and controlled by the site owner.
2. Complete FormSubmit's recipient activation using a real test submission.
3. Verify delivery, spam handling, the confirmation redirect and required-field validation on desktop and Android.
4. Confirm the service's current privacy/retention terms and ensure the privacy notice matches the intended operation.
5. If a more controlled lead pipeline is required, replace the form service with a dedicated backend/CRM endpoint; do not put API keys in this static site.

GitHub Pages itself does not store form submissions. A successful page deployment is not proof that email delivery is active.

## Local checks

The page is plain HTML/CSS/JavaScript and has no build step. For a source-level local preview, serve the repository root and open `/prelaunch/` (the source HTML references the bundled imagery in `public/`; the Pages workflow rewrites those paths into the deployment artifact). Then check:
- mobile widths (320–390 px) and desktop layout;
- nav, CTA intent selection, keyboard navigation and reduced-motion preference;
- required fields and privacy consent;
- the actual external form delivery and redirect, once the recipient is activated.


## Visual policy and motion

The software preview is illustrative only. Do not present its sample counts, event names or status labels as live customer data or as proof that every shown workflow is released. Before wider promotion, replace or supplement it with owner-approved screenshots from the actual Zazu product and an approved social-sharing image. Keep genuine UI screenshots self-hosted, compressed, cropped for the target viewport, and free of customer/personal data. Do not reintroduce unrelated stock photographs merely to fill space.

The page uses restrained interface animation and respects reduced-motion preferences. The product preview is an honest conceptual UI illustration; the supporting event photography is self-hosted from the repository’s documented temporary stock library. Replace those photos with Zazu-owned or fully cleared South Africa-relevant imagery before commercial promotion.

## Local checks

In addition to desktop/mobile visual review, verify that the page loads without requests to third-party image hosts, that all local links/assets resolve, and that Content Security Policy does not block the stylesheet, script, favicon or enquiry form.