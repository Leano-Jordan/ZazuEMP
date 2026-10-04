# Director Engine Self-Audit — 2026-10-04

## Scope

Audit of the Zazu repository-side engineering control system after the owner reported repeated destructive UI refinement.

Inspected:
- `.ai/engineering/00_DIRECTOR_ENGINE.md`
- `.ai/engineering/05_UI_UX_IMPROVEMENT_ENGINE.md`
- `.ai/engineering/06_VERIFICATION_AND_QUALITY_ENGINE.md`
- `AGENTS.md`
- engine-related search results across `.ai/engineering`, current UI audits and regression records.

## Root control defect

The existing engine contracts already contained strong UI quality language, shared-token governance and a requirement for rendered visual review. The missing control was an explicit **baseline comparison + worse-than-baseline veto + recovery procedure**.

Without that gate, an agent could satisfy:
- source validity;
- CSS compilation;
- automated tests;
- token consistency;

while still producing a materially worse visual result.

That is the control failure behind the destructive-loop risk.

## Changes executed

### Director
Added mandatory UI safety controls:
- visual baseline recording before meaningful UI changes;
- reference-image inspection as evidence;
- smallest justified visual delta;
- WORSE-THAN-BASELINE VETO;
- recovery-before-repatching procedure;
- explicit visual acceptance states;
- Director-only final visual acceptance.

### UI/UX Improvement Engine
Added:
- baseline-first workflow;
- reference-to-implementation visual brief;
- anti-demolition rule;
- visual change budget;
- worse-than-baseline rejection;
- non-stacking recovery procedure;
- explicit visual acceptance states.

### Verification & Quality Engine
Removed a duplicated `RENDERED HUMAN-VISUAL EVIDENCE` contract discovered during this audit. The earlier, more complete section remains authoritative.

## Self-audit findings

### PASS — command hierarchy
Director remains the sole entry point and acceptance authority.

### PASS — UI ownership
UI/UX remains a capability, not a competing backlog or command hierarchy.

### PASS — shared visual authority
The engine still requires one authoritative token system and discourages competing palettes/override chains.

### PASS — regression discipline
The new visual veto now explicitly prevents automated success from overriding a materially worse rendered result.

### PASS — recovery discipline
Rejected visual work must be restored or isolated before another cosmetic attempt.

### PASS — reference discipline
Repository reference images are now an explicit input to the visual brief rather than optional inspiration.

### PASS — verification semantics
`IMPLEMENTED`, `TESTED`, `VERIFIED`, `PROVEN` and visual verification remain distinct.

### FINDING — runtime proof
The engine changes themselves were source-inspected after writing, but no local browser render was available through this execution. Therefore this control-plane change is **IMPLEMENTED / SOURCE-VERIFIED**, not runtime-proven.

### FINDING — existing CSS debt
Repository search still finds `!important` and visual-sweep-era CSS. The engine now explicitly forbids using additional override layers as the default solution, but this audit does not claim that all existing CSS debt is already removed.

## Acceptance

The engine is materially safer for UI work than before this change.

The critical behavioural change is:

**BAD UI → diagnose → bounded change → render → compare → reject/restore if worse**

instead of:

**BAD UI → broad rewrite → tests pass → declare success → patch the new damage.**

## Next engine target

Before any further broad Zazu UI redesign:
1. establish a real rendered baseline;
2. inspect the repository reference-image set;
3. identify the single visual authority currently loaded by the build;
4. select one bounded surface;
5. render desktop/mobile and light/dark where applicable;
6. apply the new veto;
7. only then continue.

No broad UI rewrite should be authorized until that evidence loop succeeds.


## Research update — effective AI-assisted UI engineering

External research confirms the direction of the hardened engine:
- Playwright supports committed screenshot baselines and repeatable visual comparisons; its documentation warns that rendering varies by environment, so baseline generation must be controlled. Reference updates are deliberate rather than automatic. (Playwright documentation reviewed 2026-10-04.)
- GitHub recommends required status checks and protected branches for enforcing validation before merge. Checks can represent builds, tests and deployments, but they do not replace human review of the result. (GitHub documentation reviewed 2026-10-04.)
- Recent agent-governance research reinforces constrained authority, strong context and progressive autonomy rather than unrestricted autonomous modification.

### Applied to Zazu
The Director/UI engines now explicitly use:
OBSERVE → BASELINE → HYPOTHESIS → BOUNDED CHANGE → RENDER → AUTOMATED CHECKS → HUMAN VISUAL REVIEW → ACCEPT/REJECT → RECORD → NEXT.

This is the intended operating model for the next Zazu visual recovery pass.
