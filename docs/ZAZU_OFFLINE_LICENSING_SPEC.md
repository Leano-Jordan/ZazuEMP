# Zazu Offline Licensing Specification

**Status:** Signed-token verification/import and experience-tier ceiling implemented; production issuer and module-level feature enforcement pending  
**Date:** 2026-10-02

## Purpose

Zazu must be licensable for paying customers without requiring an internet connection to keep the core application running.

The first Zazu customers are expected to be solo or very small businesses. Their normal business operations must not stop because mobile data, Wi-Fi or the internet is unavailable.

## Licensing model

A Zazu business has a local commercial entitlement stored in its own Zazu installation.

The entitlement records:

- business;
- plan;
- activation/status;
- start date;
- expiry date where applicable;
- enabled product capabilities;
- local activation/check information.

The local entitlement is the authority used by the installed application while offline.

## What this means for the first customers

There is **no Zazu cloud server requirement for first-customer licensing**.

A customer can receive a license/activation from Rosscore and activate Zazu locally. After activation, Zazu can determine whether the entitlement is valid without contacting Rosscore.

Subscription renewal can therefore be handled by issuing a new local license/activation value. Internet is not required for the application to continue operating between renewals.

## Important separation

Licensing is separate from backup.

- **License:** answers whether this installation/business is entitled to use Zazu.
- **Backup:** protects the customer's business information.
- **Cloud service:** is a future online service for optional backup, synchronization and connected features.

The first release must not pretend that an online Zazu backup exists when it does not.

## Offline behaviour

An active local license must permit the core offline-capable business workflows to continue.

Loss of internet must not:

- log the owner out solely because the connection disappeared;
- disable customer/job/quote/requirements work;
- disable recording of payments received;
- disable purchasing, preparation, costs or other core local work;
- require a remote license check.

## Renewal

For the initial commercial model, a paid period may expire locally.

When that happens, the product should provide a clear renewal path rather than silently failing.

The renewal path must support a manually supplied new entitlement so that a customer is not forced to have internet merely to renew an offline installation.

## Security direction

The production license value should be cryptographically signed by Rosscore and verified locally by Zazu.

The signing private key must never be shipped with Zazu.

Zazu should contain only the public verification key.

The production activation flow should also distinguish:

- valid;
- expired;
- not yet active;
- revoked;
- malformed/tampered.

Do not use a shared secret embedded in the application as the final production licensing mechanism.

## Signed offline token format — version 1

The local verifier accepts a two-part token: `base64url(payload JSON).base64url(Ed25519 detached signature)`. The exact UTF-8 payload bytes are signed; the verifier checks the detached signature before parsing or trusting any fields.

Required payload fields: `version=1`, local `business_id`, UUID `license_id`, `plan`, `status` (`active` or `revoked`), `starts_at`, `issued_at`, nullable `expires_at`, and a `features` object. All timestamps must include seconds and an explicit timezone (for example `2026-11-10T14:30:00+02:00` or `2026-11-10T12:30:00Z`); timezone-less and invalid calendar dates are rejected rather than interpreted using the installation's local timezone. Expiry must not precede the start date. A token for another business is rejected. A distinct signed payload must have a strictly newer issue timestamp than the installed entitlement, regardless of whether the issuer reuses the same `license_id`. The exact signed payload's SHA-256 fingerprint is retained so an identical token can be re-imported idempotently, while a different payload cannot reuse the same issue timestamp. Imports are serialized under a per-business database row lock to prevent concurrent older/newer token races. Older active tokens therefore cannot reverse a later signed revocation. Token input is size-bounded. A signed revoked token imports as inactive. Invalid signatures, malformed payloads, missing key configuration and unavailable sodium verification fail closed.

The app is configured with `ZAZU_LICENSE_PUBLIC_KEY` only: base64-encoded Ed25519 public key. The issuer's private signing key must remain outside this repository and outside customer installations. Never commit a real private key or use a development test key in production.

## Current implementation stage

The repository contains the local entitlement data foundation, local validity service and an Ed25519 signed-token import/verification method. The former unsigned `issueLocalEntitlement` helper has been removed so application code cannot use that service API to mint an entitlement without a valid issuer signature. Feature tests cover valid import, signature tampering, cross-business rejection, signed revocation, expiry, missing public-key configuration, strict timestamp parsing, same-ID rollback after revocation, exact-token idempotency, same-timestamp payload substitution and incomplete-metadata rejection.

