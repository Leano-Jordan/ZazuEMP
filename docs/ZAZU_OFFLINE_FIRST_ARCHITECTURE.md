# Zazu EMP — Offline-First Architecture Contract

**Status:** ACTIVE ARCHITECTURE DIRECTION  
**Director update:** 2026-11-20  
**Current repository branch:** `main` · **latest full application-code verification baseline:** `526bdf38dab6ff540c143b0e57dfcee468006dc3`

## Director source audit — 2026-11-20

**Latest full application/test baseline:** `526bdf38dab6ff540c143b0e57dfcee468006dc3`  
**Exact-baseline automated evidence:** Laravel 607 passed / 1 skipped / 4,158 assertions; PostgreSQL migration, registration, backup/restore and private-file recovery passed; offline browser 51 desktop + 6 mobile-viewport + 1 phone-local Work/attachment journey passed. PHPMD, Psalm, Windows V1 package and populated upgrade/rollback passed on application-equivalent parent `30e70e929dc848c0119c8452395ad225a23b7c47`. Repository workflows observed on documentation head `91f41ec` passed Director Health, Zazu Quality, Director Contract Lint and Push-on-main; SonarCloud was skipped. Subsequent commits are documentation-only and are not a fresh application verification.

### Confirmed implementation boundary

The current implementation is a hybrid cache-and-queue client, not a standalone local application runtime:

- `public/sw.js` intercepts authenticated HTML navigations. While disconnected it returns the exact user/business-cached route when available; otherwise it returns the cached `/dashboard` HTML shell. This is a cached Laravel-rendered document, not a client-side route renderer.
- The offline priming code collects same-origin links from the currently rendered page. It does not guarantee every route, every nested form, or every record view is available offline.
- `public/zazu-offline.js` restores the business-scoped IndexedDB workspace and durable queue independently of the network. This is the local data foundation.
- `resources/js/app.js` has offline form handlers for customer, job, quote, quote acceptance, supplier, inventory item, asset, expense, payment, preparation, event cost, service, inventory movement, purchase receipt, invoice, asset allocation and purchase order.
- The visible local directory renderer rebuilds customer and job rows. Customers with a local workspace identity can open an in-place relationship panel containing customer/contact/billing data and linked local Work/quotes. Supported customer fields can be edited there while disconnected; the update is durably queued and its per-record status changes from waiting to synced only after server acknowledgement. Work rows can open a device-local inspection panel showing the job/customer, services, linked quotes, preparation, costs, purchase orders and invoices. Work edits for job name, event date, venue/address, notes, service/requirement list, eligible customer reassignment and guarded lifecycle status are queued locally; the server reuses `EventLifecycleService`, rejects edits to closed Work records, rejects reassignment when quotes exist, and preserves existing requirement metadata. Per-record sync state is displayed separately from lifecycle state. Full route parity remains open. Profile-photo changes remain online-only. The existence of local queue handlers for other entities does not mean their full list/detail pages or all related workflows are locally rendered.
- Several offline form paths explicitly require related records already present in the device workspace; customer photos and service images are explicitly deferred until online.

### Root cause of the phone's “last page” behaviour

The phone can reopen a cached authenticated HTML page after the PC/server is stopped, but the service worker has no general local route/view renderer to replace server-rendered navigation and page data. A cached page can therefore remain visible while the user is effectively looking at a snapshot. The offline queue can save supported mutations, but it cannot manufacture an unvisited Laravel page or a complete local directory/detail view.

**This is the confirmed architectural gap to solve.** Do not add another cache layer or keep increasing the number of cached HTML routes as a substitute. Extend the existing IndexedDB workspace and offline UI in a bounded, workflow-first way inside the current Laravel modular monolith. Preserve the existing online screens; provide local rendering and navigation for the V1 workflows explicitly declared offline-capable, and clearly disable/disclose server-only actions.

### V1 offline workflow matrix — source-derived, current `main`

