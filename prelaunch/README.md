# Zazu EMP pre-launch site

This directory is a standalone static marketing site for GitHub Pages, separate from the Laravel/Vite application.

## Contents

- `index.html` — landing page and early-access/demo enquiry form
- `styles.css` — responsive layout, visual system and motion
- `script.js` — section navigation, scroll progress, scroll reveals and form interaction
- `privacy.html` — enquiry privacy notice
- `thanks.html` — form redirect destination
- `image-credits.html` — public photograph sources and licence notes
- `favicon.svg` — site mark

The hero contains an explicitly labelled conceptual interface preview, not a screenshot of a live customer account. Its example work and statuses must not be represented as real customer records or proof that every depicted workflow is released. The page uses real photographs served from Pexels' image CDN. Source pages and licence information are listed on `image-credits.html`; four distinct source photographs are reused across five placements. Pexels images are remote dependencies, not bundled assets, so they require access to `images.pexels.com`. The homepage Content Security Policy and deploy audit permit that host and no other external image host. The site has no remote font dependency and does not use generated artwork.

## Publish

The workflow at `.github/workflows/deploy-prelaunch.yml` deploys only this directory to GitHub Pages when files under `prelaunch/` or the workflow change on `main`.

1. In repository **Settings → Pages**, set the deployment source to **GitHub Actions** if it is not already selected.
2. Ensure GitHub Actions are permitted for this repository.
3. Check the **Actions → Deploy Zazu pre-launch site** run.
4. Confirm the published URL from deployment output and repository Pages settings. The expected URL is `https://leano-jordan.github.io/ZazuEMP/`.

## Lead capture — activation required

The form posts to FormSubmit and is addressed to `rosscorelabs@outlook.com`. Recipient verification/activation and end-to-end delivery have not been proven by source-level checks. Before public promotion, submit a real test from desktop and Android, verify delivery and spam handling, check required-field/consent validation, confirm the redirect and review the provider's current privacy/retention terms. GitHub Pages does not store submissions. Never put private API keys in this static site.

## Responsive, accessibility and visual checks

Review desktop, tablet and phone widths (including 320–390px and the 391–700px breakpoint), keyboard focus, reduced-motion preferences, page centring, horizontal overflow, local links, form state and image loading. Fix overflow at its source; root-level clipping is only a safety boundary, not a substitute for correct sizing. Keep one owner for each responsive rule rather than appending blanket override blocks.

Motion should support the scan path: a restrained hero sequence, finite preview entrances, hover feedback and scroll reveals. Avoid constant floating/shimmering decoration, huge stagger delays, autoplay content or movement that competes with the message. Respect `prefers-reduced-motion`.

Photographs illustrate types of occasions Zazu is designed to support. They do not imply pictured people, event organisers or venues use or endorse Zazu. Preserve source notes and review relevant terms before reuse. Prefer actual, owner-approved Zazu screenshots when available; keep them self-hosted, compressed and free of customer/personal data.

## Security and content policy

- Keep the strict Content Security Policy and CSS/JavaScript external to the HTML.
- Do not add inline style attributes or inline scripts.
- Do not introduce fonts, analytics or image hosts outside the documented allowlist without reviewing privacy and deployment checks.
- Do not include credentials, personal customer data, fake testimonials, invented social proof or unverified availability claims.
