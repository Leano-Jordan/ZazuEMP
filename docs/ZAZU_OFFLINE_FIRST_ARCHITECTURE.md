# Zazu EMP — Offline-First Architecture Contract

**Status:** ACTIVE ARCHITECTURE DIRECTION  
**Director update:** 2026-10-03  
**Current source baseline:** main

## 1. Non-negotiable product rule

Zazu is **offline-first**.

Internet connectivity is an enhancement, not the foundation of everyday business operation.

The core product must be capable of doing normal business work when the internet is unavailable. Online mode may add richer connected features, synchronization, remote backup and integrations, but it must not make the local business workflow dependent on the cloud.

## 2. Current architecture

The current repository is a Laravel modular monolith with a local relational database.

Current local authority:

**Laravel application + local database + local/private storage**

Current browser layer:

**responsive web UI + service worker for static assets**

Current offline licensing layer:

**local BusinessLicense + OfflineLicenseService**

Current limitation:

The phone workspace now maintains a local IndexedDB working copy and supports local creation/editing of the current mobile operational records without a PC. Offline writes are durably queued locally and can be transmitted to the host sync foundation after owner-approved pairing. The complete production business domain is not yet mirrored locally.

Therefore the present application is **phone-capable and locally persistent for the current mobile workspace, but not yet fully synchronized offline across the complete Zazu business domain**.

## 3. Target operating modes

### Mode A — Local single-device
Primary early-customer mode.

- Zazu runs locally on the owner's PC/laptop.
- The local database is authoritative.
- Core workflows continue without internet.
- Backup/export are local operations.
- Internet is optional.

### Mode B — Local-network Zazu
Next practical expansion.

- The PC/laptop acts as the local Zazu host.
- Phones/tablets connect over the owner's local network or hotspot.
- The host database remains authoritative.
- Mobile clients do not require public internet to use Zazu.
- Losing internet must not break local-network operation.

### Mode C — Device-local continuity
Required for true phone-first resilience when the host is unavailable.

- The phone/browser keeps a safe local working set.
- Local reads remain available.
- Approved mutations are recorded locally.
- Mutations receive durable local identifiers.
- Pending work is visible to the user.
- When a trusted host becomes reachable, queued mutations synchronize.

### Mode D — Connected cloud
Later optional service.

- Cloud becomes a synchronized copy/service layer.
- It does not replace local authority for offline-capable installations.
- Remote backup, cross-device access, collaboration and other connected features can be added.
- Conflict handling becomes explicit rather than silent last-write-wins.

## 4. Data ownership model

The existing relational model already provides the business ownership spine:

Business
→ Memberships / Users
→ Customers
→ Events / Work
→ Requirements / Capabilities
→ Quotes / Versions / Items
→ Preparation
→ Purchasing / Receiving
→ Inventory / Assets
→ Costs
→ Invoices / Payments / Expenses
→ Attachments / Documents
→ Audit

Every synchronized record must retain its business ownership and parent relationships.

A sync mechanism must never become a second source of business truth.

## 5. Offline data classes

### Safe local operational data
Good candidates for local copies and offline writes:

- customers and contacts;
- jobs/events;
- requirements;
- services/capabilities;
- preparation items;
- assets;
- inventory records and movements;
- suppliers;
- purchase orders and receiving;
- costs;
- invoices;
- payment records;
- expenses;
- business settings required for local calculations and documents.

### Local presentation state
Can be device-specific and does not require business synchronization:

- theme;
- helper state;
- primary presentation focus;
- experience presentation preference;
- navigation state;
- draft UI state.

### Private files
Attachments, receipts, photos and business documents require a separate local-file/synchronization path.

A database row alone is not proof that the file exists locally or has synchronized.

### Online-only enhancements
Examples:

- cloud backup;
- remote notifications;
- external messaging integrations;
- AI enrichment;
- maps/other external services;
- cross-location connected features.

These may fail gracefully without disabling core local work.

## 6. Offline mutation model

Future disconnected mutations should follow:

