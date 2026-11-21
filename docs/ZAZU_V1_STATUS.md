## Director execution checkpoint — 2026-11-21 (receiver sync and bootstrap security closure)

**Canonical repository:** `Leano-Jordan/ZazuEMP` · **write target:** `main`.  
**Verified application/test source head:** `559e66b87de8617f967b722b55b0fbfd9b116495`.  
**Exact-head workflow:** [Laravel / PostgreSQL / offline-browser run 44880113367](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44880113367).

### Deep-audit corrections delivered
- Receiver sync no longer acknowledges a delivery prefix after persisting a generic bounded bootstrap that omitted an affected older aggregate. The receiver sends the exact delivery sequences with bootstrap; the server resolves primary and related aggregates within business/device scope and includes them before acknowledgement.
- Normal 500-record working sets prioritize recently revised aggregates while preserving display order. Explicit pairing selections remain pinned and cannot silently expand due to deliveries.
- Bootstrap now revalidates active device status and the persisted sync-token hash under the business-first lock order before returning business data. Stale requests after revocation or credential replacement receive HTTP 401.
- The precached offline client was advanced to `zazu-static-v58`.

### Exact-head automated evidence
- Laravel **632 passed / 1 skipped / 4,388 assertions**.
- Offline browser: **59 desktop Chromium passed**, **7 mobile-viewport passed**, **1 phone-local Work/attachment/reconnect journey passed**.
- PostgreSQL migration, real account/workspace registration, backup/restore and private-file recovery smoke: **PASS**.
- PHPMD, Zazu Quality, Composer security audit, secret scan, application check, Director Health, Push-on-main and Actions/Python/JavaScript analysis: **PASS**. SonarCloud was **SKIPPED**, not counted.
- Regressions close CASE-ZAZU-0034 / ENG-DATA-004 and CASE-ZAZU-0035 / ENG-AUTH-001. CASE-ZAZU-0033 and the full sync workflow matrix remain separate and OPEN.

### Release disposition
**V1 remains NOT READY. Evidence maturity: 75/100 (+2)** for the newly verified receiver reconciliation and bootstrap authorization controls. This is not production-readiness certification.

**Still OPEN:** physical Android cold launch/restart/reconnect; intended-PC installation recovery drill; live-business quote → invoice/payment → actual cost/profit reconciliation; POPIA operational/privacy sign-off; complete offline sync matrix and exhaustive writer audit. ZAZU name clearance remains deferred by Founder direction. DB-001 remains DORMANT / MONITORING; its historical root cause remains unproven.

No production deployment or live business data changed. No branches were created.

---

## Director execution checkpoint — 2026-11-21

**Canonical repository:** `Leano-Jordan/ZazuEMP` · **write target:** `main`.  
**Verified offline/session test head:** `1768de8b00ff0064b3b53cd757d4118fbfe04608`.  
**Follow-up business-archive hardening head:** `0bb76a158e22b9671ce5dfc32b54a29c81cda72b`.

### Targeted implementations delivered
- Offline navigation preserves cached Customer/Work creation, invoice creation and Work-detail pages while falling back to the authenticated dashboard shell for unsupported or unprimed routes.
- Offline workspace navigation uses Zazu's active-business `PermissionService`, not Laravel `@can`.
- Service-worker navigations propagate same-origin redirects after an expired web session and do not cache the final public login page as authenticated HTML.
- Phone-local attachment queue writes, Work identity reconciliation, attachment listing and file retrieval are bound to the active offline session generation.
- Business archive restore now rejects every ZIP entry except `manifest.json`, `data.json` and payload entries declared by the validated file manifest. Unexpected entries are rejected before transaction or target-workspace mutation.

### Exact-head automated evidence
- Offline/session source head `1768de8`: Laravel **627 passed / 1 skipped / 4,334 assertions**; PostgreSQL migration/business/private-file recovery smoke **PASS**; desktop Chromium **58 passed**; mobile-viewport Customer/Work **7 passed**; phone-local attachment journey **1 passed**. All reported security, quality, application and health checks passed. Sonar analysis was skipped.
- Archive hardening head `0bb76a1`: Laravel **628 passed / 1 skipped / 4,340 assertions**; PostgreSQL migration/recovery smoke **PASS**; desktop offline-browser check was still running at the time of this checkpoint. Other reported analysis, application, security and quality checks passed.
- Windows package build and PowerShell fresh-install verification: **PASS** on source commit `490100e2ae7a4793db3e06c37833cba7b2033fb9`.

### Release gates — unchanged
**Still OPEN:** physical Android cold launch/restart/reconnect; intended customer-PC installation recovery drill; live-business commercial reconciliation; complete offline sync matrix; POPIA operational controls; ZAZU name clearance. Automated browser/CI and Windows package smoke tests do not replace owner-side installation acceptance. **V1 remains NOT READY; no readiness-score uplift is claimed.**
---

## Director execution checkpoint — 2026-11-20

**Canonical repository:** `Leano-Jordan/ZazuEMP` · **write target:** `main` only.  
**Verified code SHA:** `041314fd80147758dd2edb33c187b16328aea938`.  
**Exact-head Laravel:** [44810699348](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44810699348) — **PASS**, 623 passed / 1 skipped / 4,283 assertions.  
**PostgreSQL migration + registration + database/private-file recovery smoke:** **PASS**.  
**Offline browser:** 57 desktop Chromium passed, 7 mobile-viewport passed, 1 dedicated Pixel 7-viewport Work-create/attachment/reconnect journey passed.  
**PHPMD** [44810699344], **Zazu Quality** [44810699254], **Director Health** [44810699270], **Push-on-main** [44810699529]: **PASS**. SonarCloud was skipped.

### Current gate interpretation
- **Offline account/session transition — AUTOMATED PASS** on this exact SHA. Logout/session preservation, expired session re-authentication, owner/manager account transition, trusted-device revocation retry, cross-business pairing denial, stale response protection and pending-work preservation are covered. Physical-device/lost-device operational acceptance remains separate.
- **Active Work attachments — AUTOMATED PASS** on this exact SHA. Offline file queueing, durable local storage, reconnect upload, retry/idempotency, server identity reconciliation, private download cache, cross-tab re-pair and late-response/logout protections pass. The dedicated Pixel 7-viewport Work-create/attach/reconnect journey also passes; this is not physical Android cold-restart proof.
- **Revision-aware stale-write protocol — IMPLEMENTED; FULL SYNC MATRIX OPEN.** The command-parent revision regression passes for quote acceptance → Work, payment → invoice, purchase receipt → purchase order/inventory, inventory movement → inventory item and asset allocation → asset. Full replay/reconciliation across all supported offline operations remains open.
- **Commercial workflow — SEEDED AUTOMATED PASS; LIVE RECORD RECONCILIATION OPEN.** The current test scenario reconciles R11,500 invoice including VAT, R3,450 deposit, R8,050 final payment, R10,000 net sales excluding VAT, R2,800 actual cost, R7,200 indicative contribution and 72% margin. This is not audited accounting profit or proof against live business records.
- **DB-001 — DORMANT / MONITORING.** Founder reports no recent recurrence; root cause remains unproven and no fix is claimed. Do not resume active investigation unless it recurs or new evidence appears. Installation recovery is a separate OPEN gate.
- **Still OPEN:** real Android cold launch/restart/reconnect; intended-PC installation recovery drill; real-business commercial reconciliation; POPIA operational controls; ZAZU name clearance. The complete offline sync matrix also remains OPEN.

