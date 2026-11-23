# Zazu EMP — Director Widespread Engineering Audit

**Date:** 2026-11-23  
**Repository:** `Leano-Jordan/ZazuEMP`  
**Canonical branch:** `main` (no branches created)  
**Current repository HEAD:** `9646a87012c60f4562f4ce9f162cbbb99123a565` (documentation-only).  
**Verified application/test head:** `c57bbd7233a325ed34deb9129f93fa0c9f7b6236`  
**Disposition:** V1 remains **NOT READY**. The product foundation is substantial; recovery acceptance, offline physical proof, populated commercial reconciliation and legal/brand closure remain release gates.

## 1. Scope and method

Repository inventory at the audited head: **849 tracked files**, including 198 application PHP files, 46 controllers, 41 services, 79 migrations, 146 PHP tests, 10 Playwright E2E specs, 84 Blade views and 125 Markdown documents.

This was a broad repository/control review with deeper source inspection across:
- onboarding, business focus, capability catalogue and workspace profile;
- business archive encryption, upload, restore, tenant remapping, private media and installation recovery;
- finance, quote-to-invoice, payment, purchase-order receiving, inventory and event profitability;
- offline sync endpoints, trusted-device checks, mutation identity/revision handling and phone-local attachments;
- route/middleware boundaries, security/privacy controls, Windows launch/package workflows and CI definitions.

The audit also reconciled owner-reported installed-PC/linked-phone results and current GitHub Actions evidence. It is not a line-by-line manual review of every tracked file, a live production penetration test, or a physical-device certification.

## 2. Owner acceptance ledger — strike-off reconciliation

These are the owner-reported checks already completed. They are recorded as completed and must not be assigned back to the Founder:

- [x] Zazu starts on the installed PC and the database reports it is up to date.
- [x] Normal connected operation on the PC.
- [x] Normal connected operation on the linked phone.
- [x] PC and phone operate against the same workspace.
- [x] Fictional customers, quotes, invoices, suppliers, purchase orders and inventory records are populated; the Founder reports these areas function as intended.
- [x] Installation backup exists and was independently verified.
- [x] Business archive was downloaded and independently verified.
- [x] Onboarding was exercised and feedback was supplied; implementation was revised to capture business focus before catalogue setup and support multiple business activities.
- [x] An encrypted business archive restore was attempted against a disposable empty workspace. **Result: FAIL** (“archive failed to load”; subsequent SYS-002/429). This is a completed test attempt, not a passed restore gate. The original archive, passphrase and disposable target must be preserved; do not retry against the live business.

Still not recorded as accepted:
- [ ] Successful encrypted business archive restore on the intended installed environment.
- [ ] Full installation restore on the intended PC, including database, configuration/key handling, private media and post-restore workspace verification.
- [ ] Actual Android offline cold launch, host outage, offline write, force-close/restart and reconnect acceptance.
- [ ] Transaction-level reconciliation using the existing populated owner workspace from accepted quote through invoice, payments, actual costs and profit.
- [ ] POPIA operational sign-off and ZAZU name clearance.

The connected PC/phone passes do not imply offline cold-start/restart passes. No duplicate records, re-pairing, backup recreation or basic connected-startup repetition is required.

## 3. Confirmed defects found and corrections made

### REC-001 — Restore committed before its audit entry (P1, corrected)

**Defect:** `BusinessArchiveService::restore()` committed imported rows and files. `BusinessExportController` then wrote `business.archive_restored` after the service returned. If the audit insert failed, the request reported failure after the target workspace had already been populated. A retry would then be rejected because the target was no longer empty.

**Correction:** the archive service now accepts a post-restore callback and executes it inside the same database transaction as row/profile restoration. Any audit exception rolls back the database work; the service's existing failure path removes newly written files.

**Regression:** the encrypted restore flow installs a SQLite trigger that deliberately rejects the restore audit insert. The test asserts that no customer record remains in the target and that newly written private files are removed while the source file remains.

