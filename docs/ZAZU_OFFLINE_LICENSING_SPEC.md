# Zazu Offline Licensing Specification

**Status:** Active architecture direction; signed-token verification implemented at service level, activation UI/enforcement pending  
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

Required payload fields: `version=1`, local `business_id`, UUID `license_id`, `plan`, `status` (`active` or `revoked`), `starts_at`, `issued_at`, nullable `expires_at`, and a `features` object. Expiry must not precede the start date. A token for another business is rejected. An older signed renewal cannot replace a newer signed entitlement; the signed issue timestamp is retained locally for this rollback check. Token input is size-bounded. A signed revoked token imports as inactive. Invalid signatures, malformed payloads, missing key configuration and unavailable sodium verification fail closed.

The app is configured with `ZAZU_LICENSE_PUBLIC_KEY` only: base64-encoded Ed25519 public key. The issuer's private signing key must remain outside this repository and outside customer installations. Never commit a real private key or use a development test key in production.

## Current implementation stage

The repository contains the local entitlement data foundation, local validity service and an Ed25519 signed-token import/verification method. The former unsigned `issueLocalEntitlement` helper has been removed so application code cannot use that service API to mint an entitlement without a valid issuer signature. Feature tests have been added for valid import, signature tampering, cross-business rejection and signed revocation. The service method is not yet connected to an owner activation screen or route, and there is no production issuer/key-management workflow or application-wide enforcement boundary.

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
