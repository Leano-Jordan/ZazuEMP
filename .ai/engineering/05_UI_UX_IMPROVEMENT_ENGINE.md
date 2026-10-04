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


## HUMAN-EYE / CREATIVE CRITIQUE

This capability is part of the UI/UX engine. It is NOT a separate engine or command hierarchy.

Its purpose is to detect interface problems that can survive functional tests and still be visibly or cognitively poor to a human.

The engine must actively challenge rendered UI rather than merely confirm that it works.

### Human-eye review

For meaningful UI work inspect the rendered interface and evaluate:

- first visual impression;
- information hierarchy;
- scan path;
- grouping and proximity;
- alignment;
- spacing rhythm;
- density;
- whitespace;
- typography hierarchy;
- control prominence;
- navigation clarity;
- state communication;
- responsive composition;
- empty/populated/error states;
- theme consistency;
- commercial polish.

Use evidence-based language. Do not report "I don't like it"; identify the observable reason and user consequence.

### Visual attack questions

Ask:

- What does the eye see first, and is that the correct priority?
- Can the primary action be identified quickly?
- Are secondary actions competing with the primary action?
- Is the user forced to scan too far horizontally or vertically?
- Are related elements visually grouped?
- Is unrelated information visually competing?
- Does anything look cramped, orphaned, stranded or unfinished?
- Does the interface technically fit while still feeling poor?
- Does this look like deliberate Zazu UI or generic/generated CRUD UI?
- What would a first-time business user misunderstand?
- What would a professional SaaS reviewer notice immediately?
- Can the same information be communicated with less cognitive effort?

Do not redesign merely because another treatment is fashionable.

### COLOR SYSTEM GOVERNANCE

Colour is semantic infrastructure, not decoration.

Use one shared token authority for:

- canvas/background;
- surface/panel;
- elevated surface;
- primary text;
- secondary text;
- muted text;
- border;
- focus;
- primary action;
- secondary action;
- success;
- warning;
- danger;
- informational states;
- interactive hover/active states.

Rules:

1. Prefer semantic roles over page-specific colour choices.
2. Maintain a coherent palette relationship across light and dark themes.
3. Do not introduce a new colour when an existing semantic token represents the same meaning.
4. Do not use colour as the only carrier of status or meaning.
5. Check text and control contrast against their actual rendered backgrounds.
6. Keep accent colour subordinate to operational content unless the accent is the intentional primary action.
7. Avoid colour competition between navigation, status, actions and decorative surfaces.
8. New visual tokens require a shared-system reason, not a one-screen preference.
9. Review colour in context: adjacent surfaces, borders, text, icons, disabled states, focus and selected states must form a coherent relationship.
10. Light and dark themes are separate render targets and both require review.

Do not blindly apply a "60/30/10" or similar aesthetic formula. Zazu is an operational workbench; semantic hierarchy, contrast, task clarity and consistency take precedence over decorative ratios.

### FORM WIDTH / COGNITIVE LOAD

Do not stretch every field to fill the available viewport.

Field width should reflect the information being entered.

Prefer:

- compact controls for short values;
- moderate widths for names, codes and identifiers;
- bounded wider fields for addresses and descriptive content;
- full-width only when the content or workflow genuinely benefits from it.

Avoid:

- enormous single-line inputs with large unused interiors;
- long form rows containing unrelated fields;
- wide controls that force unnecessary eye travel;
- inconsistent field widths that destroy grouping.

Use grouping and progressive disclosure before adding more horizontal space.

### TABLE / DATA-DENSITY GOVERNANCE

Tables must be designed for the user's task, not for exposing every available database column.

Before widening a table, ask:

1. What decision is the user making?
2. Which columns are essential to that decision?
3. Which information can be moved into row expansion, an inspector, detail view or contextual action?
4. Which columns are redundant?
5. Can labels or values be shortened without losing meaning?
6. Does the table require horizontal scrolling at the target viewport?
7. Is the user forced into excessive left-right eye tracking?

Default rules:

