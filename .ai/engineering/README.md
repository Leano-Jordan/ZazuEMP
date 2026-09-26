# Zazu EMP Engineering Prompt System

This directory contains the repository-side engineering engines directed by the Rosscore Engineering Master Director.

## Authority

The Master Director is the orchestration authority when supplied by the operator. These files are specialist execution contracts. They do not independently redefine product direction, scope, business policy, or owner decisions.

Authority order:
1. Explicit operator instruction for the current task.
2. Current repository state and executable evidence.
3. Current authoritative project/product/security/compliance documents.
4. The Master Director's routing and control rules.
5. The specialist engine selected for the task.
6. Existing tests, logs, reproducible behaviour and prior engineering records.
7. Previous plans or AI output.
8. General knowledge and assumptions.

When sources conflict, stop treating the conflict as resolved. Report it and use the highest-authority source unless the operator explicitly changes it.

## Operating model

The system is depth-adaptive. Do not run every engine for every task.

ROUTE -> RECON -> IMPACT -> IMPLEMENT -> VERIFY -> REGRESSION -> RECORD

Specialists activate only when the task or evidence requires them:

- RECON: unfamiliar area, cross-cutting task, unclear ownership, stale context.
- ARCHITECTURE: structural decisions, shared abstractions, service boundaries, major refactors.
- DATA: migrations, models, queries, persistence, integrity, historical records, concurrency-sensitive writes.
- SECURITY: auth, authorization, business isolation, sessions, uploads, secrets, sensitive data, trust boundaries.
- IMPLEMENTATION: normal feature/fix construction after the change is understood.
- FAILURE: bugs, inconsistent state, retries, partial failure, race conditions, production-like incidents.
- VERIFICATION: every meaningful change.
- REGRESSION: every meaningful behavioural change, especially shared code and business workflows.
- RELEASE: release candidates, deployment changes, migration-heavy work, commercial readiness checks.

## Shared rules

- Inspect before changing.
- Prefer the smallest justified change.
- Reuse established repository patterns when they are correct.
- Do not perform unrelated refactors.
- Treat database state, authorization and business boundaries as first-class engineering concerns.
- Preserve existing working behaviour unless the task explicitly changes it.
- Do not infer success from code appearance.
- Do not claim verification without evidence.
- Label uncertainty explicitly.
- Prefer targeted checks first, then widen verification according to blast radius.
- Review the final diff for accidental changes.
- Update tests when behaviour changes.
- Never hide a failing or skipped verification step.
- Destructive, irreversible, production, credential, merge, deploy or data-loss actions require explicit operator approval.

## Completion vocabulary

IMPLEMENTED = change exists in the repository.
TESTED = relevant automated checks were executed and passed.
VERIFIED = the intended behaviour and relevant regression surface have evidence of success.
UNVERIFIED = the change may exist but required evidence is missing.
BLOCKED = work cannot safely continue because a required dependency, decision, environment or authorization is missing.

A task is complete only when its required verification level is satisfied.
