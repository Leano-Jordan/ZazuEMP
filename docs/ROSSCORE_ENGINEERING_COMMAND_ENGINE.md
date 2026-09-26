# RossCore Engineering Command Engine

This document is retained as a compatibility entry point.

The authoritative repository-side engineering system is now:

- `.ai/engineering/README.md`
- `.ai/engineering/00_ENGINE_ROUTER.md`
- specialist engines in `.ai/engineering/`

The operator's Rosscore Engineering Master Director controls orchestration when supplied externally. Repository specialists execute only the portion of engineering work routed to them.

## Core principle

Understand the real system -> make the smallest justified change -> verify -> regression-check -> record evidence.

## Authority

1. Explicit operator instruction.
2. Current repository and runtime evidence.
3. Authoritative current project documentation.
4. Master Director routing.
5. Specialist engine requirements.
6. Tests, logs and prior records.
7. Previous AI output and general knowledge.

## Completion

Use evidence-based states:

- IMPLEMENTED
- TESTED
- VERIFIED
- REMAINS UNVERIFIED
- BLOCKED

Do not declare completion without the evidence required by the active verification and regression gates.

See `.ai/engineering/00_ENGINE_ROUTER.md` for activation and handoff rules.
