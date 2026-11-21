## Empty private-storage restore — implementation checkpoint 2026-11-21

- Restore now treats the v2 manifest declaration as authoritative even when private storage contains zero files.
- An empty staging directory replaces stale target private files; rollback remains in the existing guarded path.
- Added a database + empty-private-storage integration regression proving stale files are removed. Exact-head CI pending.
- Owner-operated restore and private-media acceptance remain open.

---

## Backup/restore archive compatibility — implementation checkpoint 2026-11-21

- Fixed a mismatch where backup could publish up to 50,002 entries but restore accepted only 50,000.
- Backup and restore now share limits: 50,000 private files, 50,002 ZIP entries, and 2 GiB uncompressed total. Backup verifies the completed ZIP including its manifest before publication.
- Added boundary regression tests and an empty-private-storage restore integration test that proves stale target files are removed. Exact-head workflow [44937037910](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44937037910) passed Laravel **673 / 1 skipped / 4,575 assertions**, PostgreSQL migration/recovery smoke, **63 desktop + 7 mobile + 1 host-outage + 1 phone-local attachment/reconnect browser journeys**, PHPMD and Zazu Quality.
- Windows package build, fresh PowerShell install, populated database/private-media restore and empty-private-storage restore passed [workflow 44937732880](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44937732880) against the same runtime source. The owner-operated isolated restore drill and independent off-host copy remain mandatory; automated CI does not close the physical recovery gate.

---

## Offline host-outage monitor — exact-head acceptance PASS — 2026-11-21

