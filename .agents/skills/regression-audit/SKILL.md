---
name: regression-audit
description: Verify Zazu changes against their affected surface, protected contracts, failure history, and regression controls.
---

Use this skill after any meaningful implementation, test-contract change, or shared-component change.

1. Re-fetch the changed files from current main and inspect the actual final source.
2. Compare changed files with intended scope and identify shared blast radius.
3. Verify the direct target first, then its protected contracts and nearest dependent surfaces.
4. Classify evidence precisely: IMPLEMENTED, TESTED, VERIFIED, PROVEN, UNVERIFIED, or BLOCKED.
5. For failures, use failure-forensics instead of creating an unrelated patch.
6. Consult .ai/engineering/REGRESSION_LEDGER.md and .ai/engineering/FAILURE_CASES.md before declaring a known regression family safe.
7. Do not weaken a regression test simply to make it green. Update a test contract only when repository evidence proves the old assertion is stale or technically invalid.
8. For shared UI, inspect light/dark, responsive, interaction, accessibility, and component-owner contracts.
9. For data/finance workflows, verify authoritative data flow and failure-safe state transitions.
10. Record new evidence, newly discovered regressions, and durable controls in repository-side state when the cycle materially changes project knowledge.

A verification result is evidence about the layer tested, not proof of the whole product.
