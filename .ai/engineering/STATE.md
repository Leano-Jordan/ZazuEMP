# ZAZU EMP — ENGINEERING CONTROL STATE

## Identity
- Product: Zazu – Event Management Platform
- Repository: Leano-Jordan/ZazuEMP
- Canonical branch: main
- Master ENGINE: Morpheus
- Owner-facing nickname: Jarvis

## Authority
1. Explicit owner instruction
2. Current repository state
3. Current living documentation / ledgers
4. Verified runtime / CI evidence
5. Verified external research
6. Historical records / chat memory

## Current control model
BASELINE → TARGET → INSPECT → DESIGN → CHANGE → VERIFY → BREAK → ACCEPT → RECORD → NEXT

Director owns authority, routing, scope, sequencing, evidence reconciliation and acceptance. Specialist engines are bounded capabilities and do not maintain competing state or acceptance authority.

## Active Director target
DIRECTOR V2 CONTROL-PLANE CLEANUP / HARDENING

Goal: smaller, self-consistent, genuinely testable control plane. No new engineering engines, F1–F8 redesign or application feature work.

### This cycle completed
- Failure-case registry literal escaped-newline corruption repaired.
- Control-plane formatting guard added.
- Director contract CI renamed to Director contract lint.
- Director eval contract validator remains in the lint job.
- PHPUnit 12-failure and 269-pass/7-fail historical batches registered as persistent cases/codes.
- Calendar error family added to engineering taxonomy.
- Reserved engineering codes explicitly marked linked or reserved.
- CASE-ZAZU-0011 moved to BLOCKED after correction/no-progress budget exhaustion.
- October historical state moved to .ai/archive/2026-10.md.
- This STATE file is now the short current-state authority.

## Open / blocked cases
- CASE-ZAZU-0011 — Calendar runtime lockout/reachability: BLOCKED. Owner-authenticated runtime evidence is required. Do not patch further until that evidence exists.
- Other active cases: consult .ai/engineering/FAILURE_CASES.md only when the active target requires them.

## Failure-loop rules
- Load the persistent case before correcting a repeated failure.
- Same fingerprint reuses the existing case.
- Hypothesis renaming does not reset the budget.
- Maximum 2 correction attempts per hypothesis/mechanism.
- Maximum 3 no-progress cycles per case.
- Budget exceeded → STOP PATCHING → FORENSICS / ESCALATION.
- Green rerun without materially new evidence is not closure.

## Current verification boundary
- Repository-side control-plane changes are inspectable through GitHub.
- Live Director behavioural execution is not available through the repository connector.
- Director evals may be structurally validated, but must not be reported as live behavioural PASS without a live runner.
- Local PHPUnit/browser/runtime evidence requires the owner's runtime and must be recorded from actual execution.

## Current application direction
Zazu remains offline-first and commercially focused. Core release assurance prioritizes correctness, data integrity, security, workflow integrity, UX/accessibility, recovery, operability, deployment safety and release evidence.

## Current next application target
After control-plane cleanup: reconcile current CI evidence, then continue the highest-risk offline foundation target. Do not reopen blocked Calendar work without runtime evidence.

## Control-plane cleanup sequence
1. Formatting guard / registry repair / failure registration / taxonomy cleanup — completed.
2. Block exhausted Calendar case — completed.
3. Keep STATE short and archive dated history — completed.
4. Make CLAUDE.md / AGENTS.md point to one canonical control contract where needed.
5. Strengthen eval fixture/disposition validation.
6. Record CI evidence against the exact head.
7. Commit-prefix discipline and weekly archive operation.

## Evidence language
- IMPLEMENTED — repository change exists.
- TESTED — relevant automated check actually ran and passed.
- VERIFIED — intended behaviour has sufficient evidence.
- PROVEN — repeated realistic/production/recovery evidence exists.
- UNVERIFIED — required evidence unavailable.
- BLOCKED — safe progress materially prevented.

## Update rule
After every meaningful cycle:
- reconcile this file;
- update failure cases / regression ledger when a new pattern appears;
- update decision log when a durable rule changes;
- update readiness register when a release gate changes.

Last updated: 2026-10-06
