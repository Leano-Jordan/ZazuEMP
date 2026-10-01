# ZAZU EMP UI/UX IMPROVEMENT ENGINE

## Mission

Continuously improve the interface so Zazu feels like one deliberate commercial product rather than a collection of generated screens.

UI/UX is a product-quality capability, not decorative work.

## Activation

Automatically active for:
- UI/UX work;
- frontend implementation;
- navigation;
- dashboards;
- forms;
- tables;
- responsive behaviour;
- accessibility;
- interaction design;
- visual-system changes;
- visible state/feedback changes;
- usability complaints.

## Responsibilities

### INFORMATION HIERARCHY
Every page should make clear:
- where the user is;
- what record/context is active;
- what matters now;
- what actions are primary;
- what happens next.

### OPERATIONAL USABILITY
Optimize recognition over recall, contextual actions, minimal unnecessary navigation, clear feedback and recoverable errors.

### VISUAL SYSTEM
Zazu must have one authoritative design-token system. Do not append another visual sweep that redefines the same root tokens.

The shared visual authority owns:
- page/canvas surfaces;
- card/panel surfaces;
- text hierarchy;
- border hierarchy;
- primary/secondary/semantic colours;
- focus states;
- typography roles;
- spacing and control dimensions;
- light/dark theme mappings.

A visual change must first modify the shared authority, then inherit through components. Hardcoded component colours are allowed only for genuinely content-specific imagery or semantic exceptions with documented contrast intent.

Typography must be judged as a hierarchy, not as isolated font sizes: page title → section title → body → supporting text → labels/metadata. Small or low-contrast text that carries operational meaning is a defect.

Preserve the current Zazu shared blue reference system only through that single authority.

Prefer disciplined spacing, semantic colour, strong typography hierarchy, structural separation where needed, restrained depth and meaningful interaction states.

Avoid card soup, button soup, decorative chrome, repetitive generic grids, unnecessary pills, excessive animation and invented placeholders.

### RESPONSIVE BEHAVIOUR
Use container queries for reusable component behaviour where appropriate. Use viewport media queries when behaviour genuinely depends on viewport/device/shell/accessibility preference.

### ACCESSIBILITY
Review keyboard use, focus visibility, labels/instructions, contrast, state feedback, touch targets and reduced motion.

## VISUAL REGRESSION RULE

For meaningful visual work, verify both themes and at least desktop + mobile layouts. Check:
- foreground/background contrast;
- typography weight and size hierarchy;
- border consistency and spacing rhythm;
- navigation/header alignment;
- focus/hover/active states;
- overflow/clipping;
- responsive stacking;
- semantic colour meaning.

Static source checks do not close the visual gate by themselves; rendered browser evidence is required.

## SHARED-PATTERN RULE

Before creating a new UI pattern:
1. search existing Zazu UI;
2. reuse or improve the shared pattern where appropriate;
3. avoid near-duplicate components;
4. review shared-change blast radius.

## UX REGRESSION RULE

A visual improvement that breaks workflow is a failed change.

Guardian should consider layout, interaction, navigation, feedback, mobile, theme and accessibility regression for meaningful UI work.

## CONTINUOUS IMPROVEMENT

When a systemic weakness appears, improve the shared source instead of repeatedly patching individual screens.

Do not force redesign when the existing structure already works.
