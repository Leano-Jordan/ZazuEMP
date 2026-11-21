# Zazu EMP — V1 Known Limitations

**Evidence baseline:** application/test source `d6c1d577b6ed0bd18bef5d1c869ccc00782b35cb`  
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

## Trusted-device authority boundary

- A paired offline device is approved by a business owner and receives a business-scoped sync credential. The credential is intentionally retained across ordinary web logout so queued work is not stranded; logout does not revoke the trusted device.
- Offline mutations are attributed in the server audit log to the owner who approved the device. This is attribution, **not per-user authentication**: the server does not independently verify which staff member is physically using the shared trusted device for each mutation, and normal staff role permissions are not re-evaluated per offline mutation.
- Treat the device as a shared business data surface. Require a device screen lock, controlled physical custody and immediate owner revocation if it is lost, shared outside the business or reassigned. The owner must review the selected bootstrap data before pairing.
- Existing devices that were paired before owner attribution was introduced may have no recorded approver. Their historical audit rows remain unattributed; owner re-pair/re-registration establishes attribution for subsequent sync activity. Do not retroactively guess an approver.

## iOS compatibility boundary

- V1 physical offline acceptance is Android-only. iPhone/iPad offline cold launch, storage persistence, background behaviour and reconnect synchronisation have not been tested or certified.
- Do not promise iOS offline reliability or parity in V1. Online use may be available where supported, but offline behaviour remains unverified until a separate device acceptance pass is completed.

## Database engine coverage boundary

- The current V1 host target is the owner's Windows PC using SQLite. The full Laravel suite runs against SQLite; PostgreSQL currently receives migration and recovery smoke coverage, not the full commercial/sync feature suite.
- This is a conditional limitation, not a blocker for the SQLite owner-PC V1. If a PostgreSQL host or pilot is selected, run the commercial and sync feature suites against PostgreSQL in CI before that pilot. Do not infer full PostgreSQL parity from the smoke test.

## Password policy boundary

- New registrations and password resets require a minimum of 10 characters.
- Zazu does not call an online breached-password service. This avoids making password creation dependent on internet access in LAN/offline deployments; users should choose unique passphrases.

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

These limitations do not by themselves block V1 where they match the frozen scope. Remaining owner-dependent gates include the Windows installation backup/off-disk copy/restore drill, physical Android offline acceptance, populated commercial reconciliation, POPIA operational sign-off and ZAZU name clearance. PostgreSQL commercial/sync testing becomes a gate only if a PostgreSQL host is selected. Any new blocker must be a reproducible failure in an agreed V1 workflow, a demonstrated security/privacy/data-integrity failure, or an explicitly owner-approved requirement.
