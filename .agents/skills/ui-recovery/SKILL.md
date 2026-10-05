---
name: ui-recovery
description: Safely refine or recover Zazu UI without destructive visual rewrites, duplicate CSS layers, or loss of established visual decisions.
---

Use this skill for UI/UX-affecting work.

1. Establish the rendered/source baseline and identify the canonical owner of the affected visual rule.
2. Read docs/ZAZU_UI_UX_AUDIT.md, docs/ZAZU_TYPOGRAPHY_STANDARD.md, relevant UI recovery/regression records, and .ai/engineering/REGRESSION_LEDGER.md.
3. Search for competing declarations before adding any new rule. Prefer modifying the canonical owner over appending another override.
4. Make the smallest justified visual delta. Do not combine unrelated palette, shell, typography, layout, and responsive changes without evidence of a shared root cause.
5. Protect deliberate Zazu characteristics already accepted by the repository: blue-slate light direction, intentional dark/image-led command surfaces, semantic foreground tokens, compact operational density, responsive navigation ownership, and established Helper behavior.
6. For a suspected regression: STOP → preserve baseline/evidence → identify responsible declaration → restore the affected state → record the failed approach → only then continue.
7. Automated CSS/test success is not visual acceptance. Rendered evidence is required when the target is visual.
8. Update the failure/error index when a UI failure creates a reusable failure signature.

Do not replace deliberate Zazu styling with a generic modern-SaaS treatment merely because it sounds professional.