This matrix distinguishes an IndexedDB mutation handler from a complete offline screen. A form being queue-capable does not prove that its directory, detail page, attachments, or every related action can be opened and rendered without Laravel.

| Domain | Local read/render evidence | Local write handler | Required device-local prerequisites / limits |
|---|---|---|---|
| Customers | Customer directory merges pending local customers; an in-place device-local relationship panel reads customer/contact/billing fields plus linked local jobs and quotes, and supports local edits. Server-rendered show/edit routes remain snapshots. | Create and update | Updating an existing server customer requires that customer to already exist in the local workspace. Profile-photo add/remove is online-only. |
| Work / jobs | Work register merges local jobs; the in-place Work inspector reads job/customer, services, quotes, preparation, costs, purchasing and invoices. Cached Laravel detail HTML remains a snapshot. | Create and update, including requirements, eligible customer reassignment and guarded lifecycle transitions | Related customers must exist locally; reassignment is rejected after a quote exists; closed jobs cannot be edited. Local jobs can hold phone-local attachments in the dedicated attachment store. |
| Quotes | Dashboard renders local quote rows with an expandable device-local detail panel for latest version, lines, linked invoice and payments; no standalone local quote register/detail route exists. | Create and queued acceptance | The related job must exist locally with server identity. At least one quote line is required. Acceptance is exposed in the local detail panel and remains subject to server validation on sync. |
| Quote acceptance | No independent local acceptance directory is present | Accept | The quote must exist locally and have status `sent`. |
| Suppliers | Records can exist in local workspace; no local supplier directory renderer is present | Create and update | Existing update requires the supplier record in the local workspace. |
| Services / catalogue | Records can exist in local workspace; no local catalogue directory renderer is present | Create and update | Service images are online-only. |
| Inventory items | Records can exist in local workspace; no local inventory directory renderer is present | Create | A selected capability is resolved from the local catalogue when supplied. |
| Assets | Records can exist in local workspace; no local asset directory renderer is present | Create and update | Existing update requires the asset in the local workspace; capability association requires a locally available capability. |
| Inventory movements | No local movement directory renderer is present | Create | The inventory item must exist locally; a selected job must also exist locally. |
| Purchase orders | No local purchase-order directory renderer is present | Create | Supplier is required locally; a selected job and capability references must resolve locally; at least one line is required. |
| Purchase receipts | No local receiving directory renderer is present | Receive | Purchase order and each received line must exist locally; at least one positive received quantity is required. |
| Preparation | No local preparation directory renderer is present | Create | Parent job must exist locally. |
| Event costs | No local cost directory renderer is present | Create | Parent job must exist locally. |
| Invoices | No local invoice directory renderer is present | Create | Related quote must exist locally and already be accepted. |
| Payments | No local payment directory renderer is present | Create | Related invoice must exist locally. |
| Expenses | No local expense directory renderer is present | Create | Any selected supplier, job or purchase order must exist locally. |
| Asset allocations | No local allocation directory renderer is present | Create and update | Asset must exist locally; any selected job must exist locally; updating requires the allocation to be present locally. |

**Attachment boundary**

- The Work workflow can store selected files in the business-scoped phone attachment store and retain them until a verifiable server attachment identity is returned.
- Pending local jobs can queue attachments from the local Work row.
- The active Work detail attachment panel is designed around a server-backed event ID; the local-job row is the separate path for a not-yet-synchronized job.
- Customer profile-photo changes and service images explicitly require a connection.
- Do not infer that other documents, receipts, or media are device-local merely because their parent record has an IndexedDB representation.

**Navigation and read boundary**

