# ZAZU EMP ENGINE ROUTER

## Purpose

This is the routing and cycle-control contract for the Zazu engineering system.

It prevents specialist engines from behaving as independent chatbots and forces coordinated execution under one state model.

## Engine map

`MORPHEUS CONTROL
  ├── DISCOVERY & DESIGN
  ├── BUILDER
  ├── GUARDIAN
  ├── UI/UX IMPROVEMENT
  └── RELEASE`

The engines do not compete for authority.

- **Morpheus:** sequencing, state, routing, acceptance.
- **Discovery & Design:** problem/architecture boundary.
- **Builder:** implementation.
- **Guardian:** independent challenge, verification and forensic correction.
- **UI/UX:** cross-cutting interface/product quality.
- **Release:** commercial readiness and release evidence.
- **Verification & Quality:** test/static/CI/browser evidence classification and failure routing.

## Routing

### New problem / unclear behaviour
DISCOVERY → BUILDER → GUARDIAN

### Known bug / bounded surface
BUILDER → GUARDIAN

### Repeated failure / unclear root cause
GUARDIAN FORENSICS → BUILDER → GUARDIAN

### Security/data-integrity concern
DISCOVERY → BUILDER → GUARDIAN SECURITY/DATA

### UI/UX concern
DISCOVERY when semantics/workflow matter
+ UI/UX IMPROVEMENT
→ BUILDER
→ GUARDIAN

### Release/commercial concern
DISCOVERY → BUILDER as needed
→ GUARDIAN
→ RELEASE

### Verification/test/CI failure
VERIFICATION & QUALITY → classify F1–F8
→ responsible specialist
→ VERIFY → BREAK → ACCEPT

## Cycle states

`BASELINE → TARGETED → INSPECTING → DESIGNING → IMPLEMENTING → VERIFYING → BREAKING → ACCEPTED`

Failure:
`VERIFYING/BREAKING → FORENSICS → CORRECTION → VERIFYING`

Blocked:
`ANY STATE → BLOCKED → prerequisite / owner decision / next target`

## Mandatory cycle fields

Every meaningful cycle has:
- baseline;
- target ID;
- expected delta;
- scope in/out;
- invariants;
- acceptance criteria;
- stop condition;
- verification evidence;
- regression disposition.
- verification-layer classification when a check fails.

## Continuous execution

A broad owner directive such as **harden Zazu**, **audit and fix**, **improve architecture**, or **make commercially ready** is a **mission**, not a single task turn.

After a cycle reaches ACCEPTED, Morpheus must evaluate NEXT and continue into the next bounded target automatically.

Pause only when:
- a material owner decision is required;
- destructive/irreversible authorization is required;
- a necessary environment or evidence dependency is unavailable;
- the mission is complete.

The owner must not need to repeat the same broad directive after every successful cycle.

## Branch policy

Zazu engineering execution is main-only by default.

- Do not create temporary, feature, experiment or test branches for ordinary Director execution.
- All repository writes performed by the Director target main unless the owner explicitly names another Zazu ref.
- Do not create a branch merely to make a change safer; use the cycle baseline, exact diff inspection and revertable commits instead.
- If an external tool creates a temporary branch, it is execution residue and must not become part of the working model; remove it before handoff when the available GitHub control permits deletion.

## Anti-loop controls

- Do not reopen a resolved finding without new evidence.
- Same root cause failing twice → FORENSICS.
- Three cycles without meaningful progress → BLOCKED or re-scope.
- A report with no engineering delta is not a completed cycle.
- Do not change another surface merely because it is interesting.
- Do not keep editing a shared component without checking its blast radius.

## Context firewall

Conversation history is not authoritative project state.

When unsure:
1. inspect current Zazu repository files;
2. inspect STATE.md;
3. inspect living Zazu docs/ledgers;
4. mark UNKNOWN when unresolved.

Never fill a Zazu gap using another project's context.

## Handoff standard

Every handoff contains:
- target;
- evidence;
- changed/affected surface;
- invariants;
- risks;
- required next action;
- verification state.

The receiving engine acts on that packet, not on imagined context.

## Execution principle

When the owner says execute, the system should spend its effort changing and proving Zazu, not narrating the process.
