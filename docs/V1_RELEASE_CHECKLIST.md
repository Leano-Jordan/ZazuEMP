# Zazu EMP — V1 Release Checklist

Latest full application-code verification baseline: 041314fd80147758dd2edb33c187b16328aea938.
Latest receiver-sync and bootstrap-auth hardening source head: 559e66b87de8617f967b722b55b0fbfd9b116495 (backend gates pass; offline-browser acceptance still running).
Subsequent repository documentation reconciliations do not change application code; consult the live `main` ref for the current commit.
Disposition: NOT READY · Evidence maturity: 73/100 unchanged.


## Receiver reconciliation and bootstrap authorization — 2026-11-21

**Source head:** 559e66b87de8617f967b722b55b0fbfd9b116495.

- [x] Laravel: 632 passed / 1 skipped / 4,388 assertions.
- [x] PostgreSQL migration/registration/recovery smoke.
- [x] PHPMD, Zazu Quality, application, Composer audit, secret scan, Director Health, Push-on-main and Actions/Python/JavaScript analysis. SonarCloud was skipped.
- [x] Receiver bootstrap includes the exact pulled delivery prefix and all primary/related aggregates outside the normal 500-record window.
- [x] Normal bounded collections promote recently revised aggregates while retaining display order.
- [x] Explicit Customer/Work/Quote/Preparation pairing selections remain pinned; targeted delivery cannot silently broaden them.
- [x] Bootstrap revalidates active device status and token hash under business-first locks; revocation and credential-replacement regressions pass.
- [ ] Offline-browser pull → targeted bootstrap → local persist → acknowledgement sequence; workflow is still running.
- [ ] Full sync matrix and physical-device cold launch/restart/reconnect remain open. CASE-ZAZU-0034 and CASE-ZAZU-0035 remain open until the browser run passes on this source head.

Evidence maturity remains 73/100 until the browser acceptance completes. No uplift is claimed from partial CI.

---
## Exact-head automated acceptance — 2026-11-20

- [x] Laravel [44810699348](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44810699348): 623 passed / 1 skipped / 4,283 assertions.
- [x] PostgreSQL migration, registration, database backup/restore and private-file recovery smoke: PASS in the same run.
- [x] Offline desktop Chromium: 57 passed; mobile viewport: 7 passed; dedicated Pixel 7-viewport Work-create/attachment/reconnect journey: 1 passed.
- [x] PHPMD [44810699344], Zazu Quality [44810699254], Director Health [44810699270] and Push-on-main [44810699529]: PASS. SonarCloud skipped.
- [x] Offline account/session transition automated acceptance: current-main desktop E2E + Laravel feature acceptance passed. Physical-device and operational lost-device testing remain distinct.
- [x] Active Work attachment automated acceptance: current-main desktop and mobile-viewport E2E passed, including phone-local file creation, queueing and reconnect sync. Physical Android restart remains open.
- [x] Command-side aggregate revision regression: quote acceptance → Work, payment → invoice, purchase receipt → purchase order/inventory, inventory movement → inventory item, asset allocation → asset passed in `SyncRevisionAggregateTrackingTest`.
- [ ] Full offline sync workflow matrix, physical Android cold launch/restart/reconnect, intended-PC recovery drill, real-business commercial reconciliation, POPIA operations and ZAZU name clearance remain OPEN.

DB-001 remains DORMANT / MONITORING by Founder report; its historical root cause is unproven and it is not an active release blocker. Installation recovery remains OPEN independently. V1 remains NOT READY; evidence maturity remains 73/100 with no uplift.

---
## Dormant database incident — owner installation (status updated 2026-11-20)

- **Disposition: DORMANT / MONITORING — not an active V1 blocker.** Founder reports DB-001 has not appeared recently.
- Historical references retained: `2bc77a48-5c6c-4095-afe2-fa079f60e4ba` and `aa9080f4-9580-41c9-a643-439b31928ffa`; later recurrence history remains in the incident record.
- Root cause remains unproven. Do not claim a fix or spend active engineering time reproducing it unless the error recurs or new evidence appears.
- Windows startup source checks migration status, creates and verifies a full database/private-files backup before applying pending migrations, and verifies final migration state.
- [ ] **Separate V1 gate:** complete the controlled installation recovery drill on the intended Windows installation with representative data and private files. This remains open regardless of DB-001 recurrence status.

