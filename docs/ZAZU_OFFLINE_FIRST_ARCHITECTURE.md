# Zazu EMP — Offline-First Architecture Contract

**Status:** ACTIVE ARCHITECTURE DIRECTION  
**Director update:** 2026-10-03  
**Current source baseline:** main

## 1. Non-negotiable product rule

Zazu is **offline-first at the device workflow level**.

A supported installed device must remain useful when internet access or the production server is unavailable. Connectivity adds shared data access, synchronization, remote backup and integrations; it must not erase the device's ability to continue declared offline workflows.

Offline support is workflow-specific. It does not mean the complete Laravel application or every V1 screen is mirrored locally.

**Production deployment direction:** Zazu will use a paid persistent server when the owner is ready to fund production hosting. The owner's Windows PC is development-only. Do not treat it as the permanent customer host or make a local Windows/LAN server a prerequisite for the product architecture.

## 2. Current architecture

The current repository is a Laravel modular monolith with a relational database and private file storage. During development it runs locally; the future customer deployment target is a paid persistent server, not the development workstation.

The browser/PWA client has:
- a service worker for the application shell and user-and-business-scoped authenticated page cache;
- a business-scoped IndexedDB workspace containing the bounded working set copied to the device;
- a durable local mutation queue;
- an owner-approved device pairing and sync token;
- server-side business-scoped mutation handlers, idempotency and conflict recording.

When connected, the server is authoritative for shared business records. When disconnected, a device can read and mutate only the records and workflows already supported by its local workspace. Those local changes remain pending on that device until synchronization succeeds.

The complete production business domain is not mirrored locally. The current app is not yet a complete disconnected replacement for the full web application.

## 3. Target operating modes

### Mode A — Local development
- Zazu runs on the developer's workstation for implementation and testing.
- The Artisan development server is temporary tooling.
- VS Code is an editor, not a runtime dependency; the local server can be started separately.
- This mode is not a customer production topology.

### Mode B — Connected production
- A paid persistent server hosts Laravel, the shared database and private business files.
- Supported devices connect over HTTPS.
- The server is authoritative for shared business state while reachable.
- Hosting provider, instance size and production database are selected later through compatibility, recovery and cost evidence.

### Mode C — Device-local continuity
- An installed device keeps its bounded business working set in IndexedDB.
- Supported reads remain available without internet/server access.
- Approved mutations are durably queued and visibly pending.
- Local work survives reload/restart within browser storage limits.
- Another device does not automatically see these changes while disconnected.

### Mode D — Reconnection and reconciliation
- Queued mutations synchronize when the production server is reachable again.
- Retries must be idempotent.
- Parent/child identities and business ownership must be preserved.
- Consequential conflicts must be visible and recoverable, never silently overwritten.
- Attachments require their own durable local-file and synchronization path.

**Not currently promised:** multi-device synchronization while the cloud server is unreachable. That would require a separately designed local peer/host mode and is not implied by device-local offline support.

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

The service worker now supports the implemented device-local continuity model. It precaches the shared Zazu client assets and the real application shell, then caches successful authenticated HTML responses only inside a cache partitioned by both the authenticated Zazu user identity and active business identity. It also retires older cache generations during activation.

The service worker is not the business-data authority. Business records and pending mutations remain in the local IndexedDB client store; synchronized server data remains business-scoped on the Laravel backend. The cache must never be treated as a substitute for local business state or as a cross-user or cross-business shared cache.

Do not broaden authenticated caching beyond this user-and-business-scoped shell/navigation role merely to claim "offline support".

## 12. Evolution path

The architecture should evolve incrementally:

**Current Laravel backend → paid production deployment → bounded device-local working set → durable offline writes → safe reconnect/reconciliation → expanded workflow and attachment parity**

Each stage must be independently usable and testable. The paid server is the planned connected production backend; device-local storage provides continuity during outages. Do not introduce a local Windows production host or multi-device peer synchronization without a separately approved architecture decision.

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

