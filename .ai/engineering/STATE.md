## Director source audit refresh — 2026-11-20

**Repository:** `Leano-Jordan/ZazuEMP` · **write target:** `main` only. **Latest full application-code verification baseline:** `526bdf38dab6ff540c143b0e57dfcee468006dc3`. The 2026-11-20 source audit and subsequent memory, readiness, architecture, status, roadmap and release-checklist reconciliations are documentation-only; none changes application code. The latest full application-code verification remains 526bdf38dab6ff540c143b0e57dfcee468006dc3.

- Latest completed repository-wide workflows observed during this audit: Rosscore Director Health, Zazu Quality, Director Contract Lint and Push on main all passed on `91f41ec`. These are documentation-head checks, not application acceptance. The Zazu Quality jobs were npm audit/service-worker authentication cache-race checks, Composer security audit and secret scan; this is not a Laravel or full offline-browser run.
- Latest full application verification remains on `526bdf3`: Laravel 607 passed / 1 skipped / 4,158 assertions; PostgreSQL migration/registration/backup/restore/private-file recovery passed; offline browser 51 desktop + 6 mobile-viewport + 1 phone-local Work/attachment journey passed. Treat this as the latest full application-code evidence; later `0bd468f`, `91f41ec` and context-reconciliation commits changed documentation only.
- Current product boundary: bounded IndexedDB workspace and mutation queue; customer and Work local directories/inspection, plus a dashboard quote detail/acceptance panel. Cached Laravel HTML remains a snapshot, not a general local route renderer. Do not state app-wide offline parity.
- Source-traced sync path: `setupZazuOfflineForms` / local customer and Work edit handlers → `ZazuOffline.queueMutation` → IndexedDB state + queue → `SyncController::push` → business/device-scoped recorder and transactional applier → `OfflineDomainMutationHandler` → `SyncEntityIdentityRegistry` for model results → push acknowledgement/local ID reconciliation. Other-device delivery uses contiguous delivery sequences; the client obtains the current bootstrap snapshot, applies it only when its local queue/conflicts are clear, then acknowledges the contiguous delivery prefix.
- Existing protections confirmed: stable business-scoped record UUID mapping; mutation replay identity checks; business/device/mutation lock order; atomic domain apply + delivery publication; retryable infrastructure failures remain queued; pull and acknowledgement cannot skip a delivery sequence. The pending client queue is sorted by creation time; do not log this as a queue-order defect.
- **Critical open gap confirmed against current source:** existing-record offline updates carry no authoritative base version. Handlers resolve the current server row and update it without a stale-snapshot precondition. The conflict path handles domain/validation rejection and mutation-ID payload mismatch, not stale-record versions; conflict actions are retry/discard, not a safe rebase. This can overwrite another trusted device's intervening edit.
- The source audit mapped all 17 declared offline mutation entity types and their UI entry, IndexedDB target, server handler, identity outcome and reconciliation route in `docs/V1_RELEASE_CHECKLIST.md`. This is a static trace; it does not count as runtime acceptance.
- **Expanded online/offline writer trace — partial, 2026-11-20:** offline update-capable handlers mutate customer + nested contacts, Work + nested requirements, preparation items, quote + quote versions/items, inventory items, event costs, assets + allocations, suppliers, capabilities and purchase orders. Online write paths confirmed include CustomerController update/contact creation; CustomerContactController create/update/delete; WorkController update; SupplierController update; BusinessCapabilityController update; AssetController update/allocate/release; QuoteService revision/draft writes and QuoteController version/status/acceptance writes; EventPreparationController status updates; PurchaseOrderController status/receiving and PurchaseOrderReceivingService item/stock effects; InventoryController stock movement; EventLifecycleService event status transitions. This is an initial source trace, not yet an exhaustive writer inventory. Finance/payment/invoice, customer-contact aggregate, inventory, purchasing and command-side-effect paths still require complete route/service/model transaction review before implementation. Further confirmed command paths: FinanceTransactionService::recordPayment creates a Payment and updates Invoice status under business → event → invoice locks; PurchaseOrderReceivingService::receive creates a receipt, updates purchase-order lines/order status and creates inventory movements under business → event → order locks; InventoryController::movement creates an inventory movement under business → event → inventory-item locks. Asset allocation/release similarly mutates both allocation rows and asset status. These are consequential command paths: preserve their idempotency and domain invariants while advancing every affected snapshot revision exactly once per logical transaction.
- Revision design must use a shared aggregate concurrency boundary for both online and offline writers. Incrementing only in the offline handler, relying on updated_at, or adding a naive per-model observer is insufficient: nested writes may advance multiple times and an online writer that does not acquire the same lock can still race. Do not begin a partial implementation until all writer paths and transaction boundaries are mapped.
- Next engineering target remains the server-enforced stale-version contract across supported existing-record updates, including same-device causal mutation chaining and explicit, loss-preserving rebase. Do not use browser timestamps, last-write-wins, or weaken domain idempotency for payment, receipt, stock and allocation commands. This requires focused tests before claiming implementation or acceptance.
- Still open and separate: full sync acceptance matrix; physical Android offline cold launch/restart/reconnect; installed-PC DB-001 diagnostics/recovery; live commercial reconciliation; POPIA operational sign-off; ZAZU name clearance. PC-to-phone pairing remains CLOSED. V1 remains NOT READY; evidence maturity remains 73/100.

---

## Founder-directed target addition — offline sync reliability across V1 (2026-11-20)

- The Founder has explicitly added **offline sync reliability across the full supported V1 workflow matrix** as an active release target. Sync quality is not currently at the required standard across the board.
- Track this separately from standalone phone-local offline launch and separately from PC-to-phone pairing. **PC-to-phone pairing is CLOSED by Founder confirmation and must not be reopened.**
- Acceptance must cover the complete declared UI → IndexedDB → durable mutation queue → server handler → stable entity identity → acknowledgement/pull/bootstrap → reconciled UI path for every supported offline operation.
- Required checks include restart durability; retries after offline, timeout and partial responses; duplicate/reordered delivery idempotency; parent/child references; cursor/pagination and bootstrap consistency; device/business isolation; explicit conflict preservation and resolution; attachment bytes/metadata/linkage; and domain rules for financial, inventory, purchasing, scheduling and asset mutations.
- No new offline engine, duplicate queue, cache-only workaround, relaxed isolation or idempotency assertion, or blanket offline claim. Inspect and extend the existing architecture only where evidence requires it.
- Release checklist: `docs/V1_RELEASE_CHECKLIST.md`; sole readiness register: `.ai/engineering/READINESS_REGISTER.md`; canonical offline policy: `Leano-Jordan/Rosscore-Labs/docs/ROSCOR_OFFLINE_FIRST_STANDARD.md`.
- **Latest exact-head evidence — `526bdf38dab6ff540c143b0e57dfcee468006dc3` (2026-11-20):** Laravel PASS — 607 passed / 1 skipped / 4,158 assertions; PostgreSQL migration/registration/backup/restore/private-file recovery PASS; offline browser PASS — 51 desktop Chromium tests, 6 mobile-viewport tests and 1 phone-local Work/attachment journey. This head adds a fresh-page/cold-launch browser regression proving the authenticated cached shell, local customer/Work records and durable queued mutations survive a new page in the same installed-browser context. Zazu Quality and Push-on-main PASS; SonarCloud skipped. PHPMD, Psalm, Windows V1 package and populated upgrade/rollback also PASS on application-equivalent parent `30e70e929dc848c0119c8452395ad225a23b7c47`; `ae3d49c` and `526bdf3` are test-only commits after that application-code baseline.
- **Sync source corrections on this head:** bootstrap replacement now preserves local state while failed mutations/open conflicts remain; the client reconciles canonical identities for `event_cost`, nested `payment`, `inventory_movement` and `asset_allocation`; disconnected inventory receipts update local stock/history; final offline invoice payment sets local status to paid; and retryable attachment uploads re-enter the backoff scheduler while retaining their idempotency key. Regression tests cover the relevant paths.
- Stale six-digit E2E fixtures were aligned with the existing eight-digit pairing contract. This is test maintenance, not a reopening of the Founder-confirmed PC-to-phone pairing, which remains CLOSED.
- **Still OPEN:** broad offline sync acceptance across the whole supported workflow matrix; stale concurrent-update detection/base-version conflict handling; actual Android standalone offline launch/restart/reconnect; DB-001 on the installed PC; installed-target recovery; live commercial reconciliation; POPIA operations and ZAZU mark clearance. Keep release NO-GO and do not uplift the official 73/100 evidence-maturity score from CI alone.
- Engineering corrections committed directly to `main`: `b07110b` allows a deliberate second offline form save only after the saved fields change and advances the service-worker cache to v41; `30e70e9` splits the two workspace purge command responsibilities without changing deletion safeguards; `ae3d49c` corrects the payment E2E assertion to compare the pushed mutation with the queued payment identity. No branches were created.
- The offline form correction is verified by the 51 desktop + 6 mobile + 1 phone-local attachment browser acceptance on exact head. It does not close stale-update detection, full sync parity or physical Android acceptance. Keep release NO-GO; no readiness uplift.