**V1 remains NOT READY. Evidence maturity remains 73/100; no score uplift is claimed.** No production deployment or live business data changed.

---

## Director update — 2026-11-18 (DB-001 diagnostic classification)

**Canonical repository:** `Leano-Jordan/ZazuEMP` · **branch:** `main`  
**Commit:** `f63c9080a18389265da094df84cd24b02ecc7fc0`  
**CI:** [Laravel run 44252182123](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44252182123) — Laravel tests **PASS**.

### DB-001 diagnostic improvements
- The existing reference-scoped download now includes a safe database failure category derived from the private driver message; the original message, SQL, bindings, customer data, credentials and full paths remain excluded.
- SQLite categories distinguish missing table, missing column, database lock/schema lock, read-only access, disk I/O, malformed database, open failure, disk full, foreign-key/unique constraint, busy and unclassified failures.
- PostgreSQL SQLSTATEs distinguish missing table/column, unique/foreign-key constraints, serialization conflict, deadlock and connection failure.
- Added feature coverage for the reference download allowlist/redaction and recorder classification.
- Automated evidence: **552 passed / 1 skipped / 3,844 assertions**. PostgreSQL migration smoke, Windows package build and fresh-install verification, PHPMD, Psalm, Zazu Quality, Director Health, populated upgrade/rollback and Push-on-main: **PASS**. Offline-browser acceptance is still running; SonarCloud is skipped.

### Required owner-side evidence
The actual Windows installation has not yet produced its reference-scoped diagnostic report in this conversation. The historical DB-001 cause is therefore **not yet proven or repaired**. Obtain the report from the affected installation before choosing a database repair. V1 remains **NOT READY**.

---

## Director update — 2026-11-18 (commercial sharing and invoice numbering)

**Canonical repository:** `Leano-Jordan/ZazuEMP` · **branch:** `main`  
**Application-code baseline:** `1f72eada34a774089efcb9a65ee84b0b93003269`  
**Exact-head CI:** [run 44208836341](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44208836341) — **PASS**.

### Commercial engineering delivered
- Signed, read-only customer receipt pages for invoice-linked payments, with WhatsApp handoff and browser Print / Save as PDF.
- Consecutive invoice numbering in the existing `INV-YYYYMM-` format, scoped per business and month, protected by a durable sequence row and business transaction lock.
- Online issuance and offline invoice sync use the same allocator. Offline records refresh to the canonical server number after sync.
- Existing numeric invoice numbers seed the counter; voided numbers remain consumed; idempotent retries do not allocate a second number.

### Exact-head automated evidence
- Laravel: **546 passed / 1 skipped / 3,805 assertions**.
- Offline browser: **45 desktop Chromium tests passed**, **6 mobile-viewport tests passed**, and **1 phone-local attachment journey passed**.
- PostgreSQL migrations, account/workspace registration and database/private-file backup/restore: **PASS**.
- Windows V1 candidate build and Windows PowerShell fresh-install verification: **PASS**.
- PHPMD, Psalm, Zazu Quality, Director Contract Lint, Director Health, populated upgrade/rollback and Push-on-main: **PASS**. SonarCloud: **SKIPPED**.

### Remaining release gates
COM-01 remains partial: direct PDF-file attachment and physical desktop/Android sharing acceptance remain open. South African tax-adviser/legal review of invoice numbering and tax-invoice presentation remains open. DB-001, intended-installation recovery, live-business reconciliation, physical Android acceptance and POPIA operational sign-off remain open. **V1 remains NOT READY; no readiness score uplift is claimed.**

---

## Director update — 2026-11-17 (offline customer detail and archive validation hardening)