- The only routes explicitly primed by the current bounded first-launch flow are `/customers`, `/customers/create`, `/work`, and `/work/create`.
- The visible local directory renderer covers Customers and Work. Customer relationship details and supported edits are rendered in place from IndexedDB, including linked Work/quote summaries. Work has a local inspection view that assembles available operational and financial records from IndexedDB. Dashboard quote rows open a local detail panel showing the saved quote version, lines, linked invoice and payments; this is not a standalone quote register or route renderer. The panel exposes the existing quote-acceptance queue contract and updates local status optimistically, leaving final acceptance validation to the server during sync. The cached invoice-creation form hydrates newly accepted, server-backed quotes from IndexedDB so the existing invoice mutation can be queued against the local quote identity; the invoice register/detail remain server-route surfaces.
- The dashboard offline workspace also renders defined local collection lists and saved-detail panels for suppliers, catalogue, inventory items, assets, purchase orders, invoices, expenses and event costs. These are IndexedDB-backed local views, not cached Laravel records.
- Disconnected top-level navigation prefers the authenticated dashboard shell for unsupported routes. Exact cached pages remain available for the supported creation and attachment journeys: `/customers/create`, `/work/create`, `/finance/invoices/create`, and numeric `/work/{id}` detail pages. The worker still falls back to the dashboard when those exact cache entries are absent.
- Route-aware workspace navigation selects the corresponding IndexedDB collection panel for supported module paths. Work subroutes for quotes and costs/travel are mapped before the general Work route.
- Offline workspace links use the same `PermissionService::allows()` policy as route middleware. They are rendered only for the active business membership's permissions; Laravel `@can` is not the application's business-role authorization service.
- Attachment saves, event-ID reconciliation, attachment list reads and local file retrieval capture the offline session generation. A logout or re-pair transition prevents an earlier asynchronous operation from returning or writing data across that boundary.
- This improves local list/detail reachability but does not make Laravel forms, every record detail route, or every workflow available offline. Cached Laravel HTML remains snapshot presentation, not a local route implementation.

**Evidence source:** `resources/js/app.js` (`setupZazuOfflineForms`, `setupZazuOfflineDirectoryViews`), `public/zazu-offline.js`, `public/sw.js`, `resources/views/dashboard.blade.php`, and the focused offline Playwright suite on `main`.

### Next implementation sequence

1. **Matrix complete.** Use this table as the baseline; do not re-audit or re-list the same handlers.
2. **Implemented:** the dashboard device-local workspace renders locally stored customers, jobs and quotes from IndexedDB when the Zazu origin is unavailable; synced records remain visible and sync state is explicit. This is a dashboard workspace, not general local route/detail parity.
3. **Customer relationship read/edit slice implemented and browser-verified:** the in-place device-local panel reads customer details, linked Work and linked quote summaries, and queues supported profile/contact/billing edits. Per-record sync status is cleared only after server acknowledgement. This does not claim full customer route parity.
4. **Work read/edit slice implemented in source:** local Work inspection assembles job/customer, services, quotes, preparation, costs, purchasing and invoices. Supported field edits, requirement-list changes, eligible customer reassignment and guarded lifecycle transitions queue locally. The server reuses `EventLifecycleService`, rejects closed-record edits and preserves requirement metadata; reassignment is rejected when quotes exist. The Work inspector, disconnected Work inspection and customer reassignment journeys are included in the exact-baseline offline browser suite at `526bdf3`; broader field/requirement edit permutations remain part of the OPEN sync acceptance matrix.

5. **Quote read/acceptance slice implemented in source:** dashboard quote rows expose an expandable local panel for the saved version, quote lines, linked invoice and payments. The panel queues the existing quote_acceptance mutation and updates local state. Disconnected inspection of a quote version, linked invoice and payments is included in the exact-baseline offline browser suite at `526bdf3`. The complete acceptance → server acknowledgement/lifecycle transition → invoice handoff remains part of the OPEN sync acceptance matrix; do not infer that the read-panel test proves that full chain.

6. Keep focused browser acceptance for direct offline navigation, local reads/writes, reload while disconnected, and reconnect reconciliation. Do not count a cached page snapshot as a local read.