## 14. Trusted-device credential lifecycle

The active sync bearer credential is stored server-side only as a SHA-256 hash in the paired device metadata. The plaintext token is returned once during pairing/provisioning.

- A newly paired device receives a 180-day inactivity expiry.
- Every authenticated sync request checks expiry before entering the endpoint, then refreshes `last_seen_at` and moves `token_expires_at` forward by 180 days.
- A missing or expired expiry is rejected with HTTP 401; the device must be paired again.
- Owner revocation removes the token hash and clears its expiry immediately.
- Migration backfills existing active paired devices from `last_seen_at`, falling back to `created_at`; already-idle devices do not receive a fresh 180-day period from migration date.
- This is rolling inactivity expiry, not periodic token rotation. The token value remains unchanged while the device is active.

The lifecycle has automated expiry, extension, pairing and revocation regression coverage. Physical-device acceptance remains separate.

## 15. Current status

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

The phone workspace is a **bounded offline operational foundation**, not a complete disconnected replacement for the full web application. Explicit conflict-resolution UX is implemented, with retry/discard behavior and browser regression coverage. **Work attachment offline support is now integrated into the active authenticated app**: `resources/js/app.js` binds the attachment flow to the Work form in `resources/views/work/attachments.blade.php`, and the active `ZazuOffline` client stores selected files in business-scoped IndexedDB, shows pending/local files, caches private downloads and uploads queued files on reconnect using the existing idempotency key. This applies to an already persisted Work record with a server event ID. The client also reconciles queued records carrying `event_local_id` after a locally created Work record receives its server identity, but a complete rendered offline-create-Work → attach-file journey remains unverified. Automated Laravel/source-contract and Playwright coverage exists; installed-device force-close/reopen, durable file persistence, offline opening and real reconnect/conflict recovery remain subject to physical acceptance. Full workflow parity across every Laravel screen is not claimed.


## Director hardening update — 2026-10-06

The offline phone queue now reconciles server receipts deterministically:

- successfully applied mutations are removed from the local pending queue;
- rejected mutations remain visible as failed/pending rather than being silently discarded;
- local dirty state is recalculated after every reconnect attempt;
- already-applied mutations are therefore not repeatedly resent merely because the phone restarted;
- the phone UI no longer offers quote creation as an offline mutation while the server-side quote domain handler is not implemented.

This preserves the rule that offline capability must expose only workflows that have a safe synchronization contract.


## 2026-11-01 attachment integration audit — historical finding, superseded

The audit correctly identified that the legacy standalone `public/offline-attachments.js` script was not integrated into the authenticated application. That finding described the source state on 2026-11-01 only; it is not the current implementation status. The active integration was subsequently implemented in `resources/js/app.js` and `resources/views/work/attachments.blade.php`, using the business-scoped `ZazuOffline` client in `public/zazu-offline.js`. The legacy standalone script remains unused and must not be loaded directly.

**Current disposition (reconciled 2026-11-10):** offline Work attachment queueing, local-file retrieval, reconnect upload and private download caching are implemented in the active app and have dedicated Laravel/source-contract and Playwright coverage. Physical installed-device acceptance remains open. The full offline-created Work → attachment UI journey is not yet proven; do not claim that every new offline-created Work record can be opened and attached to before it receives a server identity.

## 2026-10-07 attachment sync historical implementation notes (not active integration)

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



## Device-local logout and queued-change safety — current contract

A web-account logout and trusted-device revocation are deliberately separate operations. The local business workspace is device-scoped, not tied to the currently signed-in web account.

