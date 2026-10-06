# ZAZU EMP — ENGINEERING CONTROL STATE

## Identity
- Product: Zazu – Event Management Platform
- Repository: Leano-Jordan/ZazuEMP
- Canonical branch: main
- Master control: Morpheus / Director
- Owner-facing nickname: Jarvis

## Authority
1. Explicit owner instruction
2. Current repository state
3. Current living documentation / ledgers
4. Verified runtime / CI evidence
5. Verified external research
6. Historical records / chat memory

## Canonical control contract
`.ai/engineering/00_DIRECTOR_ENGINE.md` is the single canonical Director contract.
`.ai/engineering/00_ENGINE_ROUTER.md` is a compatibility pointer only.
Entry files must point to the canonical contract and must not define a competing read order.

## Control loop
IDENTITY → BASELINE → TARGET → ROUTE → INSPECT → CHANGE → VERIFY → BREAK → RECONCILE → ACCEPT/REPAIR → RECORD → NEXT

## Active target
**Browser smoke regression convergence / onboarding authentication evidence**

Completed in this cycle:
- CASE-ZAZU-0012 and CASE-ZAZU-0013 reopened as **CORRECTED / VERIFICATION PENDING**.
- FAILURE_CASES duplicate registry block removed; active index now includes CASE-ZAZU-0010–0013.
- Router-only execution/acceptance rules consolidated into the canonical Director contract.
- Stale canonical-control pointers reconciled.
- Director eval dispositions pinned; owner review declared for `.agents/evals/**`.
- Control-plane formatting guard widened to `.ai/**`, `.agents/**`, `docs/**`, `.github/**`, plus root entry files.
- STATE shortened to current control-plane facts.

## Open / blocked cases
- CASE-ZAZU-0010 — CORRECTED; browser/runtime verification pending.
- CASE-ZAZU-0011 — BLOCKED; owner-authenticated Calendar runtime evidence required. Do not patch further without new evidence.
- CASE-ZAZU-0012 — CORRECTED / VERIFICATION PENDING; current-head PHPUnit/CI evidence required.
- CASE-ZAZU-0013 — CORRECTED / VERIFICATION PENDING; current-head PHPUnit/CI evidence required.
- CASE-ZAZU-0014 — CORRECTED / VERIFICATION PENDING; serial onboarding browser evidence required to classify the desktop session failure.

## Evidence rules
- IMPLEMENTED = repository change exists.
- TESTED = relevant automated check actually ran and passed.
- VERIFIED = intended behaviour has sufficient evidence.
- PROVEN = repeated realistic/production/recovery evidence exists.
- UNVERIFIED = required evidence unavailable.
- BLOCKED = safe progress materially prevented.
- A green narrow check never closes a broader behaviour gate.
- No case is CLOSED without its required evidence.

## Current CI boundary
The earlier browser run for `0a10d05` is not evidence for this final control-plane head. Current-head CI must be observed after this cycle. Record the exact run URL and SHA before closing any pending case.

## Application gates after control-plane cleanup
1. Consume current-head Laravel/quality/PHPMD/Psalm/CodeQL/browser CI results.
2. Close or reclassify CASE-ZAZU-0014 from a serial onboarding run; do not patch authentication/session code from parallel-only evidence.
3. Complete populated commercial workflow traversal.
4. Exercise backup/restore.
5. Exercise populated upgrade/rollback.
6. Complete physical phone/tablet acceptance.
7. Complete legal/privacy operational controls.
8. Complete dependency/licence notice audit.
9. Complete Zazu brand/trade-mark clearance.
10. Final Director re-audit against the selected release head.

Do not reopen blocked Calendar patching without authenticated runtime evidence.

Last updated: 2026-10-06 — active browser target reconciled by Director