## Current code follow-up — 2026-11-17

- Job date/time relationships now use one validator for online creation/editing and offline sync.
- The job form uses shared labels, plain-English guidance and browser date/time limits.
- DB-001 and account error copy is simpler; the product-wide language rule is in `docs/ZAZU_PLAIN_LANGUAGE_STANDARD.md`.
- Source and regression tests are committed on `main`; exact-head CI is pending. No readiness uplift is claimed.

## Event scheduling and rental logistics — owner-authorised V1 addition (2026-11-17)
Implementation baseline: `71c8fc5c7aeba392b95e26aee694945b286f2c8e` on `main`.
- [x] Multi-day event date range, start/end time, setup and collection dates persist and validate online.
- [x] Schedule clash warnings identify overlapping active work without blocking an operator save.
- [x] Offline-created schedule fields survive sync and are included in the device workspace bootstrap.
- [x] Calendar displays multi-day events and includes setup/collection logistics days.
- [x] Asset booking defaults use the event setup/collection window; existing overlap and independent-return protections remain intact.
- [x] Exact-head PostgreSQL migration/recovery and desktop/mobile offline-browser regression pass (run 44152431508).

## Offline sync reliability — owner-directed active target (2026-11-20)

Offline synchronization is a separate V1 reliability target from phone pairing and from standalone phone-local launch.

**Verified narrow corrections (2026-11-20):** Offline forms prevent duplicate submission until saved fields change; a changed receipt quantity or payment amount can be deliberately submitted as a new local mutation. The service-worker cache was advanced for the related client changes. Revision-aware stale-write protection is now implemented: the server enforces aggregate revisions, rejects stale snapshots, preserves the remote version and supports safe keep-local rebase/use-server restoration. These corrections do not close the broader sync matrix or physical phone acceptance. The Founder reports that sync is not yet up to standard across supported workflows; do not infer parity from one successful customer/job or attachment journey.

- [x] Complete a static source trace for every currently declared offline entity/operation from UI action → IndexedDB state → mutation queue → server handler → identity mapping → acknowledgement/pull/bootstrap → visible reconciled state. This closes the inventory task only; runtime acceptance remains open.

**Receiver bootstrap completeness correction — 2026-11-21 (implementation committed; exact-head CI pending)**

The receiving client now sends the exact delivery sequence prefix to /api/sync/bootstrap. The controller validates and caps the list; SyncBootstrapDeliveryTargetResolver resolves primary and related aggregates only for the current business and destination device. The builder adds those records to the current receiver snapshot even when they are outside a normal 500-record collection. Ordinary bounded collections are ranked by latest sync-identity update time, fallback to record updated_at, and then returned in the existing display order. The client applies and persists the snapshot before acknowledging the prefix. Service-worker cache v58 delivers the updated precached offline client.

Regression tests cover revision-ranked refresh, delivery inclusion outside 500, cross-device sequence isolation, commercial second-device reconciliation, receipt/inventory reconciliation, and browser pull → targeted bootstrap → acknowledgement ordering. CASE-ZAZU-0034 / ENG-DATA-004 records this defect. This does not close CASE-ZAZU-0033 or the complete V1 offline-sync matrix.

**Source-level sync path map — verified against main on 2026-11-21**

Shared transport: `resources/js/app.js::setupZazuOfflineForms` and the local customer/Work editors build the supported payloads. `public/zazu-offline.js::queueMutation` persists each mutation alongside the business-scoped workspace in IndexedDB; `pending` orders the queue by creation time and `push` sends batches to `SyncController::push`. The server records mutation identity/sequence, applies it under the business → device → mutation transaction/lock order, and publishes receiver deliveries atomically. `OfflineDomainMutationHandler::apply` dispatches the entity-specific handler. Model results are registered through `SyncEntityIdentityRegistry` and returned as canonical IDs; command effects without their own model identity are reconciled through their owning records. On the receiving device, `SyncMutationProtocol::pull` returns only a contiguous delivery prefix; the client fetches the authoritative bootstrap snapshot, applies it only when its local queue/conflicts are clear, then acknowledges the contiguous delivery sequence.

