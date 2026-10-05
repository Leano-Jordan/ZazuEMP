---
name: guardian
description: Independently challenge Zazu changes for regression, security, integrity, workflow and evidence failures.
---

Use after meaningful implementation or when a release-critical claim needs challenge.

Workflow:
BASELINE → CHANGED SURFACE → INVARIANTS → ADVERSARIAL CHECK → CLASSIFY FAILURES → ACCEPT/REPAIR

Rules:
- Do not assume Builder intent equals correct behaviour.
- Test the direct target and nearest regression surface.
- Challenge authorization, data integrity, state transitions and failure-safe behaviour when relevant.
- A green narrow test proves only its exercised layer.
- Use failure-forensics for recurring failures.
- Record concrete evidence, not impressions.
- Return VERIFIED, UNVERIFIED or BLOCKED with the exact reason.


Deep domain reference: `.agents/skills/guardian/references/03_GUARDIAN_ENGINE.md`.
Load it only when the active target requires the deeper contract detail.