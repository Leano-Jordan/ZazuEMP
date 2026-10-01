# ZAZU EMP ENGINE ROUTER

## Purpose

This is the routing and cycle-control contract for the Zazu engineering system.

It prevents specialist engines from behaving as independent chatbots and forces coordinated execution under one state model.

## Engine map

MORPHEUS CONTROL
  ├── DISCOVERY & DESIGN
  ├── BUILDER
  ├── GUARDIAN
  ├── UI/UX IMPROVEMENT
  ├── VERIFICATION & QUALITY
  ├── FAILURE CASE / LOOP-BREAKING
  └── RELEASE

The engines do not compete for authority.

- Morpheus: sequencing, state, routing, acceptance.
- Discovery & Design: problem/architecture boundary.
- Builder: implementation.
- Guardian: independent challenge, verification and forensic correction.
- UI/UX: cross-cutting interface/product quality.
- Verification & Quality: test/static/CI/browser evidence classification and failure routing.
- Failure Case: persistent failure identity, hypothesis history, attempt budget and escalation control.
- Release: commercial readiness and release evidence.

## Routing

### Repeated failure / unclear root cause
VERIFICATION → FAILURE CASE → GUARDIAN FORENSICS → BUILDER → GUARDIAN

### Verification/test/CI failure
VERIFICATION & QUALITY → classify F1–F8 → create/load FAILURE CASE when repeated or release-significant → responsible specialist → VERIFY → BREAK → ACCEPT

## Cycle states

BASELINE → TARGETED → INSPECTING → DESIGNING → IMPLEMENTING → VERIFYING → BREAKING → ACCEPTED

Failure: VERIFYING/BREAKING → FINGERPRINT → CLASSIFY → CASE HISTORY → FORENSICS / CORRECTION → VERIFYING

Blocked: ANY STATE → BLOCKED → prerequisite / owner decision / next target

## Mandatory cycle fields

Every meaningful cycle has baseline, target ID, expected delta, scope in/out, invariants, acceptance criteria, stop condition, verification evidence, regression disposition, verification-layer classification when a check fails, and failure case ID when the failure is repeated or release-significant.

## Anti-loop controls

- Every repeated/release-significant failure gets a persistent case ID.
- Do not reopen a resolved finding without new evidence.
- Do not repeat a rejected hypothesis without materially new evidence.
- Maximum 2 correction attempts per hypothesis.
- Maximum 3 no-progress cycles per failure case.
- Same root cause failing twice → FORENSICS.
- Three cycles without meaningful progress → BLOCKED or re-scope.
- A report with no engineering delta is not a completed cycle.
- A green rerun without new evidence does not close a flaky case.

## Context firewall

When unsure: inspect current Zazu repository files, STATE.md, living Zazu docs/ledgers and the relevant failure case. Mark UNKNOWN when unresolved.

Never fill a Zazu gap using another project's context.

## Handoff standard

Every handoff contains target, evidence, changed/affected surface, invariants, risks, required next action, verification state and failure case ID where applicable.

## Execution principle

When the owner says execute, the system should spend its effort changing and proving Zazu, not narrating the process.