# Zazu EMP — Task Packet

Use internally for every meaningful engineering cycle.

## Identity
- Repository: `Leano-Jordan/ZazuEMP`
- Branch/ref:
- Baseline commit:
- Cycle/Target ID:

## Objective
### Goal
What measurable engineering outcome must change?

### User outcome
What should the user be able to do or understand afterward?

### Business outcome
What operational/commercial risk or capability changes?

## Scope
### In
- 

### Out
- 

## Current evidence
- Observed behaviour:
- Relevant files:
- Runtime/CI evidence:
- Known findings:
- Unknowns:

## Change design
- Affected surface:
- Architecture boundary:
- Expected invariants:
- Dependencies:
- Regression blast radius:
- Rollback/checkpoint:

## Acceptance criteria
- [ ]

## Verification
### Structural
- [ ]

### Automated
- [ ]

### Runtime/manual
- [ ]

### Regression/adversarial
- [ ]

### UI/UX/accessibility
- [ ] Human-eye / creative critique completed where applicable
- [ ] Colour/theme relationships reviewed
- [ ] Form field widths appropriate to content
- [ ] Table width / horizontal scan burden reviewed
- [ ] Responsive composition reviewed
- [ ] Visual hierarchy and scan path reviewed

## Stop conditions
Stop and escalate when:
- repository identity is uncertain;
- scope becomes materially ambiguous;
- safe behaviour cannot be inferred;
- destructive action requires authorization;
- repeated attempts stop producing new evidence.

## Completion state
- IMPLEMENTED:
- TESTED:
- VERIFIED:
- PROVEN:
- UNVERIFIED:
- BLOCKED:

## Evidence record
- Changed files:
- Verification evidence:
- Regression disposition:
- Readiness gate impact:
- Decision-log entry:
- Next target:

The owner does not manually fill this for ordinary tasks. Engines maintain it as the execution contract.


## Director V2 failure-case fields

Add these fields whenever a check fails or the target is release-significant:

- Failure case ID:
- Failure fingerprint:
- F1–F8 classification:
- Current hypothesis:
- Hypotheses rejected:
- Attempts used / budget:
- Current diagnostic layer:
- Known-good checkpoint:
- New evidence since last attempt:
- Escalation decision:

A repeated failure must not be treated as a fresh task without first loading its case history.


## Director synchronization

The Task Packet is subordinate to Director state.

Specialist engines update evidence here, but Director remains responsible for sequencing, reconciliation, acceptance and next target.

For UI-affecting work, UI/UX records human-eye findings and Verification records rendered evidence where required.


## Director Decision Gate

Before meaningful work is authorised, Director records:

- Finding class: RELEASE BLOCKER / FOUNDATION / REGRESSION / MAINTAINABILITY / ENHANCEMENT / EXPLORATION
- Evidence:
- Impact:
- Urgency:
- Effort:
- Priority decision:
- Churn state: GREEN / AMBER / RED
- Scope decision: IN / DIRECTLY RELATED / DEFERRED / OUT
- Release impact:
- Decision:
- Required scope substitution, if any:

### Scope substitution rule

New work enters the active target only when required by the target, required by a newly discovered security/data-integrity/release risk, exchanged for another in-scope item, or explicitly authorised by the owner.

### Meaningful-progress rule

The cycle must produce a material reduction in risk/uncertainty, root-cause knowledge, acceptance progress, architectural clarity or verification evidence. Additional commits or tests without such a delta do not count as progress.