**Status:** CLOSED at code/automated-test level. The exact-head `c57bbd72` Laravel run passed 744 tests / 1 skipped / 5,230 assertions, including the forced audit-write failure rollback regression. This is a separately discovered source defect, not a proven explanation for the Founder's earlier upload failure.

### RUNTIME-001 — Windows PHP upload limits were implicit (P1, corrected at launcher level)

**Defect:** the Windows host launcher started PHP's built-in server without explicit `upload_max_filesize`, `post_max_size`, `max_execution_time` or `max_input_time` values, while the restore form accepted uploads up to 100 MiB. The effective limits were therefore inherited from whichever PHP CLI configuration the machine supplied. A lower `post_max_size` can cause PHP to discard the request before Laravel receives the uploaded archive.

**Correction:** the shared Windows host launcher now starts PHP with:
- `upload_max_filesize=100M`
- `post_max_size=110M`
- `max_execution_time=300`
- `max_input_time=300`

A Windows Ops contract check verifies these settings remain attached to the actual server argument list.

**Status:** Windows Ops workflow [45202625276](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/45202625276) passed on `9b95591`. The installed-PC archive failure's exact original cause remains unproven; successful physical restore after the updated launcher is still required.

### MAINT-001 — Onboarding and archive encryption classes exceeded maintainability limits (P2, refactored)

**Findings:** PHPMD identified `OnboardingController` class complexity 55 (threshold 50), `BusinessArchiveEncryptionService` complexity 67, and later `OnboardingController`'s 11 public methods (threshold 10).

**Corrections:**
- Extracted onboarding progress/prerequisite state into `OnboardingProgressService`.
- Moved business-profile onboarding actions into `OnboardingBusinessController`, keeping route names and user-facing paths unchanged.
- Extracted authenticated Sodium stream processing into `BusinessArchiveEncryptionStream`; `BusinessArchiveEncryptionService` now owns passphrase validation and private temporary-file lifecycle.
- Removed obsolete stream read/write helpers left in the encryption service after extraction.

**Verification:** PHPMD passed on `2d7b1146` [run 45205108169](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/45205108169). Final route wiring is retained on current `main`; exact-head Laravel and Zazu Quality passed on `c57bbd72`.

### MAINT-002 — Archive controller PHPDoc formatting (P2, corrected)

Pint rejected non-canonical array PHPDoc spacing in `BusinessExportController`. The docblocks and route import ordering were corrected; exact-head Zazu Quality passed on `c57bbd72`. No mass formatting of unrelated legacy files was performed.

### OFFLINE-001 — Customer metadata discarded when photo change is deferred (P1, corrected)

**Defect:** When an operator created or edited a customer offline while selecting a profile photo or requesting photo removal, the offline submit handler displayed a connection warning and returned before queuing the otherwise valid customer metadata. The warning told the operator to save the details first, but the details were not saved.

**Root cause:** The unsupported photo-change guard aborted the entire customer mutation instead of excluding only the unsupported file operation.

**Correction:** The handler now records that a photo change must be deferred, builds and queues the normal business-scoped customer metadata mutation, and omits both photo bytes and photo-removal intent. The local save notice explicitly confirms that customer details were saved and the photo change was not.

**Regression:** The offline browser journey selects a photo, submits the customer, verifies the metadata is in the local state and pending queue, asserts photo fields are absent from the mutation, and checks the operator-facing notice.

**Verification:** Exact-head `c57bbd72` Laravel workflow [45229069891](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/45229069891) passed: 744 tests, 1 skipped, 5,230 assertions; offline browser 64 desktop tests, quote single-unit acceptance, 7 mobile-viewport tests, host-outage acceptance and phone-local attachment journey all passed; PostgreSQL migration/recovery passed. Zazu Quality [45229070049](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/45229070049) passed. SonarCloud was skipped.

**Scope:** Offline customer text/contact metadata is saved; profile-photo upload/removal remains online-only. This does not claim offline image upload support.
## 4. Cross-domain review conclusions

