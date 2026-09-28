# Zazu EMP — Documentation Authority Map

## Purpose

This map prevents agents from treating historical notes, old status snapshots or compatibility documents as current instructions.

## Current authority

### Engineering control plane
1. `.ai/REPOSITORY_IDENTITY_LOCK.md` — repository/context firewall
2. `.ai/engineering/README.md` — system overview
3. `.ai/engineering/00_ENGINE_ROUTER.md` — routing and state machine
4. `.ai/engineering/STATE.md` — current engineering state
5. `.ai/engineering/READINESS_REGISTER.md` — current commercial-readiness direction
6. `.ai/engineering/DECISION_LOG.md` — durable decisions
7. `.ai/engineering/REGRESSION_LEDGER.md` — known failure patterns
8. specialist engine contracts in `.ai/engineering/`

### Product/project context
- `memory.md` — living project context and historical evidence; current control files above take precedence.
- `docs/ZAZU_CHAT_CONTEXT.md` — compact chat bootstrap; not a substitute for current repository state.
- `docs/PROJECT_GENESIS.md` — discovery/planning framework.
- `docs/HUMAN_FIRST_DISCOVERY.md` — human-first discovery framework.

### Current product/release views
- `docs/ZAZU_V1_STATUS.md` — current V1/release position.
- `docs/ZAZU_DEVELOPMENT_CHECKLIST.md` — current gate map.
- `docs/ZAZU_2027_HARDENING_SCORECARD.md` — evidence maturity and future-scale separation.
- `docs/ZAZU_COMMERCIAL_LEGAL_REGISTER.md` — current legal/commercial control register.
- `docs/ZAZU_PRIVACY_BASELINE.md` — privacy/security engineering baseline.

### Compatibility entry point
- `docs/ROSSCORE_ENGINEERING_COMMAND_ENGINE.md` — compatibility/integration pointer. It does not outrank the repository-side Zazu control plane.

## Historical records

Files with dated audit/hardening names are evidence of what happened at that time.

Examples:
- `docs/ZAZU_HARDENING_2026-09-27.md`
- `docs/ZAZU_SWEEP_AUDIT_2026-09-27.md`
- `docs/ZAZU_UI_UX_AUDIT.md`

Historical records must not be used to infer current state when the current control files differ.

## Conflict rule

When two documents disagree:
1. explicit current owner instruction;
2. current repository implementation;
3. current engineering control files;
4. current product/release registers;
5. dated historical records;
6. external research;
7. old AI/chat memory.

Do not silently reconcile a conflict by guessing. Record the conflict and resolve it deliberately.

## Update rule

When repository reality changes, update the relevant current-state document in the same engineering cycle.

Do not repeatedly append current status to old historical files.
