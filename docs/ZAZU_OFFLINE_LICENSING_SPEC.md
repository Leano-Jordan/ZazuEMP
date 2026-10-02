# Zazu Offline Licensing Specification

**Status:** Active architecture direction  
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

## Current implementation stage

The repository now contains the local entitlement data foundation and local validity service.

This stage intentionally does **not** enforce licensing across the application yet. Enforcement should only be introduced after the activation/recovery UX, renewal behaviour and owner-safe recovery path are tested.

## Future stages

1. Signed offline license format.
2. Owner activation screen.
3. Installation identity and safe reactivation.
4. Owner-visible license status/expiry.
5. Renewal/import flow that works offline.
6. Application enforcement at the correct commercial boundary.
7. Optional online licensing/backup/synchronization service later.