- keep high-value columns visible;
- keep related values adjacent;
- align numeric values consistently;
- use tabular numerics where comparison matters;
- avoid repeating the same information in multiple columns;
- use compact but readable row density;
- allow secondary detail to move to an inspector/detail surface;
- prefer responsive column prioritisation over squeezing every column into a narrow viewport;
- use horizontal scrolling only when the dataset is inherently wide and no better representation exists;
- if horizontal scrolling is necessary, preserve contextual identification where practical;
- do not make users repeatedly scan from the far left to far right for routine decisions.

A wide table is not automatically a defect. A table is a defect when its width creates avoidable cognitive or operational strain.

### EYE-TRACKING / SCAN-PATH RULE

The interface should support predictable scanning.

Prefer:

- strong left alignment for text;
- consistent columns;
- meaningful proximity;
- clear section boundaries;
- predictable action placement;
- visual grouping;
- progressive disclosure;
- concise labels;
- stable row structures.

Avoid:

- unrelated items sharing the same visual weight;
- repeated full-width containers;
- scattered actions;
- unnecessary centre alignment of operational text;
- excessive horizontal movement;
- long decorative headings that push useful information downward;
- multiple competing "primary" actions.

The goal is not to literally measure eye movement. The goal is to reduce avoidable visual search and cognitive load.

### COMMERCIAL POLISH CHECK

For important screens ask:

> Does this look intentionally designed for a real business, or merely technically assembled?

Look for:

- unfinished spacing;
- generic cards;
- inconsistent control geometry;
- weak hierarchy;
- arbitrary colours;
- excessive shadows/radii;
- awkward empty states;
- crowded toolbars;
- unexplained icons;
- redundant navigation;
- unnecessary visual noise.

Creative improvements must have a user or workflow benefit.

### CREATIVE ALTERNATIVES

When a meaningful weakness is found, propose no more than three materially different alternatives.

For each alternative record:

- problem addressed;
- user benefit;
- implementation complexity;
- regression risk;
- fit with existing Zazu patterns.

Prefer one strong improvement over cosmetic idea generation.

### HUMAN-EYE ACCEPTANCE

A visual target is not accepted merely because:

- PHPUnit passes;
- Playwright passes;
- CSS compiles;
- accessibility assertions pass.

For meaningful UI changes, rendered evidence must show that:

- hierarchy remains clear;
- important content is not unnecessarily wide;
- fields are appropriately bounded;
- tables do not create avoidable eye-tracking strain;
- colour relationships remain coherent;
- responsive layouts preserve the intended task;
- no clipping, overlap or visual regression is present.

When the rendered result is technically valid but materially weak, record a UI/UX finding and route it back through Director.

### SHARED ENGINE SYNCHRONIZATION

UI/UX findings are Director findings.

The UI/UX engine does not create an independent backlog or competing "next action".

It must return:

- finding ID;
- evidence;
- affected surface;
- root/shared-pattern hypothesis;
- severity;
- recommended correction;
- regression risk;
- verification required.

Director reconciles the finding with Builder, Verification, Guardian, Failure Case and Release state.

The next engine action is always derived from the shared Director state.


## VISUAL RECOVERY / NON-DESTRUCTIVE IMPLEMENTATION GATE

The UI/UX engine is an improvement engine, not a visual-reset engine. Its job is to improve the existing product without destroying useful identity, hierarchy or working patterns.

### Baseline first

Before a meaningful visual change:
1. inspect the current rendered target;
2. inspect the existing shared visual authority;
3. inspect recent UI changes when available;
4. inspect repository reference images;
5. record the specific visual characteristics being targeted;
6. identify the smallest shared cause that can address the finding.

Never infer the desired design from the words "modern SaaS" alone.

### Reference-to-implementation rule

Reference images must produce an explicit visual brief before implementation. The brief should identify observable characteristics, not vague adjectives:
- dominant/background surface relationship;
- accent hierarchy;
- contrast relationship;
- typography hierarchy;
- density and whitespace balance;
- navigation/control distinction;
- panel and table treatment;
- responsive composition.

The implementation should adapt these principles to Zazu's existing system rather than replacing Zazu with a generic template aesthetic.

### Anti-demolition rule

Do not perform a broad visual rewrite merely because several screens look imperfect.

Do not simultaneously replace the root palette, typography, spacing, component geometry, shell and responsive layout without evidence of one shared root cause.

Do not solve a visual conflict by adding another competing stylesheet, override chain, !important rule or page-specific palette.

### Visual change budget