---

## Phone pairing discoverability and eight-digit contract correction — 2026-11-19

- Root cause confirmed from current source: owner settings generated an eight-digit code and `SyncController::provision` required eight digits, but `public/zazu-offline.js` silently truncated the phone submission to six digits. The phone entry itself was only exposed through a hidden query-string prompt.
- Corrected the client to normalize and strictly validate all eight digits before sending the provisioning request; it no longer truncates or submits incomplete codes.
- Added a discoverable **Pair this phone** form to the authenticated app shell. It is revealed on phone-sized/mobile user-agent layouts only while the device is unpaired, provides numeric/one-time-code input, eight-digit validation and inline error feedback, and keeps the owner-generated code's 10-minute/single-use approval model intact.
- Removed the hidden query-string/browser-prompt flow. The phone reloads after successful pairing so the newly bootstrapped business workspace is loaded.
- Bumped the precached service-worker static cache from v38 to v39 and updated the service-worker cache regression test. This is required so installed phones do not keep executing the old truncated client.
- Added a focused feature regression for the rendered pairing entry, strict eight-digit client contract, removal of the prompt and cache version. Existing server tests already cover eight-digit generation and one-time provisioning.
- Updated `docs/ZAZU_OFFLINE_FIRST_ARCHITECTURE.md` with the user-visible pairing flow and cache invalidation rule.
- Source and regression changes committed directly to `main`; no branches created. **Tests/CI and rendered phone acceptance have not yet been verified on this exact head.** The owner's old plain-HTTP Wi-Fi address remains unsuitable for installed PWA/service-worker acceptance; the separate private HTTPS setup must complete before physical phone acceptance.
- No change to logout/session semantics, local queues, business-scoped IndexedDB identity or server-side pairing authorization.

---

## Commercial direction adopted — 2026-11-19

- Added the Founder-approved commercial positioning and execution direction to `docs/COMMERCIAL_ARCHITECTURE_RESEARCH_2026-11-16.md`.
- Positioning: **Run your business. Manage every job. Know what you actually earn. Even offline.** Keep Zazu broad across event/catering and related service businesses; do not narrow to one trade without customer evidence.
- Commercial proof remains the existing event/job → quote → acceptance → invoice → payment → purchasing/receiving → incurred costs → event contribution → export chain. Do not describe indicative event contribution as audited accounting profit.
- WhatsApp quote handoff is an evaluation of the existing signed-link workflow, not approval for a new messaging subsystem. Inspect existing behavior before deciding whether a gap exists.
- Trust requirements are clarified: POPIA operational sign-off remains open; invoice numbering does not establish SARS tax compliance; claims about privacy, export/recovery and offline capability must match demonstrated behavior.
- First-impression priorities are the landing page, dashboard, quote workflow, signed public quote page and customer PDF. Improve the existing visual system; require rendered desktop/phone evidence for UI acceptance.
- A fictional, ZAR-valued demo business is a commercial preparation task. Do not seed demo data into real customer installations.
- This direction does not amend the V1 scope freeze or create new release gates. Engineering remains focused on existing V1 blockers and observed workflow defects.
- No new branch created. Documentation committed directly to `main`.

---

## Sentinel Owner Admin consolidation — 2026-11-19

- Integrated business-scoped Sentinel checks into the existing `/owner` Owner Admin page. The checks are limited to read-only database response, active owner membership and this business's customer/event/service data paths; they do not call the installation-wide incident recorder or return global incident metadata.
- Removed the standalone Sentinel Rescue controller/view. The authenticated owner-only legacy `/sentinel/rescue` route redirects to `/owner#business-health`. Persistent System navigation now exposes Owner Admin; the Founder destination is labelled Founder Control Centre and provides a direct return to the business workspace when one is active.
- Removed the Owner Admin metric tiles that repeated dashboard metrics. The page now focuses on business health and owner actions (settings, audit, business export/recovery, compliance and services).
- Founder Control Centre now states that the registered customer licensing registry is not live fleet telemetry. Remote customer installations do not currently report health/connectivity; no heartbeat or remote telemetry was introduced.
- Focused feature coverage was updated/added. **Tests and rendered UI are not yet verified on the runtime or exact CI head.** No branch was created; all changes were committed directly to `main`.

---

## Rosscore Labs Founder Control Centre and Sentinel direction — 2026-11-19

- The user is the sole Founder, owner and final authority for Rosscore Labs and Zazu EMP. Address him directly; never frame the Founder as hypothetical, replaceable or a role that may pass to someone else. Company identity: **Rosscore Labs**.
- `/admin/sentinel` is the Founder's installation-wide oversight dashboard. Its purpose is to monitor every Zazu instance and platform health. It should provide fleet/instance status and operational visibility without exposing unnecessary customer or business-sensitive records.
- Sentinel Rescue (`/sentinel/rescue`) is to merge into the existing business Owner Admin experience as its health, diagnostics and safe-recovery capability. Avoid a separate owner-facing Rescue page/destination and duplicated owner dashboard information.
- Maintain strict scopes: Founder Control Centre = cross-instance/platform oversight; Owner Admin with integrated Sentinel Rescue = diagnostics for the active authorised business only. Never expose the global incident ledger or another business's data to a business owner.
- Use one coherent Sentinel capability with server-enforced data scopes. Both surfaces must be discoverable through persistent navigation; no manual URL entry as the intended workflow. Keep a clear return path to the business workspace. Link visibility is not a security boundary.
- This records the agreed direction only. No application code, routes, permissions or UI have been changed by this documentation update. Implementation requires an authorised execution batch and source-level inspection first.

---

## Provisional customer/privacy contact confirmed — 2026-11-19

- Owner-approved current contact: `rosscorelabs@outlook.com`.
- Recorded as a provisional Zazu customer-support, privacy/data-subject request and initial customer incident-reporting channel in the privacy notice draft, data-subject request procedure, incident response runbook, customer terms draft, documentation index and commercial/legal register.
- Replace consistently when a dedicated Zazu address is established. This email is not a substitute for statutory regulator reporting or an approved emergency escalation process.
- These documents remain pre-launch drafts; publication still requires operational mailbox testing, provider/identity details and appropriate legal review.

## Manual offline licence issuer implementation — 2026-11-19

- Added the existing-app Artisan command `zazu:license:issue` for manual first-customer signed-token issuance. It supports the approved tier identifiers, explicit expiry or deliberate never-expire choice, signed feature metadata, renewal IDs and revocation IDs.
- The command reads a base64 Ed25519 secret key only from an external file, rejects key files inside the Zazu project, and verifies that the derived public key matches the configured app verification key. It does not create or store a key, change customer data, add feature restrictions or contact a remote service.
- Added feature tests for verifier compatibility, default feature-object serialization, expiry choice, revocation identity, project-local key rejection, public/private key mismatch, invalid timestamps and invalid feature metadata.
- Added the local Business ID to the existing owner-only licence settings section so a customer can provide the exact business reference needed for manual issuance; added a focused settings regression test.
- Updated the existing offline licensing specification with command usage and the remaining key-custody boundary.
- Issuer source/test and owner-reference UI/test changes are committed directly to `main`; latest application/test commit: `1c5033bbba9f028238dbedc1f7fcd7a408997e16`. The licensing specification and this state record were updated afterward.
- Local syntax check: command passes `php -l` on PHP 8.4.24. Timestamp parser spot-check accepts explicit-offset/Z timestamps and rejects an invalid calendar date.
- **Not yet verified:** GitHub Actions / full Laravel tests on the exact current head. The latest combined commit-status query returned no status records; this is not a CI pass.
- **Production boundary remains open:** no production key was generated or used. Owner-controlled key provisioning, NTFS ACL restriction, protected recovery copy, and verification that the customer package excludes the private key are required before issuing real customer licences. No score uplift is claimed.