| Entity / operation | User-facing offline entry | Local state target | Server handler | Identity / reconciliation |
|---|---|---|---|---|
| Customer — create/update | `customers/create`, `customers/edit`; local customer relationship editor | `customers` | `customer` | Customer identity; canonical server ID returned and refreshed from bootstrap |
| Job — create/update | `work/create`; local Work inspector editor | `jobs` | `event` | Job identity; requirement children reconcile inside the job snapshot |
| Quote — create | `work/quotes/create` | `quotes` | `quote` | Quote identity; quote version/lines are part of the refreshed quote snapshot |
| Quote acceptance — command | Dashboard quote panel and `quotes/show` acceptance form | Existing quote status/version | `quoteAcceptance` | No separate command identity; accepted quote state is confirmed by bootstrap |
| Supplier — create/update | `suppliers/create` (create/edit form) | `suppliers` | `supplier` | Supplier identity and canonical ID |
| Service/capability — create/update | `capabilities/create`, `capabilities/edit` | `catalogue` | `capability` | Service identity and canonical ID |
| Inventory item — create | `inventory/create` | `inventory_items` | `inventoryItem` | Inventory-item identity and canonical ID |
| Asset — create/update | `assets/create` (create/edit form) | `assets` | `asset` | Asset identity; allocations are nested in the asset snapshot |
| Expense — create | `finance/expense-create` | `expenses` | `expense` | Expense identity and canonical ID |
| Payment — create | `finance/payment-create` | `invoices[].payments` | `payment` | Nested payment identity; client resolves it through its parent invoice |
| Preparation — create | `preparation/create` | `preparations` | `preparation` | Preparation identity and canonical ID |
| Event cost — create | `costs/create` | `costs` | `eventCost` | Event-cost identity and canonical ID |
| Inventory movement — create | Inventory movement forms on `inventory/index` | `inventory_items[].movements` and on-hand quantity | `inventoryMovement` | Nested movement identity; client resolves through the inventory item |
| Purchase receipt — command | Receive form on `purchasing/show` | Purchase-order items and inventory/movement state | `purchaseReceipt` | No separate receipt identity; receipt effect is reconciled through purchase order and inventory snapshots |
| Invoice — create | `finance/invoice-create` | `invoices` | `invoice` | Invoice identity and canonical ID |
| Asset allocation — create/return | Allocation/release forms on `assets/index` | `assets[].allocations` and asset status | `assetAllocation` | Nested allocation identity; client resolves through the asset |
| Purchase order — create | `purchasing/create` | `purchase_orders` | `purchaseOrder` | Purchase-order identity; items are nested under the order |

**Coverage boundary recorded from source:** the UI currently exposes offline update forms for customers, Work, suppliers, services/capabilities and assets. The server also accepts update/upsert operations for preparation items, quote/draft versions, inventory items, event costs and purchase orders, although their current offline UI entry is create-only. These handlers remain part of the server protocol contract and must receive stale-write protection rather than being assumed create-only. The local list/detail renderer remains concentrated on Customers, Work and the dashboard quote panel; for other entities, an IndexedDB record or queued handler does not prove an offline directory/detail screen. Quote acceptance and purchase receipt are commands whose effects are reflected through their parent records, not standalone synced records. Command effects can also change other synchronised aggregates (for example invoice payments, inventory receipts/movements and asset allocations); their idempotency and atomicity must be retained while affected revisions advance.

