# Rosscore Project Manifest — Zazu EMP

**Status:** Active product repository  
**Company:** Rosscore Labs  
**Product:** Zazu EMP — Event & Catering Management Platform  
**Repository:** `Leano-Jordan/ZazuEMP`  
**Director:** Jarvis (owner-facing); Morpheus is the canonical control plane.

## Product purpose

Zazu EMP supports event and catering operators in managing the operational lifecycle around customers, jobs/events, services, quotes, invoices and payments, suppliers, purchasing and receiving, inventory, assets, costs, attachments, reporting and business administration.

The product is assessed as a professional Rosscore Labs product. This repository is Zazu EMP only; it is not the source of truth for SwiftOrder or other Rosscore products.

## Operating model

Zazu is designed as an offline-first product. Its exact supported offline workflows, local persistence, queueing, synchronization, conflict handling and evidence boundaries are recorded in the Zazu offline architecture and release records.

Company-wide policy: [Rosscore Labs Offline-First and Disconnected-Operation Standard](https://github.com/Leano-Jordan/Rosscore-Labs/blob/main/docs/ROSCOR_OFFLINE_FIRST_STANDARD.md).

A cached page or service-worker shell is not proof of full offline operation. Physical installed-device acceptance remains open until the secure-origin disconnect, full close/reopen, local-work recovery and reconnect sequence passes on the supported devices.

## Repository and branch boundaries

- `main` is the canonical engineering branch. All source, tests, documentation, audits and maintenance changes belong on `main`.
- `deploy/render-staging` is an intentional Render deployment-only branch. Preserve it. Do not use it for development or routine engineering changes.
- Only the designated Render deployment process may update the deployment branch, and only when deployment is explicitly authorised.
- Do not create feature or development branches.
- No production deployment is implied by a successful Docker preview build.

## Director control

- Canonical control contract: `.ai/engineering/00_DIRECTOR_ENGINE.md`.
- Repository identity lock: `.ai/REPOSITORY_IDENTITY_LOCK.md`.
- Current engineering state: `.ai/engineering/STATE.md`.
- Readiness ledger: `.ai/engineering/READINESS_REGISTER.md`.
- Release gate ledger: `docs/V1_RELEASE_CHECKLIST.md`.

## Current release position

**V1: NO-GO.** Automated application, security, browser and populated-upgrade evidence is recorded against exact source commits. This does not close physical installed-device acceptance, representative operator backup/restore, deployment rollback, operational privacy/legal closure, official Zazu brand clearance, or production licensing issuer/key custody and application-wide enforcement.

The last formally reconciled evidence-maturity score remains 69/100. No score uplift is inferred from individual engineering fixes or CI success.