- Before logout, the client probes the same-origin health endpoint with a bounded `GET /up` request. If the host cannot be reached, logout is cancelled and the workspace, queue and paired-device token remain untouched; a disconnected browser cannot reliably end the Laravel web session.
- When the host is reachable, the user receives an explicit confirmation that signing out ends only the web session. The business-scoped IndexedDB workspace, pending/failed mutations, attachments and sync token remain on that device.
- On confirmation, the client asks the service worker to clear the user-scoped authenticated HTML cache, then submits the normal Laravel logout form.
- The offline workspace is not erased and the device is not automatically unpaired. This allows the same trusted device to resume its business workspace after a later authorized sign-in.
- To remove a device from the business, an owner must revoke it through Business Settings. Revocation invalidates its sync credential; local data cleanup on a lost/unavailable device is a separate device-management concern.
- The offline client's `clearLocalSession()` API is a distinct destructive cleanup operation: it clears the attachment workspace, IndexedDB state and mutation queue, then attempts bounded remote token revocation. It is not called by ordinary web-account logout.
- Regression coverage must verify unreachable-host preservation, confirmation cancellation, ordinary logout with workspace/token/queue preserved, same-business reauthentication, and explicit device revocation/cleanup separately. Installed-device acceptance remains a separate verification layer.


## Offline asset cache versioning

The service worker serves `/zazu-offline.js` and `/offline-attachments.js` cache-first. Once an installed client has cached those paths, changing the server-side file alone does not invalidate the old cached response. Any behavior change to a precached offline client or its startup contract must bump `CACHE_NAME`, update the matching regression assertion, and verify the newly activated worker on the target SHA. Otherwise an installed phone can keep running obsolete code indefinitely while CI passes against a clean browser profile.



## Logout requires a reachable authentication host

The Laravel web session cookie is server-managed. A disconnected browser cannot reliably complete the `POST /logout` action. The active client probes the same-origin health endpoint with a bounded, uncached `GET /up` request before submitting logout. If the host cannot be reached, it keeps the local workspace, queue and device token intact and asks the user to reconnect before trying again.

A successful probe does not guarantee that the subsequent logout POST cannot fail; this remains a normal network race. The actual installed-device and disconnected-server acceptance remains required.


## Authenticated page-cache generation boundary

Private HTML responses may finish after logout or another authenticated user takes over. The service worker now attaches an identity-generation snapshot to each navigation/HTML request and rejects late cache writes once logout or an identity switch advances the generation. The `POST /logout` request invalidates the generation immediately; queued page priming is aborted and late responses cannot silently restore a previous user's cache. This protects the asynchronous cache boundary; cross-user privacy acceptance still requires a real browser regression with delayed responses and multi-account sessions.


The persisted active-user identity read is generation-checked at every asynchronous boundary as well. A delayed Cache API read can no longer repopulate the in-memory active identity after logout has cleared it.

## Director offline continuity cycle — 2026-10-21

### Confirmed launch boundary

The phone's current origin `http://192.168.137.237:8000` remains tied to the PC-hosted Laravel process. Closing that process removes the origin's server responses. On this non-loopback plain-HTTP address, the browser also cannot register the service worker because the origin is not a secure context. The client warning for this case is implemented; it diagnoses the unsupported origin but does not provide hosting.

### New regression coverage

Added a behavioral test that loads the real `public/sw.js`, primes an authenticated dashboard into the user-scoped cache, makes the simulated host unavailable, and verifies navigation returns the saved dashboard. This extends the current service-worker race tests from logout privacy to the disconnected dashboard path.

Source/test status: **IMPLEMENTED**. The new test has not yet been run by current-main CI; do not mark it TESTED until the exact run completes.

### Deployment boundary

This architecture specification does not select or configure a production hosting provider. Production deployment is a separate Zazu-specific release activity and must verify the actual PHP runtime, database, persistent private-file storage, backups, HTTPS, monitoring and rollback path. Hosting choices must not be inferred from another product's infrastructure.

### Remaining acceptance

1. Run the new service-worker test and all current-main CI checks on the exact resulting SHA.
2. Establish a stable HTTPS Zazu host with durable database, private-file storage, backups and a documented recovery path; the VS Code development server must not be the production origin.
3. Install/update the PWA from that HTTPS origin on the target phone.
4. Force-close the app, switch off the PC, disable internet, and verify launch, cached navigation, local reads, supported mutations, reload/restart persistence, visible pending work, and reconnect reconciliation.
5. Test logout, user switching, queue preservation, conflict handling and attachment recovery on the installed device.