- [ ] Verify every supported mutation is durable across reload, force-close and restart, and remains visibly pending until the server confirms it.
- [ ] Verify safe retries for transient/offline/timeout/partial responses; duplicate and reordered delivery must not duplicate consequential records or lose queued work.
- [ ] Verify stable local identities, parent/child references, business scoping, idempotency and server canonical IDs across create → update → related mutation sequences.
- [ ] Verify pull cursors, pagination, acknowledgements and bootstrap/re-bootstrap do not skip or replay changes across devices and businesses.
- [ ] Verify conflict detection and retry/discard handling preserve both authoritative and local data; consequential conflicts must not be silently overwritten.
- [ ] Verify concurrent edits from two trusted devices cannot silently overwrite a newer server record across every aggregate and indirect writer. The revision/predecessor protocol is implemented and has focused server regression coverage; complete the exhaustive writer/transaction audit and end-to-end cross-device replay. Confirm stale conflicts preserve both local and remote values, safe rebase creates a new immutable mutation ID, and sequential same-device edits chain to the actual predecessor result.
- [ ] Verify attachment bytes, metadata, job linkage, checksums/idempotency, retry and download/open behavior, including the supported offline-created Work linkage path.
- [ ] Verify supported financial, inventory, purchase/receipt, schedule and asset mutations preserve server validation, money/date precision and lifecycle rules.
- [ ] Run targeted server/client regression coverage and the full exact-head Laravel + browser acceptance suites; then verify representative sync journeys on the actual PC and phone.
- [ ] Record every unsupported offline operation as online-only or otherwise explicitly limited. Do not imply full app-wide offline parity.

**Latest application test evidence:** Laravel PASS — 621 passed / 1 skipped / 4,271 assertions; PostgreSQL migration/registration/backup/restore/private-file recovery PASS on `5b56a7c`. Offline-browser acceptance on that commit: 56 passed, 1 failed because the final Settings conflict-success message was hidden after the last conflict resolved. The UI correction is committed on `f50fcb1`; exact-head browser acceptance is pending. Do not report the earlier 526bdf3 test count as current.

**Sync source corrections verified in this batch:** preserved failed/conflicted local records from bootstrap replacement; fixed canonical identity reconciliation for event costs, nested payments, inventory movements and asset allocations; applied disconnected stock receipts to local inventory balance/history; aligned final offline payment with paid invoice status; and scheduled retryable attachment failures through existing backoff while preserving idempotency. Targeted E2E coverage is included.

**Still unaccepted:** complete sync coverage across all supported operations; exhaustive online/indirect/bulk writer audit; exact-head browser acceptance; actual Android standalone offline launch/restart/reconnect. Revision-aware stale-write protection is implemented but does not close the full sync gate. PC-to-phone pairing remains CLOSED. Overall V1 remains NOT READY.

## Release acceptance gates
- [ ] Physical Android acceptance: secure-origin install, disconnected cold launch/restart, local records/attachments and reconnect sync on actual device.
- [ ] Installed-target recovery: controlled database + private-media backup/restore on intended Windows installation.
- [ ] Live commercial reconciliation: populated quote → invoice → deposit/final payment → purchasing/costs → completion → profit/margin.
- [ ] POPIA operational sign-off: privacy notice, retention/deletion, access controls and incident procedure.

## Event scheduling implementation evidence — 71c8fc5

- Laravel: PASS — 532 passed / 1 skipped / 3,714 assertions.
- PHPMD: PASS.
- Windows candidate build, fresh PowerShell install and SQLite/private-media recovery: PASS on 2ef2a0b, the same application-code implementation before the final regression-test-only commit.
- PostgreSQL migration/recovery and desktop/mobile offline-browser acceptance: PASS on exact source HEAD 71c8fc5 (run 44152431508).
- Zazu Quality: RED on GHSA-fc5q-39xc-vjpf / CVE-2026-112366. No patched version is published; no advisory suppression or breaking Vite downgrade applied.

## Historical CI verification — fd82f6a (superseded by newer evidence recorded above)