The owner Business Settings page now displays the installed entitlement's plan, status, start and expiry dates and provides a signed-token import form. The POST endpoint is inside the authenticated business-context and owner middleware boundary, is rate-limited, rejects oversized/missing tokens, verifies the signed token against the active business, and records a business-scoped audit entry without storing the raw token in the audit log or old form input. A successful import/renewal is available offline once the token has been supplied.

This is an activation/import interface, not a production issuer. The application enforces the workspace entitlement ceiling for experience presentation, but there is not yet a module-level feature-gating matrix. Production issuer/key-management workflow and installation identity/reactivation remain outstanding. Do not represent the full paid-feature matrix as commercially enforced until those controls are implemented and verified.

Licensing is intentionally separate from full offline application operation. A valid license permits entitled local use; it does not by itself provide phone-local data storage, offline mutation queues or synchronization.

Full offline operation follows the separate contract in `docs/ZAZU_OFFLINE_FIRST_ARCHITECTURE.md`.

## Future stages

1. Signed offline license format.
2. Owner activation screen.
3. Installation identity and safe reactivation.
4. Owner-visible license status/expiry.
5. Renewal/import flow that works offline.
6. Application enforcement at the correct commercial boundary.
7. Optional online licensing/backup/synchronization service later.



## Signed offline licence verifier — current-head verification — 2026-11-10

- Refactored Ed25519 token verification into `SignedOfflineLicenseVerifier`; `OfflineLicenseService` now coordinates verification, anti-rollback and persistence. The service no longer exposes unsigned local entitlement issuance.
- Current regression coverage: valid import, expiry, tamper rejection without replacing an existing entitlement, cross-business rejection, signed revocation, missing public key fail-closed, and rejection of an older renewal token.
- PHPMD initially rejected the monolithic verifier for cyclomatic/NPath/method-length complexity. The verifier was split into bounded methods and a dedicated verifier class; the PHPMD workflow now passes.
- Full Laravel suite on commit `b9fc97d58e923200caf7e515926453b390aab0f3`: **422 passed, 2,985 assertions**. This run also caught two stale source-contract assertions in `PwaOfflineFoundationTest`; those were corrected to match the actual `dataset` API and local-event-ID method signature before this passing run.
- Current-head checks: PHPMD **PASS**; Psalm security scan **PASS**; Zazu quality workflow **PASS**; populated upgrade/rollback workflow **PASS**; Laravel **PASS**. Browser smoke is still running and is not yet accepted as passed.
- Relevant commits: `79f41fc` initial verifier; `f01371a` remove unsigned issuer; `39194b5` input bound/anti-rollback; `5544e07` verifier refactor; `632e148` dedicated verifier; `9e82b7c` DI; `5a54a21` attachment DOM regression correction; `b9fc97d` local attachment method-signature regression correction.
- Remaining licensing blockers: owner activation/status UI, production issuer and private-key custody, installation identity/reactivation, renewal UX, and enforcement at the correct business boundary. No readiness score uplift yet; V1 remains **NO-GO** pending browser smoke and remaining release gates.


## Director reconciliation — activation implementation and enforcement boundary (2026-11-10)

The current implementation has advanced beyond the original stage list above. Treat the following as the authoritative current status; the historical stage list is retained for audit history.

- **Implemented in source:** owner Business Settings status display and signed-token import/renewal; Ed25519 detached-signature verification; local business binding; explicit-timezone date validation; expiry/revocation status; anti-rollback and idempotent re-import; per-business import serialization; owner-only authenticated/rate-limited route; business-scoped audit record; no raw token in audit data or flashed old input.
- **Automated regression coverage present:** valid offline import, expired and revoked states, signature tampering, cross-business rejection, missing-key fail-closed behaviour, strict timestamps/calendar dates, rollback prevention and owner authorization.
- **Not implemented / not certified:** production issuer and private-key custody; installation identity and reactivation; application-wide plan/feature enforcement; storage/user limits; and end-to-end production licence lifecycle.
- **Important boundary:** the presence of a signed entitlement record and BusinessLicense::isActive() does not itself enforce commercial access. Current route declarations continue to use role/permission middleware; no global entitlement gate was evidenced in the inspected route group. Do not add a blanket “no licence = blocked” middleware: that would risk locking existing workspaces and conflicts with the agreed Basic Free fallback and 30-day trial model.
- **Commercial dependency:** enforceable entitlements require an owner-approved Basic/Advanced feature matrix and exact downgrade behaviour. The current commercial profile describes limits as proposed, not final. Until those rules are approved, implementation must not invent limits or silently disable customer data/workflows.
- **Production key custody:** never generate or commit a real issuer private key in this repository. Production signing must be performed in a separate controlled issuer environment with protected backup and documented recovery/rotation. The app receives only the public key.