7. Keep physical Android PC/server-off acceptance as final proof; CI is not a substitute.


**2026-11-20 implementation update:** `public/sw.js` now prefers the cached dashboard shell for disconnected top-level navigation while preserving cached Customer/Work creation forms. `resources/js/app.js` maps supported module paths to existing IndexedDB workspace panels, including Finance expenses and Work quote/cost subroutes. `resources/views/dashboard.blade.php` exposes permission-aware navigation to supported modules. Regression coverage was added to `tests/js/service-worker-auth-cache.test.js` and `tests/Feature/PwaOfflineFoundationTest.php`. Cache version: `zazu-static-v55`. Latest implementation commits: `5cd264e`, `743acf5`, `ae2bdec`, `f60fec9`, `73f50c6`, `a0acc1a`, `f3ccdcc`. CI on the latest commit has been queued but has not yet produced results. This is automated source-level progress, not physical Android acceptance.

The physical Android gate remains OPEN.

---

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

**Implementation audit note — 2026-11-20:** a revision-aware sync implementation is now committed on `main`. The server stores monotonic aggregate revisions, rejects stale updates before mutation, preserves local and remote snapshots in `stale_record_version` conflicts, and supports new-ID keep-local rebases. Same-device mutations chain against the exact result revision of their predecessor. The implementation remains under verification: CASE-ZAZU-0033 stays OPEN until exact-head Laravel/PostgreSQL/offline-browser acceptance and an exhaustive audit of Eloquent and query-builder online writers are complete.

### 7.1 Required stale-update protection before V1 release

This is an acceptance/design requirement, not a claim that version-aware conflict handling is already implemented.

**Existing-record update contract**

1. Every offline update to an existing server record carries the authoritative record version observed by the device when that local edit chain began. A version may be a monotonic revision or another server-generated token, but it must change on every relevant server-side update, including ordinary online edits.
2. The server compares that expected version with the current record while holding the same business/record lock used for the update. A mismatch must not mutate the record; it creates a `stale_record_version` conflict containing a safe local payload and the current remote snapshot.
3. A same-device sequence of edits made while disconnected must remain valid. The queue must preserve causal order and the server must accept a later mutation only against the exact result version of its applied predecessor. It must not trust "same device" alone: another device or online user may have changed the record between the two mutations.
4. Conflict resolution must preserve the immutable original mutation and both value sets. Retrying a stale edit requires an explicit rebase against a refreshed remote version and a new mutation identity where appropriate. A blind retry of the same stale payload must not be presented as a resolution.
**Online-writer implementation and remaining audit:** the 2026-11-20 lock-normalization slice now applies business-first locking to customer/contact updates, capability updates, supplier updates, asset update/release, quote draft revisions and Work customer/contact snapshot writes. Lifecycle, finance, inventory and purchasing command paths already acquire the business lock first. `SyncEntityRevisionTracker` advances registered revisions for online Eloquent root and nested writes; the offline applier suppresses those listeners while applying a queued mutation and advances the affected aggregate once. This event coverage is not yet proof that every writer is covered: raw query-builder bulk updates/deletes and indirect command paths still require a final writer-by-writer audit.

5. The version contract applies to every existing-record update accepted by the current offline domain handlers: customer (including nested contacts), Work/event (including nested requirements), preparation item, quote/draft version and lines, inventory item, event cost, asset, supplier, service/capability and purchase order. The current offline UI exposes edit forms for a narrower subset; a server handler that accepts update/upsert remains part of the protocol contract and must not bypass stale-write protection. Command mutations such as payment, purchase receipt, stock movement and asset allocation retain their domain-specific idempotency and invariant checks; they must also advance every affected synchronised aggregate revision atomically without weakening those command guarantees.

**Required regression scenarios**