| Area | Assessment | Remaining boundary |
|---|---|---|
| Onboarding and business focus | Multi-focus persistence and niche-relevant catalogue categories are implemented with feature tests. | Owner must accept the revised first-run experience; do not claim usability closure from tests alone. |
| Core event lifecycle | Event-centred model and lifecycle guards remain coherent. | Real operator acceptance of end-to-end job completion. |
| Commercial workflow | Automated quote acceptance, invoice conversion, deposits/final payments, actual costs and profit reconciliation exist. | Reconcile the existing populated owner workspace with actual values. |
| Purchasing/inventory | Receiving, idempotency, stock and event-cost linkage have regression coverage. | Physical stock/GRN reconciliation on the populated workspace. |
| Tenant/data integrity | Business-scoped queries, FK remapping, identity validation and adversarial isolation tests are present in reviewed paths. | No independent live-install penetration test is claimed. |
| Offline continuity | IndexedDB workspace, mutation queue, host-outage detection, reconnect and phone-local attachment journeys have automated coverage. | Actual Android cold launch, force-close/restart and disconnected write/reconnect remain unverified in the recorded evidence. |
| Recovery | Encrypted V2 business archives, legacy V1 compatibility, tenant-scoped remapping and Windows/CI recovery tests exist. Three defects were found and corrected as recorded above. | Original installed restore failure has not been physically cleared; full installation recovery remains a separate gate. |
| Privacy/legal | POPIA documentation, data minimisation guidance and operational templates exist. | Information Officer, real provider/operator inventory and terms, retention/disposal, operational exercises and legal approval remain open. |
| Brand | No software defect identified in this audit. | ZAZU name clearance remains open. |

No additional confirmed finance/tenant-isolation defect was established in the sampled controller/service/test review. That is not a claim that the entire product is vulnerability-free.

## 5. Evidence and CI disposition

| Evidence | Result |
|---|---|
| Laravel + desktop/mobile offline + PostgreSQL baseline on `e948f0a` | PASS — 743 passed, 1 skipped, 5,225 assertions; 64 desktop E2E + 7 mobile E2E; PostgreSQL migration/recovery passed — [run 45198671060](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/45198671060) |
| Windows Ops host-script checks including explicit PHP upload limits | PASS — [run 45202625276](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/45202625276) |
| Windows V1 package/fresh-install and encrypted/legacy archive acceptance | PASS — [run 45202614826](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/45202614826) |
| PHPMD after onboarding/encryption refactor | PASS — [run 45205108169](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/45205108169) |
| Director Health on final code head `620ab99b` | PASS — [run 45205116118](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/45205116118) |
| Exact-head Laravel on `c57bbd72` | PASS — 744 passed / 1 skipped / 5,230 assertions; [run 45229069891](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/45229069891) |
| Exact-head offline browser acceptance on `c57bbd72` | PASS — 64 desktop + quote single-unit + 7 mobile viewport + host-outage + phone-local attachment journey; same Laravel workflow |
| PostgreSQL migration/recovery on `c57bbd72` | PASS — same Laravel workflow |
| Zazu Quality on `c57bbd72` | PASS — [run 45229070049](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/45229070049) |
| Windows V1 package/fresh install on runtime-identical application code `51f809ce` | PASS — [run 45229065626](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/45229065626) |
| Director Health on runtime-identical application code `51f809ce` | PASS — [run 45229065739](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/45229065739) |
| SonarCloud | SKIPPED — not counted as a pass |

The earlier 743-test result is historical baseline only. The exact-head `c57bbd72` run now verifies the archive transaction change, maintainability refactors, offline customer metadata/photo boundary and the complete current browser acceptance suite.

## 6. Formal V1 scorecard

The canonical 13-domain method remains the formal scoring basis. Each domain is scored 0–5 for evidence maturity; it is not a percentage of features built. The previous 77/100 figure in the hardening notes was not independently recalculated under the canonical 13-domain method and must not be presented as the formal weighted V1 score.