**Canonical repository:** \`Leano-Jordan/ZazuEMP\` · **branch:** \`main\`  
**Verified application/test source:** \`325dc671d1005fd157fdaa64979d818a66961f0c\`.

### Corrections committed
- Customer offline detail rendering no longer references the Work-only \`currentJob\` variable. The invalid reference could stop the customer panel from rendering.
- Malformed business archive JSON is now returned as a controlled archive validation error instead of an uncaught JSON exception. A dedicated regression verifies the upload is rejected without changing business records.
- Offline Work requirement reconciliation was split into focused helpers to remain within PHPMD complexity limits without changing reconciliation behaviour.
- Offline Work lifecycle and archive malformed-upload tests were separated into focused test classes to keep test classes maintainable.
- The Work-detail browser assertion now handles the customer name appearing in more than one detail row.

### Exact-head automated evidence
- Laravel: **520 passed / 1 skipped / 3,635 assertions**.
- PostgreSQL migration, account/workspace registration, and database/private-file backup/restore: **PASS**.
- PHPMD: **PASS**.
- Zazu Quality, Composer security audit and secret scan: **PASS**.
- Offline browser acceptance was still running when this checkpoint was recorded; no browser pass is claimed here.
- The populated commercial reconciliation regression is included in the passing Laravel suite. It verifies accepted quote → invoice → deposit → final payment → received purchasing → actual cost → event completion → event profitability, including report output and exclusion of void/planned/cancelled values.

### Release disposition
**V1 remains NOT READY.** Automated recovery and commercial regression coverage are verified, but they do not replace the installed Windows recovery drill or the owner's live-business reconciliation. Physical Android installed-app cold launch/restart/reconnect and POPIA operational sign-off remain open. Brand/name clearance remains deferred by owner direction. No live business data or production installation was changed.

---

## Director update — 2026-11-17 (offline Work editing and lifecycle)

**Current source head:** `ac761414a9ec4339812646e25238d5b4ae888c85` on canonical `main`.

### Implemented in source
- Existing Work records can now edit their service/requirement list from the device-local Work inspector. The change updates the local IndexedDB record and queues a `job:update` mutation.
- Existing Work records can be reassigned to another locally cached customer while disconnected. The selected customer identity and name are saved locally and queued; server sync rejects reassignment once any quote exists for that Work record.
- Server sync reconciles the requirement list only when the mutation explicitly includes `services`. Matching requirements retain quantity, unit, notes, status and capability metadata; removed descriptions are removed; new descriptions receive the configured service category where available.
- Offline Work edits now include lifecycle status transitions using the same `EventLifecycleService` transition and completion/cancellation guards as connected workflows.
- Closed Work records cannot be edited from the offline inspector. The server also rejects closed-record mutations and invalid transitions.
- Existing business/event locking order is retained.

### Regression coverage added
- Laravel feature tests cover requirement reconciliation and preservation of existing requirement metadata, valid confirmed → in-progress transition, blocked completion while preparation remains open, and closed-record edit rejection.
- Laravel and Playwright coverage also exercise offline customer reassignment on Work records without quotes.
- Playwright coverage now exercises local service-list editing and a status transition while disconnected, including the queued mutation and updated local Work row.

### Verification boundary
These source and test changes are committed, but the Laravel and Playwright suites have **not** been executed against this head. GitHub currently reports no combined status entries or workflow runs for this commit. No test pass is claimed.

**Release remains NOT READY.** Physical Android installed-app offline acceptance, installed-target database/private-media recovery, live populated-business commercial reconciliation, and POPIA/privacy/brand closure remain separate acceptance gates. No branches, production deployment, or live business data were changed.

---

## Director reconciliation — 2026-11-17 (offline customer and Work read/edit workflows)

**Canonical repository:** `Leano-Jordan/ZazuEMP` · **branch:** `main` · **verified head:** `3726f2f0bf08e6a3ae5e7c6dd0169d1aebc97565`.

### Device-local workflows now verified
- **Customers:** the in-place relationship panel reads customer/contact/billing/notes fields and linked local Work/quote summaries. Supported customer profile, primary-contact and billing fields can be edited while disconnected. The save updates the local working copy and queues the customer update atomically in IndexedDB.
- The customer row shows **Waiting to sync** while an update is queued. After server acknowledgement and returned identity, the per-record marker clears and the row returns to **Synced**. Conflicts and re-pair requirements take priority. Profile-photo changes remain online-only.
- **Work inspection:** local Work rows can open a device-local panel assembled from IndexedDB: job/customer details, services, linked quotes, preparation items, costs, purchase orders and invoices. It does not request a Laravel job detail page while disconnected.
- **Work editing:** job name, event date, venue/address and notes can be edited while disconnected. The edit is durably queued and the row shows its sync state separately from the job's lifecycle status. The existing customer relationship and lifecycle status are preserved by this bounded edit path.
- Work lifecycle transitions, customer reassignment, service/requirement edits and full server-rendered Work route parity remain open.
- Playwright covers offline customer read/edit, durable queueing, reconnect acknowledgement, returned server identity and sync-state clearing, plus Work inspection and Work edit queueing with lifecycle-status preservation.
- Removed the unrelated Render-labelled Docker preview workflow. Zazu has no Render service or deployment.

### Exact-head acceptance evidence
- Laravel [run 44029733667](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44029733667): **PASS — 503 passed / 1 skipped / 3,566 assertions**.
- Offline browser acceptance in the same run: **39 desktop Chromium passed**, **6 mobile-viewport passed**, and **1 phone-local attachment journey passed**.
- PostgreSQL migrations, real account/workspace registration, and database/private-file backup/restore: **PASS**.
- Zazu quality [run 44029733727](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44029733727) and Push on main [run 44029733364](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44029733364): **PASS**. Director Health [run 44029704946](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44029704946) and Windows V1 candidate package [run 44029705007](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44029705007): **PASS** on the immediately preceding application-code checkpoint. SonarCloud was **SKIPPED**, not counted.

### Release decision
**V1 remains NOT READY. Evidence maturity remains 73/100; no score uplift is claimed.** Physical Android installed-app cold launch/restart/reconnect, the installed-target recovery drill including private media, live populated-business quote/payment/cost/profit reconciliation, and POPIA operational/privacy sign-off remain open. CI browser success does not replace physical-device acceptance. No production deployment, live business database or customer data changed.
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

## Director reconciliation — 2026-11-16 (Windows V1 package verified)

**Canonical repository:** `Leano-Jordan/ZazuEMP` · **branch:** `main`  
**Package verification source:** `9a1fc33004c52d7ee4b5a16d94dc7d94b578e5f0` · **workflow:** [43966804827](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43966804827).

- A clean Windows V1 candidate ZIP builds with production Composer dependencies and compiled Vite assets. Manifest integrity and referenced asset presence are checked before packaging.
- The Windows PowerShell first-install smoke **PASSED** on PHP 8.4/SQLite: `.env` and APP_KEY creation, database migrations, login route boot, HTTP `/login` 200 and a usable local HTTP session cookie (no `Secure` attribute).
- Zazu Quality [43966805237](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43966805237), Director Contract Lint [43966804706](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43966804706), Director Health [43966804574](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43966804574), and Push on main [43966803035](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43966803035): **PASS**.
- Package: `ZazuEMP-v1-candidate-9a1fc33.zip`; standalone SHA-256: `33a60db7fb40a6031634c3d97fc1d3600b46e6994c2276f48fc08ca448c980a7`. It is a fresh local Windows installation candidate; it is not an updater or a hosted SaaS deployment. PHP 8.4+ remains a host prerequisite.
- The installer defaults to loopback HTTP and therefore sets `SESSION_SECURE_COOKIE=false`; HTTPS-only operation must set it to `true`. Keep the local HTTP server off the public internet.
- Commercial architecture research is recorded at `docs/COMMERCIAL_ARCHITECTURE_RESEARCH_2026-11-16.md`. Market pattern: event/job as one source of truth, versioned proposal changes, and connected payments, purchasing, preparation and profitability.
- **Formal evidence maturity: 73/100** (previously 71/100; +2 for the verified package/installer and Windows first-install evidence).
- **V1 remains NOT READY** pending owner-PC/browser and intended phone acceptance, installed-target backup/restore with private media, live-business quote/payment/cost/profit reconciliation and POPIA operational/privacy sign-off. Brand/name clearance remains deferred by owner direction.
- No production deployment, live customer database or customer data changed.

---
## Prior Director reconciliation — 2026-11-16 (dashboard and automated V1 evidence)

**Canonical repository:** `Leano-Jordan/ZazuEMP` · **branch:** `main` · **current documentation head:** `ee301b2b5732c849acfc015137f528cbd3604811` · **verified application/test source:** `8cdadcd1563bd30c88b15153ca91181a2d5abb23` · **compiled CSS manifest descendant:** `dfc01749af5718011332a9e86d303b9622fe7ff4`.

- Dashboard theme recovery is complete in source: light and dark dashboard aliases use shared Zazu tokens; the radial/linear hero and business-image overlay remain. The visual foundation test compares computed styles in both themes.
- Exact application/test run [43952349496](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43952349496): **500 passed / 1 skipped / 3,549 assertions**; dedicated offline browser **37 desktop passed**; mobile-viewport acceptance **6 passed**; phone-local attachment journey **1 passed**; PostgreSQL migration/registration and database/private-file recovery smoke **PASS**.
- The manifest-only descendant updates the CSS reference to `assets/zazu-product-authority-DTNoJ_s9.css`. Exact descendant run [43952861421](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43952861421): all Laravel, offline-browser and PostgreSQL jobs **PASS**. Zazu Quality [43952861428](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43952861428), Director Health [43952861384](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43952861384), and Push on main [43952861462](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43952861462): **PASS**. SonarCloud [43952861463](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43952861463) was skipped and is not counted.
- Server-side offline security review found no new defect requiring an architecture change: sync credentials are hash-stored, active/expiry/revocation checks are enforced, device revocation is owner/tenant-scoped, and attachment transfer/read paths enforce business scope and idempotent payload matching. Existing tests cover stale responses, re-pairing, expired/revoked tokens and cross-business pairing rejection.
- **Formal evidence score remains 71/100**; no readiness uplift is awarded for this visual correction or automated viewport verification.
- **Release disposition: NOT READY.** Physical Android installed-device acceptance, intended-installation recovery drill, live commercial-record reconciliation and POPIA operational sign-off remain open. Brand/name clearance remains deferred by owner direction.
- Actual rendered appearance on the owner's PC remains **UNVERIFIED**; CI browser success is not real-PC acceptance.
- No production deployment or live customer data changed. All engineering changes stayed on `main`; no branch was created.


---

## Director reconciliation — 2026-11-14 (exact-head offline V1)

**Canonical repository:** `Leano-Jordan/ZazuEMP` · **branch:** `main` · **verified application/test head:** `d09fdbfbe4bdbb7cfaed9ca1ea125313910fa3e0`.

- Laravel [43593454044](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43593454044): **496 passed / 1 skipped / 3,510 assertions**.
- Dedicated offline browser acceptance: **32 passed**; separate mobile-viewport customer/Work acceptance: **1 passed**; phone-local attachment journey on mobile viewport: **1 passed**.
- PostgreSQL migration, registration, database backup/restore and private-file recovery: **PASS**.
- Zazu quality, Composer security audit and secret scan [43593453976](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43593453976): **PASS**. Push-on-main CodeQL [43593453848](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43593453848): **PASS**. PHPMD [43593169089](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43593169089): **PASS**.
- The focused offline CI now includes PWA shell, offline session transition, Work attachment sync and offline customer/Work creation. Service-worker page priming has an explicit completion acknowledgement; the offline test waits for full priming before disconnecting.
- Pending local customers/jobs are visible from the cached dashboard, with links to the cached Customers and Work directories. The supported primed routes remain limited to `/customers`, `/customers/create`, `/work` and `/work/create`. This is not full application offline parity.
- **Release disposition: NOT READY.** Physical Android installed-app cold launch/restart/reconnect, installed-target recovery drill, live commercial-record reconciliation and POPIA operational sign-off remain open. Brand/name clearance is deferred by owner and is not an active engineering target.
- No production deployment or live customer data was changed.

---

## Director reconciliation — 2026-11-14 (V1 source and offline acceptance)

**Canonical repository:** `Leano-Jordan/ZazuEMP` · **branch:** `main` · **current control/workflow head:** `652d4d58979ce7016f03e6318ffb5a6944bbe184` · **application/test head:** `f9307b9dd477cc0cf6dd4f51abf8a818b53bae75`.

- Laravel [run 43591107542](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43591107542): **496 passed / 1 skipped / 3,502 assertions**.
- Dedicated offline-browser acceptance in the same run: **30 passed / 0 failed / 0 flaky** on desktop Chromium; separate Pixel 7 mobile-viewport customer/Work acceptance: **1 passed**; phone-local attachment journey: **1 passed**.
- PostgreSQL 16 migrations, business registration, database backup/restore and private-file recovery smoke: **PASS**.
- Zazu quality, Composer security audit and secret scan [run 43591107583](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43591107583): **PASS**.
- Director Contract Lint [run 43591107537](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43591107537), Director Health [run 43591107577](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43591107577) and Push-on-main CodeQL [run 43591107054](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43591107054): **PASS**.

### Offline customer and Work slice

- The dashboard now shows locally pending customers and jobs from the existing IndexedDB working set.
- The device-local workspace links to the cached customer and Work directories.
- First authenticated dashboard launch primes `/customers`, `/customers/create`, `/work` and `/work/create`.
- The automated browser test verifies that the active service worker primes those routes in the active business cache, then creates customer/job records while disconnected, reloads, confirms local records remain visible, navigates into both directories and verifies reconnection sync.
- This is a bounded customer/Work offline capability. It does **not** establish complete local rendering for every Laravel route or full offline parity across the application.

### Other V1 gates

- **Business data portability — IMPLEMENTED / AUTOMATED-VERIFIED:** 23 business-scoped CSV datasets and the tenant-scoped `.zazu` archive export/restore are available. Archive restore remaps record IDs, restores linked records and referenced business files, and refuses non-empty or already-paired target workspaces.
- **Commercial reconciliation — SEEDED AUTOMATED-VERIFIED:** the accepted quote → invoice → deposit → final payment → actual cost → event profit path is covered by populated regression data. Live business-record reconciliation remains an operator acceptance task.
- **Installation recovery — CI-VERIFIED / OPERATOR DRILL OPEN:** PostgreSQL/database/private-file recovery passes in CI. The controlled backup → restore → data/media/security verification drill on the intended installed target remains open.
- **Physical offline acceptance — OPEN:** secure-origin installation on the actual Android device, cold launch with the PC/server unavailable, supported disconnected workflows, force-close/reopen persistence and reconnection must still be proven on-device.
- **POPIA operational controls — OPEN:** privacy, retention and incident procedures require owner sign-off. **Brand/name clearance is deferred by owner** and is not a current V1 engineering gate.

**Release decision: NOT YET READY.** The bounded offline browser workflow and automated engineering gates are green. Physical device acceptance, the installed-target recovery drill, live commercial reconciliation and operational privacy/legal sign-off remain. No production deployment or live customer data was changed.


---

<!-- Historical assessments below are retained as an audit trail. The newest dated Director reconciliation at the top is authoritative; older snapshots may contain statuses that were true at their assessment date and have since been superseded. -->

## Director reconciliation — 2026-11-13 (exact-head CI and archive gate closure)

**Canonical repository:** `Leano-Jordan/ZazuEMP` · **branch:** `main` · **verified head:** `5888123f1941beb478b31870a6e782e0f8ef8053`.

- Exact-head Laravel workflow [43491595380](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43491595380): **PASS** — Laravel suite 486 passed / 1 skipped / 3,475 assertions; PostgreSQL migration, registration and database/private-file recovery passed; dedicated offline-browser acceptance passed.
- Exact-head PHPMD [43491595199](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43491595199): **PASS**. The archive round-trip regression test was split into focused fixture/assertion helpers after PHPMD identified its 128-line test method; no warning suppression was added.
- Exact-head Psalm [43491595173](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43491595173): **PASS**.
- Exact-head Zazu quality, secret scan and CodeQL push workflow [43491594809](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43491594809): **PASS**.
- Populated upgrade/rollback [43491595120](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43491595120): **PASS**.
- Business archive restore is now **IMPLEMENTED / AUTOMATED-VERIFIED** on this head. The round-trip test covers linked tenant records, supplier → purchase order → purchase-order item remapping, tenant exclusion, private attachment bytes and public catalogue media. The passing test confirms the earlier overlapping simple/composite FK remapping correction. This does not replace an operator-run restore on the actual installed target.
- Offline account/session transition and active Work attachment flows are automated-verified by the passing Laravel and dedicated offline-browser jobs. Real phone/tablet persistence and secure-origin installation remain unverified.
- Quote → payment → profit remains **SEEDED AUTOMATED-VERIFIED**; this does not claim a manual reconciliation of real business records.
- Installation recovery remains **CI-VERIFIED / OPERATOR DRILL OPEN**. Business archive restore and installation-wide backup/restore are separate capabilities.
- Physical device acceptance remains open; the owner's current plain-HTTP LAN address does not satisfy the secure-origin installed-PWA precondition.
- POPIA operational controls, privacy notice publication/review, retention and incident procedures, and Zazu name/trade-mark clearance remain open. Do not claim legal clearance.
- No production deployment, live customer data, database or non-main branch was changed.

**Release decision: NOT YET READY.** Automated engineering gates are green; owner-controlled physical installation/recovery and legal/brand gates remain. No evidence score uplift is claimed by this reconciliation.

---

## Director reconciliation — 2026-11-13 (supersedes stale archive status below)

- Latest observed repository documentation HEAD: `7d95ee61da48d5fcf3ec4ed06ddbab0696df7b8d` on `main`.
- Business archive restore is now **IMPLEMENTED / EXECUTION UNVERIFIED**, not “not implemented”. The duplicate-FK remapping correction is in `app/Services/BusinessArchiveService.php` commit `51bba91ddfe4f438b4b979c14f5e67e6864ac048`; the focused round-trip regression fixture is in `tests/Feature/BusinessArchiveTest.php` commit `e4daa4d1b12378479b2990360877f964719d0a5a`. The fixture exercises supplier → purchase order → purchase-order item links and the overlapping simple/composite foreign-key case.
- The correction always resolves foreign-key values from the original archived row, avoiding a second constraint attempting to remap an already-remapped target ID.
- Acceptance remains **OPEN** until the focused archive round-trip and its safety tests execute and pass on a commit containing both changes. The latest combined-status lookup returned no contexts and the commit-workflow lookup exposed no runs; no pass is claimed.
- The archive test currently asserts 28 tables, 11 records and 2 files, plus restored tenant-scoped relationships and private/public attachment payloads.
- Director CI efficiency changes are present on `main`: duplicate PHP verification was removed from Quality, full browser regression is restricted to browser-relevant PRs or manual dispatch, and the unique service-worker regression plus secret scan remain. These workflow edits also require their own Actions evidence.
- Existing installation-level PostgreSQL/database+private-file CI evidence does not close the controlled installed-environment recovery drill. Physical phone/tablet acceptance, POPIA operations and Zazu name clearance remain open.
- No customer data, live database, deployment service or `deploy/render-staging` branch was changed.

---

# Zazu EMP — current V1 gate status (2026-11-13)

**Canonical repository:** `Leano-Jordan/ZazuEMP`  
**Canonical branch:** `main`  
**Verified application/test HEAD:** `245aa47f7ef74e6e133fff867670ecafabb603b8`.

## Current release position

The exact-head automated test batch now passes for offline session transitions, active Work attachments, tenant/device isolation, multi-customer sync identity reconciliation, commercial reconciliation, PostgreSQL migration/backup/restore, static quality and populated upgrade/rollback.

**Technical V1 is still NO-GO for owner acceptance**, not because these automated suites are failing, but because the installed-device and operational recovery checks have not been completed. The business-level restorable archive is implemented and automated-verified as recorded in the newer archive-gate reconciliation above; the actual installed-environment operator recovery drill remains open.

## Current V1 reconciliation

- **Offline account/session security — AUTOMATED PASS.** Chromium browser acceptance verifies interrupted logout preserves the signed-in session and queued work; switching from owner to manager in the same business preserves the trusted business workspace; expired web-session re-authentication preserves the pending offline mutation and device pairing. Business-switch installation-ID reuse is rejected with HTTP 409 without changing the original business's trusted device.
- **Offline Work attachments — AUTOMATED PASS.** The active Work attachment workflow passes browser acceptance for local queueing, reconnect upload, server-ID reconciliation, duplicate retry, offline file reuse, cross-tab re-pair and stale asynchronous response protection. Do not count the dormant `public/offline-attachments.js` as the active implementation.
- **Multi-customer sync — AUTOMATED PASS.** Two queued customer creates reconcile to distinct server IDs and replay without duplicates. The owner's actual existing IndexedDB customers remain unobserved by CI and must not be recreated.
- **Commercial reconciliation — AUTOMATED PASS.** A seeded scenario validates the complete accepted quote → invoice → deposit → final payment → actual cost → event profit path. Values: R11,500 invoiced including VAT; R3,450 deposit; R8,050 final payment; R10,000 net sales excluding VAT; R2,800 actual cost; R7,200 indicative contribution; 72.00% margin. This is not a claim that live customer records have been manually checked.
- **Business data portability — IMPLEMENTED / AUTOMATED-VERIFIED.** Twenty-three business-scoped CSV datasets and the relationship-complete tenant-scoped `.zazu` archive/restore are available; customer CSV/XLSX import preview remains available separately. Real installed-environment recovery is a distinct open gate.
- **Installation recovery — CI VERIFIED, OPERATOR DRILL OPEN.** The CLI backup/restore supports SQLite, MySQL and PostgreSQL; PostgreSQL database and private-file round-trip passes in CI. The controlled installed-environment recovery drill remains required.
- **Physical acceptance — OWNER VERIFICATION REQUIRED.** Real secure-origin phone/tablet installation, disconnected workflow, local record and attachment persistence, force-close/reopen and reconnect sync remain unverified.
- **POPIA / legal / brand — OPEN.** Operational controls, retention/disposal ownership, incident handling, and professional Zazu name/trade-mark clearance remain to be completed.

## Exact-head CI evidence

- Laravel + PostgreSQL + dedicated offline browser acceptance: [43473670275](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43473670275) — **PASS**.
- Full Playwright browser regression: [43473670319](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43473670319) — **PASS**.
- Zazu quality: [43473670378](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43473670378) — **PASS**.
- Psalm: [43473670232](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43473670232) — **PASS**.
- PHPMD: [43473670420](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43473670420) — **PASS**.
- Populated upgrade/rollback: [43473670272](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43473670272) — **PASS**.
- Push-on-main: [43473671064](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43473671064) — **PASS**.
- Director Health: [43475031359](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43475031359) — **PASS**.
- SonarCloud: [43473670372](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43473670372) — **SKIPPED**, not counted as a pass.

**Evidence-maturity score remains 71/100** pending a formal re-score. Passing test corrections do not automatically justify a score increase.

---

# Zazu EMP — V1 Software / Commercial Status

**Assessment date:** 2026-11-09  
**Repository:** `Leano-Jordan/ZazuEMP`  
**Canonical branch:** `main`  
**Verified application/test source commit:** `d55539ddfd52c7fa5140714288c5885dbcc99e3a`.

## Current position

## Latest Director scorecard — 2026-11-09

**V1 evidence-maturity score: 69/100 (3.46/5), up from 60/100.**  
**Verified application/test source:** `d55539ddfd52c7fa5140714288c5885dbcc99e3a`.

- Laravel: 414 passed / 2,938 assertions.
- Browser smoke: 65 passed / 22 skipped / 0 failed or flaky.
- Zazu Quality + secret scan, Psalm, PHPMD, populated upgrade/rollback and push-on-main: all passed.
- Director Contract Lint passed on the preceding control-plane documentation commit; it was not triggered by this test-only source commit.
- SonarCloud: skipped, not a pass.

The uplift now also covers recovery/data portability: CI restores the seeded populated business database and private media, verifying key records and 14 operational table counts. This moves recovery from 2/5 to 3/5. The real installed-environment restore remains open.

**Release position: NO-GO.** Remaining gates: secure-origin installed-device offline acceptance, real installed-environment database/private-media restore, real deployment rollback, trusted-device acceptance, operational privacy/legal closure and official brand clearance. LAN launcher implementation is committed but has not yet been verified on the owner's PC.

See `docs/ZAZU_V1_SCORECARD_2026-11-04.md` for the full 13-domain scorecard and `docs/V1_RELEASE_CHECKLIST.md` for exact CI run links.

Zazu EMP has a substantial connected operational foundation across authentication/business context, customers and jobs, requirements/services, quotes and revisions, preparation, purchasing, inventory/assets, costs, finance, reporting, search, attachments and responsive UI.

Historical automated verification snapshot (superseded by the Director scorecard above):
- Laravel: **407 passed / 2,758 assertions**
- Zazu Quality + secret scan: **PASS**
- Psalm: **PASS**
- PHPMD: **PASS**
- Populated upgrade/rollback: **PASS**
- Browser smoke: **65 passed / 22 skipped / 0 failed**
- Director health report: **PASS**
- SonarCloud: **SKIPPED**, not a pass

Run IDs and release evidence are recorded in `docs/V1_RELEASE_CHECKLIST.md`.

**Release position: NO-GO.** The application/test source has a fully green automated verification batch, but this is not commercial release certification. Installed-device disconnected operation, representative customer data/private-media restore, deployment rollback, operational legal/privacy closure and official Zazu brand clearance remain outstanding.

## Current V1 capability reconciliation

- Global search now includes business-scoped metadata search of job attachments as well as the existing record domains.
- Phone-local Work attachment queue/upload/download and reconnect safeguards exist and pass automated coverage; physical installed-device acceptance remains open.
- Business export now covers 23 owner-only, tenant-scoped CSV datasets.
- Customer CSV/XLSX import preview/matching exists. Product/service and supplier import coverage remains an R499 commercial gap.
- The V1 system-feedback requirement is met by meaningful success/error toasts, status indicators and confirmation flows. Scheduled reminders are a separate R799 team-management gap.
- Staff assignment to work records is not implemented in the current data model. It is recorded as an R799 team-management gap, not falsely marked complete or treated as a R299/R499 release blocker.
- The dashboard remains owner-accepted as a design target; exact-head browser regression is green. No styling rollout was implied by that acceptance.

## Current release gates

1. Installed-device offline launch, local Work/attachment operation, force-close/reopen and reconnect acceptance on the intended secure origin.
2. Representative backup → restore → data/private-media verification on an isolated target deployment.
3. Real deployment rollback exercise.
4. POPIA/privacy/terms, retention/disposal ownership and incident escalation closure.
5. Official Zazu name/trade-mark clearance and professional review.
6. Final Director release audit after the above evidence is recorded.

## Historical-chat reconciliation

| Historical idea | Current repository state on 2026-10-03 |
|---|---|
| Event/business operational core | IMPLEMENTED — customer, job/event, requirements, capabilities, quotes, preparation, purchasing, inventory/assets, finance and reporting are connected around the operational record. |
| Experience modes | IMPLEMENTED foundation — Basic / Intermediate / Advanced are stored per business membership and already influence dashboard detail. |
| Primary niche focus | NEW FOUNDATION — stored per business membership with four presentation choices. Sound & DJ is the third niche and the concrete first iteration reference. |
| Progressive disclosure | PARTIAL / FOUNDATION — hierarchical navigation already exists; the workspace-focus screen now uses a native disclosure for secondary experience detail. Wider page-by-page adoption remains future iteration. |
| Command palette | IMPLEMENTED — global workspace search / quick access is present. |
| Niche-specific dashboard emphasis | PARTIAL — selected niche now appears in workspace context and dashboard copy; deeper niche-specific surfacing is not yet implemented. |
| Full offline business-data operation | PARTIAL / IN PROGRESS — IndexedDB local store + durable queue + reconnect sync cover the documented operational mutation domains. Phone-local Work attachment queue/upload/download is now IMPLEMENTED AT SOURCE with business-scoped persistence, batch idempotency, reconnect upload and attachment download caching; exact-head Laravel/Playwright execution and installed-device proof remain pending. Full disconnected parity remains open. |
| Online/offline parity | DIRECTION ACTIVE — the phone workspace is now a real local operational surface; it is not yet a complete disconnected replacement for every desktop workflow. |
| Local host / Wi-Fi clients | NOT IMPLEMENTED — remains an architecture target, not a current capability. |
| Historical infrastructure/capacity claims | EXCLUDED — not treated as requirements without repository or measured evidence. |

## Current-head verification boundary

The last proven application baseline was `92730f7cb660ae06d9df3c014eccf4f0d3e9188e`. The previously recorded candidate `aa1e3ca8921625abca16fe4bbc8546746b4bf135` is historical and must not be presented as the current application head. Subsequent source changes and the latest attachment retrieval correction require fresh exact-head Actions reconciliation. This status page is not a substitute for that run evidence.

## Release-critical work remaining

### Commercial completion
- Customer-facing quote delivery/presentation
- Quote acceptance/deposit lifecycle
- Complete invoice/document workflow
- Payment/reconciliation completion

### Integrity and control
- Complete authorization coverage
- Complete audit/activity continuity
- Parent/child and concurrency verification across critical mutations
- Representative existing-database migration/upgrade verification

### Recovery and operations
- Real backup/restore drill
- Restore of representative data and private media
- Rollback procedure
- Operational diagnostics/observability
- Final deployment/configuration review

### Previous verified release evidence

- Laravel: **202 passed / 1,174 assertions**
- Browser: **7 passed / 8 skipped**; the complete registration journey runs once on Chromium to avoid shared-IP registration throttling, while responsive entry coverage is separate
- Zazu Quality: **passed**
- PHPMD: **passed**
- Psalm: **passed** after removing the redundant init step and applying a narrowly scoped Laravel 13.34.0 compatibility shim for one unsupported `@phpstan-this-out` annotation
- Fresh SQLite migration, Blade compilation, asset build/manifest verification and application configuration validation: **passed**

### Runtime proof still required on the latest head
- Browser critical-path traversal
- Desktop/mobile verification
- Light/dark verification
- Accessibility verification
- Real local database/browser verification where CI cannot prove the owner's environment

## Engineering-system status

The repository now has a state-driven AI engineering control system:

- Morpheus/Jarvis control plane
- Discovery & Design
- Builder
- Guardian with Forensics / Break / Security / Data Integrity modes
- Release
- UI/UX Improvement
- persistent STATE / DECISION / REGRESSION / READINESS ledgers
- anti-loop and context-firewall controls

This documentation refactor is **engineering-control work**, not application feature completion.

## Evidence language

IMPLEMENTED, TESTED, VERIFIED and PROVEN are intentionally different states.

Historical CI/test figures in older records remain historical and are not restated as current evidence unless a current run is observed.

## Direction

The next Zazu engineering effort should close the highest-risk remaining release gate: populated commercial workflow → real recovery → representative upgrade → rollback → final Director certification.


## Jarvis update — 2026-10-07

Historical 2026-10-07 prototype note: a standalone phone-local attachment transfer layer was prototyped, but later inspection confirmed it is orphaned and not loaded by the authenticated application. It is not a working V1 feature. Server-side upload/download/list endpoints exist; the active Work panel remains online-only.

Remaining attachment gate: prove the browser/device flow against a real populated workspace, including a newly created offline job receiving its server identity before its queued attachment uploads.


### Offline attachment integration correction — 2026-11-01

The earlier phone-local attachment transfer note is not a verified active feature. Current-main inspection shows `/offline-attachments.js` is precached but is not loaded by the authenticated application layout and expects legacy globals absent from the current app shell. The Work screen supports online job attachments only. Phone-local attachment queueing/upload/download remains **NOT IMPLEMENTED** until the component is properly integrated and covered by browser/device tests. See `docs/ZAZU_OFFLINE_FIRST_ARCHITECTURE.md`.


### Current Director reconciliation — 2026-11-01

- The Work attachment panel now exposes the existing private download endpoint alongside Remove. The endpoint retains the `work.view` permission and controller business-isolation checks.
- A feature regression now checks that the Work page renders the download link and that an authorized user receives the private file download. A separate existing regression continues to reject foreign-business attachment downloads.
- These changes are committed to canonical `main`; their exact-head Laravel/quality/browser execution is **PENDING**. No current-head test pass is claimed here.
- The active phone PWA still does not load the legacy offline attachment script. Offline attachment queue/upload/download remains **NOT IMPLEMENTED** in the active app. Do not confuse online Work attachment retrieval with offline phone attachment parity.
- Stable HTTPS local-PC hosting, origin migration, disconnected installed-device launch, full offline parity, populated backup/restore, rollback and legal/privacy/brand closure remain open release gates.


## Director recovery-scope correction — 2026-11-04

A security review found that the former business settings backup/restore endpoints invoked installation-wide commands. The commands snapshot or replace the entire configured database and private storage, so exposing them to an individual business owner created cross-business disclosure and destructive-restore risk. The HTTP endpoints and UI controls have been removed. The settings page now states that installation recovery is operator-managed and business self-service export/restore is not available in this build.

The CLI backup/restore commands remain available for controlled installation-operator use. Automated command-level round-trip and hostile-archive tests remain supporting evidence, not proof of a real recovery drill. Tenant-scoped export/restore is implemented and automated-verified in the newer reconciliation above; the installation-wide operator recovery drill remains open.


## Offline transition and attachment hardening — 2026-11-04

- Phone-local attachment storage is now business-scoped; pending attachments are included in logout assessment; active-business attachment storage is cleared during local session cleanup.
- This is storage/lifecycle hardening only. Offline attachment upload/download remains **NOT IMPLEMENTED** in the active Work screen.
- DEC-019 retains the owner-approved business-trusted device model. Logout, expired sessions, same-business account changes, business switches, interrupted cleanup, late responses and remote device revocation still require acceptance testing.
- Exact-head Actions are pending. See `docs/ZAZU_OFFLINE_TRANSITION_AND_ATTACHMENT_HARDENING_2026-11-04.md`.

## V1 specification scorecard — 2026-11-04

The current evidence-maturity assessment is **3.0/5 (60/100)** across 13 V1 capability areas. This is an evidence score, not a percentage of features built. The detailed area-by-area score, method, blockers and execution order are recorded in `docs/ZAZU_V1_SCORECARD_2026-11-04.md`.

The product has a substantial connected commercial and operational core. It remains **NO-GO for commercial release** while real recovery/rollback, bounded offline acceptance, the offline account/session security challenge, legal/privacy operations and Zazu brand clearance remain open. Latest-head CI for `d1e2a4397b064d3fbfe7fc2cf1dc59236e00922f` was queued at assessment time.


## Exact source-head verification — 2026-11-04

Application source SHA `d1e2a4397b064d3fbfe7fc2cf1dc59236e00922f`: Laravel, Zazu quality, Psalm, PHPMD, populated upgrade/rollback and push-on-main checks passed. Browser smoke remains in progress; SonarCloud was skipped and is not counted as a pass. See run links in `docs/ZAZU_V1_SCORECARD_2026-11-04.md`. The documentation-only main commits after this source SHA do not alter application code. Do not close the browser gate until it passes.


## Phone-local Work attachment integration — 2026-11-07

**Source status: IMPLEMENTED. Runtime status: UNVERIFIED.**

The active authenticated Work attachment panel is now connected to the current business-scoped `ZazuOffline` client. The phone can atomically save a selection of up to 10 supported files (20 MB each) into its business-scoped IndexedDB attachment store, retain each UUID idempotency key and retry upload through the paired device API. The Work panel can read locally cached files while disconnected; paired devices also fetch and cache private server attachments for later offline access. The existing Laravel authenticated upload/download controls remain the online path.

The sync client keeps the local Blob until it receives a verifiable server attachment ID. Rejected or interrupted uploads remain on the phone with retry/error state. Logout assessment continues to include pending attachments; business-scoped local session cleanup continues to clear the attachment store. Uploads require a server-backed event ID. Pending records from the previous phone queue that retain `event_local_id` are now reconciled against `state.jobs` when the job receives its server ID, before upload is attempted.

Regression coverage now includes:
- Laravel source-contract assertions for the active offline attachment client and Work panel;
- Playwright scenarios exercising a real Work page, offline local file persistence, reconnect upload with the original idempotency key, remote attachment caching, and `event_local_id` reconciliation after offline Work identity resolution;
- existing server idempotency tests for duplicate retries and rejection of changed file content or event ownership;
- paired-device API tests for attachment-list metadata, private download success, foreign-business list denial and foreign-business private download denial (with the foreign file confirmed present).

The service-worker cache was advanced to `zazu-static-v30` because `/zazu-offline.js` is served cache-first. All source and test changes are on `main`; latest observed implementation/test SHA before the documentation update: `bd47ec672ea0f4b609614ce24c3baa9d1ac4bd0a`.

**Evidence boundary:** GitHub combined commit status returned no status contexts for the earlier `bc1de6224971803bdecee18dc8bcbed7781c5287` head; no Laravel, build or browser pass is claimed for the new source head. The added Playwright test and feature assertions remain unexecuted until exact-head CI is observed. Physical installed-device acceptance and disconnected Work-page availability remain unverified. The new browser regression covers attachment parent-ID reconciliation once the job has a server ID; the complete rendered create-offline-job → sync-job → attach-file user journey is still unverified.


## Trusted-device session transition coverage — 2026-11-07

The session-transition matrix now has additional source-level regression coverage:

- A Playwright scenario preserves a paired business workspace and queued mutation when the `/up` authentication-host probe is unreachable; after connectivity returns it completes logout, re-authenticates the same account and checks that the trusted business device and pending mutation remain available.
- Owner device revocation is tested against bootstrap, pull, push and attachment-list APIs using the formerly valid bearer token; cross-business owner revocation remains denied.

**Implementation/test status:** tests added on `main`; **execution status: UNVERIFIED**. The exact current-main Laravel and browser workflows must pass before the security/offline evidence score can rise. Physical-device testing, expired web-session acceptance, interrupted logout, stale in-flight response handling and the owner lost-device runbook remain outstanding.


## Recovery integrity hardening — 2026-11-07

New backups are now format version 2 and include SHA-256 checksums for the database snapshot and every private-storage payload file. Restore validates the exact payload/checksum file set and streams the payload hashes before extraction or installation mutation. Version-1 archives remain accepted for backward compatibility; new version-2 archives require the checksum manifest. Automated tests cover checksum generation and rejection of a modified private file without replacing the currently installed private probe.

**Implementation:** committed on `main`. **Verification:** exact-head Laravel/quality execution remains UNVERIFIED. Checksums protect against accidental corruption/content drift, not archive forgery; the manifest is not signed. The real operator backup → restore → record/media/access verification drill remains a release blocker, so recovery readiness stays at 2/5 and V1 remains NO-GO.


Backup compatibility coverage was extended after the initial integrity note: the test suite now verifies rejection of a version-2 archive with no checksum map and restores a valid version-1 archive without checksums. These cases are committed but remain unexecuted until exact-head CI is observed.


## CSP inline-style attribute reduction — 2026-11-10

- Removed the six dynamic image `style=` attributes from the public landing page. Their image URLs now flow through response-nonce-authorized CSS custom properties and class selectors in the landing document head.
- Removed the dynamic inline `style=` attribute from the admin landing-image preview. Its per-field preview rule now uses a response-nonce-authorized style element and a stable field-derived element ID.
- Replaced the workspace-recovery sign-out form's inline `display:inline` declaration with the `zazu-auth-inline-form` class in the application stylesheet.
- Added SecurityHeadersTest source assertions that these active Blade views contain no inline style attributes and that the landing/admin dynamic CSS uses nonce-authorized style blocks.
- Commits: `4c80e28` landing page; `40711e5` admin preview; `b51f15a` recovery form; `e6735d0` stylesheet; `f8a236a` regression assertions.
- Guardian review: source-level inspection confirms the three changed views have zero `style=` attributes. The legacy `public/offline-attachments.js` contains inline attributes but is not imported by the active authenticated layout; it remains a dormant legacy asset and was not reactivated or modified. `resources/js/app.js` also uses CSSOM `style.setProperty/removeProperty` for the floating helper's measured nav-avoidance position, so the policy's `style-src-attr 'unsafe-inline'` allowance is deliberately retained until that runtime behaviour is redesigned and tested.
- Verification boundary: changed files re-fetched; no PHPUnit, Blade compilation, browser run or exact-head CI execution is available here. No CSP relaxation was made, no readiness score uplift is claimed, and V1 remains **NO-GO**.

## Director reconciliation — 2026-11-14 (post-Codex source review)

- Repository `main` now includes documentation reconciliation commits `c505521c01c75050d12e11e514c3576a975d3fcd` and `e290ba5bfe05776439d4fcf9efbf61105de0be7f`; the reviewed application/test change remains `51da528532d6758bb997630e886948fabcf3ef3a`.
- The dashboard CTA theme-token correction is source-reviewed. Both light/dark token definitions exist in `resources/css/app.css`; the dashboard stylesheet and feature regression now use/assert the shared tokens.
- Compiled `public/build/` assets are generated by CI and intentionally ignored by Git. CI must rebuild and validate the manifest before this later CSS/test head can be marked green.
- **Exact-head CI status:** not confirmed for `51da528`. The recorded Laravel/asset-build PASS remains tied to `d09fdbfbe4bdbb7cfaed9ca1ea125313910fa3e` (run `43593454044`); do not carry that PASS forward.
- **Release remains NOT READY.** Physical Android installed-device acceptance, controlled installation recovery drill, live business-record quote/payment/profit reconciliation, and POPIA operational sign-off remain open. Zazu name clearance remains deferred by owner.
- No application runtime, offline engine, database, customer data, deployment, or non-main branch was changed during this review.

### Operational-document readiness detail

- **Recovery runbook exists:** `docs/RELEASE_BACKUP_RESTORE_RUNBOOK.md` contains Windows and PostgreSQL operator procedures, preflight, restore, post-restore checks and a pass/fail evidence list. The missing item is execution against an isolated copy of the intended installed environment, not another written procedure.
- **POPIA documents exist as drafts:** privacy notice, data-subject request procedure, retention/disposal schedule and incident-response runbook are present. They are not operationally signed off: public request/contact channels, Information Officer appointment/registration status, escalation route, real procedure test and legal/accounting review still need confirmation.
- **No self-service data-subject request portal is claimed.** Current manual procedure is the documented boundary; ordinary CRUD deletion is not a POPIA rights workflow.
- **Commercial proof remains operator-side:** seeded quote/invoice/deposit/final-payment/actual-cost/profit automation is already verified in the recorded source lineage. Do not re-run or expand seeded tests without a reproducible defect; the remaining acceptance is reconciliation using the owner's real business records.

## Director reconciliation — 2026-11-14 (offline workspace sync-state correction)

- **Current verified source/control head:** `7e2531583090ce2bfab2acea71dbfe0e7f3951f0` on canonical `main`.
- The offline dashboard workspace previously labelled every locally rendered Work row as `Pending sync`, even when the IndexedDB record already carried a server identity. This was misleading whenever pending work caused the workspace panel to appear alongside previously synchronized jobs.
- Corrected the row presentation: server-backed local jobs now show their actual job reference (with a safe ID fallback) and `Synced`; only jobs without a server identity show `Local reference / Pending sync`.
- Added a focused Playwright regression for a synced job coexisting with pending local work. The test verifies the reference label/value, synced state and absence of the false pending label.
- **Exact-head Laravel run [43634136062](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43634136062): PASS** — 496 passed, 1 skipped, 3,511 assertions; PostgreSQL migration, real registration, database backup/restore and private-file recovery smoke: PASS.
- **Offline browser acceptance:** 34 passed; mobile customer/Work acceptance: 3 passed; phone-local attachment journey: 1 passed. All three jobs passed on the exact head.
- **Quality/security:** application quality, Composer security audit and secret scan passed; Push-on-main / CodeQL passed; Director Health passed. SonarCloud was skipped and is not counted as a pass.
- The build produced `assets/app-zy7aGQjZ.js`; the committed manifest now references that exact generated asset.
- The offline workflow matrix is recorded in `docs/ZAZU_OFFLINE_FIRST_ARCHITECTURE.md`. It makes the boundary explicit: only Customers and Work have visible local directory renderers today; other domain mutation handlers do not imply complete local read/navigation parity.
- **Release remains NOT READY.** Physical Android cold launch/restart/reconnect, installed-target recovery drill, live business-record reconciliation and POPIA operational sign-off remain open. This correction does not resolve the documented cached-dashboard fallback architecture.
- No production deployment, customer data, live database or non-main branch was changed.

## Director reconciliation — 2026-11-14 (device-local quote summary)

- Added a Quotes section to the existing dashboard device-local workspace. It renders quote records already present in the business-scoped IndexedDB state, shows the related Work name where available, and displays the quote's sync state. It does not pretend to provide a complete quote directory or detail screen.
- The offline workspace now becomes visible when there is pending customer, Work **or quote** data. A quote summary therefore remains visible even when no customer/job mutation is currently pending.
- Added Playwright coverage for a locally stored draft quote linked to an already-synchronized local job. The test checks the draft label, parent Work name, currency and waiting-to-sync state.
- Application/test evidence: source/test commit `126efb6b4d1f832cc1e7f67db51e0124a962b5f7`, Laravel workflow [43634683863](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43634683863): **496 passed, 1 skipped, 3,511 assertions**; PostgreSQL migration/registration/backup/private-file recovery: **PASS**; offline browser: **35 passed**; mobile customer/Work: **4 passed**; phone-local attachment: **1 passed**.
- The same run confirms the new local quote summary and synced/pending job-label regression. Exact compiled app bundle from repeated CI builds: `assets/app-DILCeBlI.js`.
- Manifest alignment commit: `45d5929e615a480b1983ccd53c9892cd10b5fea2`. The latest application code/test changes are unchanged from the verified source/test commit; manifest-only follow-up is undergoing its own full workflow verification.
- **Scope boundary:** Customers and Work remain the only device-local directory renderers. Quotes now have a dashboard summary only; all other entity collections remain local data/queued mutation foundations without complete local directory/detail parity.
- V1 remains **NOT READY** pending physical Android acceptance, installed-target recovery drill, live commercial-record reconciliation and POPIA operational sign-off. No readiness score uplift is claimed.


## Commercial document sharing checkpoint — 2026-11-18

COM-01 is PARTIAL on `main`: signed public quote pages now support browser Print / Save as PDF; quote details hand off to the signed customer view through WhatsApp; issued/paid invoices have a seven-day signed, read-only customer view with WhatsApp handoff and browser Print / Save as PDF. Invoice-linked receipts now have the same signed print/share path. Full CI browser/mobile-viewport acceptance passes; physical-device acceptance and direct PDF-file attachment remain open. Consecutive invoice numbering is implemented as a business/month-scoped `INV-YYYYMM-000001` series across online issuance and offline sync; local offline records refresh to the canonical server number after sync. Legal/tax-adviser review remains open. DB-001 is DORMANT / MONITORING by current Founder report; the separate installation-recovery gate remains OPEN. V1 remains NOT READY.
