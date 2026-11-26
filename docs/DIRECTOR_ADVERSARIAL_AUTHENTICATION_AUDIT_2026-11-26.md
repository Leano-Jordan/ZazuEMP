# Director Adversarial Authentication Audit — 2026-11-26

**Status:** Investigation in progress. This is a source-inspection checkpoint, not a completed penetration test or release clearance.  
**Repository:** Leano-Jordan/ZazuEMP  
**Reference:** main at ebeda7a266c0564a0cff48bcf96a7335028d55d1 when this checkpoint began.  
**Scope:** Session fixation/hijacking and concurrent account transitions; browser credential exposure/XSS/backups; pairing-code brute force/device takeover; cross-business token substitution/stale replay; password reset/owner removal/device revocation races; offline logout/account switching/queued transaction preservation.

## Executive position

The current architecture has several sound controls: session ID regeneration at login and registration, session invalidation on logout, auth.session middleware on the main authenticated workspace, one-use expiring hashed pairing codes, random device bearer tokens stored server-side as SHA-256 hashes, device active/expiry checks, operation-level device revalidation, business-scoped sync queries, and explicit offline queue preservation across web logout and workspace switching.

That is not sufficient evidence to call the system hardened. The offline credential is deliberately stored unencrypted in business-scoped IndexedDB, so the unlocked browser profile and same-origin JavaScript remain trusted. Pairing protection is currently an IP-based 30-per-minute limit on the public provisioning endpoint; distributed guessing and expensive candidate scanning need adversarial validation. Account/session transition races and owner-removal semantics need targeted tests.

No exploit against a live system was attempted. No production data was accessed or changed.

## 1. Session fixation, hijacking and concurrent account transitions

### Verified controls

- AuthController::store uses Auth::attempt and then regenerates the session ID.
- Registration uses Auth::login followed by session regeneration.
- Logout calls Auth::logout, invalidates the session and regenerates the CSRF token.
- Password reset rotates the remember token, logs the user in on a regenerated session, deletes other database-backed sessions, invokes logoutOtherDevices, and revokes trusted devices for currently owned businesses.
- Session defaults: database driver, 120-minute lifetime, HttpOnly cookie, SameSite=Lax, Secure in production by default. SESSION_ENCRYPT defaults false; this concerns server-side session storage, not whether the browser cookie is encrypted by Laravel's cookie middleware.
- Main workspace routes use auth, auth.session, and business.context.

### Open adversarial checks

- Reuse a pre-authentication session ID after login/registration and prove it cannot authenticate.
- Race two login/logout/password-reset requests sharing the old session; prove stale writes cannot resurrect an authenticated session or overwrite the selected business context.
- Test password reset against concurrent authenticated requests and remembered-device cookies.
- Review the business.switch route separately: it is in an auth group, not the auth.session group. Establish whether stale password-hash sessions can still mutate the selected business context and whether this is only a harmless context write or a meaningful bypass.
- Test session cookie attributes under actual production HTTPS/reverse-proxy configuration, not only config defaults.

## 2. Credential leakage through browser storage, XSS and backups

### Verified controls and limitations

- The sync bearer is stored in state.sync.token inside business-scoped IndexedDB and sent as an Authorization bearer header.
- It is not intentionally stored in localStorage/sessionStorage or URLs. The pending logout-revocation marker stores only business ID, installation UUID and request timestamp.
- Offline logout clears CacheStorage authenticated pages but deliberately retains the IndexedDB business workspace and bearer; the owner-facing warning distinguishes sign-out from trusted-device revocation.
- CSP uses a per-response nonce, disallows unsafe-inline, blocks style attributes, and limits script sources to self plus nonce. CSP does not contain a same-origin script that is already trusted.
- Business archive restore refuses a target containing sync-device state; archive definitions do not import trusted-device credentials.
- The application does not encrypt IndexedDB workspace data, attachment blobs or the bearer token. This remains a documented HIGH residual risk for copied browser profiles, unlocked devices and same-origin script compromise.
- Database backups may contain device rows and SHA-256 bearer hashes. A hash is not itself a valid bearer token, but backup confidentiality and restore handling still require review.

### Open adversarial checks

- Search every browser persistence, URL, console, telemetry, exception and diagnostic path for raw bearer/pairing values.
- Inspect every generated business archive and full-install backup manifest for device credentials, reset tokens, session rows and sensitive metadata.
- Inject hostile customer/job/attachment content into the actual rendering paths and verify no script execution or credential exfiltration is possible.
- Verify logout/revocation cache behaviour across all service-worker cache versions and multiple open tabs.
- Verify backup encryption, access control, retention and disposal at the intended installation target.

## 3. Pairing-code brute force and device takeover

### Verified controls

- Pairing code is generated with random_int(10000000, 99999999) (eight decimal digits).
- Default lifetime is 10 minutes; owner may request 5–30 minutes.
- Only the password hash of the pairing code is stored.
- Provisioning endpoint is public by design and has throttle:30,1 (IP-based Laravel limiter).
- Provisioning locks pending candidates, checks expiry and hash, changes status to active and nulls pairing hash/expiry in the same database transaction. A consumed code is therefore not reusable after successful provisioning.
- Pairing and sync API responses receive no-store/private cache headers.
- Successful pairing returns a 64-character random bearer; only SHA-256 hash is stored.

### Preliminary risk requiring adversarial validation

The current throttle is per source IP, not per pairing code, pending device or business. A distributed attacker can obtain more attempts from multiple source IPs. Each invalid request scans all currently pending, unexpired devices and performs a password-hash check for each candidate; this can become a resource-amplification vector as pending pairings accumulate. This is a source-derived risk hypothesis, not a demonstrated takeover.

