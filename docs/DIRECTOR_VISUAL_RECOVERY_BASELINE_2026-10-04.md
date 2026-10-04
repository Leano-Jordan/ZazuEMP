# Zazu Director — Visual Recovery Baseline

Date: 2026-10-04
Repository: `Leano-Jordan/ZazuEMP`
Branch executed: `main`

## Evidence inspected

The complete repository reference collection under `reference ui ux images/` was inspected before the recovery pass. The references establish:

- a corporate blue family from pale blue through deep navy;
- dark, clearly bounded navigation rails;
- visible surface elevation and component states;
- larger, readable typography and labels;
- compact but information-rich dashboard/table layouts;
- explicit distinction between tabs, links, buttons, fields, badges and status;
- deliberate mobile-first composition;
- strong calendar/table information density;
- hospitality/event imagery used as supporting visual context rather than as the application surface itself.

The provided current-state screenshots were also treated as the visual baseline:

- mobile business setup showed pale field cards, extremely weak label/text contrast, excessive vertical whitespace and poor control grouping;
- desktop quotes showed a washed-out application canvas, weak shell hierarchy, tiny navigation controls and a large low-information command region.

## Director recovery decision

The previous visual-sweep stack is no longer treated as a collection of incremental patches. `resources/css/zazu-final-visual-sweep.css` is now a compact canonical final visual layer consuming the shared tokens in `resources/css/app.css`.

No functional Blade workflow was rewritten for this pass. The recovery is deliberately concentrated in the shared visual foundation so the same correction propagates across the product.

## Foundation contract

### Identity
Primary: `#5592FC`
Primary deep/hover: `#416AD7`
Primary light: `#6FA4FF`
Primary pale: `#9EC8FF`
Primary shadow: `#C2DCFF`
Deep blue page foundation: `#395886`
Secondary accent: teal `#0E8F83` (light) / `#35C7B8` (dark)

### Surfaces
Light mode uses blue-tinted surfaces and fields. Application backgrounds are not pure white.

Dark mode uses layered navy/blue surfaces with light foreground text.

### Interaction hierarchy
Buttons are physical filled/outlined controls.
Tabs live inside a segmented navigation group with a distinct active state.
Ordinary content links remain text links with underlines.
Form controls have visible borders, filled blue-tinted fields, readable labels and a clear focus ring.
Navigation uses a dark rail and obvious active state.
Statuses retain semantic colours instead of borrowing the primary blue.

### Density
Content width is capped at 1320px.
Command/hero regions are compact and information-led.
Cards use restrained 10px radii and visible elevation.
Lists and tables use denser row rhythm.
A form is one work surface; individual fields are not separate floating cards.

### Responsive
Mobile keeps usable 44–46px controls.
Section tabs remain available as a horizontally scrollable group rather than disappearing.
Forms collapse to a single column at phone width.
The existing mobile drawer/navigation architecture remains intact.

## Verification status

Source-level verification completed for the shared visual system.

Rendered browser verification is **not claimed** because this execution environment cannot access the user's local `127.0.0.1` Zazu runtime.

The current screenshots are therefore treated as **pre-recovery evidence**, not approved golden screenshots. Actual screenshot baselines should only be locked after the recovered UI is rendered and visually approved on the target desktop and phone widths.

## Non-regression rule for future agents

Before any further UI change:

1. inspect the current approved screenshots;
2. inspect the repository references;
3. change the existing canonical visual system rather than adding another visual-sweep file;
4. render desktop + mobile in both themes where applicable;
5. reject any result that is less readable, less differentiated or more spacious than the approved baseline.

## Refinement pass — 2026-10-04

The first recovery established the palette but over-applied the blue canvas to light-mode application content. That produced the wrong visual relationship: light cards floating on a medium-blue page with white page typography.

Refinement decision:
- light mode canvas is now a very pale blue-gray (#E7EFF7);
- light application surfaces are near-white blue-tinted (#F7FAFD);
- the sidebar remains deep navy;
- the topbar is a light elevated surface in light mode;
- page/content typography is dark ink;
- blue is concentrated into navigation states, primary actions and command/hero surfaces;
- command/hero blocks are compact and visibly blue rather than oversized pale rectangles;
- form fields are explicitly flat inside the form surface, preventing each field from becoming a separate floating card;
- mobile controls remain touch-sized while information density is improved.

This is the preferred light-mode hierarchy going forward: navy shell → pale blue canvas → elevated light surfaces → blue action accents.
