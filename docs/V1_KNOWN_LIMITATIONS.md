# Zazu EMP — V1 Known Limitations

**Evidence baseline:** application/test source `7193038a2b2d14ea71278050a0d7c26ce246bf86`  
**Status:** engineering record; physical-device acceptance remains open.

This document records boundaries that are explicit in the V1 scope and current regression coverage. It is not a promise of capabilities that have not been verified.

## Offline operation

- Offline support is **bounded**, not full offline parity. The tested local workflow covers customer creation, Work/job creation linked to a locally created customer, durable local persistence across reload, and queued reconnect synchronisation.
- Work attachments can be captured against an offline-created Work record, stored locally, and uploaded/reconciled when the record receives its server identity and connectivity returns. Remote attachment caching is business/Work scoped.
- Customer profile photos are **connection-required**. The UI must not claim the photo was saved offline; the regression test expects the explicit “Customer profile photos need a connection” message and verifies no mutation is queued.
- Some operations remain online-dependent. Where an operation is not in the tested offline boundary, users must not be told it has been saved locally.
- No full disconnected multi-device synchronisation or full feature parity is claimed. Device-local pending work is reconciled when connectivity returns.

## Device and installation acceptance

- Playwright coverage includes desktop Chromium, Pixel 7 emulation and a tablet viewport. Stateful offline/session/registration contracts run once on Chromium to avoid duplicating those stateful journeys across projects.
- Browser emulation is not physical-device proof. Installed PWA launch, storage survival across real device restarts, reconnect and duplicate-safe sync still require the bounded phone/tablet acceptance on a trusted HTTPS origin.
- The phone/tablet acceptance result is **OPEN** until real devices and the tested release commit are recorded. Do not describe V1 as physically device-certified before then.

## Deliberately deferred product capabilities

- Full enterprise identity/SSO.
- Autonomous Zazu Helper actions or autonomous business decisions. The V1 Helper is guidance/contextual assistance, not an operator.
- Advanced analytics/business intelligence and predictive finance/forecasting.
- Advanced catalogue intelligence and procurement automation.
- Complex workflow designer and advanced resource optimisation.
- Staff assignment and scheduled assignment/reminder alerts are deferred to the R799 team-management phase; core system feedback/toasts remain in V1.

## Evidence references

- `e2e/offline-work-creation.spec.js`: real customer and Work forms, offline creation, local persistence across reload, and normal directory visibility.
- `e2e/pwa-offline.spec.js`: true offline customer save, customer-to-Work local relationship, offline dashboard navigation, queue persistence, conflict/re-pair/session boundaries, and explicit customer-photo connectivity boundary.
- `e2e/offline-attachment-sync.spec.js`: offline Work attachments, local download, server identity reconciliation, reconnect upload, private file caching and logout/session race coverage.
- `docs/PHYSICAL_DEVICE_ACCEPTANCE.md` and `docs/OWNER_V1_ACCEPTANCE_RUNBOOK.md`: required real-device acceptance procedure.
- `.ai/engineering/V1_SCOPE_FREEZE.md` and `docs/v1-capability-map.md`: binding V1 boundary and deferred capabilities.

## Release interpretation

These limitations do not by themselves block V1 where they match the frozen scope. The remaining acceptance item is real installed-device offline acceptance, followed by the final release decision. Any new blocker must be a reproducible failure in an agreed V1 workflow, a demonstrated security/privacy/data-integrity failure, or an explicitly owner-approved requirement.