**User action → local transaction → local durable change record → visible pending state → synchronization → server/host acknowledgement → reconciliation**

Every mutation needs:

- stable local mutation identifier;
- business identifier;
- entity type and entity identifier;
- operation;
- created time;
- originating device/install identifier;
- payload or deterministic replay information;
- retry state;
- failure reason where applicable.

The mutation record is not a replacement for the authoritative business record. It is the transport/replay history for synchronization.

## 7. Conflict policy

Do not introduce silent conflict resolution for consequential records.

Conflicts require classification.

Examples:

- independent additions → merge;
- same record, non-overlapping fields → potentially merge;
- same financial value changed in two places → require review;
- stock movement created independently → preserve both movements, then reconcile derived quantity;
- status transition conflict → apply domain state rules and surface the conflict;
- attachment added on two devices → preserve both unless explicitly removed.

Financial, inventory and permission state must never be silently overwritten by a stale device.

## 8. Idempotency

The current application already uses idempotency and business-scoped uniqueness in important commercial mutations.

Offline synchronization must preserve that property.

A retry must never duplicate:

- payments;
- expenses;
- purchase receipts;
- inventory movements;
- other consequential mutations.

## 9. Identity

Future installations/devices require stable identities independent of the internet.

Target concepts:

- installation identity;
- device identity;
- local record identity where needed;
- server/cloud identity after synchronization.

Numeric database IDs remain useful inside one local database, but distributed synchronization must not depend on locally generated numeric IDs being globally unique.

The initial stable-record-identity foundation is implemented in `sync_entity_identities`. It maps a business-owned local record and stable entity type to a UUID, scoped uniquely within that business. Repeated registration is idempotent; importing an identity cannot reassign it to another record or entity type. This registry is not yet connected to domain mutation handlers or a device bootstrap transport.

## 10. UI rule

Offline mode should not be a degraded "error page".

The product should clearly communicate:

**Offline — working locally**

rather than:

**No internet — feature unavailable**

Only actions that genuinely require connectivity should be unavailable, and the UI should explain why.

Online mode should enrich the experience without making offline users feel like they are using a broken edition of Zazu.

## 11. What the current service worker should do

The service worker now supports the implemented device-local continuity model. It precaches the shared Zazu client assets and the real application shell, then caches successful authenticated HTML responses only inside a user-scoped cache derived from the authenticated Zazu user identity. It also retires older cache generations during activation.

The service worker is not the business-data authority. Business records and pending mutations remain in the local IndexedDB client store; synchronized server data remains business-scoped on the Laravel backend. The cache must never be treated as a substitute for local business state or as a cross-user shared cache.

Do not broaden authenticated caching beyond this user-scoped shell/navigation role merely to claim "offline support".

## 12. Migration path

The architecture should evolve incrementally:

**Current local database → resilient local reads → local business-data store → queued writes → synchronization protocol → conflict handling → optional cloud**

Each stage must be independently usable and testable.

Do not build a complete distributed synchronization system before local offline workflows are measured and proven.

## 13. Release gates for true offline capability

Before claiming full offline operation:

- fresh install can establish local authority safely;
- normal customer/job workflow works disconnected;
- records survive browser/app restart;
- offline mutations survive restart;
- queued changes are visible;
- reconnection retries safely;
- duplicate delivery does not duplicate business effects;
- parent/child ownership remains intact;
- financial and stock reconciliation remains correct;
- attachment synchronization is deterministic;
- conflicts are explainable and recoverable;
- a failed sync never corrupts the local authoritative state.

## 14. Current status

**Implemented foundation**
- local relational business data model;
- business-scoped ownership enforcement;
- transactional/idempotent critical mutations;
- local private storage;
- offline licensing foundation;
- static-asset service worker;
- responsive UI;
- source mutations and per-device delivery/cursor tracking;
- business-scoped stable UUID identity registry for existing local records.

**Not yet complete**
- complete phone-local mirror/read parity for every V1 business screen and workflow;
- explicit conflict-resolution UX for consequential concurrent changes;
- full bidirectional incremental synchronization/read reconciliation across the complete business domain;
- cloud synchronization.

