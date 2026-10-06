# ZAZU EMP — ENGINEERING ERROR INDEX

This is the normalized engineering diagnostic vocabulary for recurring failures.

## Two error layers

1. Runtime error catalog — user-facing codes such as DB-001, AUTHZ-001 and APP-001.
2. Engineering diagnostic codes — ENG-* codes used by Director to identify the actual failure mechanism, regardless of how the user-facing error is rendered.

Do not collapse these layers.

## Recognition rule

OBSERVE → SEARCH ERROR INDEX → LOAD CASE → FINGERPRINT → CLASSIFY F1–F8 → REVIEW HISTORY → DIAGNOSE → CORRECT → REGRESS → RECORD

A recurring symptom reuses its engineering code and CASE-ZAZU ID unless materially new evidence proves a different mechanism.

## Engineering code format

ENG-FAMILY-NNN

Codes are permanent once assigned. Never recycle a code.

## Core diagnostic families

| Family | Meaning | Typical signatures |
|---|---|---|
| SYNTAX | Source does not parse | unmatched delimiter, parse error, class discovery failure |
| SCHEMA | Runtime database differs from repository contract | missing table/column, migration drift, schema-version mismatch |
| ROUTE | Route registration/ownership/state mismatch | 404, wrong owner, inactive route, navigation dead-end |
| AUTH | Authentication/session failure | missing/expired session, auth-state mismatch |
| AUTHZ | Authorization/permission mismatch | wrong role, direct bypass, missing permission mapping |
| CONTRACT | Implementation and test/UI/API contract disagree | stale assertion, selector drift, response contract, semantic state |
| FIXTURE | Seed/test data violates a real contract | wrong type, UUID, default, sequence, invalid state |
| RUNTIME | Environment/runtime causes application behavior | extension mismatch, cache state, asset/runtime mismatch |
| CI | Workflow/runner execution issue | YAML, job, permission, runner-state failure |
| TOOL | Static analysis/tooling failure | PHPMD, Psalm, lint, dependency/tool version |
| JS | Client bootstrap/interaction defect | stale initializer, undefined function, event wiring |
| CSS | Visual rule ownership/cascade defect | duplicate declaration, cascade conflict, token drift |
| UI | Interaction/layout/visual state defect | hit-test, clipping, overlap, responsive state |
| DATA | Data/invariant corruption or unsafe mutation | duplicate mutation, ownership, partial write |
| RECOVERY | Backup/restore/update safety failure | restore path, rollback, incomplete recovery |
| FLAKE | Non-deterministic or load-sensitive outcome | timeout, race, concurrency, worker sensitivity |

## Current named failures

| Engineering code | Case | Name | F1–F8 | Status |
|---|---|---|---|---|
| ENG-SCHEMA-001 | CASE-ZAZU-0001 | Local schema migration drift | F4 | CLOSED |
| ENG-FIXTURE-001 | CASE-ZAZU-0002 | Offline/restore contract mismatch | F1/F2 | CLOSED |
| ENG-JS-001 | CASE-ZAZU-0003 | Stale shared JavaScript initializer | F1 | CLOSED |
| ENG-SYNTAX-001 | CASE-ZAZU-0004 | Restore command PHP parse failure | F1 | CLOSED |
| ENG-CONTRACT-001 | CASE-ZAZU-0005 | E2E accessible-name/label mismatch | F2 | CLOSED |
| ENG-FIXTURE-002 | CASE-ZAZU-0006 | Seeded idempotency key violates UUID contract | F3 | CLOSED |
| ENG-UI-001 | CASE-ZAZU-0007 | Closed inspector intercepts mobile input | F1 | CLOSED |
| ENG-CONTRACT-002 | CASE-ZAZU-0008 | Browser verification setup/locator drift | F2 | VERIFIED |
| ENG-FLAKE-001 | CASE-ZAZU-0009 | Parallel browser load timeout | F8 | CLOSED |
| ENG-ROUTE-001 | CASE-ZAZU-0010 | Navigation active-route ownership drift | F1 | CORRECTED |
| ENG-UI-002 | CASE-ZAZU-0011 | Calendar runtime lockout/reachability failure | F1/F2 | BLOCKED |\n| ENG-CONTRACT-003 | CASE-ZAZU-0012 | PHPUnit 12-failure stale/brittle contract batch | F2/F7 | CLOSED |\n| ENG-CONTRACT-004 | CASE-ZAZU-0013 | PHPUnit 269-pass/7-fail stale-checkout batch | F2/F7 | CLOSED |

## Known related mechanisms

Secondary codes may be attached when evidence shows they contribute to the same case:

- ENG-CALENDAR-001 — linked to CASE-ZAZU-0012 as the Calendar semantic active-state contract mechanism.
- ENG-CALENDAR-002 — linked to CASE-ZAZU-0011 as the corrected Calendar month/holiday rendering mechanism; CASE-ZAZU-0011 remains BLOCKED pending runtime evidence.
- ENG-CSS-001 — linked to CASE-ZAZU-0012 where canonical visual ownership was the affected contract.
- ENG-AUTHZ-001 — RESERVED; no confirmed current case linkage.
- ENG-RUNTIME-001 — RESERVED; no confirmed current case linkage.
- ENG-CI-001 — RESERVED; no confirmed current case linkage.
- ENG-DATA-001 — RESERVED; no confirmed current case linkage.
- ENG-RECOVERY-001 — RESERVED; no confirmed current case linkage.

These codes are not interchangeable with the primary case code and must only be attached when evidence supports them.

## F1–F8 mapping

| Class | Meaning |
|---|---|
| F1 | Application defect |
| F2 | Verification/test contract defect |
| F3 | Fixture/data defect |
| F4 | Environment/runtime state defect |
| F5 | CI/workflow defect |
| F6 | Tooling/static-analysis defect |
| F7 | Contract drift |
| F8 | Flaky/non-deterministic verification |

The F1–F8 class says where the failure originates. ENG-* says what mechanism it is.

## Fingerprint standard

Build a normalized fingerprint from:

target | layer | workflow phase | expected | actual | assertion/signature | relevant runtime/data state

A changed file, changed line number, or different wording does not create a new failure by itself.

## New-code procedure

When a confirmed mechanism has no existing code:
1. choose the narrowest existing family;
2. assign the next unused number in that family;
3. record the code in the case;
4. add the case to this index;
5. add a regression control when the mechanism can recur;
6. never silently repurpose an older code.

## Runtime catalog boundary

The runtime catalog remains authoritative for safe operator-facing errors. Engineering codes belong in internal diagnostics and repository records unless a deliberate product decision exposes them.
