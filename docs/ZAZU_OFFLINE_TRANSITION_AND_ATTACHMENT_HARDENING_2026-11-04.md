# Offline session transition and attachment hardening — 2026-11-04

> **Superseding status — 2026-11-13, exact main `245aa47f7ef74e6e133fff867670ecafabb603b8`.** The open items below describe the original 2026-11-04/07 state and are historical. Current source has an active Work attachment workflow; full Playwright browser run [43473670319](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43473670319) passed, including the active attachment suite and current session-transition tests. Laravel, dedicated offline browser acceptance and PostgreSQL recovery passed in [43473670275](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43473670275). Same-business owner→manager hand-off, expired web-session recovery, and business-switch device installation isolation are now covered. **Physical phone/tablet acceptance remains open.** Do not use the historical “dormant client” or “not integrated into Work” statements below as current status.

---

## Scope

This cycle addresses the first two Director priorities in order:
1. Trusted-device session and logout boundary.
2. Phone-local attachment transfer foundation.

All changes are on canonical `main`. No deployment, database schema, customer data, or hosting configuration was changed.

## Architecture boundary retained

DEC-019 defines the phone as an owner-approved, business-trusted device. Its offline workspace is intentionally business-scoped, not isolated by each staff login. A user-scoped IndexedDB redesign would conflict with the approved shared-device workflow and could strand unsynchronized work.

The server-rendered page cache remains user-scoped. The offline business workspace remains business-scoped. Staff role permissions on the normal web UI are not a separate record-level privacy boundary for data the owner has explicitly copied to a trusted phone.

## Implemented in this cycle

- Added a business-specific attachment database name: `zazu-phone-attachments-business-<business-id>`.
- Updated the dormant attachment transfer client to use the same business-specific database and stamp new/downloaded records with the business ID.
- The logout-time discard flow was superseded: account logout now preserves business-scoped local work and device trust.
- Account logout ends the web session and clears only the user-scoped service-worker cache; it does not clear business-local data.
- Device revocation remains an explicit owner action in Business Settings. Same-business logout/re-login retains the offline queue, attachments and sync token.
- Updated the PWA regression assertions for business-scoped attachment storage and logout cleanup.

## Important migration and compatibility boundary

The old global `zazu-phone-attachments` database is not automatically migrated or deleted. Existing legacy records do not carry a reliable business identity, so assigning them to the current business or clearing them could expose or destroy another business's files. The legacy database is left untouched pending an explicit, verifiable migration or owner-led export.

## Not yet complete

- The attachment transfer client remains dormant and is not integrated into the active Laravel Work attachment panel. This cycle hardens its storage boundary; it does not claim offline attachment upload/download is now a working V1 feature.
- The account/session transition challenge still needs exact-head automated and browser acceptance for logout, interrupted logout, same-business account transition, business switch, expired session, stale response completion, and device revocation.
- Physical-device testing remains required.
- Existing local attachment data in the legacy global database needs a safe inventory/migration decision before any cleanup.

## Verification

- Source and test changes are committed directly to `main`.
- Application/test head under verification: `67332defe19d8370d963d563ec5ace9b4425df75`.
- On that exact head, Laravel, Zazu Quality, Psalm Security, populated upgrade/rollback and PHPMD passed. Browser smoke run `41893159816` remained in progress; browser acceptance is PENDING.
- No physical-device or live-server acceptance was performed.

## Next execution

1. Complete the session-transition acceptance matrix against the approved DEC-019 trust model. Preserve the business-scoped offline workspace and paired-device token across account logout; device revocation remains an owner action.
2. Integrate attachments into the active Work page using the current sync-token API, business-scoped IndexedDB, server idempotency, offline retry, local open/download, and account/logout cleanup.
3. Add active-path browser tests for offline create, reconnect upload, duplicate retry, downloaded file reuse, business separation, logout cleanup, and revocation.

## Follow-up: logout inspection regression — 2026-11-04

The exact-head browser run `41883363935` failed after registration and sign-out. The captured Playwright trace shows the logout guard deliberately stopped the request with: `Zazu could not safely inspect local offline changes. Close other Zazu tabs, reload, and retry signing out.` The browser remained authenticated; this was not a successful logout.

Code inspection found that the attachment IndexedDB open handler treated the IndexedDB `blocked` event as an immediate terminal error. The event is informational: the open request may still complete when an older tab releases its database connection. The active offline client now waits for both workspace and attachment database open requests to resolve rather than rejecting immediately on `blocked`. Both connections close on `versionchange`, allowing updated older tabs to release a database for schema upgrades.

- Attachment database change: `b21514d0f1daac5cd1620de464832e867f4ccea9`
- Workspace database change: `ff9adec55baae647b60b1fdb73454d8256e8011a`
- Regression assertions: `5afdd831a233b992818b440351e342ec188bacdc` and `5656abe1751a28ab8818c99e19a28bea00f04b0a`
- Current source/test head: `5656abe1751a28ab8818c99e19a28bea00f04b0a`
- GitHub commit-status endpoint currently returns no status records for this head. Exact-head CI and browser rerun: **not yet verified**.
- This is a targeted correction, not closure of the session-transition challenge. Same-business account changes, business switches, expiry, interrupted cleanup, stale async completions and remote revocation still need explicit acceptance.


## Session-transition acceptance extension — 2026-11-07

The 4 November open acceptance matrix has been advanced with current-main regressions:

- `e2e/offline-session-transition.spec.js` seeds a paired business-scoped device workspace and a pending customer mutation, makes the same-origin `/up` probe unreachable, and verifies logout is blocked with the safety alert while the authenticated session and pending mutation remain intact. It then restores reachability, completes account logout, signs into the same account again and verifies the business device pairing and pending mutation remain available.
- `SyncDeviceManagementTest` now verifies owner revocation rejects the previously valid bearer token across bootstrap, pull, push and attachment-list endpoints, not only bootstrap.
- Existing cross-business owner-revocation denial remains covered.

**Source status:** IMPLEMENTED / tests added. **Runtime status:** UNVERIFIED until the exact `main` head reports passing Laravel and Chromium Playwright jobs. This does not replace physical-device acceptance, expired-session testing, interrupted logout, stale in-flight response testing or the owner-operated lost-device drill.

Latest test source commits: `614c24131edc3023c05baad5e583614a8f4c7156` (browser fixture) and `4de9e8bf018404fe1b67e16a7d55b0ec09ef7109` (server revocation matrix).