- Device A and device B both load version V1. B updates to V2. A submits an update based on V1. The server must retain B's V2 values, reject A's stale mutation and preserve A's local values in an open conflict.
- One device makes two or more sequential edits to the same record while offline. After reconnection, the ordered chain must apply without a false self-conflict when no external write intervened.
- If another device writes after the first mutation in that same-device chain has applied but before the next mutation is accepted, the later mutation must conflict rather than overwrite the intervening write.
- Conflict retry/rebase must keep the original mutation immutable, use the refreshed remote version and a new mutation ID, preserve user-selected values and remain business/device scoped.
- Repeat the above for representative consequential records and verify online edits update the version token as well as offline edits.

**Design guardrail:** do not ship last-write-wins, do not compare timestamps only in the browser, and do not suppress conflicts by disabling offline editing. The check must be enforced server-side under lock.

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

The stable-record-identity registry is integrated into the current sync path. `SyncBootstrapPayloadBuilder` identifies records included in the device workspace; `OfflineDomainMutationHandler` registers newly applied model results against the mutation's local UUID; and `SyncController::push` returns the canonical record identity to the originating device. The registry is business-scoped and prevents a record from changing entity type or being reassigned to another record. This identity layer is not a concurrency/version layer: stale-update detection remains OPEN and is specified in section 7.1.

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



## Device-local logout and queued-change safety

Ordinary web-account logout and trusted-device revocation are separate operations. The current direct `POST /logout` behavior and its offline failure boundary are defined in **Logout request and offline boundary** below. The former `GET /up` preflight description is obsolete and must not be used as implementation guidance.

The offline client's destructive `clearLocalSession()` operation remains separate from ordinary logout: it clears the attachment workspace, IndexedDB state and mutation queue, then attempts remote device-token revocation. If the host is unreachable, it retains only the business-scoped installation UUID and retries through the owner-authenticated endpoint on the next online owner session. It is not called by ordinary web-account logout.

## Offline asset cache versioning

The service worker serves `/zazu-offline.js` and `/offline-attachments.js` cache-first. Once an installed client has cached those paths, changing the server-side file alone does not invalidate the old cached response. Any behavior change to a precached offline client or its startup contract must bump `CACHE_NAME`, update the matching regression assertion, and verify the newly activated worker on the target SHA. Otherwise an installed phone can keep running obsolete code indefinitely while CI passes against a clean browser profile.



## Logout request and offline boundary

The Laravel web session cookie is server-managed. The client submits the actual CSRF-protected `POST /logout` request directly; a separate `GET /up` preflight is deliberately avoided because it adds a network race and can block a valid logout request.

The client confirms logout only after Laravel returns a successful redirect. If the request fails or the server cannot be reached, it tells the user that the web session remains active, keeps the business-scoped offline workspace, queued mutations, attachments and trusted-device credential untouched, and allows a retry. It does not pretend that clearing browser state is equivalent to invalidating the server session.

The service worker also guards this boundary: it clears authenticated page cache only after its own `POST /logout` fetch receives the same successful redirect. The page client sends an idempotent cleanup message after receiving that confirmed response, covering clients that were not yet controlled by the worker. Cache cleanup remains best-effort; server-side session invalidation is authoritative. Regression coverage exercises failed logout, confirmed logout, late-response cache races and preservation of queued business work.


## Authenticated page-cache generation boundary

Private HTML responses may finish after logout or another authenticated user takes over. The service worker attaches an identity-generation snapshot to each navigation/HTML request and rejects late cache writes once a confirmed logout or an identity switch advances the generation. A failed or unreachable `POST /logout` does not clear the authenticated page cache or advance the generation: the web session remains active, so the installed workspace must remain launchable. After Laravel confirms the successful logout redirect, the worker clears the user-scoped cache and advances the generation; late responses from the previous session cannot silently restore it.

The persisted active-user identity read is generation-checked at every asynchronous boundary as well. A delayed Cache API read can no longer repopulate the in-memory active identity after confirmed logout has cleared it.

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