- Application-code baseline a5ead69: Laravel 526 passed / 1 skipped / 3,681 assertions; offline browser acceptance 45 desktop + 6 mobile-viewport + 1 phone-local attachment; PostgreSQL migration/registration/backup/private-file recovery passed.
- Repository head fd82f6a changes only the Windows package workflow after that application-code baseline.
- Windows V1 Package run 82: PASS — candidate package built; Windows PowerShell first install passed; Windows-native backup/restore recovered both a disposable SQLite record and private-media bytes.
- Director Health, Director Contract Lint and Push-on-main passed.
- LAN asset delivery regression passed at a5ead69: a remote client with a stale loopback Vite hot marker receives compiled same-origin CSS/JS. The supplied 13 Nov HTTP screenshot predates this correction; physical Android HTTPS launch remains required.
- Zazu Quality passes with a temporary, exact-advisory exception for GHSA-fc5q-39xc-vjpf / CVE-2026-112366 in the picomatch build-tool dependency chain. The underlying advisory remains open/unpatched; the audit script allows only this finding and its exclusive dependency chain. Composer security audit and secret scan passed. No breaking Vite downgrade was applied.
- The Windows CI recovery test is supporting evidence only; it does not close the controlled recovery drill on the intended installed target.
## Closed acceptance
- [x] Windows fresh-install candidate package, PowerShell first-install smoke and Windows-native SQLite/private-media backup/restore — Windows V1 Package run 82 passed at fd82f6a; not an updater or hosted deployment.
- [x] Seeded commercial workflow automation — verified; reopen only on reproducible defect.
- [x] Offline financial mutation precision/rejection — quote lines, payments, purchase orders/receipts, event and asset costs, expenses and inventory movement use strict cents/hundredths validation.
- [x] Desktop trusted-device consent — fresh browsers do not auto-register; owner explicitly trusts the current PC from Settings.
- [x] Destructive offline workspace cleanup revocation retry — only the installation UUID is retained after an unreachable host; the next authenticated owner session retries the owner-scoped revocation. Ordinary web logout remains non-destructive.
- [x] Password reset revokes active and pending devices across all owner businesses, clears token/pairing credentials and audits each business; staff-only memberships are excluded.
- [x] Serialized-asset date availability — online/offline tests pass for non-overlap, overlap, open-ended dates and independent returns at 00af9adda.

## Current release position
V1 remains NOT READY. Latest full application-code verification baseline is 526bdf3: Laravel 607 passed / 1 skipped / 4,158 assertions; PostgreSQL migration/registration/backup/restore/private-file recovery passed; offline browser 51 desktop + 6 mobile-viewport + 1 phone-local Work/attachment journey passed. PHPMD, Psalm, Windows V1 package and populated upgrade/rollback passed on application-equivalent parent 30e70e9. Latest completed repository workflows observed on documentation head 91f41ec: Rosscore Director Health, Zazu Quality, Director Contract Lint and Push-on-main passed; SonarCloud was skipped. These are historical exact-head results, not a fresh full application run on later documentation-only commits. The owner-operated acceptance gates and DB-001 remain open. Evidence maturity remains 73/100; no score uplift.


## Scheduling exact-head acceptance closure
- GitHub Actions run `44152431508`: Laravel, PostgreSQL migration/recovery and offline-browser jobs all succeeded.
- Offline browser job `134284080089`: desktop/browser, mobile-viewport Work/customer and phone-local attachment journeys passed.
- PostgreSQL job `134284080622`: migrations, account/workspace registration and backup/restore/private-file recovery passed.
- Laravel job `134284080768`: service-worker/session contracts, LAN asset-origin checks, Blade/manifest/config checks and PHPUnit/Pest passed.
- Scheduling engineering/CI subgate: CLOSED. Physical Android installation and disconnected cold-launch/reconnect remain a separate owner-operated gate.


### DB-001 diagnostic contract clarification — 2026-11-17

The incident logger records privacy-minimised metadata only (request ID, classification, exception class, route/action, method/path, timestamp and source file/line). It intentionally excludes exception messages, SQL, bindings and stack traces. The incident-framework documentation has been corrected to match this implementation.

The owner-installation entry matching `aa9080f4-9580-41c9-a643-439b31928ffa` remains required. Use it to identify the exception class, source file/line and route; if those bounded fields do not isolate the cause, reproduce locally under controlled diagnostics. This documentation correction does not close DB-001 or verify the startup migration against the intended installation.


### Backup-first verification hardening — 2026-11-17

- [x] Backup creation now reopens the completed ZIP and validates ZIP consistency, manifest/driver, exact payload membership and every SHA-256 checksum before publishing it.
- [x] Added a regression for valid archive acceptance and tampered private-payload rejection.
- [ ] Exact-head Laravel and Windows workflow verification.
- [ ] Run backup-first migration on the intended installation and confirm the previously failing workflow succeeds.


### Zazu quality gate — 2026-11-17