The current implementation now covers the major operational mutation domains listed above, but source implementation alone is not release proof. Runtime execution of the newly added regression set, physical disconnected-device trials, attachment synchronization and conflict/recovery behaviour still require evidence.

This contract therefore establishes the architecture direction without falsely claiming those future layers already exist.

## Director implementation update — 2026-10-06

The phone-local foundation is now implemented beyond static PWA caching:

- IndexedDB persists the local workspace and durable mutation queue.
- The installed phone workspace supports offline customer, job and service creation/update.
- Browser storage persistence is requested where supported to reduce eviction risk.
- Reconnect pushes queued mutations to the sync endpoint.
- Customer, job/event and service mutations now have explicit server-side handlers with business scoping, whitelisted fields and synchronization identities.
- Purchasing, receiving, supplier maintenance, quotes, quote acceptance, invoices, payments and expenses now have explicit offline domain contracts.
- Inventory items, inventory movements, operational event costs, assets and asset allocation/return now have explicit offline domain contracts.
- Replayed applied mutations are idempotent and do not duplicate records.
- Unsupported mutation types remain pending rather than being guessed into domain state.
- Local dirty state prevents a bootstrap refresh from overwriting unsynchronised phone changes.

The phone workspace is a **broad offline operational foundation**. Attachment/file synchronization and explicit conflict-resolution UX are now implemented, including local attachment queuing, server transfer, idempotency, conflict retry/discard and browser regression coverage. It is **not yet a complete disconnected replacement for the full web application**; full incremental bidirectional synchronization, broader capability parity and final realistic offline/recovery proof remain open.


## Director hardening update — 2026-10-06

The offline phone queue now reconciles server receipts deterministically:

- successfully applied mutations are removed from the local pending queue;
- rejected mutations remain visible as failed/pending rather than being silently discarded;
- local dirty state is recalculated after every reconnect attempt;
- already-applied mutations are therefore not repeatedly resent merely because the phone restarted;
- the phone UI no longer offers quote creation as an offline mutation while the server-side quote domain handler is not implemented.

This preserves the rule that offline capability must expose only workflows that have a safe synchronization contract.


## 2026-10-07 attachment sync implementation

The phone workspace now has a dedicated attachment transfer layer alongside the mutation queue:
- attachments use separate IndexedDB storage so binary files do not inflate the JSON mutation queue;
- files up to 20 MB use the existing private attachment storage policy and an idempotency UUID;
- online upload is business-scoped and retry-safe;
- offline-created attachment records remain on the phone as pending files and are retried when connectivity returns;
- synced attachment metadata can be listed by event and downloaded back into local phone storage;
- local phone opening does not require the PC after the attachment has been stored locally.

This still deliberately distinguishes **server identity** from local identity: the current attachment transfer targets already-synced jobs with a server event ID. Attachments on a brand-new local job remain queued until that job receives its server identity.


## 2026-10-07 sync atomicity hardening

The server-side mutation path now publishes the resulting sync delivery inside the same database transaction that applies the domain mutation. If delivery creation fails, the domain write and applied marker roll back together instead of leaving an applied mutation with no downstream delivery. A focused regression test covers this failure boundary.

## Director offline-origin and acceptance contract — 2026-10-16

