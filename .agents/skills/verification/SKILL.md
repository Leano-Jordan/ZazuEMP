---
name: verification
description: Classify and verify Zazu tests, browser checks, static analysis, CI, runtime and evidence claims before acceptance.
---

Use for verification planning, failed checks and evidence review.

Workflow:
ASSERTION → EXECUTION → OBSERVATION → CLASSIFICATION → EVIDENCE → REGRESSION → DISPOSITION

F1–F8:
F1 application, F2 test/verification, F3 fixture/data, F4 environment, F5 CI/workflow, F6 tooling/static analysis, F7 contract drift, F8 flaky/non-deterministic.

Rules:
- Do not patch application code until the failure layer is classified.
- Green rerun without new evidence does not close a recurring failure.
- Keep test contracts that protect real invariants.
- Distinguish IMPLEMENTED, TESTED, VERIFIED, PROVEN, UNVERIFIED and BLOCKED.
- For UI, automated pass does not replace rendered acceptance.
- Route recurring failures through failure-forensics.


Deep domain reference: `.agents/skills/verification/references/06_VERIFICATION_AND_QUALITY_ENGINE.md`.
Load it only when the active target requires the deeper contract detail.