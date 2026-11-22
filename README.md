# Zazu – Event Management Platform (Zazu EMP)

Zazu EMP is a proprietary event-management platform for small businesses operating across catering and event-related services, including catering, tents/chairs, sound/DJ, baking/cookies, decor, rentals, photography, camera hire and combinations of these services.

## Windows installation candidate

A first installable Windows V1 candidate is available as a clean ZIP package. It includes production PHP dependencies, compiled frontend assets, a first-run installer and Windows launchers. It does not require Composer or Node.js on the target PC.

- Read [Windows package installation](docs/V1_PACKAGE_INSTALLATION.md) before installing.
- Host requirement: 64-bit Windows and PHP 8.4+ with the documented extensions. The package does not bundle PHP.
- The ZIP is for a **fresh local installation**, not an in-place updater or hosted SaaS deployment.
- Use the dedicated private-LAN HTTPS procedure for phone/tablet access. Do not expose the local HTTP development port to the internet.
- Physical-device acceptance, the installed-target recovery drill, live-business reconciliation and POPIA operational sign-off remain separate release gates.

The market and architecture findings informing the product's event-centred workflow are recorded in [Commercial Architecture Research — 2026-11-16](docs/COMMERCIAL_ARCHITECTURE_RESEARCH_2026-11-16.md).

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

For AI/coding work, read:
1. [.ai/REPOSITORY_IDENTITY_LOCK.md](.ai/REPOSITORY_IDENTITY_LOCK.md)
2. [.ai/engineering/00_DIRECTOR_ENGINE.md](.ai/engineering/00_DIRECTOR_ENGINE.md) — canonical Director control contract
3. [.ai/engineering/STATE.md](.ai/engineering/STATE.md) — current state only
4. The relevant specialist skill/contract for the active target
5. Failure ledgers only when the active target requires them
6. Archives only when historical evidence is needed

The canonical control contract is `.ai/engineering/00_DIRECTOR_ENGINE.md`; `.ai/engineering/00_ENGINE_ROUTER.md` is a compatibility pointer only and is not part of the canonical read order.

The repository-side engineering system is the authoritative control layer for Zazu execution.

For the physical Android PWA install and disconnected-device acceptance route, follow [the Android offline install runbook](docs/ZAZU_ANDROID_OFFLINE_INSTALL_ACCEPTANCE.md). The old plain-HTTP LAN address is not a supported service-worker installation origin.

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

## Automated quality gates

See [the quality gates register](docs/QUALITY_GATES.md) for measured frontend asset budgets, accessibility coverage, documentation checks and release safeguards.

## Database compatibility

See [the database compatibility matrix](docs/DATABASE_COMPATIBILITY.md) for the exact SQLite and PostgreSQL environments exercised by CI. MySQL/MariaDB are not currently certified Zazu V1 deployment targets.

## Product direction

Zazu EMP is designed as a reusable commercial event-management platform.

The event/job is the central operational record. Related information such as customer, requirements, quote, services, purchasing, preparation, costs, payments, documents and activity should remain connected to that job where practical.

Supported capability examples include catering, rentals, sound/DJ, decor, baking, photography, camera hire and combinations of event services. Businesses define their own reusable capability catalogue rather than receiving a fixed industry list. **Zazu must adapt its default workspace to the business capabilities selected by the owner:** presets and combinations must meaningfully shape relevant navigation, catalogue suggestions, event intake, requirements, operational guidance, reporting and Helper recommendations. Irrelevant service options should not clutter a business's normal workflow, while shared event records, historical data, permissions and the ability to enable additional capabilities remain intact. This is a mandatory product requirement; see [Adaptive Business Presets and Event Components](docs/ADAPTIVE_BUSINESS_PRESETS_AND_EVENT_COMPONENTS.md).

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


## Pre-launch website

The separate static pre-launch site lives in [`prelaunch/`](prelaunch/) and is deployed by [`.github/workflows/deploy-prelaunch.yml`](.github/workflows/deploy-prelaunch.yml). It does not replace or run inside the Laravel application.

- Expected GitHub Pages URL: https://leano-jordan.github.io/ZazuEMP/
- Publish source: GitHub Actions; repository Pages settings may require a one-time source selection.
- Lead capture currently routes through FormSubmit to `rosscorelabs@outlook.com`. The mailbox must be activated with the provider and end-to-end delivery verified before public promotion.
- See [pre-launch setup notes](prelaunch/README.md) and [privacy notice](prelaunch/privacy.html) for deployment and lead-capture limitations.
