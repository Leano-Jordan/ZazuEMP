# Zazu Offline Storage & Trusted-Device Threat Model

**Status:** Engineering assessment complete; DEC-019 and V1 control boundaries documented. Automated credential-exposure regression strengthened; physical device hardening and policy-owner acceptance remain OPEN.  
**Reviewed:** 2026-11-24  
**Scope:** Current PWA offline workspace, IndexedDB persistence, trusted sync credential, service-worker caches and logout/revocation behaviour.  
**Out of scope:** A claim of independent native-app sandboxing, application-level encryption, or physical Windows/Android hardening.

## Executive finding

Zazu's offline persistence is business-scoped, but it is not encrypted by the application. The local workspace, queued mutations, attachments and trusted sync bearer credential are available to scripts running in the Zazu origin and to anyone who can access the same unlocked browser profile. Business-specific database names provide separation by workspace; they are not an access-control or encryption boundary.

**Architecture decision DEC-019:** a trusted device is an owner-approved shared business workspace, not a per-staff offline identity. Sharing it among the business's authorised staff is intentional. However, every person with access to that trusted browser profile inherits the approved offline workspace and mutation capability; normal staff permissions are not re-checked for each offline mutation. Access by an unauthorised person is therefore a **HIGH** risk. This is an explicit trust-model constraint, not a newly discovered server-side credential-storage defect.

## Verified implementation facts

### 1. Credential issuance and server validation

- Primary device registration and owner-approved pairing generate a 64-character random bearer token.
- The server stores SHA-256(token) in the device metadata, not the plaintext bearer.
- Sync middleware hashes the presented bearer and matches it to an active device; it also checks expiry.
- The device token has a 180-day idle expiry. Successful authenticated activity refreshes the expiry window.
- Revocation removes the stored token hash and clears expiry. Sensitive sync operations revalidate current device state and token hash within their operation transaction.
- API and pairing responses receive Cache-Control: no-store, private, Pragma: no-cache and Expires: 0.

### 2. Client persistence and exposure

- The client stores state.sync.token as part of the current business workspace in IndexedDB. The same database contains business records and mutation queue data.
- Requests use that value as Authorization: Bearer.
- The service worker and offline client do not encrypt IndexedDB records or attachment blobs before persistence.
- The local-storage pending-revocation marker contains the business ID, installation UUID and request timestamp; it does not contain the bearer token. The token itself remains in IndexedDB.
- The business-scoped database name is a namespace, not a security boundary. Same-origin JavaScript can access it.

### 3. Logout is not device revocation

- A successful web logout ends the Laravel web session and clears the authenticated service-worker page cache.
- The offline IndexedDB workspace, pending queue and trusted sync credential are intentionally retained. The logout confirmation warns that the sync credential remains on the browser and that offline changes are attributed to the approving owner.
- A pending owner-authenticated revocation can be retried later using the non-secret installation identity. This marker does not itself revoke the device.
- Therefore, signing out is not equivalent to removing local business data or revoking the trusted device. A shared, lost or reassigned device must be revoked by an owner.

### 4. Script and cache boundaries

- The application CSP uses a per-response nonce, script-src self plus the nonce, and does not allow unsafe-inline; style attributes are prohibited. This is a meaningful XSS mitigation.
- CSP does not protect IndexedDB or bearer credentials from a malicious script that is already executing as trusted same-origin code. A compromised same-origin dependency/build or successful same-origin script injection can read local records and use the sync token.
- The service worker stores authenticated pages and selected private media in user/business-scoped CacheStorage. Context changes and successful logout clear those caches. This does not clear or encrypt the IndexedDB workspace.

## Threat assessment

