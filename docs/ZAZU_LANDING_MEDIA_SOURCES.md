# Zazu EMP — Pre-launch photography sources

**Current host:** Pexels image CDN (`images.pexels.com`)  
**Attribution page:** `prelaunch/image-credits.html`  
**Role:** illustrative photography for the Zazu EMP pre-launch site

The current page uses real Pexels photographs, not generated artwork. The public media credits page links to source pages and the Pexels licence. The photos are illustrative only and do not imply customer use or endorsement.

## Source register

| Placement | Subject | Creator/source | Pexels source page |
|---|---|---|---|
| Hero and audience | Community gathering in South Africa | Blinkiing Studio | https://www.pexels.com/photo/crowd-on-a-bazaar-in-south-africa-10807180/ |
| Feature: plan the job | People reviewing event-planning material | RDNE Stock project | https://www.pexels.com/photo/two-people-looking-at-an-event-planner-paper-7648033/ |
| Feature: stay in step | Live music and audience | Josh Sorenson | https://www.pexels.com/photo/people-gathering-at-concert-894566/ |
| Feature: costs and visibility | Business meeting / conference attendees | Matheus Bertelli | https://www.pexels.com/photo/team-of-workers-in-meeting-15141493/ |

Four source photographs are reused across five page placements. Requests are made to HTTPS `images.pexels.com/photos/...` URLs. GitHub Pages does not bundle these remote images; availability depends on the image host and visitor connection.

## Reuse and presentation safeguards

- The public `prelaunch/image-credits.html` page links to Pexels' licence: https://www.pexels.com/license/.
- Photography is illustrative only; do not claim pictured people are Zazu customers, partners or endorsers.
- Do not replace these photographs with generated illustrations. Keep the page photo-led until approved product screenshots are available.
- The CSP and deploy audit allow remote images only from `images.pexels.com` beneath `/photos/`. Review policy and workflow if the host changes.
- Do not hotlink extra photos merely to fill whitespace. Choose imagery that supports the section's message and remains relevant to South African event-related businesses.
- Recheck current Pexels terms and source pages before reuse, cropping, modification or redistribution.

The image CDN is a runtime dependency. If an image fails on the published site, verify its exact URL in the browser/network panel before changing assets or provider.
