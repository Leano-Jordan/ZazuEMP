# Zazu EMP pre-launch site

This directory is a standalone static marketing site intended for GitHub Pages. It is deliberately separate from the Laravel/Vite application so marketing deployment does not replace the product runtime or PWA assets.

## Contents

- `index.html` — landing page and early-access/demo enquiry form
- `styles.css` — responsive styles, using Zazu's existing blue/navy/mint palette
- `script.js` — small progressive-enhancement interactions
- `privacy.html` — enquiry privacy notice
- `thanks.html` — form redirect destination
- `favicon.svg` — simple site-specific mark

The dashboard is a clearly labelled conceptual CSS illustration, not a screenshot of a live customer account. Licensed stock photography is used as CSS background imagery from Pexels and Unsplash; those background images require an internet connection. No remote font dependency is loaded.

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

The page is plain HTML/CSS/JavaScript and has no build step. Serve the `prelaunch/` directory using any local static file server and check:
- mobile widths (320–390 px) and desktop layout;
- nav, CTA intent selection, keyboard navigation and reduced-motion preference;
- required fields and privacy consent;
- the actual external form delivery and redirect, once the recipient is activated.


## Photography and motion

The pre-launch page uses two remote background photographs:
- Catering staff preparing an event outdoors: [Pexels photo 37485594](https://www.pexels.com/photo/catering-staff-preparing-glassware-outdoors-37485594/), used under the [Pexels licence](https://www.pexels.com/license/).
- Formal event table: [Unsplash photo](https://unsplash.com/photos/a-table-set-up-for-a-formal-event-9WlBSDqBlZc), used under the [Unsplash licence](https://unsplash.com/license).

The page includes subtle entrance and floating-card animations, with reduced-motion preferences respected. Images are remotely hosted, so they require an internet connection and may be unavailable if the image provider changes its URL; self-host optimized, licensed copies if offline asset independence is required. The photos are illustrative and do not represent Zazu customers or product screenshots.