### Open adversarial checks

- Test one-use semantics under concurrent correct-code requests.
- Test expired, malformed, reused and revoked pairing attempts.
- Measure provisioning cost with realistic pending-device counts and invalid guesses.
- Establish whether multiple source IPs can guess a live code within its validity window at a practical rate.
- Check pending-code cleanup, uniqueness/collision handling and whether an attacker can pair against the wrong business when several businesses have active pairing codes.
- Evaluate layered controls that do not lock out legitimate phone pairing: short-lived pairing identifier, attempt accounting, global/service-level abuse controls, bounded pending candidates and owner-visible pairing alerts.

## 4. Cross-business token substitution and stale-token replay

### Verified controls

- Sync middleware hashes the presented bearer and requires an active device with unexpired token.
- Device activity refresh is conditional on the same active state, token hash and unexpired timestamp, followed by a fresh authorization check.
- SyncDeviceAccessGuard locks the business and device, verifies active status and compares the authenticated snapshot's token hash to the current hash.
- Bootstrap, pull, acknowledgement, push, conflict, attachment and revoke paths use operation-level revalidation through their protocol/controller services.
- Business IDs are derived from the authenticated device record, not accepted as caller-selected tenant scope. Sync queries filter by that device's business ID.
- Existing tests cover revoked/replaced snapshots, stale pulls/acks/bootstrap, attachment access, conflict handling, business switching and installation rebind attempts.

### Open adversarial checks

- Attempt every sync route with a token from business A while varying any business, device, entity and attachment identifiers to business B.
- Race token replacement/revocation against bootstrap, pull, acknowledgement, push, conflict resolution, attachment upload/list/download and retry.
- Confirm no endpoint bypasses SyncDeviceAccessGuard or equivalent protocol locking.
- Verify old token cannot revoke a replacement token, and new token cannot inherit another installation's business data.

## 5. Password reset, owner removal and device revocation races

### Verified controls

- Password reset changes password and remember token, invalidates other database sessions, calls logoutOtherDevices, and revokes all sync devices/pairings in businesses where the user currently has the owner role.
- Device revocation locks the target row, removes bearer/pairing hashes and expiry, marks it revoked and audits the change.
- Sensitive sync operations lock/revalidate device state so revocation and protected operations serialize.
- The inspected route/controller tree did not expose a dedicated business-member removal endpoint. Owner removal semantics therefore cannot be certified from this pass; if membership removal is implemented elsewhere or introduced later, it must atomically revoke affected approvals/devices or explicitly preserve business-owned trusted-device access under a documented policy.

### Open adversarial checks

- Race password reset with active sync push/pull, new pairing, primary-device registration and device reapproval.
- Test reset-token replay, expiry, enumeration resistance and concurrent reset consumption.
- Test owner membership removal/demotion against pairing creation, device reapproval, pending pairing and active device access.
- Decide and document whether removing an owner revokes all business devices they approved, all devices for the business, or neither; business-level credentials and owner attribution must not be conflated.
- Verify reset/revocation events are audited without logging credentials.

## 6. Offline logout, account switching and queued transaction preservation

### Verified controls

- The offline runtime uses separate IndexedDB databases per business ID, not per user ID. This is consistent with the documented DEC-019 shared business workspace model; it is not per-staff isolation.
- Offline logout intentionally preserves the business workspace and queued mutations when the web host is unreachable; it does not falsely claim the server session was ended.
- On successful web logout, the current E2E contract preserves queued changes and attachments on the device. The web session and the offline trusted-device credential are separate boundaries.
- Workspace switching retains separate IndexedDB business queues, clears authenticated page caches for the previous context, and restores the original business queue when switching back.
- Offline logout revocation retry stores only a non-secret installation identity in localStorage; it retries through the next owner-authenticated web session.
- Pairing increments a local session generation, aborts in-flight sync and preserves pending local work during re-pairing.

### Open adversarial checks

- Switch account A → account B in the same browser profile, including two tabs and an in-flight write; verify no stale DOM/cache response is shown under the new identity.
- Verify the intended DEC-019 behaviour where multiple authorised staff use the same trusted business profile, including clear attribution disclosure.
- Switch between businesses while a local mutation, attachment write, upload response or sync acknowledgement is in flight; prove no cross-business queue contamination or data loss.
- Simulate host loss during logout, server-side revocation, password reset and account switch. Prove the UI never reports server revocation before confirmation and queued financial changes remain recoverable.
- Test stale service-worker pages after account change, session expiry and cache migration.

## Audit disposition at this checkpoint

| Area | Source-inspection result | Adversarial test status |
|---|---|---|
| Session fixation / hijacking | Strong baseline controls present; concurrent transition behaviour not yet proven | OPEN |
| Browser credential leakage / XSS / backups | Known HIGH residual risk: unencrypted IndexedDB bearer and business data | OPEN |
| Pairing brute force / takeover | Eight-digit one-use code, 10-minute default, IP throttle; distributed guessing and scan amplification need testing | OPEN |
| Cross-business substitution / stale replay | Strong business scoping and operation-level credential guards present | OPEN |
| Password reset / owner removal / revocation | Reset revokes devices for current owner memberships; member-removal lifecycle not found in inspected routes | OPEN |
| Offline logout / account switching / queued work | Business-scoped queue preservation and separate credential/session boundaries documented | OPEN |

**No overall security PASS is issued.** The next engineering gate is to add focused adversarial regression tests for pairing abuse, concurrent account/session transitions, reset/revocation races, cross-business identifier substitution, and offline multi-tab account changes. Findings must be classified by demonstrated exploitability, impact and affected trust boundary before implementation decisions are made.
