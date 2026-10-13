# Zazu EMP — Pre-launch image assets

Status: active, self-hosted image set  
Source: original SVG illustrations created for the Zazu EMP pre-launch page  
Location: `prelaunch/images/`  
Public credits: `prelaunch/image-credits.html`  
Reuse licence: Creative Commons Attribution 4.0 International (CC BY 4.0)

## Current illustrations

- `south-african-event-host.svg` — event host / business gathering
- `wedding-celebration.svg` — wedding celebration
- `community-event.svg` — community and youth event
- `live-performance.svg` — live music and AV
- `cultural-festival.svg` — cultural gathering
- `event-planning-team.svg` — planning and coordination

These are original vector illustrations, not photos of real people, Zazu customers or actual events. They are intended to broaden the visual representation beyond catering displays while avoiding unsupported endorsements.

## Reliability and licensing

- The images are stored in the repository and deployed as part of the static Pages artifact.
- The page uses relative asset URLs and a strict `img-src 'self'` Content Security Policy.
- The deployment audit checks that all six assets exist, are SVGs, and are copied into the published artifact.
- No external image host is needed; images should load wherever the landing page itself loads.
- Reuse is allowed under CC BY 4.0 with attribution to Rosscore Labs, a link to the licence, and a note about changes where applicable.
- If photographs replace these illustrations later, verify each image's licence, attribution requirements, and permission to depict the people/events in the intended context.
