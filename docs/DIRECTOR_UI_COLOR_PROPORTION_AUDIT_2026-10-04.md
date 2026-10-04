# Director UI / Colour / Proportion Audit — 2026-10-04

## Trigger

Owner feedback identified three concrete defects:

1. Zazu's light interface was visually over-dependent on white/near-white surfaces.
2. The colour system had competing blue families rather than one controlled brand hue with restrained supporting colour.
3. Controls and content surfaces were allowed to become wider/larger than the information hierarchy required.

## DesignUp/DesignerUp lesson applied

The reviewed Elizabeth Alli / DesignerUp colour-theory material reinforces a useful UI rule: start from a controlled primary colour, use supporting colours intentionally, and adjust hue/saturation/brightness rather than accumulating arbitrary colour swatches.

For Zazu this maps to:

- **Primary:** cobalt/indigo family, now aligned to `#3949E8` with `#5B5CFF` as a vivid supporting state.
- **Secondary:** sea-glass `#008E88`, reserved for supporting emphasis.
- **Semantic colours:** success/warning/danger remain meaning-specific rather than decorative.
- **Neutrals:** the page plane and cards are deliberately separated instead of treating every surface as white.

## What was wrong in the repository

The final visual layer had accumulated a blue family headed by `#1E40AF` / `#2563EB` while `app.css` already defined the intended cobalt-iris family around `#3949E8`. That was a genuine source of colour-harmony drift.

The layout also used a 1480px content maximum and a 14px/9px surface-control radius system, encouraging a spacious card-heavy appearance. Several controls explicitly used full-width utility classes.

## Director correction executed

The existing `resources/css/zazu-final-visual-sweep.css` was refined in place rather than creating another competing stylesheet.

Changes:

- light page plane moved to `#EEF0F8`;
- cards shifted to a slightly cool off-white `#FBFCFF`;
- card/header layers receive a visible neutral-blue separation;
- final visual blue tokens now align with the cobalt-iris family;
- sea-glass is reinstated as the supporting accent;
- shared content maximum tightened from 1480px to 1320px;
- shared container radii tightened to 10px / 6px;
- general action buttons use compact intrinsic width rather than accidental stretching;
- common form controls cap at 500px, with wide fields capped at 760px;
- filter/resource controls cap at 320px;
- table padding was slightly tightened;
- command surfaces reduced from a 96px minimum to 84px.

Responsive behaviour is preserved: form fields return to full available width on small screens, and authentication's intentional full-width submit remains full-width.

## Regression rule

The visual correction must remain inside the existing final visual authority. Future UI work should not create another global colour layer.

The remaining visual acceptance requirement is rendered testing on desktop, real phone and real tablet. A source-only result is not a final visual sign-off.
