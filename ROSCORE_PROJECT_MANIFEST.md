# Rosscore Project Manifest — Zazu EMP

Status: Active product repository
Company: Rosscore Labs
Product: Zazu EMP — Event & Catering Management Platform
Repository: Leano-Jordan/ZazuEMP
Canonical branch: main (only engineering write target)
Director: Jarvis (owner-facing); Morpheus is the canonical control plane.
Current baseline (2026-11-17): ac79543732f2c3c5345cd095dce6e55eb16ac319

## Product purpose
Zazu EMP supports event and catering operators in managing customers, jobs/events, services, quotes, invoices/payments, suppliers, purchasing/receiving, inventory, assets, costs, attachments, reporting and business administration. It serves catering, event equipment hire, sound/DJ, baking, decor, rentals, photography/camera hire and related operators.

## Operating model
Zazu is offline-first. Exact supported offline workflows, local persistence, queueing, synchronization, conflict handling and evidence boundaries are recorded in the Zazu offline architecture and release records. A cached page or service-worker shell is not proof of full offline operation. Physical installed-device acceptance remains open until secure-origin disconnect, cold restart, local-work recovery and reconnect pass on supported hardware.

## Repository and branch boundaries
- main is the canonical engineering branch and the only write target for source, tests, documentation and maintenance.
- Never create feature, development, review, temporary or deployment branches.
- Preserve other refs; do not update, merge, rename or delete them without explicit owner direction.
- Do not introduce Render, AMPPS or another unapproved deployment workflow.
- No production deployment is implied by a successful build or CI run.

## Director control
- Canonical control contract: .ai/engineering/00_DIRECTOR_ENGINE.md
- Repository identity lock: .ai/REPOSITORY_IDENTITY_LOCK.md
- Current engineering state: .ai/engineering/STATE.md
- Sole readiness register: .ai/engineering/READINESS_REGISTER.md
- Release gates: docs/V1_RELEASE_CHECKLIST.md

## Current release position
V1: NOT READY. Baseline CI at ac795437 passed Laravel (520 passed, 1 skipped, 3,635 assertions), PostgreSQL migration/registration/recovery, offline browser acceptance, Zazu quality and CodeQL. SonarCloud was skipped. Evidence maturity remains 73/100 without uplift. Remaining operator gates: physical Android offline/reconnect acceptance, installed-target recovery with private media, live populated-business financial reconciliation and POPIA operational sign-off.

This is the canonical root manifest. docs/ROSCOR_PROJECT_MANIFEST.md is retained as a legacy pointer only.
