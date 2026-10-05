# ZAZU EMP — FAILURE CASE / LOOP-BREAKING ENGINE

## Purpose

The Failure Case Engine is the control layer that prevents Morpheus and specialist engines from repeatedly applying variations of the same unsuccessful diagnosis or patch.

A failing check is a **case**, not a task.

The case remains open until its cause is established and the required evidence closes it, or it is explicitly BLOCKED.

## Prime rule

> **No repeated investigation without materially new evidence.**

If the same failure fingerprint returns, load the existing case before proposing another correction.

Do not repeat a rejected hypothesis, failed correction strategy or identical verification path unless new evidence materially changes the hypothesis.

## Failure-case lifecycle

`OBSERVED → FINGERPRINTED → REPRODUCED → CLASSIFIED → HYPOTHESIZED → EXPERIMENTED → CONFIRMED/REJECTED → CORRECTED → REGRESSED → CLOSED`

If evidence is insufficient:

`ANY STATE → BLOCKED`

If the same failure returns:

`REOPENED → LOAD HISTORY → ESCALATE`

## Required failure-case record

Every non-trivial repeated or release-significant failure records:

- case ID;
- first observed baseline/HEAD;
- current baseline/HEAD;
- target/workflow;
- exact symptom;
- expected result;
- actual result;
- failure fingerprint;
- environment/runtime state;
- data/fixture state;
- verification layer;
- F1–F8 classification;
- hypotheses;
- experiments attempted;
- evidence for/against each hypothesis;
- changes attempted;
- rejected approaches;
- confirmed root cause;
- correction;
- regression family;
- runtime/adversarial evidence;
- remaining uncertainty;
- closure state.

## Failure fingerprint

The fingerprint should identify the failure using stable observable facts rather than prose alone.

Minimum components:

`target + verification layer + workflow phase + expected/actual + failure/assertion signature + relevant runtime/data state`

A changed code location alone does not create a new failure.

## Hypothesis ledger

Each hypothesis has:

- hypothesis ID;
- statement;
- evidence supporting it;
- evidence against it;
- experiment;
- result;
- status: UNVERIFIED / CONFIRMED / REJECTED;
- next diagnostic layer.

### Hypothesis rule

A rejected hypothesis remains rejected unless new evidence materially changes the conditions.

## Attempt budget

Default budget:

- maximum **2 correction attempts per hypothesis**;
- maximum **3 no-progress cycles on one case**.

After either limit:

**STOP PATCHING → FORENSICS / ESCALATION**

The Director may lower the budget for high-risk security/data/recovery failures.

## Escalation ladder

Use the next diagnostic layer rather than endlessly refining the current one:

1. Reproduction
2. Assertion/test contract
3. Application path
4. Fixture/data state
5. Session/authentication/cache/filesystem/runtime
6. Browser/real workflow
7. Instrumentation/logging
8. Architecture/design boundary
9. BLOCKED / owner decision

Skipping a layer is allowed only when evidence already rules it out.

## State contamination gate

Before blaming application code, consider:

- database state;
- stale session;
- cache;
- compiled views/assets;
- browser storage;
- filesystem/uploads;
- queue/job state;
- authentication state;
- fixture mutation;
- prior test side effects.

If contamination is plausible, isolate and reproduce before changing production code.

## Known-good checkpoints

A checkpoint records enough evidence to reproduce the starting condition:

- HEAD;
- database/fixture state;
- authentication state;
- relevant configuration;
- passing narrow check;
- runtime state where relevant.

Experiments should be compared against a known-good checkpoint whenever practical.

## Green-test rule

A green automated test closes only the verification layer it actually exercised.

It does **not** automatically prove:

- browser behaviour;
- populated runtime behaviour;
- recovery;
- CI execution;
- production-like data;
- cross-role authorization;
- commercial acceptance.

## Closure gate

A case may be CLOSED only when:

1. root cause is supported by evidence;
2. correction is implemented;
3. the narrow regression passes;
4. the relevant regression family passes;
5. required runtime/adversarial evidence is satisfied;
6. remaining uncertainty is recorded;
7. the case is added to the persistent history.

If required evidence is unavailable, mark **UNVERIFIED** or **BLOCKED**, not CLOSED.

## Persistent registry

Active and historical cases are stored in:

`.ai/engineering/FAILURE_CASES.md`

The registry is append-oriented. Do not erase rejected hypotheses or failed approaches; they are loop-prevention evidence.

## Director behaviour

When a case repeats:

1. identify the existing case;
2. compare fingerprints;
3. read prior hypotheses and attempts;
4. reject duplicate work;
5. select the next diagnostic layer;
6. create new evidence;
7. only then authorize another correction.

The Director is successful when uncertainty is reduced, not when it produces another patch.
