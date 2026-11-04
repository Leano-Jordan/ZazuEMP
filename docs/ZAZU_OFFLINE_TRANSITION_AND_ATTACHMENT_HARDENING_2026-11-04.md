# Offline session transition and attachment hardening — 2026-11-04

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
- Logout assessment now inspects pending local attachments as well as queued mutations and conflicts.
- Logout confirmation includes the count of pending attachments before asking the owner to discard the local copy.
- Local session cleanup clears the active business's attachment store as well as its offline mutation workspace. If attachment inspection or cleanup fails, logout cleanup fails closed rather than silently leaving an attachment copy behind.
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
- Exact source/test head: `cdea317643dd5e226d685f84c32f51d875defc80`.
- GitHub Actions run `41882179271` (Push on main / CodeQL) was still in progress at the time this note was written. No test pass is claimed from that run.
- No physical-device or live-server acceptance was performed.

## Next execution

1. Complete the session-transition acceptance matrix against the approved DEC-019 trust model. Preserve pending work unless the owner explicitly confirms discard.
2. Integrate attachments into the active Work page using the current sync-token API, business-scoped IndexedDB, server idempotency, offline retry, local open/download, and account/logout cleanup.
3. Add active-path browser tests for offline create, reconnect upload, duplicate retry, downloaded file reuse, business separation, logout cleanup, and revocation.

## Follow-up: logout inspection regression — 2026-11-04

The exact-head browser run `41883363935` failed after registration and sign-out. The captured Playwright trace shows the logout guard deliberately stopped the request with: `Zazu could not safely inspect local offline changes. Close other Zazu tabs, reload, and retry signing out.` The browser remained authenticated; this was not a successful logout.

Code inspection found that the attachment IndexedDB open handler treated the IndexedDB `blocked` event as an immediate terminal error. The event is informational: the open request may still complete when an older tab releases its database connection. The active offline client now waits for that request to resolve and closes its attachment database connection on `versionchange`, allowing older tabs running the updated client to release the database for upgrades.

- Application change: `b21514d0f1daac5cd1620de464832e867f4ccea9`
- Regression assertion: `5afdd831a233b992818b440351e342ec188bacdc`
- Exact-head CI and browser rerun: **not yet verified**.
- This is a targeted correction, not closure of the session-transition challenge. Same-business account changes, business switches, expiry, interrupted cleanup, stale async completions and remote revocation still need explicit acceptance.
