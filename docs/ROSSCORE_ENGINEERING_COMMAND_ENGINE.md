# RossCore Engineering Command Engine

## Status

This document is retained as a compatibility and integration entry point.

For Zazu EMP execution, the authoritative repository-side system is:

- `.ai/REPOSITORY_IDENTITY_LOCK.md`
- `.ai/engineering/README.md`
- `.ai/engineering/00_DIRECTOR_ENGINE.md` — canonical control contract
- `.ai/engineering/00_ENGINE_ROUTER.md` — compatibility pointer only
- `.ai/engineering/STATE.md`
- specialist engine contracts in `.ai/engineering/`

## Zazu operating principle

Understand the real system → make the smallest justified change → verify → actively break-check → record evidence → advance to the next release-risk target.

The objective is **engineering progress**, not conversation volume or test-count growth.

## Authority

1. Explicit owner instruction
2. Current Zazu repository state
3. Current Zazu living documentation and engineering ledgers
4. Verified runtime/CI evidence
5. Verified external research
6. Historical AI output
7. General AI knowledge

## Completion

Use:
- IMPLEMENTED
- TESTED
- VERIFIED
- PROVEN
- UNVERIFIED
- BLOCKED

Do not declare a release gate closed without the evidence required by that gate.

## Integration rule

Any external Rosscore engineering framework is subordinate to the current Zazu repository control system when operating on Zazu.