- [x] Exact-head quality workflow run `44179163841` passed on `76be1f234e7726352c2e15b511b37b9084d2ddfa`: application, Composer security audit and secret scan.
- [x] Application job passed dependency installation, the scoped npm audit policy and service-worker authentication cache-race tests.
- [ ] Replace the temporary GHSA-fc5q-39xc-vjpf exception when a verified picomatch fix is published or the advisory is withdrawn. This exception is not a dependency fix.


### New-job form regression closure — 2026-11-17

- [x] Restore required Work / event name input on the new-job form; controller validation and persistence remain unchanged.
- [x] Keep the offline-browser acceptance tests intact; all three previously blocked journeys now pass.
- [x] Exact-head Laravel run `44184183003`: Laravel, PostgreSQL migration/recovery and offline-browser jobs all PASS, including mobile customer-to-Work and phone-local attachment acceptance.
- [x] Zazu Quality `44184183085`, Windows package + PowerShell fresh install `44184182900`, Director Health `44184182984` and Push-on-main `44184180721`: PASS. SonarCloud skipped.
- DB-001 remains open. No readiness score uplift; V1 remains NOT READY.


### DB-001 recurrence — 2026-11-18

- [ ] Resolve owner-reported DB-001 reference `895e2c23-bc6b-4919-a891-1e8e5c1e27e4`; cause remains unconfirmed pending the matching runtime record.
- [x] Add safe, reference-specific Windows log extraction at `ops/windows/Get-ZazuIncident.ps1`; output is restricted to allowlisted incident metadata and database diagnostics.
- [ ] Apply the targeted correction after inspecting the matching record and verify the affected workflow on the intended installation.
- Do not reset, reinstall or alter business data as an investigative step. Release remains NOT READY.


### COM-01 customer document handoff — PARTIAL (2026-11-18)

- [x] Signed customer quote view supports browser Print / Save as PDF.
- [x] Quote detail provides WhatsApp handoff to the signed customer view.
- [x] Issued/paid invoice detail provides WhatsApp handoff to a seven-day signed read-only invoice view; the view supports browser Print / Save as PDF.
- [x] Invoice-linked payment receipts have a signed, read-only customer view with WhatsApp handoff and browser Print / Save as PDF.
- [x] Online invoices and offline invoice sync share consecutive business/month-scoped numbers in the existing `INV-YYYYMM-` format; offline records refresh to the canonical server number and voided numbers are not reused.
- [ ] Tax-adviser/legal review of invoice numbering and tax-invoice presentation.
- [ ] Direct generated PDF-file attachment and desktop/Android practical acceptance remain open.
- DB-001 is DORMANT / MONITORING by current Founder report; the separate installation-recovery gate remains OPEN.


### Offline revision-aware sync hardening — 2026-11-20

- [x] Server-side monotonic aggregate revisions and stale-update rejection are implemented.
- [x] Same-device mutation chains use actual predecessor result revisions; unresolved predecessors remain queued/retryable instead of creating duplicate conflicts.
- [x] Keep-local conflict resolution uses a new immutable mutation ID; use-server resolution records the current revision for later dependent edits.
- [x] Parent revision advancement covers quote acceptance → Work and purchase receipt → affected inventory items, as well as payment, inventory movement and asset allocation parent changes.
- [x] Push acknowledgements expose all affected related identities; client reconciles them even when the command has no standalone identity.
- [x] Regression coverage added for cross-device stale writes, same-device chains, conflict rebase/discard, cross-record command revisions and multi-aggregate browser acknowledgement.
- [x] Service-worker cache v51 and matching test fixtures.
- [x] Exact-head offline browser acceptance including the new related-aggregate case — run 44810699348, SHA 041314f.
- [ ] Final exhaustive inventory of indirect, queued and command-side writers.
- [ ] Physical installed-device offline/restart/reconnect acceptance remains a separate owner-operated gate.

Exact-head Laravel, PostgreSQL migration smoke, PHPMD, Zazu Quality, Director Health, Push-on-main and offline browser/mobile acceptance passed on `041314f` (run 44810699348). CASE-ZAZU-0033 remains OPEN and V1 remains NOT READY until the remaining gates are evidenced. This implementation increment does not uplift the readiness score.
