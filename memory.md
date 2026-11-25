# Zazu EMP — Engineering Context

**Product:** Zazu — Event Management Platform
**Repository:** Leano-Jordan/ZazuEMP
**Canonical branch:** main only
**Accountable execution role:** Zazu Engine Director (Jarvis)
**Final authority:** Founder

Zazu is event management software, not catering software. The event is the central record. Configurable business capabilities describe the services and resources used to plan and deliver different events; catering, equipment hire, sound/AV, décor, rentals and photography are examples, not product boundaries. Never claim every specialist workflow is already implemented: current capability and release claims must match the source and accepted V1 scope.

## Operating rules

- Work directly on main; never create branches.
- Do not introduce Render or AMPPS into Zazu.
- Preserve live business data, migrations, IndexedDB, offline queues, attachments, verified backups and recovery paths.
- Never reset, clear or overwrite customer/business data; never perform an irreversible restore without explicit authority.
- The unsolicited phone-pairing field must not appear on Dashboard. Owner pairing remains in Settings → Phone sync.
- Do not weaken tests, repeat unchanged investigations, or claim unobserved CI, browser, physical-device or release evidence.
- Prefer small, evidence-backed changes over rewrites and speculative features.

## Read current authority—not old chat summaries

1. Repository identity lock: .ai/REPOSITORY_IDENTITY_LOCK.md
2. Commercial V1 mandate: docs/ZAZU_ENGINE_DIRECTOR_COMMERCIAL_V1_MANDATE.md
3. Current engineering state: .ai/engineering/STATE.md
4. Readiness register: .ai/engineering/READINESS_REGISTER.md
5. Failure cases and decision log when relevant
6. Relevant product, security, recovery or physical-acceptance procedure

Dated audits and historical checkpoints are evidence of their time, not current instructions. Current repository state and the living engineering register take precedence. This file is only a short bootstrap; it is not a parallel status ledger.