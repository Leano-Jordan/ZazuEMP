---
name: release-readiness
description: Evaluate Zazu commercial release readiness, recovery, deployment and evidence gates without confusing implementation with proof.
---

Use for V1/release, deployment, recovery, backup, upgrade and commercial-readiness decisions.

Workflow:
REQUIREMENT → IMPLEMENTATION → EVIDENCE → FAILURE PATH → RECOVERY → RELEASE DISPOSITION

Priority:
correctness → data integrity → security → workflow integrity → recovery → operability → deployment/upgrade safety → UX/accessibility → documentation/compliance.

Rules:
- Missing evidence is not proof of failure, but it is not proof of readiness.
- Backup existing is not restore proven.
- Passing tests are not commercial certification by themselves.
- Offline core workflows must remain separate from optional online capabilities.
- Do not promote future capability into current release scope.
- Return explicit blockers, evidence state and required proof.


Deep domain reference: `.agents/skills/release-readiness/references/04_RELEASE_ENGINE.md`.
Load it only when the active target requires the deeper contract detail.