The owner-generated pairing code is eight digits, single-use and short-lived. The phone's authenticated app shell exposes a discoverable pairing form on phone-sized layouts while the device is unpaired; the user enters all eight digits there. The client validates the full eight-digit value before provisioning and never silently truncates it. The older query-string-triggered browser prompt is removed. Changes to the precached offline client require a service-worker cache version bump so installed phones do not continue using stale pairing code.

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


## Offline client revision rollout — 2026-11-20

The offline mutation client now carries server base revisions, predecessor links and a durable local queue order. Because `/zazu-offline.js` is precached cache-first, this change advances the service-worker static cache from `zazu-static-v49` to `zazu-static-v50`. Existing installations must activate the new worker before the revision protocol is considered deployed. The matching service-worker cache regression fixtures were advanced with it.


## Related aggregate revision acknowledgements — 2026-11-20

Server command mutations may alter more than the record represented by their mutation ID. The push response therefore preserves the legacy `related_identity` field and additionally returns `related_identities` for every affected synchronised aggregate that can be resolved after application.

- Quote acceptance returns the Quote and linked Work identities because accepting the quote can confirm draft Work.
- Purchase receipts return the Purchase Order and inventory-item identities for the stock movements created by the receipt.
- Payment, inventory movement and asset-allocation commands continue to return the affected invoice, stock item and asset parent identities respectively.
- The phone client applies related revisions even when the command has no standalone identity. This prevents a source device from continuing with a stale local revision after its own successful command.
- Offline quote acceptance also mirrors the server's conditional draft-to-confirmed Work transition locally.

The service-worker static cache is now `zazu-static-v51`; matching worker fixtures were advanced so installed clients retrieve the acknowledgement reconciler. Automated coverage includes Laravel tests for command-side parent revision advancement and browser acceptance for identity-less commands affecting multiple local records.

Exact-head backend, PostgreSQL, quality and Director Health checks passed on implementation head `7d5f861`. The offline-browser workflow was still running when this note was added. CASE-ZAZU-0033 remains open until browser acceptance, the final online-writer inventory and physical device acceptance are complete.


## Current implementation update — 2026-11-21

The authenticated offline shell, session transition and attachment boundary advanced on `main`:

- Service-worker cache is `zazu-static-v57`. Unprimed disconnected top-level routes use the dashboard shell, while cached Customer/Work creation, invoice creation and numeric Work-detail routes retain their exact HTML where those pages are required for existing offline workflows.
- Redirected authenticated navigations now propagate the final same-origin redirect back to the browser. An expired session therefore reaches the public sign-in URL instead of rendering sign-in HTML under a protected URL; redirected public HTML is not written into the authenticated page cache.
- The workspace directory navigation calls Zazu's `PermissionService` directly. It does not use Laravel `@can`, which does not evaluate the business-role permission map.
- The active attachment client checks session generation around local attachment writes, Work-ID reconciliation, attachment reads and file retrieval. A prior session cannot surface attachment data after logout/re-pair.
- Regression coverage includes exact-route preservation, workspace permission rendering, redirect propagation/no-cache behavior, attachment session boundaries, the late-download/logout race and the phone-local attachment journey.

**Automated evidence — exact test head `1768de8b00ff0064b3b53cd757d4118fbfe04608`:** Laravel and PostgreSQL recovery passed; Chromium desktop `58 passed`; mobile-viewport Customer/Work `7 passed`; dedicated phone-local attachment journey `1 passed`. Composer security audit, secret scan, application check, PHPMD, Director Health and JavaScript/TypeScript, Python and Actions analysis passed. Sonar analysis was skipped.

**Business archive restore hardening — exact test head `0bb76a158e22b9671ce5dfc32b54a29c81cda72b`:** import now accepts only `manifest.json`, `data.json` and the file payload entries explicitly declared by the archive manifest; unexpected ZIP entries are rejected before target-workspace mutation. Laravel `628 passed / 1 skipped / 4,340 assertions` and PostgreSQL recovery smoke passed. Offline-browser acceptance was still running when this note was written.