## Operating reset and current V1 mission — 2026-11-18

**Source:** Founder-supplied read-only research report of main at cb7ab270. The report states that Laravel and browser suites were not run. Its test/CI statements are repository-documentation claims; market numbers and adoption scenarios are research estimates, not verified forecasts.

**Current assessment:** Late Alpha; the report estimates V1 completion at approximately 74%. DB-001 remains OPEN. The actual installed workflow has not yet been demonstrated error-free. There are no paying customers or completed customer pilots.

**Active priority:** DB-001 on the Founder's real installation. The first investigation is limited to one focused owner-assisted session: run the existing schema check on the Founder's PC and inspect the matching classified incident record. Establish root cause before changing code. Do not mark closed until the affected installed workflow is repeated successfully.

**Next priorities, in order:**
1. Prove the populated-record quote → invoice → payment recording → costs/expenses → event profit workflow. Manual/EFT payment recording is enough for the first pilot.
2. Establish manual EFT-paid signed licence-key issuance and activation for the first ten customers; no subscription automation without approval.
3. Complete legal/privacy information, Information Officer registration and operational POPIA responsibilities, plus a working WhatsApp support contact.
4. Run a two-week pilot with 2–3 friendly operators and ship only pilot-evidenced fixes.

**Operating constraints:** Focused execution batches, but engineering remains active; Sentinel and diagnostics remain active development targets; no new branches; main only; no Render or AMPPS in Zazu; no unrelated offline/sync expansion or new subsystem without explicit Founder approval; avoid repeating unchanged browser smoke tests; cap docs-only work. Pricing remains unresolved because existing tier models conflict and requires Founder decision.

**Progress labels are separate:** ENGINEERING COMPLETE; TESTS PASSED; VERIFIED ON FOUNDER PC; COMMERCIALLY USABLE; CUSTOMER VALIDATED. Never infer a later state from an earlier one. For installed-workflow acceptance, Founder confirmation on the actual PC is required. Documentation updates do not count as application acceptance.

**Owner/operator model:** The Founder is the sole human operator and final authority. Jarvis must state the intended scope, repository/ref, planned action and stop boundary before each distinct autonomous work batch. Do not invent staff or teams, and do not claim local-device verification without evidence.

## Sentinel local recovery implementation — 2026-11-18

- Added `/api/sentinel/recovery` as a stateless, loopback-only host-operator recovery surface outside Laravel's web/session middleware.
- The route explicitly excludes Laravel's database-backed `throttle:api` middleware; the recovery path therefore does not rely on the default database cache store.
- Access requires a loopback peer address and loopback Host header. POST also requires same-origin Origin and rejects cross-site Fetch Metadata.
- Added `zazu:sentinel-recovery-code`: creates a 256-bit random code, stores only SHA-256 hash plus five-minute expiry, invalidates prior codes, and displays the new code once.
- The code is consumed atomically under a filesystem lock and cannot be replayed. It is entered in a local form, never placed in a URL.
- The unlocked report reuses read-only `ZazuSystemHealth` checks. It contains installation health only and no customer records, attachment bytes, global incident ledger or founder console.
- Added feature coverage for unauthenticated local screen access, LAN denial, cross-origin POST denial and one-use report access.
- Updated Sentinel, DB-001 and Windows package documentation.
- **Acceptance remains pending:** GitHub Actions must pass; verify the command, route, file permissions and database/session-outage behaviour on the packaged Windows installation. This path cannot diagnose a stopped host, PHP process or broken Laravel bootstrap.
- All changes committed directly to `main`; no branches created.

## Sentinel access isolation correction — 2026-11-18

- Removed the one-year signed bearer URL from `/api/sentinel/rescue`; unauthenticated requests can no longer reach Sentinel Rescue.
- Moved Rescue to `/sentinel/rescue` behind `auth`, `auth.session`, `business.context` and the active-business `owner` middleware.
- Added explicit `sentinel.admin` route authorization for `/admin/sentinel`, separate from ordinary business-owner roles.
- Founder authorization now resolves only to the one explicitly configured email identity (`ZAZU_PLATFORM_ADMIN_EMAIL`). The legacy plural `ZAZU_PLATFORM_ADMIN_EMAILS` variable is ignored completely; business `admin` roles do not grant platform access. Existing installations must set the singular variable for the designated founder.
- Founder navigation is visible independently of business-owner navigation. Business owners see only their owner Rescue entry; the founder sees the Control Centre only when the configured identity matches.
- Added regression coverage for unauthenticated denial, removal of the old API route, authenticated owner access and updated founder identity configuration.
- **Resilience trade-off:** authenticated business-owner Rescue still depends on the normal user/session path. The separate loopback-only one-time-code host recovery path now provides installation health checks without that dependency. Packaged Windows acceptance remains open; this does not diagnose a stopped host or broken PHP/Laravel bootstrap.
- Changes were committed directly to `main`; no branches were created. Automated tests and runtime acceptance have not yet been executed in this environment.
# Zazu EMP — Current Director State

## DB-001 support capture — 2026-11-18

The DB-001 error page now offers a reference-scoped download of the matching redacted incident record through the local API. It is rate-limited and exposes only an allowlist of diagnostic fields; SQL, bindings, exception messages, customer data and credentials are excluded. This removes the need to copy diagnostic files between the Windows host and phone. The endpoint is useful only when the running installation contains the updated incident recorder. **DB-001 remains OPEN until the actual installed-workflow failure is identified, corrected and repeated successfully.** No root cause or readiness uplift is claimed.


As of: 2026-11-17
Repository: Leano-Jordan/ZazuEMP
Last verified application-code baseline: ba94b39e0460e4e57c560e98541808c762f50e25
Canonical branch: main only; never create branches. Engineering changes are active on main; no branches created.
Owner mandate: ship a dependable first-business V1; preserve scope; no Render or AMPPS workflow.

## Owner-approved event scheduling implementation — 2026-11-17

Owner authorised the focused V1 addition: multi-day event dates, start/end times, setup/collection dates, schedule clash warnings and connected rental allocation dates. Staff scheduling, quantity-based rental stock and workforce management remain deferred.

Implementation candidate is on `main` at source HEAD `71c8fc5c7aeba392b95e26aee694945b286f2c8e` (17 Nov 2026). Added an additive events migration; online Work create/edit persistence and validation; offline mutation payload/server application and workspace bootstrap; calendar multi-day expansion; schedule clash detection; and rental allocation date defaults. Added focused online/offline feature tests, including preservation of new schedule fields when older queued offline mutations arrive. At the current baseline, Laravel passes 532 tests / 1 skipped / 3,714 assertions; PHPMD passes; the Windows package and PowerShell first-install + private-media recovery pass on the immediately preceding code baseline 2ef2a0b. PostgreSQL and desktop/mobile offline-browser acceptance completed PASS for 71c8fc5 in run 44152431508. Zazu Quality remains red on the unpatched picomatch advisory. Rendered/physical acceptance is pending; no readiness uplift is claimed.

## Event scheduling implementation verification — 71c8fc5 (17 Nov 2026)

- Online event creation/editing persists event date range, start/end times, setup and collection dates; invalid date/time ranges are rejected.
- Active schedule overlaps produce a warning while preserving operator save; setup and collection logistics are included in the conflict window.
- Calendar now spans event and logistics dates and labels setup/collection days.
- Offline job mutations carry the schedule fields; server-side validation protects the fields and older queued mutations cannot erase schedule values they do not know about.
- Asset allocation defaults follow the event setup/collection window; existing serialized-asset overlap/return protections remain in force.
- Laravel: PASS — 532 passed / 1 skipped / 3,714 assertions. PHPMD: PASS.
- Windows V1 package, fresh PowerShell install and Windows SQLite/private-media recovery: PASS at 2ef2a0b (same scheduling implementation; latest commit after that is regression-test-only).
- PostgreSQL migration/recovery and desktop/mobile offline-browser acceptance: PASS at 71c8fc5 (run 44152431508; all three jobs succeeded).
- Zazu Quality remains RED on the unpatched high-severity picomatch build-tool advisory; no breaking downgrade or suppression applied.
- No readiness uplift. Physical Android, intended-installation recovery, populated-business financial reconciliation and POPIA remain operator gates.

