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