**Current disposition:** source regression coverage expanded; production hosting and physical disconnected-device proof remain **OPEN**. Zazu is offline-first by architecture, but complete disconnected parity and VS Code independence are not yet certified.

## Director offline continuity and Work workflow update — 2026-11-01

### Host reachability versus browser connectivity

Marked offline forms now probe the actual same-origin Zazu endpoint with a short timeout. If the network interface reports online but the Zazu host cannot respond, supported forms use the device-local mutation queue instead of attempting a normal Laravel POST. This covers the case where Wi-Fi remains connected but the PC-hosted development server has stopped. A reachable origin retains the normal server-rendered Laravel flow.

### Atomic local persistence

The local mutation queue, the `local_dirty` marker and the staged optimistic workspace copy are now written in one IndexedDB read/write transaction over the state and mutations stores. The client adopts the new in-memory state only after transaction completion. A transaction failure therefore cannot intentionally leave the queue and displayed working copy diverged. The service-worker cache was advanced to `zazu-static-v29` because the offline client is precached.

### Offline customer and Work records

Customer creation now carries the supported text profile, including primary/day/night contacts and billing/registration/tax fields. A profile-photo upload is rejected with a clear message while disconnected; the client does not claim an unqueued file was saved.

The Work form resolves a selected server-backed or phone-local customer to its stable local UUID. Phone-local customer options are added to the customer selector from IndexedDB. A queued Work mutation includes that UUID, job type/date/address, optional selected server contact IDs and service selections. On synchronization, the server resolves the customer UUID within the paired device's business, stores the actual customer foreign key and snapshots primary contact data, then creates the selected service requirements.

The sync dependency sorter recognizes `customer_local_id`, so a batch submitted with a Work mutation before its local customer is reordered to apply the customer first. Unknown or cross-business customer identities are rejected and retained as traceable conflicts.

### Pairing bootstrap

The owner settings screen previously requested a pairing code with `selection: []`, while the provisioning endpoint interpreted that as an intentionally empty selection. Pairing now treats an empty selection as the default bounded working set: up to 25 recent customers, 25 jobs, 25 quotes and 25 preparation records. Each is selected through active-business queries and receives a stable local identity. Job records carry their customer local identity; quote and preparation records carry their parent job identity. The settings page discloses the initial business data copied to the trusted paired phone.

The bootstrap remains bounded rather than mirroring the entire database. Supporting catalogues, suppliers, purchase orders, invoices, expenses, inventory, assets and costs remain capped in the controller.

### Evidence and limits

The source changes and focused regression cases are implemented. Current-main CI/E2E remains unverified until the exact resulting SHA completes Laravel, Quality, browser, PHPMD, Psalm and populated upgrade/rollback. A browser regression is not a substitute for an installed-device test.

This work does **not** certify complete offline parity. Still open: every V1 screen's disconnected read/write parity, complete bidirectional reconciliation, attachment/private-file offline parity, real conflict-resolution recovery, fresh installed-app launch while both PC and internet are unavailable, stable HTTPS hosting with durable storage, and customer-environment backup/restore/rollback.


## Business-scoped phone storage

The phone's IndexedDB workspace is namespaced by the active business ID from the authenticated application shell. A legacy `zazu-client` workspace is migrated only when both its saved business and paired device business IDs match the active business. The scoped database keeps a migration tombstone through local logout so cleared legacy work cannot be re-imported. This prevents a different business using the same browser origin from inheriting another business's local workspace or queued mutations.


## Owner deployment clarification — 2026-11-03

This section supersedes earlier historical notes in this document that described local-PC hosting as the primary customer mode or cloud as a merely optional later add-on.

