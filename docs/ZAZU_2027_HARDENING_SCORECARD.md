## Director execution checkpoint — offline mutation operation integrity — 2026-11-21

**Code/test head:** [e95dd5c](https://github.com/Leano-Jordan/ZazuEMP/commit/e95dd5cd8966c7457de70e9a4bc882709db7d30b).

- Confirmed and corrected inconsistent operation validation in the customer, job and service offline mutation handlers.
- Unsupported delete commands are now rejected before any domain write; expanded regression exercises all 17 registered offline entity handlers and verifies no domain records are written.
- Expanded Laravel regression: **676 passed / 1 skipped / 4,647 assertions** [workflow 44948063650](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44948063650).
- PHPMD: **PASS** [44948063618](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44948063618); Zazu Quality: **PASS** [44948063564](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44948063564).
- PostgreSQL migration/registration/backup/restore/private-file recovery smoke: **PASS** in [Laravel workflow 44947482747](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44947482747).
- Runtime-identical offline browser acceptance: **63 desktop Chromium + 7 mobile viewport + 1 mobile host-outage + 1 phone-local attachment/reconnect passed**, no failures or flaky retries in [workflow 44947482747](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44947482747). A repeat browser run on test-only head `0e619e9` was still running; no result is claimed for that repeat.

**Score disposition:** evidence maturity remains **77/100**. This focused integrity fix is not a weighted-score uplift and does not close the broader offline sync matrix. V1 remains **NOT READY**.

---

## Director convergence checkpoint — exact repository reconciliation — 2026-11-21

**Documentation head:** [3e91346](https://github.com/Leano-Jordan/ZazuEMP/commit/3e91346ec239aac85543ecdd096c5e69571a5c2a) on `main`. This commit changes the offline architecture checkpoint documentation only; it does not change application code.

**Latest verified application/test baseline:** [04db872](https://github.com/Leano-Jordan/ZazuEMP/commit/04db8722a66ff8c97d62fef855f7ba07073934c7). Recorded exact-head evidence: Laravel **675 passed / 1 skipped / 4,581 assertions**; PostgreSQL migration/recovery smoke **PASS**; offline browser **63 desktop + 7 mobile viewport + 1 LAN host-outage + 1 phone-local attachment/reconnect passed**; PHPMD, Psalm, Zazu Quality, Director Health, populated upgrade/rollback, Windows V1 package/fresh install and Push-on-main **PASS**. SonarCloud was skipped and is not counted as a pass.

**Current documentation-head workflows:** Zazu Quality [44941545302](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44941545302), Director Contract Lint [44941545341](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44941545341), and Push on main [44941544684](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44941544684) **PASS**. These documentation/control checks are not a fresh application test run.

**Security / isolation review:** CASE-ZAZU-0040 is closed at source and automated-test level. The repository also contains targeted offline expense-reference isolation regressions covering foreign event, supplier and purchase-order identities, wrong morph targets, and corrupted cross-business identity mappings. These are source/test evidence, not a black-box penetration test of a running installation.

**Score disposition:** evidence maturity remains **77/100**. No uplift is awarded for documentation-only commits or viewport emulation. The weighted mandate score has not yet been independently re-baselined against all nine categories; do not represent 77/100 as a verified weighted score under the new mandate.

**Release gates still OPEN:** physical Android installed-app cold launch / host outage / force-close / restart / reconnect; actual owner-PC off-disk backup and full installation restore with private media; populated live-business quote-to-payment-to-expense-to-profit reconciliation; POPIA operational sign-off; ZAZU name clearance. V1 remains **NOT READY**.

**Next engineering sequence:** continue targeted adversarial offline authorization and account/session transition review; then complete source-backed commercial reconciliation and recovery checks. Physical device and owner-operated recovery evidence must remain separate from automated CI.

---

## Director closure checkpoint — stale sync-device authorization — 2026-11-21

**Verified application code head:** [7d4b097](https://github.com/Leano-Jordan/ZazuEMP/commit/7d4b097a13d02f3d5369bf8e75e2dc85c361beb7) on `main`.

- Closed CASE-ZAZU-0036 / REG-027: conflict listing/resolution, attachment upload/list/download and self-revocation now revalidate active device status and persisted token hash under a business-first/device-row lock inside the operation transaction.
- A stale bearer credential cannot revoke a replacement credential after device re-pairing.
- Five stale-snapshot regression tests added.
- Laravel [44931555043](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44931555043): **670 passed / 1 skipped / 4,552 assertions**.
- Offline browser in the same run: **62 desktop Chromium + 7 mobile viewport + 1 phone-local Work/attachment/reconnect passed**.
- PostgreSQL migration/recovery, PHPMD [44931555002](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44931555002), Psalm [44931555131](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44931555131), Zazu Quality [44931555024](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44931555024), populated upgrade/rollback [44931555044](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44931555044), Director Health [44931554979](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44931554979), and Windows V1 package [44931554910](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44931554910): **PASS**. SonarCloud skipped.
- **Evidence maturity remains 77/100; no score uplift claimed.** Automated browser coverage does not replace physical Android cold-launch/restart/reconnect, actual owner-PC off-disk restore, live populated-business financial reconciliation, POPIA operational sign-off or ZAZU name clearance. V1 remains **NOT READY**.

**Next engineering target:** continue the offline account/session transition adversarial review and complete the supported offline workflow matrix. Keep physical-device and owner-operated recovery evidence separate from automated CI.

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

## Director closure checkpoint — 2026-11-21 (CASE-ZAZU-0033 stale-write protection)

**Verified source/test head:** `51a75eb1d06017211bd7808093d1863c930b6d71` on `main`. Laravel **634 passed / 1 skipped / 4,399 assertions**; PostgreSQL migration/recovery **PASS**; offline browser **59 desktop + 7 mobile + 1 phone-local Work/attachment/reconnect passed**; PHPMD, Zazu Quality, Composer audit, secret scan and application checks **PASS**. [Exact-head workflow](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44881431689).

CASE-ZAZU-0033 / ENG-DATA-003 is **CLOSED**. The stale-write contract now has paired-device rejection, same-device causal chains, conflict/rebase payload preservation, parent-revision propagation and business-isolation evidence. The final online writer review found and corrected the remaining primary-contact creation mass-update bypass; other identified bulk operations are paired with tracked parent updates or are restore/protocol writes outside existing domain identities.

**The full offline sync workflow matrix remains OPEN and separate.** Physical Android cold launch/restart/reconnect, intended-installation recovery, live-business commercial reconciliation and POPIA operational sign-off remain OPEN. Evidence maturity: **77/100 (+2)**. The uplift is for exact-head closure of CASE-ZAZU-0033: paired-device stale-write rejection, same-device causal/rebase coverage, online writer audit correction and passing Laravel/PostgreSQL/browser acceptance. This is engineering evidence maturity, not release readiness. V1 remains **NOT READY**.

---

## Director deep-audit checkpoint — 2026-11-21 (customer-contact writer correction)

- **Source/test head:** `51a75eb1d06017211bd7808093d1863c930b6d71` on `main`; Laravel **634 passed / 1 skipped / 4,399 assertions**; PostgreSQL migration/recovery, PHPMD and desktop/mobile browser acceptance **PASS** (59 desktop, 7 mobile, 1 phone-local attachment/reconnect). A second paired-device stale-write regression now proves revision-1 writes cannot overwrite revision-2 server state.
- Corrected a confirmed online revision bypass in primary-contact creation: existing primary contacts are now updated individually under lock instead of through a relationship mass update that bypassed Eloquent events.
- Added a regression proving the parent Customer revision advances while preserving exactly one primary contact.
- **Readiness remains 75/100; V1 remains NOT READY.** CASE-ZAZU-0033 / ENG-DATA-003 is CLOSED on exact-head evidence; the full supported offline sync matrix remains a separate OPEN gate. Physical Android, intended-installation recovery, live-business commercial reconciliation and POPIA operational sign-off remain separate owner gates.

---
## Director execution checkpoint — 2026-11-21 (receiver reconciliation + bootstrap auth)

**Application/test source head:** `559e66b87de8617f967b722b55b0fbfd9b116495` on `main`.

- Laravel [44880113367](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44880113367): **632 passed / 1 skipped / 4,388 assertions**.
- PostgreSQL migration/registration/recovery smoke: **PASS** in the same run.
- PHPMD [44880113352], Zazu Quality [44880113347], Director Health [44880113380], Push-on-main [44880113289], Composer audit, secret scan, application and Actions/Python/JavaScript analysis: **PASS**.
- SonarCloud [44880113403]: **SKIPPED**, not counted.
- Offline-browser acceptance in Laravel run [44880113367](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44880113367): **59 desktop Chromium passed**, **7 mobile-viewport passed**, and **1 phone-local Work/attachment/reconnect journey passed**.

### Verified corrections
- Receiver pull now sends the exact delivery prefix to bootstrap. Server resolves primary and related aggregates only for the authenticated business/device, includes those aggregates beyond the regular 500-record window and prioritizes recently revised records for the normal bounded refresh.
- Explicit pairing selections remain pinned; delivery-driven Customer/Work/Quote/Preparation records cannot silently expand an explicitly restricted workspace.
- Bootstrap now locks business before device, rechecks active status and the current bearer-token hash against the authenticated model, updates last-seen and builds the data snapshot under the same lock boundary. A revoked/replaced device cannot receive a stale bootstrap response.
- Service-worker cache is `zazu-static-v60` after the acknowledged receipt-state correction.

**Failure records:** CASE-ZAZU-0034 / ENG-DATA-004 (bounded receiver snapshot omission) and CASE-ZAZU-0035 / ENG-AUTH-001 (stale device authorization during bootstrap). Both defects are **CLOSED** on the exact source head; server-side and browser regressions passed.

**Readiness: 75/100 (+2)**. The uplift reflects exact-head verification of receiver snapshot completeness/device isolation and bootstrap revocation/credential revalidation, including backend and browser acceptance. It does not close V1 release readiness. V1 remains **NOT READY**. Physical Android cold launch/restart/reconnect, intended-installation recovery, live-business commercial reconciliation and POPIA operational controls remain open. ZAZU name clearance remains deferred. No production deployment or live business data changed.

---

## Director execution checkpoint — 2026-11-21 (receiver sync completeness)

**Application/test source head:** `472e301065a5301c80f0ffbb97f82b61c24575ba` on `main`.  
**Verification:** exact-head Laravel, PostgreSQL migration/recovery, offline-browser, PHPMD and quality workflows are pending at the time of this checkpoint. No pass is claimed yet.

### Confirmed engineering correction
The receiver pull/bootstrap/acknowledgement sequence could acknowledge a contiguous delivery prefix after applying an ordinary bounded bootstrap that omitted an affected aggregate outside a capped collection. Fixed on `main` by sending exact receiver delivery sequences with bootstrap, resolving the batch's primary and related records within business/device scope, and including those records beyond the normal 500-record working set before acknowledgement. Normal capped refreshes now prioritize recently revised aggregates using sync-identity update time, with a record timestamp fallback, while preserving display ordering. Static cache advanced to `zazu-static-v58`.

Regression coverage now targets:
- Recent online updates promoting older supplier records into the bounded refresh.
- Explicit delivery inclusion outside the bound and denial of the same sequence to another device.
- Second-device quote → acceptance → invoice → payment snapshot reconciliation.
- Purchase receipt → purchase-order and all affected inventory snapshot reconciliation.
- Browser ordering of pull → exact-sequence bootstrap → acknowledgement.

**Failure record:** CASE-ZAZU-0034 / ENG-DATA-004. Implementation and tests are committed; exact-head evidence is pending. CASE-ZAZU-0033 remains a separate open gate for exhaustive writer coverage and stale-write matrix completion.

**Readiness:** remains **73/100**, unchanged pending exact-head verification. V1 remains **NOT READY**. Physical Android cold launch/restart/reconnect, intended-installation recovery, live-business commercial reconciliation and POPIA operational controls remain open. ZAZU name clearance remains deferred. No production deployment or live data changes.

---

## Director execution checkpoint — 2026-11-21 (online revision coverage)

**Verified application/test HEAD:** `ecc6ba1e973839af2b6fe0caa25fbd927f961ab3` on `main`.

- Laravel: **630 passed / 1 skipped / 4,353 assertions**.
- Offline browser: **58 desktop Chromium passed**, **7 mobile-viewport passed**, and **1 phone-local Work/attachment/reconnect journey passed**.
- PostgreSQL migration, registration and database/private-file recovery smoke: **PASS**.
- PHPMD, Zazu Quality, Director Health, Push-on-main, JavaScript/TypeScript, Python and Actions analysis: **PASS**. SonarCloud: **SKIPPED**, not counted.
- Added exact-head online revision regressions for Work requirements, invoice items/payments, purchase-order items/receipts, asset allocations, preparation records, expenses and event costs. These passed with the full Laravel suite.

**Evidence maturity remains 73/100; no numerical uplift is claimed.** This closes a verification gap in online aggregate revision tracking, but the complete offline sync matrix and real installed-device proof remain open. Next engineering target: end-to-end cross-device replay/reconciliation across the supported mutation matrix. Physical Android cold launch/restart/reconnect, intended-installation recovery, live-business commercial reconciliation and POPIA operational sign-off remain owner acceptance gates. ZAZU name clearance remains deferred by owner direction. No production deployment or live business data changed.

---

## Director reconciliation — 2026-11-17 (V1 correction batch)

**Canonical repository:** `Leano-Jordan/ZazuEMP` · **branch:** `main` · **verified source/control head:** `7cb17334d53312265521769223bcac4f42eda772`

### Verified corrections
- **D2 — offline purchase-order money integrity:** purchase-order sync now uses the existing integer-cent `Money` utility for quantities, unit prices, line totals and order totals. Values that lose precision (for example quantity `0.004` or unit price `1.005`) are rejected instead of silently rounded. Dedicated regression test added.
- **D4 — offline bootstrap authorization:** `/offline/bootstrap` now requires the explicit `offline.bootstrap` business permission. The existing staff and manager roles are granted it; a regression verifies denied and permitted access.
- **D5 — work deletion roles:** `work.delete` is now manager-level rather than staff-level. Owner authority is unchanged; the role boundary is regression-tested.
- **D7 — generated Vite manifest:** removed the tracked `public/build/manifest.json`. Build output remains ignored/generated; the Windows launcher already rebuilds when the manifest or referenced assets are missing or stale.
- **PostgreSQL CI correction:** the PostgreSQL registration/recovery job now installs Node dependencies and builds Vite assets before rendering the dashboard. This prevents a missing generated manifest from masquerading as a database failure.
- Offline V1 engineering was explicitly resumed. `AGENTS.md`, `memory.md`, `.ai/engineering/V1_SCOPE_FREEZE.md` and the release checklist now reflect the current owner direction. Work remains on `main`; no branches were created.

### Exact-head evidence
- Laravel [run 43973662513](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43973662513): **PASS — 503 passed / 1 skipped / 3,563 assertions**.
- Offline browser acceptance in the same run: **37 desktop Chromium passed**, **6 mobile-viewport passed**, and **1 phone-local attachment journey passed**.
- PostgreSQL migrations, real account/workspace registration, and database/private-file backup/restore: **PASS** in the same run.
- Zazu quality [run 43973662490](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43973662490), PHPMD [run 43973662406](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43973662406), Director Health [run 43973662488](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43973662488), and Push on main [run 43973661651](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43973661651): **PASS**. SonarCloud was **SKIPPED**, not counted.
- Windows fresh-install package build and PowerShell first-install smoke [run 43973129806](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43973129806): **PASS** on application source `e09b8e7e7005f6be1d8af9eb3712f66b97fbdc6f`; no application source changed between that package build and the current verified head.

### Release decision
**V1 remains NOT READY. Evidence maturity remains 73/100; no score uplift is claimed.** Remaining owner/operational gates are physical Android installed-app cold launch/restart/reconnect, the installed-target recovery drill including private media, live populated-business quote/payment/cost/profit reconciliation, and POPIA operational/privacy sign-off. No production deployment, live business database or customer data changed.

---

## Current Director checkpoint — 2026-11-16 (Windows V1 package)

**Canonical repository:** `Leano-Jordan/ZazuEMP`, branch `main`  
**Verified Windows package source:** `9a1fc33004c52d7ee4b5a16d94dc7d94b578e5f0`  
**Package run:** [43966804827](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43966804827) — package assembly and Windows PowerShell first-install smoke **PASS**.

### Verified package and installation evidence

- Production-only Composer dependencies installed; Vite frontend built; manifest and referenced assets verified; clean Windows ZIP assembled with SHA-256.
- Windows PowerShell 5.1 fresh-install smoke with PHP 8.4/SQLite created the private `.env`, generated APP_KEY, ran migrations, booted Laravel routes, and fetched `/login` over loopback HTTP. The returned session cookie was checked to be browser-usable for the local HTTP profile.
- Artifact `ZazuEMP-v1-candidate-9a1fc33.zip`; standalone SHA-256: `33a60db7fb40a6031634c3d97fc1d3600b46e6994c2276f48fc08ca448c980a7`.
- Package includes production PHP dependencies, compiled frontend assets, Windows launchers/installer and customer/operator runbooks. It excludes `.env`, local DB, private business files, tests, Node modules and internal engineering controls.
- The default package uses `SESSION_SECURE_COOKIE=false` for the local `http://127.0.0.1:8000` address. HTTPS-only installations must set it to `true`; the HTTP server must never be public.
- The package is a **fresh local install candidate**, not an in-place updater or hosted SaaS deployment.
- Zazu Quality [43966805237](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43966805237), Director Contract Lint [43966804706](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43966804706), Director Health [43966804574](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43966804574), and Push on main [43966803035](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43966803035): **PASS**.

### Score and release disposition

**Evidence maturity: 73/100 (up from 71/100).** The +2 reflects verified Windows packaging and first-install evidence only; it does not certify actual owner-device use or complete commercial release readiness.

**Windows package/first-install gate: CLOSED for the defined fresh-install candidate.** The actual owner PC/browser and phone remain separate acceptance targets.

**Commercial V1 remains NOT READY** pending:
1. Owner-PC/browser rendered acceptance and physical phone acceptance for the intended phone/PWA workflows.
2. Installed-target operator backup/restore drill, including private media.
3. Live populated-business reconciliation through quote → invoice/payment → incurred costs → event contribution.
4. POPIA operational/privacy sign-off.

Brand/name clearance remains deferred by owner direction. Do not reopen already-passing seeded commercial tests without reproducible new evidence. No production deployment or live business-data change occurred.

---
# Zazu EMP — Hardening & Commercial Readiness Scorecard

## Prior Director checkpoint — 2026-11-16 (dashboard/theme stage)

**Canonical repository:** `Leano-Jordan/ZazuEMP`, branch `main`  
**Current documentation/control head:** `19bc949891d02afd1226cf0843df259b4b35e682`  
**Application/test head:** `8cdadcd1563bd30c88b15153ca91181a2d5abb23`; generated-asset manifest descendant `dfc01749af5718011332a9e86d303b9622fe7ff4`.

### Verified engineering progress

- Dashboard light/dark palette is now sourced from shared Zazu theme tokens. The dark-mode specificity defect is fixed, and Playwright asserts the computed shared-token relationship while retaining the layered hero/business-image treatment.
- Exact source/test run [43952349496](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43952349496): Laravel **500 passed / 1 skipped / 3,549 assertions**; offline browser **37 desktop passed**; mobile-viewport acceptance **6 passed**; phone-local attachment journey **1 passed**; PostgreSQL migration and database/private-file recovery smoke **PASS**.
- Manifest descendant run [43952861421](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43952861421): Laravel, offline-browser and PostgreSQL jobs **PASS**. Zazu Quality [43952861428](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43952861428), Director Health [43952861384](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43952861384), and Push on main [43952861462](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43952861462) **PASS**. SonarCloud was skipped.
- Current documentation-only reconciliation commit [19bc949](https://github.com/Leano-Jordan/ZazuEMP/commit/19bc949891d02afd1226cf0843df259b4b35e682): Zazu Quality **PASS** (application, Composer security audit, secret scan); Director Contract Lint **PASS**. Push-on-main was still running at the time of this checkpoint.
- Offline session/attachment scope is documented against current automated coverage: unreachable logout, expired web-session recovery, owner/manager account transition, business-scoped queue preservation, stale response/logout races, cross-business pairing rejection, owner revocation and active Work attachment persistence/reconnect/idempotency/private-file checks. These remain automated evidence, not physical-device proof.

### Score and release disposition

**Evidence maturity at this prior checkpoint: 71/100.** Package/installer evidence had not yet been recorded.

The remaining release gates are deliberately bounded:
1. Physical Android installed-app acceptance: secure-origin launch, disconnected cold launch/restart/force-close, local file picker/storage and reconnect on the actual phone.
2. Operator recovery drill on the intended installation, including database and private media.
3. Live populated business-record reconciliation through quote → invoice/payment → costs/profit.
4. POPIA operational/privacy sign-off.

Brand/name clearance remains deferred by owner direction. Do not reopen already-passing seeded commercial tests without a reproducible defect. Do not claim PC visual acceptance or physical Android acceptance from CI viewport tests.

**Execution control:** stay on `main`; no branches, no unrelated feature expansion, no production deployment or customer-data changes. Next engineering focus remains the highest-risk reproducible defect in V1 security/offline continuity, not future-scale work.

---

## Purpose

Current working gate for development toward a commercially usable release.

This scorecard separates implementation from evidence maturity and separates **V1 release requirements** from **future scale programmes**.

## Evidence maturity

0 — not started  
1 — designed  
2 — implemented  
3 — automated evidence  
4 — runtime/CI verified  
5 — repeatedly proven through realistic/production/recovery evidence

## Current Director assessment — 2026-11-03

The 19 October assessment below is retained as historical evidence. The 3 November source audit is recorded in `docs/ZAZU_SECURITY_AUDIT_2026-11-03.md`.

- Parser resource bounds, API security headers/request IDs and the customer-import reader/controller contract have been hardened in source; their new regression tests await exact-head CI.
- The offline workspace is intentionally business-scoped under owner-approved trusted-device pairing (DEC-019). The remaining control is trusted-device lifecycle: owner-visible device inventory/revocation, physical device lock and lost-device procedure; per-staff offline isolation is not part of the current V1 architecture.
- Main is protected but has no required status-check contexts. The owner must require current quality/security workflows.
- No live black-box verification was performed in this repository-only review because a Zazu runtime URL and test credentials were not supplied.
- No numerical readiness uplift is awarded until exact-head CI and the open security gate are addressed.

## Previous Director assessment — 2026-10-19 (historical)

**Assessment boundary:** current main has advanced beyond the last broad green application run. The last recorded broad green baseline is `7940033b6fd4f691692eec5e9d7f6ad587486f61` (2026-10-15); that result does not certify later commits. Exact current-head CI for the new service-worker behavioral suite is **UNVERIFIED** until its run is observed.

| V1 domain | Current evidence position | Remaining release work |
|---|---|---|
| Correctness | Behavioral regression suite added for three logout/identity/cache races; fresh CI execution unobserved | Verify the exact current-main run and repair any genuine regression |
| Architecture | Identity cache mutation/clear operations are serialized; duplicate CSS declarations and one unreferenced font dependency were removed | Validate through CI/browser execution; broader PHP/Blade/JS reachability scan remains open |
| Data integrity | Existing transaction, idempotency and ownership controls remain in source; newest-head execution unverified | Current-head tests and populated commercial traversal |
| Security | Generation guards plus behavioral tests now target stale identity reads, late HTML writes and identity writes racing logout | Latest-run evidence and real multi-account installed-browser challenge |
| Workflow integrity | Connected commercial flow exists; previous populated proof is for an earlier SHA | Re-run current-head workflow and reconcile job → quote → invoice/payment → purchasing/costs |
| UX / accessibility | Earlier responsive/browser acceptance is historical; this audit introduces no UI redesign | Re-run browser checks after the current code changes and preserve owner-confirmed device acceptance boundary |
| Reliability / recovery | Automated restore/rollback coverage existed at the earlier verified baseline | Real deployment-environment backup/restore (including private media) and rollback drills remain open |
| Operability | Stale health snapshots are archived; current health workflow publishes per-run artifacts and summary only | Open the exact current-main workflow run; do not rely on a persistent `latest` file |
| Upgrade / deployment | Previous populated upgrade/rollback run was green at the recorded baseline | Confirm current-head results and perform real release rollback exercise |
| Documentation / compliance | V1 ledger reconciled; stale health claims removed; duplicate control ID and dependency inventory corrected | Legal/privacy operator controls, brand/trade-mark clearance and final dependency distribution review |
| Release evidence | Current exact-head results are not yet reconciled | Record SHA + run URLs + conclusions before promoting evidence maturity |

### Score disposition

The previously recorded **80/100 commercial-readiness figure is historical (2026-10-04)**, not a current live score. This audit does **not** award a numerical uplift: source changes and new tests are implementation evidence, not a passing runtime result. Re-score only after current-head CI is observed and the open V1 operational gates are reconciled.

## V1 gate domains

| Domain | What must be true |
|---|---|
| Correctness | Critical workflows produce the intended result |
| Architecture | Core structure is understandable, bounded and maintainable |
| Data integrity | Transactions, ownership, parent/child rules, precision and concurrency are protected |
| Security | Authentication, authorization, isolation and sensitive-media boundaries are enforced |
| Workflow integrity | Jobs can move through valid operational/commercial states |
| UX/accessibility | Critical workflows are understandable and usable across required contexts |
| Reliability/recovery | Failure paths are controlled and restore has been demonstrated |
| Operability | The owner can diagnose and respond to incidents |
| Upgrade/deployment safety | Clean installs and representative existing databases can be upgraded safely |
| Documentation/compliance | Required runbooks, dependency/IP/privacy evidence exist |
| Release evidence | Current CI/browser/runtime evidence supports the release claims |

## Release blocker definition

A blocker is release-critical when it can cause:
- data loss/corruption;
- unauthorized access;
- broken critical workflow;
- unrecoverable deployment/upgrade;
- inability to restore;
- materially incorrect financial/commercial state;
- inability to operate or diagnose the product.

## Future-scale gates

These are **not automatic V1 blockers** unless product scope explicitly requires them:
- SaaS billing/entitlements
- large-data performance proof
- production soak/long-term reliability evidence
- multi-location expansion
- advanced messaging/client portal ecosystem
- advanced AI automation

The architecture should preserve future options without allowing future-scale work to displace current release-risk closure.

## Operating rule

Readiness increases when meaningful engineering risk is removed **and evidence matures**.

It does not increase merely because:
- more files were created;
- more tests were added;
- another dashboard/report was written;
- an incomplete workflow was given a new UI.

Source of truth for live status: `.ai/engineering/READINESS_REGISTER.md`.


## Director checkpoint — 2026-11-21 (finance reporting integrity)

**Verified `main` HEAD:** `c43ab8db309a2ccedd48d2298077e3a602b72a5d`.

A finance dashboard defect was corrected: draft invoices were included in the invoiced aggregate. The aggregate now counts issued and paid invoices only. A focused regression verifies that a 999.00 draft is excluded from 100.00 issued plus 200.00 paid, yielding 300.00.

Exact-head Laravel, offline browser acceptance, PostgreSQL migration/recovery, Zazu Quality, PHPMD, Director Health and Push on main passed. SonarCloud was skipped. **No score uplift is claimed** from this isolated correction. V1 remains **NOT READY**; physical Android acceptance, owner-operated recovery, populated live-business commercial reconciliation and POPIA operational controls remain release gates.
