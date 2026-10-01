# ZAZU EMP ENGINE ROUTER

## Purpose

This is the routing and cycle-control contract for the Zazu engineering system.

It prevents specialist engines from behaving as independent chatbots and forces coordinated execution under one state model.

**Director/Morpheus is the single entry point.** Specialist engines are synchronized capabilities, not independent command hierarchies.

No specialist engine owns a competing backlog, state model, acceptance decision or next-action authority.

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

## Director single-entry and synchronization contract

Every execution begins at **Morpheus / Director**.

Director establishes and maintains the shared cycle state:

- repository/ref;
- baseline;
- objective;
- target ID;
- scope in/out;
- current evidence;
- findings;
- failure cases;
- changed surfaces;
- verification state;
- visual evidence state;
- regression disposition;
- readiness impact;
- next target.

Specialist engines may inspect, reason and recommend within their authority, but they must return their findings to Director.

The synchronized handoff is:

`DIRECTOR → SPECIALIST → DIRECTOR → BUILDER → VERIFICATION → DIRECTOR → GUARDIAN → DIRECTOR → ACCEPT/REPAIR → RECORD`

A specialist may not silently continue from stale state after another engine changes the repository or evidence.

After every meaningful implementation or verification boundary, Director re-establishes current state before authorizing the next specialist action.

### UI/UX synchronization

UI/UX automatically activates for interface-affecting work.

Human-eye / creative critique is an internal capability of UI/UX, not a separate engine.

UI/UX must challenge:

- colour relationships;
- typography hierarchy;
- field widths;
- table width and scan burden;
- information density;
- whitespace;
- alignment;
- responsive composition;
- navigation hierarchy;
- visual consistency;
- commercial polish.

Verification provides rendered evidence.

Guardian challenges regressions.

Director reconciles all three before acceptance.

## Director single-entry and synchronization contract

Every execution begins at **Morpheus / Director**.

Director maintains the shared state: repository/ref, baseline, objective, target ID, scope, invariants, findings, failure cases, changed surfaces, verification state, visual evidence, regression disposition, readiness impact and next target.

Specialist engines are synchronized capabilities. They do not maintain competing state, backlogs, acceptance decisions or next-action authority.

Handoff rule:
DIRECTOR → SPECIALIST → DIRECTOR → BUILDER → VERIFICATION → DIRECTOR → GUARDIAN → DIRECTOR → ACCEPT/REPAIR → RECORD

After every meaningful implementation or verification boundary, Director re-establishes current repository/evidence state before authorizing the next action.

## UI/UX human-eye synchronization

Human-Eye / Creative Critique is an internal capability of UI/UX, not a separate engine.

For UI-affecting work, UI/UX must challenge semantic colour, theme relationships, typography hierarchy, form widths, table width, avoidable horizontal eye tracking, information density, scan path, responsive composition, visual consistency and commercial polish.

Verification supplies rendered evidence. Guardian challenges regression. Director reconciles all evidence.

## Routing

### Repeated failure / unclear root cause
VERIFICATION → FAILURE CASE → GUARDIAN FORENSICS → BUILDER → GUARDIAN

### Verification/test/CI failure
VERIFICATION & QUALITY → classify F1–F8 → create/load FAILURE CASE when repeated or release-significant → responsible specialist → VERIFY → BREAK → ACCEPT

## Cycle states

BASELINE → TARGETED → INSPECTING → DESIGNING → IMPLEMENTING → VERIFYING → VISUAL-CRITIQUE → BREAKING → ACCEPTED

For UI-affecting work, VISUAL-CRITIQUE is part of the same cycle and does not create a second workflow.

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

## Synchronized-engine rule

All engines operate on the same Director state.

They must not:

- create parallel project truth;
- invent a competing task queue;
- treat historical chat output as current state;
- assume another engine's work is complete without evidence;
- declare final acceptance independently;
- continue from a stale repository baseline.

When a specialist discovers a new issue:

`OBSERVE → RECORD IN DIRECTOR STATE → ROUTE → CORRECT → VERIFY → RECONCILE`

The next engine always consumes the updated state.

## UI human-eye quality rule

A screen may pass automated testing and still fail human-eye review.

For meaningful UI work, the Director must route through UI/UX human-eye critique and rendered verification.

The review specifically challenges:

- unnecessary wide fields;
- unnecessarily wide tables;
- avoidable horizontal eye travel;
- poor colour hierarchy;
- competing accents;
- weak contrast;
- excessive visual noise;
- generic/generated visual patterns;
- unclear primary actions;
- cramped or unfinished composition.

The objective is not subjective perfection. It is reduced cognitive load, clearer task hierarchy, coherent visual language and commercially credible presentation.


## Synchronized-engine rule

All engines consume and return the same Director state.

They must not:
- create parallel project truth;
- invent a competing task queue;
- rely on stale repository state;
- declare final acceptance independently;
- continue after another engine changes the relevant source/evidence without reconciliation.

A new specialist finding follows:
OBSERVE → RECORD IN DIRECTOR STATE → ROUTE → CORRECT → VERIFY → RECONCILE.

A UI may pass automated tests and still fail human-eye review. Visual acceptance therefore requires rendered evidence where the target materially changes the interface.


## SaaS-readiness architecture gate — 2026-10-02

Director now treats hosted SaaS readiness as an architectural readiness concern, not permission to introduce distributed infrastructure prematurely.

For material architecture changes, Director routes through:

`DIRECTOR → DISCOVERY/ARCHITECTURE → BUILDER → VERIFICATION → GUARDIAN → DIRECTOR → ACCEPT/RECORD`

The architecture review must establish:
- evidence for the problem being solved;
- authoritative source of state;
- business ownership and parent/child invariants;
- transaction/idempotency/concurrency behaviour;
- offline/local-first impact;
- hosted-SaaS migration impact;
- failure and recovery behaviour;
- operational complexity introduced;
- replacement/exit path.

Preferred evolution:

`MODULAR MONOLITH → MEASURE → OPTIMISE → TARGETED CACHE/QUEUE → SCALE → EXTRACT ONLY WHEN EVIDENCE REQUIRES`

Do not introduce microservices, Kubernetes, sharding, distributed caching or cloud-only dependencies solely because they are common SaaS patterns.

The living contract is `docs/ZAZU_SAAS_READINESS_ARCHITECTURE.md`.

**Important:** SaaS-ready architecture is not equivalent to public multi-tenant SaaS release certification. Runtime business-isolation, populated-data, recovery, deployment and adversarial authorization evidence remain release gates.