## Current repository verification — fd82f6a (17 Nov 2026)

The only repository change between application-code baseline a5ead69 and fd82f6a is `.github/workflows/zazu-windows-package.yml`; application source and tests are unchanged across that range.

Application-code CI at a5ead69:
- Laravel: PASS — 526 passed / 1 skipped / 3,681 assertions.
- Offline browser acceptance: PASS — 45 desktop Chromium, 6 mobile-viewport and 1 phone-local attachment journey.
- PostgreSQL migration, account/workspace registration, backup/restore and private-file recovery: PASS.

Windows V1 Package run 82 at fd82f6a: PASS. The production candidate built, Windows PowerShell fresh installation passed, and a new Windows-native recovery drill created a disposable SQLite record and private-media file, backed up both, mutated both, restored the archive and verified the original database value and private-file bytes.

Director Health, Director Contract Lint and Push-on-main passed at fd82f6a. Composer security audit and secret scan passed.
LAN visual asset delivery is addressed in the current source: non-loopback clients use compiled same-origin Vite assets, a stale `public/hot` marker cannot send phones to an unreachable dev server, and the Windows LAN launcher removes stale hot state and verifies the built manifest/assets. `ViteAssetDeliveryTest` specifically exercises a remote client with a stale loopback hot URL and asserts compiled CSS delivery. These changes post-date the supplied 13 Nov phone screenshot. The actual Android launch still requires the HTTPS-origin physical-device acceptance in `docs/ZAZU_ANDROID_OFFLINE_INSTALL_ACCEPTANCE.md`; the old HTTP :8000 screenshot is not proof of current behavior or offline capability.