| Threat | Likely impact | Current control | Residual risk |
|---|---|---|---|
| Authorised business staff share the trusted OS/browser profile | They share the same approved business data and offline mutation capability; actions are attributed to the approving owner | DEC-019 explicitly permits an owner-approved shared business workspace and Settings discloses the permission boundary | **Accepted architecture limitation** — not per-staff isolation |
| An unauthorised person uses the same unlocked OS/browser profile | Read business records, attachments and queued financial data; potentially use the trusted sync credential | Owner-approved device pairing; server-side revocation; business scoping | **High** — profile access is effectively trusted-device access |
| Device/browser profile is copied or stolen while accessible | Local business data and bearer may be extracted | OS account/device controls are the main protection | **High** — application storage is not encrypted |
| Same-origin XSS or compromised trusted script | Read IndexedDB and issue authenticated sync requests | Nonce CSP, no unsafe-inline, same-origin restrictions and server-side authorization | **High** — CSP reduces injection paths but cannot contain code already trusted by the origin |
| Device is lost while offline | Owner cannot immediately revoke through the unavailable host | Revocation takes effect when server is reachable; local workspace remains available | **High until revocation reaches the server** |
| Stale or revoked token is replayed after server revocation | Unauthorized sync | Hash comparison, active-state and expiry checks, transactional revalidation | **Low after confirmed revocation** |
| Workspace copied between businesses | Cross-business record exposure or queue contamination | Business-scoped DB names and identity checks during read/migration | **Reduced, not a confidentiality boundary** |

## Engineering decision

Do **not** add superficial encryption that stores its decryption key beside the ciphertext in IndexedDB. It would add complexity without protecting against same-origin script execution or a person who can use the unlocked profile.

A passphrase-derived offline key could improve protection against copied profile files, but it would introduce a lock/unlock dependency into cold launch, queue processing, attachment access and disaster recovery. It must not be introduced until key derivation, throttling, recovery, key rotation, multi-device pairing, backup/export and interrupted-write behaviour have a complete design and adversarial tests.

## Required V1 controls and acceptance

1. Preserve DEC-019: a trusted device may be shared by the business's authorised staff, but it is a shared business workspace, not per-staff offline authentication. Repeat this boundary in onboarding and deployment guidance.
2. Require the trusted device to remain under business control, with a device-level screen lock and OS storage encryption. Verify these separately on physical Android and Windows devices; CI cannot prove them.
3. Preserve the current clear distinction between **Sign out** (web session ends; local workspace and trusted-device credential remain) and **Revoke access** (owner revokes the trusted credential; revocation does not remotely erase local data). Any local wipe must be a separate explicit action.
4. Preserve the ability to revoke a lost device from another owner-authorized device. Test revocation while the target device is offline, then verify its next sync is rejected without silently deleting its local queue.
5. Keep credentials out of localStorage, URLs, logs, analytics, exports and support diagnostics. The pending-revocation marker must remain non-secret.
6. Maintain CSP, dependency/build integrity checks and regression coverage for untrusted content sinks. Any future third-party script requires a fresh threat review.
7. Do not claim encrypted local storage, secure-enclave-backed key storage, or theft resistance until implemented and physically verified.


## Regression evidence update — 2026-11-24

- `e2e/offline-session-transition.spec.js` verifies that an unreachable logout queues only the non-secret installation identity for later owner-authenticated revocation. The regression now scans **all** `localStorage` and `sessionStorage` entries and the current URL to ensure the trusted bearer credential is not exposed there.
- The bearer remains in the business-scoped IndexedDB workspace by design so already-approved offline operation can continue. This does not provide encryption or protection against same-origin script execution or access to an unlocked browser profile.
- This is automated browser evidence only. It does not prove browser-profile encryption, OS account protection, device screen-lock configuration, or remote wipe. Those remain physical/operational acceptance items.

## Evidence reviewed

- public/zazu-offline.js: IndexedDB workspace and attachment persistence, bearer request header, registration/pairing, logout-preserving state and pending revocation marker.
- app/Http/Controllers/SyncController.php: random token issuance, server-side hash persistence and revocation.
- app/Http/Middleware/AuthenticateSyncDevice.php and app/Models/SyncDevice.php: hash/active/expiry validation and 180-day idle refresh.
- app/Http/Middleware/ApplySecurityHeaders.php and tests/Feature/SecurityHeadersTest.php: CSP nonce and no-store response policy.
- public/sw.js: user/business-scoped authenticated page and private-media caches.
- resources/js/app.js: explicit logout warning and service-worker cache clearing.

**Acceptance status:** threat model documented; no encryption implementation or physical device hardening is claimed. Android and owner-PC acceptance remain separate OPEN gates.