| V1 domain | Score |
|---|---:|
| Platform, accounts, onboarding | 4/5 |
| Core lifecycle | 4/5 |
| Commercial workflow | 4/5 |
| Purchasing, receiving, inventory, costs | 4/5 |
| Data integrity and auditability | 4/5 |
| Security and tenant isolation | 4/5 |
| Offline continuity | 3/5 |
| Documents and attachments | 4/5 |
| Search, reporting and Zazu Helper | 3/5 |
| UX, mobile, tablet and accessibility | 3/5 |
| Recovery and data portability | 4/5 |
| Deployment, upgrade and rollback | 4/5 |
| Legal, privacy and brand clearance | 1/5 |
| **Total** | **46/65 = 3.54/5 = 70.8/100, reported as 71/100** |

**Score action: REAFFIRMED at 71/100.** No numerical uplift is awarded for code refactors or newly closed automated defects. Recovery remains 4/5 for its automated archive/installation evidence, but the failed installed restore attempt blocks release acceptance and prevents a 5/5 operational score.

## 7. Director decision and next execution order

**V1: NOT READY.** Do not widen feature scope.

1. [x] Exact-head Laravel, desktop/mobile offline acceptance, PostgreSQL migration/recovery and Zazu Quality passed on `c57bbd72`; Windows package/fresh install passed on runtime-identical application code `51f809ce`. SonarCloud was skipped.
2. [x] Reconciled the Founder-confirmed connected PC/phone, shared workspace, populated modules, migration check, backup verification, onboarding feedback and failed disposable archive restore into the release checklist. Do not repeat those checks.
3. Preserve the failed disposable archive restore evidence. Do not retry until the first failure stage is identified and the Founder is ready for a controlled retest; never use the live business as the test target.
4. Close the remaining owner acceptance gates: Android offline/restart/reconnect, populated commercial reconciliation, full installation restore, POPIA operations and ZAZU name clearance.

All changes in this audit were committed directly to `main`. No branch was created. No live business data, database or production environment was changed by this audit.

## 8. Current-main engineering update — 2026-11-23

**Code/test head:** `6226b5108ed3b125a795f739df68e55b010b5687`.

### Offline service image boundary

A service catalogue image selected while offline previously aborted the entire service mutation. The handler now queues supported service metadata, omits image bytes and displays an explicit deferred-image notice. CASE-ZAZU-0046 / ENG-JS-003 / REG-029.

The first browser regression run exposed a separate test-contract issue: the styled category radio's pointer path was blocked by sticky navigation/action chrome. The test now force-checks the native input while retaining the real submission and queue assertions. No production UI change was made for this test-only failure. CASE-ZAZU-0047 / ENG-CONTRACT-008 / REG-030.

Exact browser run [45235238220](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/45235238220): **65 desktop tests**, quote single-unit journey, **7 mobile viewport tests**, host-outage and phone-local attachment journeys passed.

### Customer contact search

Customer search previously matched the customer's identity/legal fields but not its linked contact records. It now searches active contact name, phone, email and label through the customer relationship. The query remains constrained by the active business; soft-deleted contacts are excluded. Regression coverage includes all four fields, deleted-contact exclusion and cross-business isolation. CASE-ZAZU-0048 / ENG-SEARCH-001 / REG-031.

Exact current-head Laravel [run 45236025738](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/45236025738): **745 passed / 1 skipped / 5,244 assertions**. PostgreSQL migration/recovery, Zazu Quality, PHPMD and Director Health passed. SonarCloud skipped. Current-head offline-browser and Windows package checks were still running at the time of this addendum.

### Score and release disposition

Formal 13-domain score remains **71/100 (46/65)**. No numerical uplift is awarded from these two engineering corrections. Search/reporting/Helper remains 3/5 pending broader V1 search and operational usability acceptance. Offline continuity remains 3/5 pending physical Android disconnected cold-start/restart/reconnect proof. Recovery remains 4/5 pending successful installed restore. Commercial workflow remains 4/5 pending the Founder’s populated live-record reconciliation. Legal/privacy remains 1/5 pending operational sign-off.

**V1 remains NOT READY.** The failed installed archive restore remains preserved; no restore retry or live-business operation was performed.


