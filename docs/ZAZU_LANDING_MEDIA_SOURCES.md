# Zazu EMP — Pre-launch visual sources

**Current deployment:** static site under `prelaunch/` on GitHub Pages  
**Photo policy:** two bundled Pexels photographs; four hand-authored SVG product UI previews  
**Public credits:** `prelaunch/image-credits.html`

## Bundled photographs

| Placement | Local asset | Source record | Use |
|---|---|---|---|
| Hero | `prelaunch/assets/hero.jpg` | Pexels image source ID 14703692; original download URL recorded in `.github/workflows/vendor-landing-media.yml` | One contextual event photograph |
| Audience section | `prelaunch/assets/sound.jpg` | Pexels image source ID 36856348; original download URL recorded in `.github/workflows/vendor-landing-media.yml` | One contextual event sound/stage photograph |

The images are stored in the repository and served from the same origin as the pre-launch page. The homepage CSP uses `img-src 'self'`; no third-party image CDN is required for the hero or audience sections. The assets reuse the already-vendored image blobs in `public/images/landing/stock/`, rather than fetching the photos at page-load time.

Pexels images can be used for commercial projects subject to the [Pexels licence](https://www.pexels.com/license/) and separate rights considerations. Preserve the explicit no-endorsement rule. Bundling a photograph is not evidence that Zazu owns the photograph.

## Product-led illustrations

The four feature panels in `prelaunch/index.html` are hand-authored SVG interface previews:
- enquiry and quotation pipeline;
- purchasing and job-readiness checklist;
- schedule and resource plan;
- job-cost breakdown with explicitly labelled illustrative values.

These are product illustrations, not screenshots from a live customer account, generated artwork, verified customer records or performance claims. Keep the relevant disclosure visible whenever sample financial or workflow values are shown.

## Maintenance rules

- The landing page must not add a remote photo host merely to populate whitespace.
- Keep all four feature mockups accessible via an SVG role and descriptive label.
- Keep photo credits and licence information on the public image-credits page, not in a long promotional disclaimer between feature sections.
- If a future page adds real Zazu screenshots, use owner-approved captures, remove customer/personal data, optimise the files, self-host the assets and label unreleased functionality accurately.
- The deploy workflow validates local references, content policy, responsive-centering guardrails and the presence of exactly two local photographs plus four SVG previews.
