---
name: failure-forensics
description: Identify, fingerprint, classify, and break recurring Zazu failures without repeating rejected diagnoses or symptom patches.
---

Use this skill whenever a check fails, an owner reports a recurring problem, or an existing failure case may have returned.

1. Search .ai/engineering/ERROR_INDEX.md for the observable symptom, error code, route, assertion, component, or failure family.
2. Load the matching CASE-ZAZU entry from .ai/engineering/FAILURE_CASES.md when one exists. Reuse the case unless materially new evidence proves a distinct failure.
3. Record the engineering diagnostic code, F1–F8 classification, exact expected/actual result, and normalized fingerprint.
4. Review previous hypotheses, experiments, corrections, rejected approaches, and attempt budget before proposing another correction.
5. Distinguish application, verification/test, fixture/data, environment/runtime, CI/workflow, tooling/static-analysis, contract-drift, and flaky/non-deterministic causes.
6. Move to the next diagnostic layer when the current hypothesis fails. Do not repeat a rejected hypothesis without materially new evidence.
7. Maximum default budget is 2 correction attempts per hypothesis and 3 no-progress cycles per case. Then STOP PATCHING → FORENSICS / ESCALATION.
8. A hypothesis rename, wording change, changed line number, or cosmetic variation does not reset the attempt budget when the underlying mechanism and fingerprint are unchanged.
9. A new hypothesis must state what materially new evidence distinguishes it from prior rejected hypotheses. If it cannot, treat it as the same hypothesis/mechanism.
10. When a new failure is confirmed, assign the next unused ENG-* diagnostic code from .ai/engineering/ERROR_INDEX.md and add the code to the persistent case record.
11. Never use an engineering ENG-* code as a substitute for Zazu's user-facing runtime error catalog.
12. Closure requires root-cause evidence plus the required verification layer(s); a green rerun alone is not enough.

The purpose is not merely to fix today's failure. It is to make the same failure recognizable next time.

Deep domain reference: .agents/skills/failure-forensics/references/07_FAILURE_CASE_ENGINE.md.
Load it only when the active target requires the deeper contract detail.