**Source:** [41d7a25](https://github.com/Leano-Jordan/ZazuEMP/commit/41d7a256f80b1e1d98cbb43509f81dc26cd600ec).

- Added periodic HEAD /up health checks because navigator.onLine can remain true while the Zazu LAN host is stopped.
- A visible, non-destructive notice now explains that the host is unavailable and links to the cached local workspace. It does not auto-redirect or discard an open form.
- Dashboard offline workspace visibility now responds to actual Zazu-origin availability, not only the network-interface state.
- Regression simulates host 503/200 transitions while the browser remains online, on desktop Chromium and Pixel 7 viewport.
- Exact-head Laravel: **670 passed / 1 skipped / 4,552 assertions**; PostgreSQL migration/recovery smoke: **PASS**; offline browser: **63 desktop + 7 mobile viewport + 1 mobile host-outage + 1 phone-local attachment journey passed with no retries**. Zazu Quality, Director Health, Contract Lint and Push-on-main: **PASS**. [Exact-head run 44934728827](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44934728827). Windows V1 package and fresh-install acceptance passed on runtime implementation head [44934642257](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44934642257). SonarCloud skipped.

**Physical acceptance still required:** install/update the PWA on the actual phone while online, open a supported route, stop the Zazu host while Wi-Fi remains connected, verify the outage notice and local workspace shortcut, then verify local reads/writes, force-close/relaunch, reconnect and queue drain. Capture the exact app build and observed result. Do not substitute browser emulation for this drill.

## Exact-head verification update — 2026-11-21

**Application code head:** [7d4b097](https://github.com/Leano-Jordan/ZazuEMP/commit/7d4b097a13d02f3d5369bf8e75e2dc85c361beb7).

- Laravel: **670 passed / 1 skipped / 4,552 assertions**.
- PostgreSQL migration/recovery smoke: PASS.
- Offline browser acceptance: **62 desktop Chromium**, **7 mobile viewport**, **1 phone-local Work/attachment/reconnect journey** passed.
- Windows V1 package: PASS.
- PHPMD, Psalm, Zazu Quality, populated upgrade/rollback and Director Health: PASS.
- SonarCloud: SKIPPED; not counted as a pass.
- Trusted-device stale-snapshot authorization case CASE-ZAZU-0036: CLOSED on this code head.

Evidence: [Laravel + browser + PostgreSQL run 44931555043](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44931555043) · [Windows package 44931554910](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44931554910) · [PHPMD 44931555002](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44931555002) · [Psalm 44931555131](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44931555131) · [Upgrade/rollback 44931555044](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44931555044) · [Director Health 44931554979](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44931554979).

**Release disposition remains NOT READY.** Physical Android cold launch/restart/reconnect, actual owner-PC off-disk restore, populated live-business commercial reconciliation, POPIA operational controls and ZAZU name clearance remain open. No readiness score uplift is claimed.

---

## Director acceptance checkpoint — 2026-11-21

**Current source/test head:** `e45cab97cbcdbc3f989bf3843d751bb0f373c305` on `main`.  
**Laravel + browser + PostgreSQL:** [44904393629](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44904393629) · **Zazu Quality:** [44904393468](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44904393468) · **PHPMD:** [44904393461](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44904393461) · **Director Health:** [44904393452](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44904393452) · **Push-on-main:** [44904393426](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44904393426). SonarCloud [44904393462](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44904393462) was skipped.

- Laravel: **653 passed / 1 skipped / 4,495 assertions**.
- Offline browser: **61 desktop Chromium + 7 mobile viewport + 1 phone-local attachment journey passed; no flaky retries**.
- PostgreSQL migration/registration/backup/restore/private-file recovery smoke: **PASS**.
- Zazu Quality, Composer security audit, secret scan, PHPMD, Director Health and Push-on-main: **PASS**.
- Offline payment and expense reconciliation have server-side and browser acceptance. Expense acceptance preserves job/supplier/purchase-order identities, canonical server identity, audit entry and idempotent replay.
- Business archive restore rejects a workspace with existing trusted-device state before importing rows/files.
- A simulated mid-restore private-file write failure proves database rollback and cleanup of the file already written.
- Expired-session browser acceptance verifies the sign-in gate while preserving the trusted offline queue; it no longer assumes the service worker must change the URL during cached-shell fallback.
- Current service-worker static cache: `zazu-static-v60`.

**Evidence disposition:** evidence maturity remains **77/100**, unchanged. These test closures improve confidence but do not replace physical Android, intended-PC recovery, live populated-business commercial and POPIA operational acceptance. V1 remains **NOT READY**.

**Next engineering target:** adversarially verify business isolation for offline financial references, especially foreign supplier/event/purchase-order identities supplied in an expense mutation.

---

## Director acceptance checkpoint — 2026-11-21

**Current source/test head:** `b26df40e150fc3b81425db81fe9cb16040970547` on `main`.  
**Laravel + browser + PostgreSQL:** [44903681914](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44903681914) · **Zazu Quality:** [44903681940](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44903681940) · **Push-on-main:** [44903681684](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44903681684).  
**PHPMD / Director Health:** passed on immediately preceding PHP-identical source head `ed201e098892bce4a86b5383585616a31f26c589` ([PHPMD 44903058798](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44903058798), [Director Health 44903058803](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44903058803)). The b26 change after that head is browser-test-only.

- Laravel: **652 passed / 1 skipped / 4,487 assertions**.
- Offline browser: **61 desktop Chromium + 7 mobile viewport + 1 phone-local attachment journey passed; no flaky retries**.
- PostgreSQL migration/registration/backup/restore/private-file recovery smoke: **PASS**.
- Zazu Quality, Composer security audit, secret scan and Push-on-main: **PASS**. SonarCloud: **SKIPPED**.
- Existing offline payment and expense flows now have server and browser reconciliation coverage. Expense acceptance preserves job/supplier/purchase-order identities, canonical server identity, audit record and idempotent replay.
- Business archive restore now has a regression proving a target with trusted-device state is rejected before imported records or files are written.
- Expired-session browser acceptance now tests the actual authentication barrier (visible sign-in modal) while preserving the trusted offline queue; it no longer assumes the service worker must change the URL when its cached-shell fallback is used.
- Current service-worker static cache: `zazu-static-v60`.

**Evidence disposition:** evidence maturity remains **77/100**, unchanged. These closures improve verification confidence but do not replace the open physical Android, intended-PC recovery, live populated-business commercial and POPIA operational acceptance gates. V1 remains **NOT READY**.

**Next engineering target:** prove business archive restore rollback and file cleanup when a private-file write fails partway through restoration.

---

## Offline expense reconciliation — 2026-11-21

**Exact source/test head:** `3c8db9bd02fec06603d48b3050f7d306d7b41070` on `main`.  
**Laravel workflow:** [44902438027](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44902438027) · **Zazu Quality:** [44902438064](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44902438064) · **Push-on-main:** [44902438499](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44902438499).

- The existing offline expense form now has browser acceptance: save while disconnected, preserve the job/supplier/purchase-order local identities, reconnect, receive the canonical expense server ID and drain the durable queue.
- Server-side feature coverage applies the real offline expense handler and verifies the ZAR amount, paid status, event/supplier/purchase-order ownership, expense identity, audit event and duplicate-safe replay.
- Laravel: **651 passed / 1 skipped / 4,480 assertions**.
- Offline browser: **61 desktop Chromium + 7 mobile viewport + 1 phone-local attachment journey passed**.
- PostgreSQL migration/registration/backup/restore/private-file recovery smoke: **PASS**.
- Zazu Quality, Composer security audit, secret scan and Push-on-main: **PASS**. PHPMD and Director Health passed on the immediately preceding PHP-identical source head. SonarCloud: **SKIPPED**.

**Evidence boundary:** the browser acknowledgement is deterministic/mocked. The PHP feature test exercises the real server-side mutation handler and database transaction. The full populated live-business commercial walkthrough remains a separate acceptance gate. Physical Android cold launch/restart/reconnect, intended-PC recovery and POPIA operational sign-off remain separate gates. V1 remains **NOT READY**; no score increase is claimed.

**Next engineering target:** strengthen business archive restore acceptance by proving a workspace with existing trusted-device state is rejected before any imported data or files are written.

---

## Offline payment reconciliation — 2026-11-21

**Exact source/test head:** `e1debd77c9d064f92895768f120ac752a34d897b` on `main`.  
**Laravel workflow:** [44901265394](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44901265394).

- The server-side offline payment handler is exercised with a real invoice and trusted device. A 25.00 ZAR payment is recorded once; invoice paid amount becomes 25.00, balance becomes 75.00, and the invoice sync identity advances from revision 1 to 2.
- The server returns the invoice as a related identity for the payment command. Replaying an already-applied mutation does not create a second payment or advance the invoice revision again.
- Browser acceptance verifies the offline payment receives its canonical payment identity and applies the related invoice revision acknowledgement; the durable mutation queue drains.
- Laravel: **650 passed / 1 skipped / 4,466 assertions**.
- Offline browser: **60 desktop Chromium + 7 mobile viewport + 1 phone-local attachment journey passed**.
- PostgreSQL migration/registration/backup/restore/private-file recovery smoke: **PASS**.
- Zazu Quality, Composer security audit, secret scan, PHPMD, Director Health and Push-on-main: **PASS**. SonarCloud: **SKIPPED**.
- No production source change was required for this payment target; this batch closes the backend and browser verification gap.

**Evidence boundary:** the browser acknowledgement is deterministic/mocked. The PHP feature test exercises the real server-side mutation handler and database transaction. The populated online commercial reconciliation remains a separate live-business acceptance gate. Physical Android cold launch/restart/reconnect, intended-PC recovery and POPIA operational sign-off remain separate gates. V1 remains **NOT READY**; no score increase is claimed.

**Next engineering target:** exercise the existing offline expense workflow end-to-end, including its linked business records, canonical identity acknowledgement and duplicate-safe replay.

---

## Offline quote-to-invoice sync acceptance — 2026-11-21

**Exact source/test head:** `36a2f605481e3985b647dda710ead7bef157b70f` · [workflow 44898943264](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44898943264).

- [x] Disconnected quote acceptance is queued and reflected in local state.
- [x] An invoice is created locally from the newly accepted quote.
- [x] Reconnect acknowledges both queued mutations and advances quote/Work revisions.
- [x] The invoice receives its server identity using its actual generated local ID; its quote-local relationship is retained.
- [x] Durable queue drains after both acknowledgements.
- [x] Offline browser 60 desktop Chromium + 7 mobile viewport + 1 phone-local attachment journey passed.
- [x] Laravel 649 passed / 1 skipped / 4,456 assertions; PostgreSQL migration/registration/backup/restore/private-file recovery smoke passed.
- [x] CASE-ZAZU-0037 / ENG-FIXTURE-007 closed as an F2/F3 test-fixture correction; production code was unchanged.
- [ ] Full offline sync matrix and live-business commercial reconciliation remain OPEN; mocked browser acknowledgements do not replace live server persistence evidence.

V1 remains NOT READY. Physical Android cold launch/restart/reconnect, intended-PC recovery and POPIA operational sign-off remain separate gates.

---

# Zazu EMP — V1 Release Checklist

Latest application/test source verification: e1debd77c9d064f92895768f120ac752a34d897b — 650 passed / 1 skipped / 4,466 assertions; PostgreSQL migration/recovery smoke PASS; offline browser 60 desktop + 7 mobile + 1 phone-local journey PASS.
Latest Windows host-operations source: 365609b4d30ca4978ba9480cf3571bb792ad442b; Windows package/fresh-install PowerShell acceptance PASS [run 44892813237]. Exact-head PHPMD, Psalm, Zazu Quality, populated upgrade/rollback and Director Health all PASS [runs 44892813263, 44892813233, 44892813235, 44892813238, 44892813214].
Disposition: **NOT READY** · Evidence maturity: **77/100**. Code implementation does not substitute for owner-PC recovery, physical Android or POPIA evidence.

## Operational reliability implementation — 2026-11-21

- [x] Daily 02:00 Windows backup task, missed-run catch-up, 14-archive retention and protected local destination implemented.
- [x] Startup backup-age warning, business-owner dashboard banner and Founder Control Centre missing/stale archive warning implemented.
- [x] Optional independent USB/synced-folder copy configured by `ZAZU_BACKUP_SECONDARY_PATH`, SHA-256 verified by the runner, with 14-archive retention and failure status; recovery-drill procedure documented.
- [ ] Observe the scheduled backup on the owner's actual PC, copy an archive off-disk and pass the controlled restore drill.
- [x] Owner approver recorded on each newly paired/re-registered device; sync audit entries use the approver as actor.
- [x] Active legacy devices with no recorded approver can be explicitly re-approved by a business owner; historical activity remains unattributed rather than guessed.
- [x] Activity audit labels trusted-device changes as performed via a trusted device approved by the recorded owner.
- [x] Trust and logout prompts disclose that device sync changes are attributed to the approving owner, not necessarily the operator.
- [x] Shared-device/per-user authority limitation and legacy-device attribution boundary recorded in docs/V1_KNOWN_LIMITATIONS.md.
- [x] Query-builder mass-write guard for revision-tracked aggregates added.
- [x] Backup status health calculation refactored below PHPMD complexity thresholds; owner warning and independent-copy status regressions covered by Laravel tests.
- [x] Existing PC installs migrate to LOG_STACK=daily at startup; 14-day log retention is configured, and startup invalidates a stale Laravel config cache before serving so the setting takes effect.
- [x] New registrations and password resets require at least 10 characters; no online breached-password service is required.
- [x] iOS offline limitations are explicitly documented; V1 physical offline acceptance is Android-only.
- [ ] Physical Android cold-launch/restart/reconnect acceptance.
- [ ] Live populated commercial reconciliation.
- [ ] POPIA operational sign-off and ZAZU name clearance.
- [ ] Full PostgreSQL commercial/sync suite only if a PostgreSQL host/pilot is selected; the current V1 owner-PC host uses SQLite.

Evidence maturity remains **77/100**. V1 remains **NOT READY** until the owner-dependent gates above are evidenced.

---

## Stale-write protection closure — 2026-11-21

**Source/test head:** `51a75eb1d06017211bd7808093d1863c930b6d71`.

- [x] Laravel: 634 passed / 1 skipped / 4,399 assertions.
- [x] PostgreSQL migration/recovery smoke.
- [x] Offline browser: 59 desktop Chromium, 7 mobile-viewport and 1 phone-local Work/attachment/reconnect journey passed.
- [x] PHPMD, Zazu Quality, Composer security audit, secret scan and application checks passed. SonarCloud was skipped.
- [x] Paired-device stale-write rejection with authoritative remote snapshot and preserved local payload.
- [x] Same-device causal chains, intervening server writes, rejected-first-mutation rebase, immutable new mutation IDs and conflict payload preservation.
- [x] Online root/nested writer review and parent revision propagation; primary-contact creation mass-update bypass corrected.
- [x] CASE-ZAZU-0033 / ENG-DATA-003 CLOSED.

The full offline sync workflow matrix remains OPEN separately, along with physical Android cold launch/restart/reconnect, intended-PC recovery drill, live-business commercial reconciliation and POPIA operational sign-off. V1 remains NOT READY; evidence maturity is 77/100 (+2 for verified stale-write protection).

[Exact-head workflow](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44881431689).

---

## Receiver reconciliation and bootstrap authorization — 2026-11-21

**Source head:** 559e66b87de8617f967b722b55b0fbfd9b116495.

- [x] Laravel: 632 passed / 1 skipped / 4,388 assertions.
- [x] PostgreSQL migration/registration/recovery smoke.
- [x] PHPMD, Zazu Quality, application, Composer audit, secret scan, Director Health, Push-on-main and Actions/Python/JavaScript analysis. SonarCloud was skipped.
- [x] Receiver bootstrap includes the exact pulled delivery prefix and all primary/related aggregates outside the normal 500-record window.
- [x] Normal bounded collections promote recently revised aggregates while retaining display order.
- [x] Explicit Customer/Work/Quote/Preparation pairing selections remain pinned; targeted delivery cannot silently broaden them.
- [x] Bootstrap revalidates active device status and token hash under business-first locks; revocation and credential-replacement regressions pass.
- [x] Offline-browser acceptance: 59 desktop Chromium passed; 7 mobile-viewport passed; 1 phone-local Work/attachment/reconnect journey passed.
- [ ] Full sync matrix and physical-device cold launch/restart/reconnect remain open. CASE-ZAZU-0034 and CASE-ZAZU-0035 are CLOSED on this exact head.

Evidence maturity is 75/100 (+2) after exact-head backend, PostgreSQL and desktop/mobile browser verification. This does not close broader V1 readiness.

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

DB-001 remains DORMANT / MONITORING by Founder report; its historical root cause is unproven and it is not an active release blocker. Installation recovery remains OPEN independently. V1 remains NOT READY; evidence maturity is 75/100, with physical Android, intended-PC recovery, real-business reconciliation and POPIA operations still open.

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


**Writer-audit correction — 2026-11-21:** primary-contact creation previously used a relationship mass update that bypassed parent Customer revision tracking. The create path now locks and updates existing primary contacts through their models, with a regression asserting revision advancement. Exact-head Laravel on `51a75eb` passed **634 / 1 skipped / 4,399 assertions**; PostgreSQL migration/recovery, PHPMD and desktop/mobile browser acceptance passed. The two-paired-device stale-write regression is included. CASE-ZAZU-0033 / ENG-DATA-003 is CLOSED; the complete offline sync matrix remains OPEN.

Regression tests cover revision-ranked refresh, delivery inclusion outside 500, cross-device sequence isolation, commercial second-device reconciliation, receipt/inventory reconciliation, and browser pull → targeted bootstrap → acknowledgement ordering. CASE-ZAZU-0034 / ENG-DATA-004 records this defect. CASE-ZAZU-0033 / ENG-DATA-003 is now CLOSED; the complete V1 offline-sync matrix remains OPEN.

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
