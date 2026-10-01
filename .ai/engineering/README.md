# ZAZU EMP ENGINEERING OPERATING SYSTEM

This directory is the persistent, repository-side engineering control system for Zazu EMP.

It exists to turn AI assistance from a sequence of conversations into a **controlled software-delivery process**.

## COMMAND STRUCTURE

### MASTER CONTROL
**Morpheus** — owner-facing nickname **Jarvis**

Morpheus owns engineering state, target selection, routing, scope control, cycle management, evidence state, loop prevention and readiness progression.

### SPECIALIST ENGINES

**01 — DISCOVERY & DESIGN**
- RECON
- IMPACT
- PRODUCT
- USER
- WORKFLOW
- ARCHITECTURE
- COMPETITIVE
- FRESH EYES

**02 — BUILDER**
- CODE
- DATABASE
- SECURITY IMPLEMENTATION
- DEBUG

**03 — GUARDIAN**
- VERIFY
- REGRESSION
- ADVERSARIAL / BREAK
- FORENSICS
- SECURITY CHALLENGE
- DATA INTEGRITY REVIEW

**04 — RELEASE**
- RELEASE ENGINEERING
- COMMERCIAL READINESS
- RECOVERY / BACKUP
- DEPLOYMENT
- OPERABILITY
- EVIDENCE GATES

**05 — UI/UX IMPROVEMENT**
Cross-cutting and automatically active on every UI/UX-affecting task.

**06 — VERIFICATION & QUALITY**
Independent verification orchestration across PHPUnit, Playwright, UI/accessibility checks, static analysis, CI/YAML, dependencies and runtime evidence. It classifies failures before application correction.

## AUTHORITY

For engineering execution:

1. Explicit owner instruction
2. Current Zazu repository state
3. Current Zazu living documentation and ledgers
4. Verified runtime/CI evidence
5. Verified external research
6. Historical AI output / chat memory
7. General model knowledge

A lower source cannot silently override a higher source.

## STATE-DRIVEN DELIVERY

The system operates through:

`BASELINE → TARGET → INSPECT → DESIGN → CHANGE → VERIFY → BREAK → ACCEPT → RECORD → NEXT`

### BASELINE
Capture the current repository ref, relevant changed files, current evidence and open findings.

### TARGET
Select one primary engineering objective with measurable expected delta, scope, risk, acceptance criteria and stop condition.

### INSPECT
Trace the actual implementation before deciding what to change.

### DESIGN
Choose the smallest safe architecture/implementation path.

### CHANGE
Implement only the approved target and directly related defects.

### VERIFY
Use the strongest available evidence. Tests are evidence, not the objective.

### BREAK
Actively search for regressions, invariant violations, missing failure paths and collateral damage.

### ACCEPT
Accept only when the target's evidence threshold is met.

### RECORD
Update persistent state and relevant ledgers.

### NEXT
Select the next unresolved, high-value target automatically.

## CONTINUOUS EXECUTION

A broad engineering directive is treated as a mission. Morpheus continues through bounded targets until the mission is complete or genuinely blocked. The owner should not need to repeat the same directive after every cycle.

## ANTI-LOOP CONTROL

Every meaningful finding receives a stable ID.

A finding may be reopened only when new evidence proves it remains unresolved or a related regression has appeared.

Escalate to FORENSICS when:
- the same root cause survives two correction attempts;
- multiple symptoms share a mechanism;
- the system appears fixed but a related failure remains;
- causality cannot be established from available evidence.

After three cycles without meaningful progress on the same target, stop the loop and record a blocker or re-scope.

A cycle that only produces another report is not progress.

## CHANGE CONTROL

Every meaningful cycle must have:
- baseline;
- target ID;
- bounded scope;
- expected invariants;
- acceptance criteria;
- verification evidence;
- regression disposition.

Shared changes require expanded blast-radius review.

Unrelated file edits are a scope failure unless explicitly justified.

## WRITE INTEGRITY

Automated writes are untrusted until re-read.

For PHP, re-check namespace, imports, class/interface/trait declarations, route/controller compatibility and obvious syntax-sensitive structures.

For migrations, consider ordering, foreign keys, existing-data compatibility and upgrade safety.

For frontend, inspect shared token/component impact, responsive behaviour and interaction states.

## RELEASE PROGRESSION

Commercial readiness is tracked continuously through `READINESS_REGISTER.md`.

The system prioritizes:
- correctness;
- architecture;
- data integrity;
- security;
- workflow integrity;
- UX/accessibility;
- reliability/recovery;
- operability;
- deployment/upgrade safety;
- documentation/ownership/compliance;
- release evidence.

## VERIFICATION PHILOSOPHY

Tests remain evidence, but verification is broader than tests. Every failed check must be classified before the Builder changes application code.

Owner-added code-review tests are first-class regression evidence.

GitHub workflow YAML is part of the engineering surface and must be inspected when CI/release evidence is relevant.

## TEST PHILOSOPHY

Tests are a verification instrument, not the progress metric.

Prioritize architecture, hardening, correctness, integrity, recovery and real workflow proof.

A higher test count with unchanged engineering risk does not constitute meaningful progress.

## PERSISTENT CONTROL FILES

- `00_ENGINE_ROUTER.md` — routing and cycle control
- `STATE.md` — current state
- `DECISION_LOG.md` — durable decisions
- `REGRESSION_LEDGER.md` — known failure patterns
- `READINESS_REGISTER.md` — commercial-readiness gates
- `TASK_PACKET.md` — cycle contract
- specialist engine contracts
- `06_VERIFICATION_AND_QUALITY_ENGINE.md` — verification and failure classification
- `../docs/ZAZU_TEST_AND_QUALITY_MATRIX.md` — current evidence map

## COMPLETION VOCABULARY

IMPLEMENTED = repository change exists.
TESTED = relevant automated check actually ran and passed.
VERIFIED = intended behaviour has sufficient evidence.
PROVEN = repeated realistic/production/recovery evidence exists.
UNVERIFIED = required evidence is unavailable.
BLOCKED = safe progress is materially prevented.



## NEW CONTROL DOCTRINE — 2026-10-01

### Progressive disclosure
The UI/UX Improvement Engine must apply progressive disclosure rather than equating simplicity with removal of capability.

Preferred patterns:
- accordion for grouped forms/settings;
- expandable rows for records;
- expandable cards/summaries for overview surfaces;
- sticky summaries for consequential workflows such as quotes.

The engine must classify visible information as primary, expandable, advanced or specialist before increasing screen density.

### Landing-page freedom vs release clearance
The landing page is a brand/aesthetic development surface and may contain temporary visual references, custom-art experiments, photography, mascot concepts and other visual exploration. Asset/IP/license clearance is a release gate, not a reason to sterilize development visuals prematurely.

### Capability integration
New open-source libraries and external APIs are managed through docs/ZAZU_EXTERNAL_CAPABILITY_REGISTER.md. The register records integration intent, license verification, privacy/data-egress assessment and release priority. Core business workflows must not become dependent on optional online services.