The Windows package build and PowerShell fresh-install verification passed on source commit `490100e2ae7a4793db3e06c37833cba7b2033fb9`.

These are CI/browser-emulation results, not proof of a physical Android cold launch, restart or reconnection. Physical Android acceptance remains OPEN.

## Receiver bootstrap completeness — 2026-11-21

A receiver sync batch must not be acknowledged merely because a general bounded bootstrap succeeded. The previous design pulled a contiguous delivery prefix, fetched the ordinary business bootstrap and acknowledged the prefix after the snapshot was stored. Several aggregate collections were capped at 500 and some were ordered by display name or creation time. An older aggregate updated online or through another device could therefore remain outside the snapshot while its delivery sequence was still acknowledged.

The correction on `main` is:

- The client includes the exact receiver delivery sequences from the pull response as `delivery_sequences[]` on the bootstrap request.
- The controller validates a maximum of 100 positive integer sequences. The builder resolves them only against deliveries belonging to the authenticated device, active business and business stream.
- The resolver includes primary and related aggregates affected by each delivered mutation. This covers normal entity updates and command effects such as quote acceptance, invoice payment, purchase receipt/inventory, stock movement and asset allocation.
- The builder unions those target records into the normal bounded collections or selected-record payload. The regular working set remains capped at 500; only records required by the current receiver delivery batch are added beyond that limit.
- Devices with an explicit pinned pairing selection are not expanded by delivery-driven customer, Work, quote or preparation entries; those selected records are admitted only when already present in the pinned selection. This preserves the existing explicit-selection contract.
- Ordinary bootstrap windows are ranked by the most recent sync-identity update timestamp, falling back to the business record's `updated_at`, then returned in the existing display order. This allows recent online edits to bubble into the bounded refresh without changing list presentation.
- The client stores the refreshed snapshot before acknowledging the highest contiguous delivery sequence. If bootstrap validation, fetch, local persistence or session checks fail, the acknowledgement is not sent.
- The service-worker static cache is `zazu-static-v58`, ensuring installed clients fetch the updated cache-first offline client.

Regression coverage includes a supplier deliberately outside the default 500-record window, online revision promotion, explicit delivery inclusion, recipient-device isolation, second-device quote → acceptance → invoice → payment reconciliation, purchase receipt → affected inventory reconciliation, and a browser assertion that the client performs pull → targeted bootstrap → acknowledgement in that order.

## Bootstrap device revalidation — 2026-11-21

The authenticated bootstrap endpoint now follows the same durable authorization boundary as pull and acknowledgement. It acquires the business lock first, re-reads and locks the active device row, compares the persisted sync-token hash with the device snapshot attached by middleware, updates last-seen activity under that lock and builds the snapshot before releasing the transaction. Revocation or credential replacement between middleware authentication and bootstrap therefore returns HTTP 401 rather than disclosing a business snapshot. This also removes the previous device-first last-seen write before business-lock acquisition.

Regression coverage exercises a stale middleware snapshot after revocation and after token replacement. On source head `559e66b`, the backend suite passed 632 tests / 1 skipped / 4,388 assertions; PostgreSQL recovery, PHPMD, Zazu Quality, Director Health, Push-on-main, Composer/secret/application checks and language-specific analysis passed. SonarCloud was skipped. Offline-browser acceptance was still running when recorded.

**Verification boundary:** receiver completeness and bootstrap authorization are implemented and backend-verified on `559e66b`. The browser workflow remains pending; this does not close the full V1 sync matrix, exhaustive writer audit or physical-device cold-launch/restart/reconnect acceptance.

**Verification boundary:** implementation and regression tests are committed. Exact-head Laravel, PostgreSQL, offline-browser, PHPMD and quality results must be observed before this correction is marked verified. This does not close the full V1 sync matrix, the final writer audit, or physical-device cold-launch/restart/reconnect acceptance.
