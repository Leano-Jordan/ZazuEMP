# Zazu – Event Management Platform (Zazu EMP)

Zazu EMP is a proprietary event-management platform for small businesses operating across catering and event-related services, including catering, tents/chairs, sound/DJ, baking/cookies, decor, rentals, photography, camera hire and combinations of these services.

## Project authority

- **Product:** Zazu – Event Management Platform
- **Short name:** Zazu EMP
- **Current owner:** Isaac Junior Lehlogonolo Maluleka
- **Development model:** Solo developer
- **Future business identity:** Rosscore Labs (not yet registered)
- **Repository:** `Leano-Jordan/ZazuEMP`
- **Repository default branch:** `main`
- **Project state:** Active foundation → commercial-readiness hardening
- **Established:** 2026-09-22

## Start here

**Read [memory.md](memory.md) first.**

For AI/coding work, also read:
1. [.ai/REPOSITORY_IDENTITY_LOCK.md](.ai/REPOSITORY_IDENTITY_LOCK.md)
2. [.ai/engineering/README.md](.ai/engineering/README.md)
3. [.ai/engineering/00_ENGINE_ROUTER.md](.ai/engineering/00_ENGINE_ROUTER.md)
4. [.ai/engineering/STATE.md](.ai/engineering/STATE.md)

The canonical control contract is `.ai/engineering/00_DIRECTOR_ENGINE.md`; `.ai/engineering/00_ENGINE_ROUTER.md` is a compatibility pointer.

The repository-side engineering system is the authoritative control layer for Zazu execution.

## Engineering system

Zazu uses one master control identity and bounded specialist engines:

- **Morpheus / Jarvis** — control plane, routing, state, acceptance and progress control.
- **Discovery & Design** — reconnaissance, workflow truth, architecture and decision boundaries.
- **Builder** — implementation, database, security implementation and debugging.
- **Guardian** — independent verification, regression breaking, forensics, security challenge and data-integrity review.
- **Release** — commercial readiness, recovery, deployment and release evidence.
- **UI/UX Improvement** — permanent cross-cutting interface and product-quality capability.
- **Verification & Quality** — test, browser, static-analysis, CI/YAML and runtime evidence classification.

The system is **state-driven, not conversation-driven**. Meaningful cycles follow:

`BASELINE → TARGET → INSPECT → DESIGN → CHANGE → VERIFY → BREAK → ACCEPT → RECORD → NEXT`

See [.ai/engineering/README.md](.ai/engineering/README.md), [.ai/engineering/06_VERIFICATION_AND_QUALITY_ENGINE.md](.ai/engineering/06_VERIFICATION_AND_QUALITY_ENGINE.md) and [.ai/engineering/STATE.md](.ai/engineering/STATE.md).

Routine Director execution targets `main`; temporary branches are not part of the normal Zazu operating model.

## Product direction

Zazu EMP is designed as a reusable commercial event-management platform.

The event/job is the central operational record. Related information such as customer, requirements, quote, services, purchasing, preparation, costs, payments, documents and activity should remain connected to that job where practical.

Supported capability examples include catering, rentals, sound/DJ, decor, baking, photography, camera hire and combinations of event services. Businesses define their own reusable capability catalogue rather than receiving a fixed industry list.

The product is intended to work across desktop/laptop workflows and responsive phone use.

## Current commercial-readiness principle

The goal is not to maximize feature count or test count.

Engineering priority is:
1. correctness
2. architecture
3. data integrity
4. security
5. workflow integrity
6. UX/accessibility
7. reliability/recovery
8. operability
9. deployment/upgrade safety
10. documentation/ownership/compliance
11. release evidence

See [.ai/engineering/READINESS_REGISTER.md](.ai/engineering/READINESS_REGISTER.md).

## Ownership and use

Unless a repository file explicitly states otherwise, Zazu EMP source code, original documentation, designs, assets, schemas, business logic and other original project material are proprietary and all rights are reserved.

Third-party components remain subject to their own licences. See [THIRD_PARTY_NOTICES.md](THIRD_PARTY_NOTICES.md).

## Documentation rule

Current state belongs in current-state files.

Dated audit/hardening records are historical evidence and must not be treated as current instructions unless explicitly promoted into the current control files.

Documentation must be updated when repository reality changes.

## Security and privacy

Do not commit passwords, API keys, private keys, tokens, customer personal information, production exports or other secrets.

Do not publish private residential/contact information in project documentation unless there is a specific legal or operational reason.

See [SECURITY.md](SECURITY.md) and [IP_OWNERSHIP.md](IP_OWNERSHIP.md).