**Parent policy:** [Rosscore Labs Offline-First and Disconnected-Operation Standard](https://github.com/Leano-Jordan/Rosscore-Labs/blob/main/docs/ROSCOR_OFFLINE_FIRST_STANDARD.md). Zazu inherits that terminology and evidence contract; this file records Zazu-specific capability and proof.

### Supported launch origin

The reported address `http://192.168.137.237:8000` is a PC-hosted LAN development address. It has two separate failure modes:

- `ERR_CONNECTION_REFUSED` means the browser could not reach a server accepting connections at that address and port. A cached PWA cannot make a stopped Laravel server available for server-dependent requests.
- Plain HTTP on a non-loopback LAN IP is not a secure browser context. Browsers restrict service-worker registration to secure contexts (normally HTTPS; loopback development origins are treated specially). See [MDN secure contexts](https://developer.mozilla.org/en-US/docs/Web/Security/Secure_Contexts).

Changing the LAN IP, leaving the PC server running, or opening the same development URL is not an offline delivery solution. A PWA must first be installed from a supported secure origin, then its shell, required assets, application data and declared offline workflows must be persisted locally. A packaged local app is the alternative when the intended delivery cannot depend on a secure hosted origin.

### Current implementation boundary

The service worker caches the static shell assets and selected authenticated HTML routes. The phone workspace separately persists a bounded set of operational records and queued mutations in IndexedDB. These mechanisms do not yet constitute a complete local replacement for every server-rendered screen and business workflow. Do not describe the current source as fully offline-capable across the entire Zazu domain until that parity has been implemented and proven.

The client now shows an explicit notice when opened in a browser context that cannot register a service worker, rather than silently pretending offline installation is available. This notice diagnoses the launch origin; it does not itself make the application offline.

### Required offline acceptance evidence

The offline release gate is only satisfied when a cleanly installed application:

1. Opens from its installed app icon after force-close, with both internet connectivity and the PC/development host unavailable.
2. Navigates the declared core screens without network calls being required to render the supported offline UI.
3. Reads locally persisted records and saves each declared offline mutation durably.
4. Retains changes after reload and a full close/reopen, with pending sync state visible where applicable.
5. Reconnects and reconciles queued work without losing changes or duplicating consequential records.
6. Reports online-only features honestly and fails gracefully when disconnected.

Source assertions and cloud CI are necessary but are not physical-device proof. The actual installed-phone test remains an explicit release gate.



## Device-local logout and queued-change safety

A logout request must treat the service-worker page cache and IndexedDB workspace as separate data stores. Clearing only cached HTML is insufficient: the local client database contains workspace data, queued mutations and the device sync token.

- Inspect the full local mutation queue before logout, including rejected/failed entries and unresolved conflicts.
- If queued work or conflicts remain, explain that signing out clears the local offline copy and request explicit confirmation. Cancelling must keep the device data intact.
- If IndexedDB cannot be inspected or cleared safely, do not silently sign out and leave the offline token/workspace behind. Explain the recovery action.
- On confirmed logout, clear both the local state store and mutation queue, then clear the authenticated service-worker page cache.
- Notify other same-origin tabs to discard in-memory offline credentials and prevent new offline writes from the signed-out session.
- After local data has been cleared, the client attempts a bounded server-side token revocation through `POST /api/sync/revoke`. If offline or unreachable, logout continues but warns that the server token may still be active.
- The endpoint invalidates the current bearer token immediately for subsequent sync requests. It does not provide a remote owner/admin device-management screen, and offline revocation cannot be proven. Treat manual device revocation/management and device-offline cleanup as a separate release risk.
- Automated regression coverage must verify cancel-preserves-queue and confirm-clears-token, workspace and queue. Installed-device acceptance remains a separate verification layer.


## Offline asset cache versioning

The service worker serves `/zazu-offline.js` and `/offline-attachments.js` cache-first. Once an installed client has cached those paths, changing the server-side file alone does not invalidate the old cached response. Any behavior change to a precached offline client or its startup contract must bump `CACHE_NAME`, update the matching regression assertion, and verify the newly activated worker on the target SHA. Otherwise an installed phone can keep running obsolete code indefinitely while CI passes against a clean browser profile.



## Logout requires a reachable authentication host

The Laravel web session cookie is server-managed. A disconnected browser cannot reliably complete the `POST /logout` action, and deleting IndexedDB first would create a false logout: local business data/token would be erased while the server session cookie remained valid. The client now probes the same-origin authentication route with a bounded, uncached `HEAD` request before local cleanup. If the host cannot be reached, the app keeps the local workspace and queue intact and asks the user to reconnect before trying again.

This is a safety guard, not a claim that every ambiguous network failure is impossible after the probe. The actual installed-device and disconnected-server acceptance remains required.