- The owner's PC is a development workstation only.
- The future production application will run on a paid persistent server when the owner approves the spend.
- Offline capability belongs in the installed device's local workspace and durable mutation/file queues; it must continue when the paid server or internet cannot be reached.
- The connected server remains the shared business-data authority when reachable.
- A device operating offline is not a shared LAN database. Other devices will not see its pending changes until safe synchronization occurs.
- No production provider, server size, DB engine or deployment spend is selected yet.
- This architecture does not authorize configuring the owner's PC as a permanent host.


## Active Work attachment integration — 2026-11-07

The authenticated Work attachment panel now uses the active `ZazuOffline` client and its business-scoped `zazu-phone-attachments-business-<business-id>` IndexedDB store. A selected batch of up to 10 files is validated and written in one read/write transaction; a quota or transaction failure therefore leaves the batch unsaved rather than partially queued. Each record retains its UUID idempotency key, event/business identity, Blob, original name, MIME metadata and retry state.

When connected, a paired device uploads pending files through the existing bearer-authenticated `/api/sync/attachments` endpoint. The local Blob is retained until the server returns a verifiable attachment ID. On Work record load, the client lists the business-scoped server attachments and caches their private download responses in the same local store. The Work panel can open locally stored files when disconnected; the existing Laravel upload and download flows remain active when the origin is reachable. Device unpair/revocation cleanup continues through the existing attachment workspace cleanup path.

The service-worker static cache is now `zazu-static-v30`; this invalidates the earlier cache-first `/zazu-offline.js` response. Regression coverage includes source-contract assertions, a browser integration test for local save/reconnect upload/remote file caching, and server idempotency tests. Current source is implemented on `main`; exact-head CI and browser execution are not yet observed. This must not be described as physically proven offline operation until the installed-device acceptance sequence is completed.

The current Work upload requires a server-backed event ID. A job created offline must first receive its server identity before its attachment can be synchronized. This remains a specific dependency to verify in the broader offline Work recovery flow.


### Offline-created job attachment identity reconciliation — 2026-11-07

The active attachment sync service also reconciles pending records carrying `event_local_id` against the synced `state.jobs` collection. Once the Work mutation receives its numeric server ID, the attachment record is updated to that ID in one IndexedDB transaction before its upload is attempted. This preserves the legacy phone queue's stable local-parent reference through the job-create sync boundary; the browser regression seeds that pre-sync shape and verifies the upload targets the resolved server job. The current Work detail upload form itself still attaches to an already persisted job; a complete offline-created job-to-attachment UI journey remains subject to rendered acceptance.


## Backup archive integrity — format version 2 (2026-11-07)

New installation backups now write a SHA-256 checksum for every payload file (database snapshot and each private-storage file) into `manifest.json`. Restore validates that the checksum map exactly matches the archive's payload entries and streams each entry through SHA-256 verification before extraction or any installation mutation. A missing, extra, malformed or mismatched checksum causes restore to stop before replacing the current database or private storage.

The restore command remains backward-compatible with format-version-1 archives that predate checksum maps; new backups are format version 2 and require the integrity manifest. This checksum layer detects accidental corruption and archive-content drift. It is not a digital signature and does not prove who created the archive or prevent a malicious party from rebuilding both payload and manifest.

Automated coverage now asserts database/private-file checksum generation in the backup round-trip and rejects a tampered private file while preserving the currently installed private probe. Source and tests are committed on `main`; exact-head Laravel/quality execution and the real operator backup/restore drill remain outstanding.


## Business-context page cache isolation — 2026-11-09

- Authenticated HTML is now cached under both the authenticated user ID and active business ID. The worker refuses to cache a page without both identities.
- A successful POST to the business-switch endpoint clears the old business page cache before the redirect's next page can be cached. Context-generation checks prevent a late response from the previous business repopulating it.
- Static cache version advanced to v31 so existing user-only page caches are retired on activation.
- This is a security/correctness prerequisite for reliable offline use with multi-business accounts. It does not alter the business-scoped IndexedDB workspace or claim full offline parity.
- JS worker regression and PHP source-contract coverage added. Exact-head CI and physical PC/mobile acceptance remain pending.