Each meaningful UI batch must state:
- target surface;
- intended visual delta;
- shared authority touched;
- expected benefit;
- affected consumers;
- regression risks;
- required rendered views.

If the expected benefit cannot be stated concretely, do not implement the change.

### WORSE-THAN-BASELINE REJECTION

After implementation, compare the rendered result with the recorded baseline.

Reject the change when it materially worsens any of:
- readability/contrast;
- hierarchy;
- navigation distinction;
- primary-action clarity;
- information density;
- useful whitespace balance;
- form usability;
- table scanability;
- responsive composition;
- theme coherence;
- reference alignment;
- commercial polish.

Passing tests does not cancel a visual rejection.

### Recovery procedure

When a visual batch is rejected:
**STOP → capture/retain evidence → isolate the responsible change → restore the previous known-good visual state or revert the responsible change → record the rejected treatment → reassess from the clean baseline.**

Do not layer a second cosmetic patch over a rejected first patch.

### Human-eye acceptance language

The engine must return one explicit visual state:
- BASELINE RECORDED;
- IMPLEMENTED / VISUAL UNVERIFIED;
- VISUAL VERIFIED;
- VISUAL REJECTED / RECOVERY REQUIRED;
- BLOCKED / INSUFFICIENT EVIDENCE.

A source-level statement such as "tokens are correct" is not visual acceptance.


## EVIDENCE-DRIVEN UI AGENT WORKFLOW — RESEARCH-ALIGNED

The engine must operate as a controlled design-development loop, not as a prompt-driven makeover.

### A. Observe before acting

For every meaningful UI batch, gather:
1. current rendered screenshots at defined viewports;
2. relevant reference screenshots/images;
3. current DOM/component structure;
4. shared token/style ownership;
5. recent diff/history for affected UI;
6. existing visual snapshots when available.

Do not infer a visual defect from source code alone when the defect is perceptual.

### B. Write a visual hypothesis

Before implementation state:
- WHAT looks wrong;
- WHY it is wrong;
- WHAT observable change should improve it;
- WHICH component/token owns that change;
- WHAT must remain unchanged;
- HOW the result will be judged.

"Make it more modern/professional" is not an actionable hypothesis.

### C. Work in reversible slices

Prefer small, reviewable batches with one visual objective. Keep changes isolated enough that a regression can be attributed and reverted. Do not mix unrelated shell, typography, palette, component and responsive changes merely because they are all visual.

### D. Compare rendered output, not intent

For stable pages, maintain browser screenshot baselines. Playwright-style screenshot comparison is an appropriate implementation mechanism. Reference snapshots must be generated in a controlled environment and updated deliberately, never automatically after every change.

Pixel differences are evidence, not automatic acceptance. Dynamic regions may be masked or stabilised. Human review remains required for hierarchy, density, readability and commercial polish.

### E. Separate objective gates from human visual judgement

Automated gates should check:
- build/syntax;
- route availability;
- console/runtime errors;
- responsive overflow;
- screenshot regression thresholds;
- accessibility/contrast where automatable;
- relevant workflow tests.

Human visual review should judge:
- hierarchy;
- clarity;
- visual density;
- action distinction;
- consistency;
- reference alignment;
- whether the product actually looks commercially credible.

A green automated suite cannot make an ugly UI green.

### F. Golden-reference discipline

A visual baseline may only be replaced when the intended design change has been explicitly accepted. Never use a changed screenshot to make the screenshot test pass without first establishing why the changed appearance is better.

### G. Escalating autonomy

The engine may operate autonomously only within an established design system and proven component patterns. New visual directions require observation and review first. Autonomy increases after repeated verified improvements; it decreases after regressions.

### H. Change budget / blast radius

The larger the shared blast radius, the stronger the evidence required. Root tokens, typography, shell layout and shared controls are high-blast-radius changes. They require affected-consumer inspection and multi-route rendered verification before acceptance.

### I. Review artifact

Every meaningful UI batch should leave a compact evidence record:
BASELINE → HYPOTHESIS → CHANGE → RENDERED RESULT → AUTOMATED RESULT → HUMAN VISUAL DECISION → ACCEPT / REJECT → NEXT ACTION.

This record is the memory the next agent needs. Do not rely on conversational memory alone.
