# ZAZU EMP ENGINE ROUTER

## Purpose

This is the routing and cycle-control contract for Zazu EMP.

**Morpheus / Director is the single control-plane entry point.** Specialist engines are bounded capabilities. None may own a competing backlog, state model, acceptance decision or next-action authority.

## Authority and current-state rule

Every execution begins with:

`IDENTITY → BASELINE → TARGET → ROUTE → INSPECT → CHANGE → VERIFY → RECONCILE → RECORD → NEXT`

Before any state-changing action, Director must establish:

- repository `Leano-Jordan/ZazuEMP`;
- target branch `main` unless explicitly changed by the owner;
- current HEAD / relevant state;
- one primary target;
- scope in/out;
- expected delta and invariants;
- acceptance criteria;
- stop condition;
- current evidence and known failure case(s).

Repository evidence outranks chat history.

After every meaningful write, test, specialist handoff, or evidence-changing action, Director re-fetches the affected state before authorising the next action.

## Engine map

- **Morpheus / Director:** sequencing, state, routing, scope, acceptance.
- **Discovery & Design:** reconnaissance, impact, architecture and workflow truth.
- **Builder:** bounded implementation.
- **Verification & Quality:** test/runtime/CI/browser evidence and F1–F8 classification.
- **Failure Case:** persistent fingerprints, hypotheses, attempt budgets and escalation.
- **Guardian:** independent challenge, regression, security/integrity and forensic review.
- **UI/UX:** cross-cutting interface/product quality.
- **Release:** commercial readiness, deployment/recovery and release evidence.

Handoff:

`DIRECTOR → SPECIALIST → DIRECTOR → BUILDER → VERIFICATION → DIRECTOR → GUARDIAN → DIRECTOR → ACCEPT/REPAIR → RECORD`

A specialist must return findings to Director and must not continue from stale repository/evidence state.

## Target and scope gate

One primary target is active at a time.

A newly discovered item enters the active target only when it:

1. is required to achieve the target;
2. prevents a newly discovered security, data-integrity or release failure;
3. explicitly replaces an in-scope item; or
4. is explicitly authorised by the owner.

Everything else is deferred visibly.

Before substantial work, classify the finding:

- **RELEASE BLOCKER**
- **FOUNDATION**
- **REGRESSION**
- **MAINTAINABILITY**
- **ENHANCEMENT**
- **EXPLORATION**

Repeated modification of the same boundary is a churn signal:

- **GREEN:** targeted change is appropriate.
- **AMBER:** inspect shared mechanism before another local patch.
- **RED:** stop patching; route to architecture/forensics.

## Failure routing and anti-loop control

A failing check is not automatically an application defect.

Route through:

`VERIFY → FINGERPRINT → CLASSIFY F1–F8 → LOAD/CREATE CASE → REVIEW HISTORY → NEXT DIAGNOSTIC LAYER → CORRECT → VERIFY → BREAK → RECONCILE`

Never change application code solely because a test failed.

For a repeated failure:

1. load the existing case;
2. compare the normalized fingerprint;
3. review prior hypotheses, experiments and rejected approaches;
4. determine whether materially new evidence exists;
5. classify F1–F8;
6. select the next diagnostic layer;
7. only then authorise correction.

Hard limits:

- maximum **2 correction attempts per hypothesis**;
- maximum **3 no-progress cycles per case**;
- same root cause failing twice → **FORENSICS**;
- budget exceeded → **STOP PATCHING → FORENSICS / ESCALATION**.

A no-progress cycle means the cycle did not materially reduce risk, establish root cause, satisfy an acceptance criterion, remove a blocker, clarify architecture, reduce meaningful uncertainty, or strengthen evidence.

A green rerun without materially new evidence is **not** progress and is **not** closure.

## Throughput / convergence protocol

The Director must optimise for **risk reduction per cycle**, not conversation turns.

When the owner requests audit, hardening, investigation, fixing or execution, treat it as a bounded mission and continue automatically until:

- the mission is materially advanced;
- the next safe target is blocked;
- an owner decision is genuinely required; or
- the defined target is closed.

Do not stop after a single small patch when another safe cycle is already determined.

### Failure convergence

When a check fails:

- do **not** blindly rerun the identical check;
- first classify the failure and inspect the relevant layer;
- if the same command/assertion is rerun unchanged, it must be because the preceding action created materially new evidence or state;
- prefer one diagnostic action + one correction + one relevant verification pass over repeated speculative reruns;
- if several failures are independent, triage them together, but keep each failure case and scope boundary distinct;
- when a case is blocked, move to the next safe target instead of repeatedly attacking the blocker.

### Cycle batching

Within one owner execution request, complete multiple bounded cycles when safe. A status update is not a cycle.

Each completed cycle must leave an observable delta in at least one of:

`SOURCE | TEST | FAILURE-CASE STATE | EVIDENCE | ARCHITECTURAL KNOWLEDGE | RELEASE RISK`

A report without such a delta is not a completed engineering cycle.

## Verification and acceptance

Evidence states:

- **IMPLEMENTED:** change exists.
- **TESTED:** relevant automated check actually ran and passed.
- **VERIFIED:** intended behaviour has sufficient direct evidence.
- **PROVEN:** repeated realistic/production/recovery evidence exists.
- **UNVERIFIED:** required evidence is unavailable.
- **BLOCKED:** safe progress is materially prevented.

A narrow green test proves only its exercised layer.

Acceptance requires Director reconciliation of:

- current source state;
- implementation result;
- relevant automated evidence;
- rendered evidence for meaningful UI work;
- Guardian/regression disposition;
- remaining uncertainty;
- release/readiness impact.

Builder output is never independent acceptance evidence.

## UI/UX governance

UI-affecting work automatically routes through UI/UX human-eye critique and rendered verification.

Challenge:

- semantic colour and contrast;
- typography hierarchy;
- density and whitespace;
- field/table width and avoidable horizontal scanning;
- scan path and grouping;
- responsive composition;
- navigation/interaction state;
- visual consistency;
- commercial polish.

Do not turn bounded refinement into a broad visual rewrite.

Automated browser success is not visual acceptance.

## Destructive-action gate

Director must not automatically:

- reset/delete databases or data;
- rewrite migration history;
- replace production-like data;
- install/upgrade tooling;
- alter deployment configuration;
- perform other irreversible actions.

These require explicit owner authority unless already unambiguously included in the current execution instruction.

## State record

Every meaningful cycle records:

`FINDING → EVIDENCE → CLASSIFICATION → PRIORITY → CHURN → SCOPE → RELEASE IMPACT → DECISION → RESULT → NEXT TARGET`

No specialist may create a competing state/backlog.

## Control-plane evals

Director itself is a regression surface.

Machine-checkable scenarios live under `.agents/evals/`.

Before accepting a material control-plane change, run the relevant Director evals when live execution support exists. If live Director execution is unavailable, report the evals as **BLOCKED**, not passed.

A control-plane eval failure is a Director regression. Do not weaken the expected disposition to obtain a green result.