Zazu Quality is currently RED at `npm audit --audit-level=high`. The current lockfile contains the development/build-tool dependency chain that resolves to picomatch versions flagged by [GHSA-fc5q-39xc-vjpf](https://github.com/advisories/GHSA-fc5q-39xc-vjpf) / CVE-2026-112366. GitHub's advisory currently lists affected versions through 4.0.7 and no patched version. The finding is in the npm development/build toolchain; the Windows runtime candidate excludes npm manifests and node_modules. This still blocks the quality workflow. Do not suppress the advisory or apply the suggested breaking Vite downgrade without a verified fix.

The Windows CI round-trip is supporting evidence only. It does not close the installed-target operator gate because it is not a recovery drill on the intended owner/customer installation.
## Verified work
Rental asset date allocation was reproduced at c7be735: a second non-overlapping booking returned HTTP 422 before date-overlap evaluation. The online/offline correction now permits non-overlapping reservations, rejects overlapping and open-ended conflicts, supports per-allocation returns, and keeps asset status allocated until all active bookings are returned. Manual physical return remains operator-confirmed.

Online and offline regressions pass at current HEAD. The optional end date is normalized once and used consistently for validation, overlap checks, persistence and audit.

Trusted-device security corrections at e5dc263: fresh desktop browsers no longer receive long-lived sync credentials automatically; an owner must explicitly trust the current device in Settings. The installed-phone behaviour remains an accepted owner limitation. If destructive local workspace cleanup cannot reach the host to revoke a device, only its business-scoped installation UUID is retained and the next authenticated owner session retries revocation through an owner-only endpoint. Ordinary web logout remains non-destructive and preserves the trusted workspace by design.

Password recovery now revokes active and pending sync devices across every business where the resetting user has the owner role, clears token and pairing credentials, and records per-business audit events. Staff-only business devices are left untouched. The regression covers an owner whose currently selected business is a staff membership, protecting cross-business recovery from the normal active-workspace save guard.

Offline financial push validation uses integer-cent / hundredths parsing and rejects excess precision before posting. Regression coverage includes quote line quantity and price, event and asset costs, inventory movement, expenses, purchase-order quantity and price, purchase receipts, and payments.

## Earlier application-code CI — e5dc263 (historical baseline)
- Laravel run 44127089629: PASS — 523 passed / 1 skipped / 3,662 assertions.
- Offline browser acceptance in the same run: PASS — 45 desktop Chromium, 6 mobile-viewport, and 1 phone-local attachment journey.
- PostgreSQL migration, registration, backup/restore and private-file recovery: PASS.
- Windows V1 package and PowerShell fresh-install verification: PASS.
- Populated upgrade/rollback, Zazu quality, Psalm, PHPMD, Director Health and Push-on-main: PASS.
- SonarCloud: SKIPPED, not counted as a pass.

No tests were weakened. Evidence maturity remains 73/100; no score uplift. Physical Android, intended-installation recovery, live commercial reconciliation and POPIA remain operator gates.

## Independent audit reconciliation
All eight Claude findings remain in the sole readiness register. Invoice numbering is unchanged pending South African tax-adviser guidance. Session auto-trust scope, commercial tiers and feature expansions remain owner decisions. Pairing-code hardening is deferred; hosted proxy behavior is an accepted limitation for local Windows/LAN V1.

## Active V1 acceptance gates
1. Physical Android installed-app cold launch, offline restart and reconnect.
2. Installed-target recovery drill including private media.
3. Live populated-business quote → invoice/payment → costs → profit reconciliation.
4. POPIA operational/privacy sign-off.

## Release
V1: NOT READY. Historical state/register/memory, checklist and changelog are preserved in docs/archive/engineering/2026-11-17-state-register-memory-history.md.


## Exact-head scheduling acceptance closure — 2026-11-17

- Source: `71c8fc5c7aeba392b95e26aee694945b286f2c8e`.
- GitHub Actions run `44152431508`: Laravel tests, PostgreSQL migration/recovery and offline-browser acceptance all completed successfully.
- Offline-browser job `134284080089`: PASS; browser suite, mobile-viewport Work/customer acceptance and phone-local attachment journey all passed.
- PostgreSQL job `134284080622`: PASS; full migration set, account/workspace registration and backup/restore/private-file recovery passed.
- Laravel job `134284080768`: PASS; service-worker/session contracts, LAN asset-origin checks, Blade/manifest/config checks and PHPUnit/Pest passed.
- Documentation-only follow-up on `main` is `8bf76fe034f888138c0542dc308e3d4d752bd304`; no application code changed after the tested source baseline.
- Scheduling engineering/CI subgate is CLOSED. Physical Android and other operator gates remain OPEN. Release remains NOT READY.


## Director follow-up — job date integrity, startup migration safety and plain English (2026-11-17)

- Introduced `EventScheduleValidator` as the single relationship check for online job create/edit and offline job sync.
- The validator rejects an end date before the start date, setup after the start date, equipment collection before the job end date, and an earlier/equal end time for a one-day job. Multi-day jobs may end at an earlier clock time on their final day.
- Job create/edit screens now share the same clearly labelled schedule fields and explain what each date/time means. The form sets browser date/time limits as values change.
- Added online and offline regression cases for conflicting dates, shared-validator relationship coverage, and form labels/date limits.
- Simplified the DB-001 and account-error wording. Added `docs/ZAZU_PLAIN_LANGUAGE_STANDARD.md` as a standing product-wide writing rule.
- Windows startup now checks migration status before starting Laravel. When updates are pending, it must create and verify a full database + private-files backup before applying them, then confirm no migrations remain pending. Backup or migration failure blocks server startup.
- **Verification:** Source is committed on `main`. Exact-head CI and PowerShell execution for this follow-up have not yet been observed. No readiness uplift is claimed.

## Open owner-reported database incident — 2026-11-17

- Repeated user-facing `DB-001` incidents were reported. References: `2bc77a48-5c6c-4095-afe2-fa079f60e4ba` and `aa9080f4-9580-41c9-a643-439b31928ffa`.
- The newer reference was supplied with the Account access error screen; the exact action that triggered that occurrence was not stated.
- Source inspection found that the Work controller writes the five new scheduling columns, while the previous Windows start path did not check or apply pending migrations. A local SQLite database behind the current code is therefore the leading explanation, **not a confirmed root cause**.
- Backup-first migration handling has now been added to the shared Windows startup script. It has not yet run against the owner's installed database. The incident remains OPEN until exact runtime evidence confirms the cause and the affected workflow succeeds.


## DB-001 diagnostic contract correction — 2026-11-17

- Inspected `app/Support/ZazuIncidentRecorder.php`, `bootstrap/app.php`, `AttachRequestId.php`, `ZazuErrorCatalog.php` and `ErrorHandlingTest.php` at the current source baseline.
- Confirmed that the authoritative incident logger deliberately records bounded metadata only: request ID, classification, exception class, route/action, method/path, timestamp and source file/line. It intentionally omits exception messages, SQL, bindings and stack traces to prevent customer-data/credential leakage. Request middleware adds the same request ID to log context and the response header.
- Corrected `docs/ZAZU_ERROR_INCIDENT_FRAMEWORK.md` on `main` (documentation-only commit `f88af665ae09b6f87f3c8d73a52c3cc5e4854dbd`) because its prior wording incorrectly claimed the routine incident log contained technical exception messages, stack traces and safe query context.
- This clarifies the diagnostic contract but does not establish the cause of either owner-reported DB-001. The installed log entry matching `aa9080f4-9580-41c9-a643-439b31928ffa` is still required to identify exception class, source file/line and route. If those fields are insufficient, reproduce under controlled local diagnostics without adding raw SQL/bindings to production logs.
- No application code changed, no database touched, no readiness uplift. DB-001 remains OPEN.


## Backup publication integrity hardening — 2026-11-17

- Closed a source-level safety gap found while reviewing the backup-first Windows startup path: the launcher previously accepted a non-empty ZIP based on process exit code and file size, while full manifest/checksum validation happened only during restore.
- `ZazuBackupCommand` now reopens the completed temporary ZIP with consistency checking, validates the Zazu format/driver/manifest, checks exact payload membership and verifies every payload SHA-256 before publishing the archive. Failed verification prevents publication.
- Added a focused regression proving the verifier accepts a valid archive and rejects a tampered private-storage payload.
- Changes are on `main`; exact-head Laravel/Windows CI has not yet been observed. This is a source correction, not proof of the owner's installed backup or migration. DB-001 remains OPEN; no readiness uplift.


## Zazu quality workflow — scoped npm advisory exception — 2026-11-17

- Exact-head GitHub Actions run `44179163841` passed all three jobs on `76be1f234e7726352c2e15b511b37b9084d2ddfa`: application, Composer security audit and secret scan. The application job completed `npm ci`, the scoped npm audit policy and the service-worker authentication cache-race tests.
- The underlying npm audit still reports GHSA-fc5q-39xc-vjpf / CVE-2026-112366 against picomatch (locked versions through 4.0.7). No patched npm version is currently published. The npm CLI's suggested force downgrade is not adopted.
- Added `scripts/audit-npm.mjs`: it permits only this exact advisory and only packages whose complete npm audit dependency chain leads to that finding. Every other high/critical finding remains blocking. The exception is temporary and must be removed when the advisory is fixed or withdrawn.
- This is a CI policy exception, **not a dependency remediation** and not a claim that the advisory is resolved. Zazu's Vite/Laravel build configuration uses repository-defined static glob patterns; no user-supplied glob input was found in the inspected Vite configuration.
- DB-001 remains OPEN. No release-readiness uplift.


## New-job form regression closure — 2026-11-17

- Root cause of the single failing Laravel workflow: `resources/views/work/create.blade.php` omitted the required `name` field while `WorkController` continued to validate and persist it. Three offline-browser journeys consequently stalled on the missing field selector; the tests were exercising the real required workflow correctly.
- Restored the required **Work / event name** field and validation feedback on `main` in application commit `ba94b39e0460e4e57c560e98541808c762f50e25`. No tests were weakened and no branches were created.
- Exact-head Laravel run `44184183003`: PASS — Laravel tests, PostgreSQL migration/registration/backup/restore/private-file recovery, and offline-browser acceptance including mobile customer-to-Work and phone-local attachment journeys.
- Supporting exact-head checks: Zazu Quality `44184183085` PASS; Windows V1 Package `44184182900` PASS including PowerShell fresh-install verification; Director Health `44184182984` PASS; Push-on-main `44184180721` PASS. SonarCloud was skipped and is not counted as a pass.
- This closes the form regression and associated CI failure only. DB-001 remains OPEN; release remains NOT READY; evidence maturity stays 73/100 with no readiness uplift.


## DB-001 recurrence — reference 895e2c23-bc6b-4919-a891-1e8e5c1e27e4 (2026-11-18)

- Owner reports another DB-001 while opening/saving account/workspace information. Reference: `895e2c23-bc6b-4919-a891-1e8e5c1e27e4`. The exact triggering action and matching runtime record are not yet available; do not infer a cause from the reference alone.
- Existing incident recorder now emits bounded database diagnostics (connection, driver, SQLSTATE, numeric driver code and operation) without SQL, bindings, exception messages or credentials.
- Added `ops/windows/Get-ZazuIncident.ps1` and `docs/DB001_INCIDENT_CAPTURE.md` on `main`. The script finds the exact request ID in Laravel logs and outputs only an allowlist of diagnostic fields. This is a safe evidence-capture path, not a root-cause fix.
- DB-001 remains OPEN until the matching record is inspected and the failing workflow is repeated successfully on the intended installation. No database changes, reset or restore performed. V1 remains NOT READY; no evidence-score uplift.


## DB-001 read-only SQLite inspection added — 2026-11-18

- Added `ops/windows/Test-ZazuDatabaseSchema.ps1` and `docs/DB001_SQLITE_SCHEMA_CHECK.md` on `main`.
- The Windows diagnostic reads Laravel migration status, checks the event schedule migration ledger entry and all five expected `events` columns, checks the historical `business_user.primary_niche` column, and runs SQLite `PRAGMA integrity_check`.
- It emits only bounded schema/status metadata to a desktop JSON report. It does not run migrations, create backups, restore archives, or alter database records/schema.
- Added a Windows Ops workflow contract check for the diagnostic's required checks and non-mutating boundary.
- This enables evidence-based selection of a repair once the owner's actual installed SQLite database is inspected. The source repository cannot inspect the owner's local database or recover the omitted SQL exception from the request ID alone.
- DB-001 reference `895e2c23-bc6b-4919-a891-1e8e5c1e27e4` remains OPEN pending the read-only report and successful reproduction on the intended installation. No readiness uplift.


## Offline cache-priming input hardening — 2026-11-18

- Reviewed the service-worker page-priming message path under issue #35.
- Found that the route filter accepted any string beginning with /; URL parsing treats protocol-relative and backslash-normalized paths as cross-origin URLs. The priming fetch uses credentials: include, so this was an unnecessary credentialed cross-origin request surface.
- Updated public/sw.js to parse and normalize each route before fetching; reject cross-origin URLs, embedded URL credentials, /api and /api/*, non-string values, and oversized route strings.
- Added tests/js/service-worker-auth-cache.test.js regression coverage proving only a same-origin non-API route is fetched.
- Commits: service-worker correction 51e71414a293b4584d1b304dcbf0d0d2d1b15b97; regression test 6517703a4d2b2263547b603d1b8dc1a9c0c2e0e1.
- Exact-head CI is queued/running. No test pass is claimed until observed. This is a narrow defense-in-depth correction; it does not close physical Android acceptance or establish full offline workflow parity.


## Sentinel control-centre mandate — 2026-11-18

- Owner directive: prioritise Sentinel as the next major engineering focus. The target is a local, self-service operations and diagnostic control centre that explains failures and collects the evidence a developer would otherwise request manually. It must remain useful without internet access.
- Audited the actual implementation at `main` HEAD `68f8729e728fd43e238a5d4ad6376ab07dbb561f`: independent privacy-filtered incident ledger, fingerprint grouping/timeline, platform-admin UI, redacted reference-scoped DB-001 download, Windows incident capture and read-only SQLite schema tooling already exist.
- Important capability gap: `/admin/sentinel` is protected by `auth`, `auth.session` and `platform.admin`; it is not currently the business-owner control surface. The installation-wide ledger has no tested per-business incident ownership boundary. Do not remove platform-admin access controls or expose the global ledger to business owners as a shortcut.
- Important resilience gap: `config/session.php` defaults `SESSION_DRIVER` to `database`. The diagnostic interface must not rely invisibly on the business database/session path it is expected to diagnose. Its fallback access path needs an explicit security design and packaged-installation verification.
- Created `docs/SENTINEL_CONTROL_CENTRE.md` defining the product contract, five control surfaces, access/isolation rules, resilience requirements, delivery sequence and acceptance criteria. Updated `docs/ZAZU_ERROR_INCIDENT_FRAMEWORK.md` to remove its obsolete statement that no dashboard exists.
- This is a scope/architecture definition only. No runtime capability has been added and no CI or production acceptance is claimed. DB-001 remains OPEN; V1 remains NOT READY; no readiness uplift.


## Sentinel control-centre implementation — 2026-11-18

- Current source head at verification: `f608effe6b890f618e2cdd5b93bb7d26e58f502a` on `main`.
- Added owner-discoverable **System → Sentinel Rescue** with a one-year temporary signed URL. The route is outside the regular authenticated web/session middleware; it was tested while `session.driver=database` and the configured SQLite database path was intentionally unavailable. The request still rendered the rescue page.
- Sentinel Rescue now performs read-only database connectivity and migration-history checks; private storage permission checks; offline support-file presence/readability checks; and independent Sentinel ledger folder/segment/write-read-delete checks.
- A failed database connection or migration read is captured through the independent ledger. The page displays a safe error code/failure category/reference and a link to the allowlisted DB-001 report.
- Raw PDO failures now inherit only the validated configured connection alias and driver, allowing SQLite failures during connection/bootstrap to be classified without storing private exception messages.
- Sentinel admin timeline DB-001 references now include a direct download action. The owner rescue access is installation-level and signed-link protected; it does not expose the platform-admin timeline or the raw global ledger.
- Exact-head run `44318909570`: Laravel/Pest/PHPUnit **563 passed, 1 skipped, 3,915 assertions**; PostgreSQL migration smoke **PASS**. The offline-browser acceptance job was still running at the time of this note.
- Exact-head run `44318909679`: PHPMD **PASS**. Exact-head run `44318909551`: Zazu quality application, Composer security audit and secret scan **PASS**.
- Windows V1 package run `44318697559` on app-source-equivalent commit `e5bb8c602b7b66403dbda49ca023cb7000856170`: package build and Windows PowerShell fresh-install verification **PASS**. No physical owner installation acceptance is inferred from CI.
- Remaining Sentinel gaps: package export/manifest, full failure knowledge base, attachment health, offline queue/sync checks, session/cache diagnostics, backup integrity and recovery checks, richer fault injection, and on-device/offline packaged acceptance.
- DB-001 reference `895e2c23-bc6b-4919-a891-1e8e5c1e27e4` remains OPEN until the actual installation's failing workflow is identified, corrected and repeated successfully. V1 remains NOT READY; no readiness score uplift. No branches created.


## Sentinel Rescue owner-dashboard refinement — 2026-11-18

- Owner reported that the standalone Rescue screen consumed too much vertical space and offered no route back into the Zazu workspace.
- Kept the standalone read-only diagnostic surface so it remains useful when the normal application shell/database path is unhealthy; added a persistent compact workspace link to the owner dashboard.
- Reworked the large introduction and instructional panels into a compact status summary and responsive health-check grid. Detailed migration names remain collapsed until requested.
- Founder environment setting confirmed by owner as `Rosscorelabs@outlook.com`. `config/zazu.php` normalizes the configured `ZAZU_PLATFORM_ADMIN_EMAIL` to lowercase. Do not hard-code the founder identity into shared defaults; each installation must set it in its private `.env`.
- Source/test update is not visually accepted until rendered on the owner's installation. Existing Sentinel security boundaries remain unchanged.


## Sentinel Rescue interaction and visual refinement — 2026-11-18

- Owner requested stronger borders and meaningful interaction rather than a static report.
- Diagnostic cards and dashboard surfaces now use higher-contrast borders and clearer hover/focus states.
- Added client-side status filters (all / failed / attention / healthy), live text search, visible-result count, expand/collapse for diagnostic details, and a recheck control that reloads the read-only health inspection.
- Controls are progressively enhanced: the filter toolbar remains hidden until its script initialises; without JavaScript the complete diagnostic report remains visible.
- No new server write endpoints, external requests, business-record access, or telemetry were introduced. Recheck reruns the existing GET health inspection.
- Automated verification is pending for the current main head; visual acceptance remains with the owner on the installed application.


## Sentinel Rescue actionable diagnostics — 2026-11-18

- Failed and attention checks now show a contextual **Recommended next step** panel based on the stable diagnostic key.
- Guidance covers database connectivity, migration state, private storage, installed-app assets, the private diagnostics directory, incident-ledger integrity and the temporary write/read probe.
- Existing DB-001 safe incident report remains the only linked diagnostic action. No automatic repair, migration, file deletion or business-data mutation was added.
- Updated the page copy to distinguish read-only database checks from the existing temporary diagnostics-file write/read probe, which attempts cleanup after each check.
- Added a focused Blade-render regression test for safe incident-ledger guidance and updated Sentinel documentation.
- Verification is pending for the new commits. Visual acceptance remains pending on the owner's installation. All commits are directly on `main`; no branches created.


## Sentinel local recovery complexity cleanup — 2026-11-18

- Refactored `EnsureLocalSentinelRecovery` into focused loopback and same-origin POST guards; retained exact local address/host restrictions, content-type, origin scheme/host/port checks and Fetch Metadata behavior.
- Refactored `ZazuLocalSentinelRecovery` into code-format, locked-read, challenge-validation and invalidation helpers. The 256-bit code, SHA-256 hash storage, five-minute expiry, lock-protected single-use behavior and invalidation fallback are preserved.
- Added feature coverage for expired and malformed challenges alongside existing cross-origin, different-port and one-time-use tests.
- PHPMD had reported complexity violations in the two recovery methods. Refactor verified by PHPMD run `44372419579` on commit `a3dc514`; scan completed successfully with no suppression or threshold change.
- Removed a duplicate incident-report link from the owner guidance panel; the existing reference-scoped DB-001 report action remains in the diagnostic facts row.
- Changes committed directly to `main`; no branches created. Full Laravel and physical-device acceptance remain pending.


## Sentinel CI regression closure — 2026-11-18

- Corrected the founder layout so workspace footer destinations are rendered only when an active business workspace exists; founders with a business retain normal workspace navigation. This resolves the founder-console navigation assertion without exposing the console to business users.
- Local recovery feature requests now explicitly model the local host request context (loopback address, HTTP scheme, host and configured port) and submit the required form content type. The recovery middleware remains unchanged and continues to enforce loopback-only access plus exact same-origin scheme/host/port and Fetch Metadata checks.
- Laravel run 44374252678 on commit a9ab1f2: **573 passed, 1 skipped, 3,956 assertions**. PostgreSQL migration smoke and offline browser acceptance were still running when this note was prepared; the preceding run 44373939367 had PostgreSQL and offline browser acceptance **PASS**.
- PHPMD run 44374538225 and Zazu quality run 44374538448 on 3d809a6: **PASS**. Laravel run 44374538236 on 3d809a6: **573 passed, 1 skipped, 3,956 assertions**. PostgreSQL migration smoke and offline browser acceptance for that exact commit were still running at this note; earlier commit 9d5ae4f had both checks **PASS**.
- Windows V1 package build and fresh-install PowerShell verification passed on commit 9d5ae4f. Director Contract Lint passed on 21a2f4e.
- No security checks were relaxed, no branches were created, and no physical-device acceptance is inferred from CI. V1 remains **NOT READY** pending physical installation/offline acceptance and remaining release gates.

## Sentinel request and incident-capture hardening — 2026-11-18

- Tightened the local recovery POST content-type guard to compare the parsed media type exactly with `application/x-www-form-urlencoded`; values that merely share the prefix are rejected. Added a regression test for the malformed suffix case. Existing loopback, origin scheme/host/port, Fetch Metadata and one-time challenge controls are unchanged.
- Hardened `ZazuIncidentRecorder`: if both the primary Laravel log channel and fallback Laravel error log fail, the recorder emits only the failure class to PHP's local `error_log` and does not allow the secondary logging failure to escape the exception-reporting path.
- Added `IncidentRecorderResilienceTest` proving the independent redacted incident ledger still receives the record during a simulated primary/fallback log-channel outage and that private exception details are not written to the ledger.
- Exact-head commit `529bd6fe47e9d7624a47c3237b87e03f508ae872`: Laravel **575 passed, 1 skipped, 3,962 assertions**; PostgreSQL migration/recovery smoke **PASS**; PHPMD **PASS**; Zazu quality application, Composer security audit and secret scan **PASS**. Offline browser acceptance was still running at the time of this note.
- Earlier CI failures on intermediate commits were traced to a malformed namespace separator in the newly added test and a PHPMD public-method count after adding the test to an already-large test class. The test was moved into its own feature-test class, syntax corrected, and PHPMD plus Laravel now pass on 529bd6f. No production safeguard was weakened.
- All changes are committed directly to `main`; no branches created. Physical Windows/Android acceptance remains separate from CI, and V1 remains **NOT READY** pending the outstanding operator gates.

## Incident ledger filesystem hardening — 2026-11-18

- `ZazuIncidentLedger` now reapplies private filesystem modes when writing: diagnostics directory `0700`, lock file `0600`, JSONL ledger `0600` (best effort on Windows).
- This tightens pre-existing paths from older installations as well as newly created paths. No incident fields or retention policy changed.
- Windows security remains dependent on effective NTFS ACLs; PHP `chmod` is not treated as proof. Installed-package ACL verification remains an acceptance requirement.
- Change committed directly to `main` as `396960a`; CI for this exact head is pending. Prior application commit `529bd6f` had Laravel 575 passed / 1 skipped / 3,962 assertions, PostgreSQL smoke PASS, PHPMD PASS and quality scans PASS; offline-browser acceptance was still running at last check.
- No branches created. V1 remains NOT READY pending exact-head CI and physical-device/package acceptance.

- Added a POSIX feature test that verifies the diagnostics directory is `0700` and the incident lock/ledger files are `0600`; the test explicitly skips on Windows because NTFS ACLs, not POSIX mode bits, determine effective access there. Commit `d1923af`; exact-head CI pending.

- PHPMD caught a malformed fully-qualified exception name in the new permission regression test (`\\RuntimeException`); corrected to `\RuntimeException` in `61dbd19`. This was test syntax only, not a production-code finding. Fresh PHPMD/Laravel runs are required before considering the permission test verified.


## Windows private-storage ACL hardening — 2026-11-18

- Windows installer `ops/windows/Install-Zazu.ps1` now removes inherited parent permissions from `storage/app/private` and normalises existing descendants to allow Full Control only for the installing Windows account, Local System and local Administrators. It refuses reparse points in this private tree.
- The Windows package workflow now boots the installed app, writes a real redacted incident-ledger probe, and verifies the private root, diagnostics directory, lock and JSONL file contain only the allowed SIDs; child entries must inherit the restricted ACL.
- Windows Ops Scripts validation on `409056b`: **PASS**. Windows package build and fresh-install smoke on `409056b`: **PASS** (before the new explicit ACL assertions). The exact workflow with ACL assertions on `d39396f` is still running; a subsequent package workflow on `ceb6c8c` is also running.
- Application hardening verification on `61dbd19`: Laravel, PostgreSQL migration smoke, offline-browser acceptance, PHPMD and Zazu quality/security scans **PASS**. The POSIX permission regression test is included in that Laravel run.
- Documentation updated in `docs/V1_PACKAGE_INSTALLATION.md` and `docs/SENTINEL_CONTROL_CENTRE.md`. Current `main` documentation head is `707dcb0`; its quality and Director Contract Lint checks are running.
- Do not claim NTFS ACL acceptance until the exact `d39396f` or later Windows package workflow completes successfully. Physical-device acceptance remains separate. No branches created; V1 remains NOT READY.

- Exact Windows package workflow `44380197892` on commit `ceb6c8c`: package build **PASS**; Windows PowerShell fresh install **PASS**, including the new real-ledger NTFS ACL assertions for the private root, diagnostics directory, lock and JSONL file. The ACL test confirmed only the installer account, Local System and local Administrators are allowed, with diagnostic children inheriting the protected ACL.
- Latest main state/documentation commit `e278d9c`: Zazu quality, Director Contract Lint and Director Health Report **PASS**. No code changes after the Windows ACL-verified package commit; only documentation/state updates.


## Expanded Windows local-data ACL boundary — 2026-11-18

- Expanded installer ACL enforcement beyond private attachments to `.env`, `database`, `storage/framework`, `storage/logs` and `bootstrap/cache`. This protects the SQLite business database, application secrets, sessions/cache, logs and cached configuration from broad inherited access in shared Windows folders.
- The generic ACL routine normalises existing descendants, gives Full Control only to the installing account, Local System and local Administrators, and refuses reparse points in protected paths. New database and runtime files inherit the protected parent ACL.
- Windows Ops Scripts validation passed for the installer change on `eded442`; its Windows package build and fresh-install smoke also passed. The expanded workflow assertions now inspect all protected roots plus inherited database/diagnostic child ACLs; exact runs on `ffae4d3` and `cc90359` are still in progress.
- Documentation updated in V1 package and Sentinel runbooks. Do not mark expanded ACL assertions accepted until a Windows package workflow containing the latest assertion set completes successfully.

- Expanded Windows ACL test initially failed because the backup/restore acceptance drill had already created `database/database.sqlite` before installation. The installer correctly normalised that pre-existing file to its own protected explicit ACL; the test incorrectly required it to inherit. The assertion now verifies the database file has an explicit protected ACL with only the allowed SIDs, while newly created diagnostics must inherit. Workflow correction committed as `c628604`; fresh Windows acceptance is queued. This was a test expectation correction; no ACL restriction was loosened.

## Existing-server migration bypass guard — 2026-11-18

- Inspection of `ops/windows/Start-ZazuDev.ps1` found that its already-running-server fast path returned before calling the backup-first database preparation routine.
- If repository files were updated while the PHP server remained running, launching the wrapper again could report “already running” without detecting newly pending migrations. This is a concrete startup-control gap, not proof of the owner-reported DB-001 cause.
- The fast path now performs read-only `migrate:status` first. If migrations are pending or the migration ledger is missing, it refuses to claim the launch is current and tells the operator to stop then restart Zazu. It never migrates while the existing server is active.
- A Windows Ops workflow contract check now verifies this guard and its non-mutating boundary.
- The normal stopped-server path remains unchanged: verified full database/private-files backup, migration, final migration-status verification, then server start.
- **Evidence pending:** Windows Ops workflow must pass on exact `main` HEAD. The owner installation has not been modified or tested by this repository change. The one pending invoice-numbering migration is not evidence that the earlier account/workspace DB-001 has recurred; the historical `business_user.primary_niche` column is present in the supplied Sentinel schema report.


## Director continuation and DB-001 isolation — 2026-11-18

- Founder explicitly confirmed: do not freeze Jarvis. Engineering, Sentinel and hardening remain active; focused work batches are a delivery discipline, not a development freeze.
- Repository review confirms the pending `2026_11_18_000001_create_invoice_number_sequences_table` migration is used by invoice issuance only: online `FinanceController::storeInvoice` and offline invoice sync through `OfflineDomainMutationHandler`. It is not called by business/account settings or dashboard account loading. It is therefore not a supported explanation for the earlier account/workspace DB-001 incident.
- The historical DB-001 fingerprint was `GET /dashboard` querying missing `business_user.primary_niche`; the current supplied Sentinel schema report confirms that column is present. The separate invoice-sequence migration remains pending and still requires backup-first installation handling before invoice issuance is considered ready.
- Windows Ops workflow initially exposed a PowerShell parse failure in the new existing-server migration guard. The failure was diagnosed and corrected on main. Exact current main HEAD `a7798c41224876aa4a6e2d98963e8cd793680909`: Windows Ops Scripts, Windows V1 Package, Zazu quality and Push on main all completed SUCCESS. This confirms CI only, not the Founder's installed PC.
- DB-001 remains OPEN pending matching current incident evidence and successful repetition of the affected installed workflow. No database migration or owner-device mutation has been performed by this repository investigation.


## Sentinel overview accuracy correction — 2026-11-18

- Source review found that the control-centre overview derived fingerprint count, occurrence total and latest-incident timestamp from the display list capped at 100 fingerprints. This made the headline figures incomplete once the retained ledger contained more than 100 distinct failure patterns; the "latest incident" could also be stale because the list is ranked by recurrence.
- Added a separate full-retained-ledger overview aggregation. The visible recurring-pattern table remains intentionally capped at 100 rows and ranked by occurrence count; headline totals and latest timestamp now use all valid records in both retained ledger segments.
- Added a feature regression test with 101 fingerprints / 201 occurrences, where the newest low-frequency fingerprint falls outside the visible top 100.
- Changes committed directly to `main`: summary `0ad2178`, controller `58bcda2`, regression `fcd7345`.
- Updated the Director contract: Sentinel/diagnostics are active engineering targets, not frozen. Founder-only platform incident access remains unchanged.
- **Verification pending:** run the application/quality workflows on the resulting exact main head. No owner installation or incident data was changed.


- Follow-on inspection found the same top-100 truncation also affected search: low-frequency fingerprints outside the displayed top 100 were impossible to find. Search now filters the complete retained fingerprint set before applying its 100-row result cap. The regression test also searches for a deliberately low-frequency fingerprint and verifies unrelated top-ranked entries are absent.
- Follow-on commits: summary search `1ac1cff`, controller `7c3a043`, search regression `fa9308b`. CI for the final combined head remains pending.

## Sentinel diagnostic-link access hardening — 2026-11-18

- Source review found the DB-001 diagnostic endpoint was rate-limited and returned only allowlisted fields, but its reference UUID URL had no signature or expiry. A copied URL could therefore be reused indefinitely.
- The endpoint now requires Laravel's signed-URL middleware in addition to the existing rate limit. Sentinel Control Centre, Sentinel Rescue and the DB-001 error page generate temporary signed report links that expire after 15 minutes.
- The report remains reference-scoped and redacted; this does not grant access to the founder console or the full incident ledger.
- Added feature coverage for unsigned, expired and path-tampered links, and assertions that all three user-facing link surfaces include signature and expiry parameters.
- Verification is pending on the exact resulting `main` head. This is source-level hardening only; no installed device, database or incident records were changed.


## Sentinel founder licensing workspace — 2026-11-19

- Added a Licensing & Entitlements section to the existing founder-only Sentinel console on `main`. It searches businesses by name/ID, displays installed licence status and dates, and provides issue/renew/revoke actions.
- Added `SentinelLicenseController` routes under the existing `auth`, `auth.session` and `sentinel.admin` boundary; issue/revoke POST actions are rate-limited. Business-owner routes and customer activation remain unchanged.
- The UI invokes the existing Ed25519 issuer server-side. The signing key path is configured through `ZAZU_LICENSE_PRIVATE_KEY_FILE` and must point outside the project; the existing issuer still validates key format, project exclusion and match against `ZAZU_LICENSE_PUBLIC_KEY`. No key was generated or deployed.
- The signed token is returned once in the founder's authenticated session for manual delivery. Application logs record metadata only; token and key bytes are not logged. Revocation requires the owner to import the signed revocation token while offline; it is not a remote kill switch.
- Added focused feature tests for founder issuance, non-founder denial and missing-key fail-closed behavior. Exact-head CI has not yet been observed. Do not mark this workflow accepted until Laravel tests, quality/security checks and relevant Windows package checks pass.
- No feature matrix or paid-feature enforcement was invented. No branches created; all changes are directly on `main`.


## Sentinel founder licensing — UUID-backed offline customer registry — 2026-11-19

- Reworked the Sentinel licensing workspace to fit Zazu's documented first-customer model: independent local installations, no mandatory cloud licensing server. The founder maintains a local registry of customer businesses using the stable licensing UUID supplied by each owner.
- Added `businesses.licensing_uuid`: migration backfills existing businesses and new Business records generate a UUID. Business Settings now displays this as the Business Licensing Reference; the numeric Business ID is explicitly local/support-only.
- Signed licence payloads are now version 2 and bind to `business_uuid`. The local verifier compares that UUID with the UUID in the customer's Business record. Version-1 tokens bound only to local numeric IDs are rejected because separate customer installations can reuse those IDs.
- Added `platform_license_customers` founder registry for business UUID, business name, optional contact email and latest licence metadata. It does not store signed token contents or private-key material.
- Sentinel now supports founder-only customer registration/search, signed issue/renewal and revocation. All actions remain inside `auth`, `auth.session`, `sentinel.admin`; issue/revoke/register actions are rate-limited. Customers still import tokens through their existing owner-only Business Settings screen.
- Issuance uses the external `ZAZU_LICENSE_PRIVATE_KEY_FILE` path and matching `ZAZU_LICENSE_PUBLIC_KEY`. The private key never enters the browser, customer installation, database or logs. No production key was generated or used.
- Split issuer payload validation and token signing into focused services to keep the command maintainable. Added feature coverage for registry registration, UUID-bound issuance, revocation, founder authorization and missing-key fail-closed behaviour; updated local import and CLI tests to version 2.
- Exact-head Laravel, PHPMD, Psalm, Quality, populated migration/rollback and Windows package evidence is still pending. Do not claim the founder workflow is accepted until those exact-head runs pass and the Windows local installation has the new migration. No production licence has been issued.
- Business-level identity is implemented; per-installation seat limits/reactivation, production key custody/rotation, approved tier matrix and module-level feature enforcement remain open. Do not infer any device-count restrictions.
- All work committed directly to `main`; no branches created.


## Navigation discoverability audit — 2026-11-19

- Founder review identified the owner-only Data Export & Recovery page as effectively hidden: it was reachable from Owner Admin and Reports, but absent from the persistent sidebar and command palette. Its route was already protected by the owner middleware; this was a discoverability defect, not an authorization defect.
- Added Data Export & Recovery to the owner System navigation, command palette and footer Control navigation. Export routes now activate the System navigation group and show a contextual section tab.
- The same audit found Compliance and Activity Audit were available through settings sub-navigation, command palette and footer, but not the persistent owner System navigation. Added direct owner-only System links for both.
- Added a focused regression test for owner discovery and active navigation on the export page. Existing route middleware and export authorization were not changed.
- Customer spreadsheet import is already discoverable from the Customers page action; event costs, preparation, requirements, travel and attachments are contextual job-level tools, not standalone workspace destinations. No duplicate top-level links added for those contextual workflows.
- Changes committed directly to main; automated test execution and rendered desktop/mobile verification remain pending. Do not claim UI acceptance until verified.

## LAN HTTPS launcher output visibility — 2026-11-19

- The double-click launcher now pauses after a successful or failed startup so the owner can read/copy the generated HTTPS URL and Caddy root-certificate path. Previously the successful command window could close immediately and hide both values.
- This is a launcher usability adjustment only. It does not change the PHP/Caddy startup sequence, certificates, firewall, database, browser storage or phone data.
- Committed directly to `main`; no branches created. Physical startup and phone certificate trust remain unverified.

## Playwright contamination guard and navigation acceptance — 2026-11-20

- Registration E2E now skips unless both `ZAZU_E2E_ALLOW_REGISTRATION=1` and `ZAZU_E2E_ISOLATED_DATABASE=1` are explicitly supplied. These flags are acknowledgements only; they do not provision or verify an isolated database.
- Added `docs/PLAYWRIGHT_DATABASE_SAFETY.md` documenting the real risk created by local `reuseExistingServer` behavior and the disposable-database requirement.
- Founder navigation feature coverage now asserts that Founder Control Centre, Owner Admin and Data export & recovery are inside the System navigation panel.
- Service-worker cache version is v40; service-worker cache tests were updated to expect v40 (the prior v39 assertions would otherwise fail after the version bump).
- No local PHP/Node runtime or live browser was available during this repository-only work. These source changes have not been runtime-tested; GitHub CI status must be checked after push.
- Existing synthetic business cleanup remains a separate local database operation. Do not broaden its exact `Browser Catering + 10 hex characters` matcher.