### Next safe engineering sequence

1. Keep exact-head CI green and finish the current browser smoke workflow.
2. Reconcile and approve the Basic/Advanced entitlement matrix, including the existing 30-day trial → Basic Free downgrade and data-preservation rules.
3. Implement a central, testable entitlement evaluator and apply it only to explicitly premium capabilities after the matrix is approved. Core local business data access must remain available in Basic Free and offline.
4. Add adversarial tests for downgrade, expired/revoked tokens, direct-route bypass, queued offline mutations and preservation/export of customer data.
5. Build the issuer separately from the customer app; complete installation identity/reactivation and key-custody controls before claiming commercial enforcement.

No V1 readiness uplift is claimed by this documentation reconciliation. Release remains **NO-GO**.


## Three-tier lifecycle alignment — 2026-11-13

The owner has confirmed that the three experience levels are also the three commercial tiers. Their canonical identifiers are `basic`, `intermediate` and `advanced`, matching `config/zazu.php`.

- A signed active entitlement may carry one of those three plan identifiers.
- A 30-day trial uses the Advanced tier and its signed expiry timestamp; when it expires, the effective commercial tier resolves to Basic Free.
- An expired, not-yet-active, revoked, absent or unrecognised entitlement must resolve to Basic Free. It must never create a workspace lockout.
- The tier resolver is a commercial-state decision, not permission to invent feature restrictions. Until the owner-approved capability matrix is implemented and tested, it must not disable routes or prevent reading, exporting or retaining customer-owned data.
- The existing per-membership experience preference remains a presentation choice; it must not mint, upgrade or replace the signed business entitlement. The business entitlement is the commercial source of truth.
- Intermediate and Advanced capability allocations and public prices remain unapproved. No limits or feature gates are inferred from the labels alone.



## Three-tier workspace entitlement lifecycle — 2026-11-13

The owner has confirmed that Basic, Intermediate and Advanced are the three commercial tiers and the matching experience levels. The workspace entitlement is business-wide; individual members retain their own experience preference within the workspace's maximum tier.

- Basic is the permanent Free fallback.
- An eligible new workspace receives an Advanced trial for 30 days from its business creation timestamp. The trial ends at the exact creation timestamp plus 30 days.
- At trial expiry, the effective tier becomes Basic Free.
- An active signed Intermediate entitlement permits Basic and Intermediate experience levels. An active signed Advanced entitlement permits all three.
- The former signed plan identifier `solo` remains an Advanced compatibility alias. New issuer output should use `basic`, `intermediate` or `advanced`.
- Any recorded licence takes precedence over trial calculation. Expired, revoked, not-yet-active or unrecognized entitlement state resolves to Basic; it does not restart the trial.
- A saved member preference above the current entitlement is presentation-clamped without overwriting the stored preference. A later valid upgrade can restore the prior choice.
- The experience-level ceiling is enforced in onboarding and self-service preference updates. This is not module-level feature enforcement.
- No module-level premium feature gates, event-count, team-size, storage or other numerical restrictions are implemented by this lifecycle change. Those require an owner-approved three-tier matrix and explicit data-safe downgrade rules.
- Existing role/permission and business-isolation boundaries remain authoritative. No blanket entitlement middleware is added to customer-data routes. Expiry/revocation must never delete, lock or withhold customer-owned records.
- Production issuer/private-key custody, installation identity/reactivation and final module-level entitlement matrix remain outstanding. Do not claim a complete paid subscription system until those controls are implemented and verified.

The central `BusinessEntitlement` resolver determines the business-wide ceiling. `ExperienceLevel` applies that ceiling to each member's saved presentation choice. `OfflineLicenseService` uses the same resolver so the trial, installed licence and preference UI cannot report conflicting effective tiers.
