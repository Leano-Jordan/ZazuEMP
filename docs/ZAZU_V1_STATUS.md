## Director recovery integrity review — 2026-11-23

- Identified that installation backup/restore staging had been created directly under `storage/app`, where default POSIX modes and inherited Windows permissions could expose temporary database dumps and private files.
- Moved backup, restore, private-storage rollback and SQLite staging artifacts under `storage/app/zazu-backups/`. The Windows installer already applies a restricted ACL to this directory; POSIX staging is explicitly restricted to 0700 directories and 0600 files.
- Added permission regression coverage and an owner-facing warning that business `.zazu` archives are not password-encrypted.
- Restore still validates archive structure/checksums before mutation and retains database/private-media rollback paths. Existing verified backup files were not touched.
- **Updated 2026-11-23:** new business V2 `.zazu` exports are passphrase-encrypted using Sodium secretstream XChaCha20-Poly1305 and Argon2id. Installation-wide scheduled backups remain unattended plain ZIP files; off-disk copies must be stored in encrypted, access-controlled locations. Legacy plaintext V1 business archives remain importable for compatibility.
- **Verification:** latest source/test changes are committed and read back from main. Exact-head CI is not yet confirmed. No pass is claimed.

---

## Director execution checkpoint — 2026-11-23 — dependency and capability consolidation

**Repository:** `Leano-Jordan/ZazuEMP` · canonical branch: `main`  
**Latest capability regression source/test commit:** `3ca0b72e4a370f94b3e9c12f42fb8840ce20d12d`  
**Latest engineering state checkpoint:** `053182a1b5c37529a170af994d88210cb4674f81`

- Reconciled direct Composer/npm requirements with their lockfiles and refreshed the transitive licence inventory: 77 Composer runtime, 34 Composer development and 147 npm entries, all with declared licence metadata.
- Updated third-party notices to exact direct locked versions and pinned remaining floating workflow actions; PHPMD now uses the repository's pinned CodeQL v4.39.3 SARIF uploader.
- GitHub withdrew GHSA-fc5q-39xc-vjpf / CVE-2026-112366 on 2026-11-18. Removed the obsolete package-specific npm audit exception; strict high-severity npm auditing remains in place.
- Added regression coverage for Job-specific Decor, Photography and Transport guidance and for capability deactivation. Business-wide active focus and staff catalogue visibility update, while owner reactivation visibility and saved Job requirements remain intact.

**Verification boundary:** these changes are committed and read back from `main`; exact-head Laravel, PHPMD, formatting, browser and Zazu Quality results for the newest commits are not yet confirmed. No new CI pass is claimed.

**Release disposition:** V1 remains **NOT READY**. The remaining acceptance gates include physical Android disconnected cold launch/restart/reconnect and attachment opening, controlled full restore on the intended Windows installation, populated live-business commercial reconciliation, POPIA operational sign-off and Zazu name clearance. Automated browser/device emulation is supporting evidence, not physical acceptance.

**Next engineering direction:** recovery integrity review and controlled restore readiness, without touching the Founder’s verified backups or asking for repeated basic checks. Inspect the business archive and installation-wide restore boundaries, rollback safety, key/configuration handling, private media and post-restore validation; preserve existing backup files and data.

---

## Director execution checkpoint — 2026-11-22 — offline cache regression verified

**Exact source/test HEAD:** `605e1d69f33fc295138813c732f20b5c2c07234d` on canonical `main`.

- Corrected the offline-readiness contract regression after the service worker changed from trusting the in-memory priming list to verifying required routes in the authenticated dynamic cache.
- Exact-head Laravel workflow [45057702836](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/45057702836): **PASS**, including service-worker cache/session contract checks, full Laravel tests, coverage report, desktop/mobile offline browser acceptance, simulated host outage, phone-local attachment/reconnect and public/auth accessibility acceptance.
- Exact-head Zazu Quality [45057702839](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/45057702839): **PASS**.
- Exact-head PHPMD [45057702867](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/45057702867): **PASS**.
- Exact-head Director Health [45057702895](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/45057702895): **PASS**.
- Exact-head CodeQL push analysis [45057702987](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/45057702987): **PASS**.
- SonarCloud [45057703012](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/45057703012): **SKIPPED**, not passed.
- The stale offline-readiness test assertion was corrected in `605e1d69`; the exact-head Laravel workflow now passes. The earlier Laravel failure on `bf7683a` is superseded.
- This closes the current automated cache-readiness regression. It does **not** prove that the previously reported phone warning is absent on the installed device, nor does it close physical Android cold-launch/restart/reconnect acceptance.
- Recovery review confirms the codebase has separate business-scoped `.zazu` export/import and installation-wide operator backup/restore. Business archive tests cover allowlisted data, remapped IDs, private media, malformed/undeclared ZIP entries, trusted-device rejection and rollback after a mid-file-write failure. PostgreSQL and Windows recovery CI are supporting evidence only.
- **Still owner-operated:** independent off-disk backup and controlled full installation restore on the intended PC; physical Android offline launch/restart/reconnect and phone attachment; live populated-business quote-to-payment-to-cost/profit reconciliation; POPIA operational sign-off and ZAZU name clearance.
- **Release decision:** V1 remains **NOT READY**. No evidence-maturity score uplift is claimed. Do not ask the Founder to recreate populated records or re-pair the phone. Do not claim physical acceptance.

**Next engineering direction:** maintain the current green baseline and address any reproducible release-critical defect only. The remaining recovery, commercial, physical and legal/brand gates require the explicitly identified owner/operator evidence; automated CI must not be represented as a substitute.

---

## DB-001 disposition — Founder clarification (2026-11-22)

**DORMANT / HISTORICAL / NON-CRITICAL — not an active engineering task or V1 release blocker.** The Founder clarifies that the DB-001 occurrence under discussion happened while testing offline event creation and a calendar-related task. It has not recurred since the subsequent offline-architecture work. It is not a confirmed current database defect, and no root cause or fix is claimed.

Do not chase this historical anomaly, request diagnostic captures, or ask the Founder to run `Test-ZazuDatabaseSchema.ps1` or `Get-ZazuIncident.ps1`. Reopen only if the same failure is reproduced in a supported workflow on current `main`, or materially new diagnostic evidence appears. The separate installation backup/restore acceptance gate remains independent and must not be conflated with DB-001.

## Director recovery integrity checkpoint — secondary backup presence — 2026-11-22

**Implementation head:** [c992b98](https://github.com/Leano-Jordan/ZazuEMP/commit/c992b986497ca1c233437212d55b3e93ad29c455).

- Fixed stale secondary-backup health: the dashboard no longer trusts a recent verified timestamp alone. It now requires the recorded archive path to resolve to a regular non-symlink file whose size and last-write timestamp match the metadata written after SHA-256-verified copy.
- Missing drive/archive or changed metadata produces **ATTENTION**. SHA-256 is verified at copy time, not re-hashed on every dashboard request. Physical disk separation and cloud-provider upload completion still require operator verification.
- Laravel: **679 passed / 1 skipped / 4,671 assertions**; backup status regression passed. Zazu Quality, PHPMD, Psalm, PostgreSQL recovery, populated upgrade/rollback and Windows fresh-install acceptance: **PASS**. Windows PowerShell 5.1 runner metadata test: [44988789562](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44988789562) **PASS**. SonarCloud: **SKIPPED**.
- Exact-head offline-browser acceptance [44989474410](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44989474410): **63 desktop + 7 mobile viewport + 1 host-outage + 1 phone-local attachment/reconnect passed**; public/auth accessibility acceptance passed.
- Evidence maturity remains **77/100**. V1 remains **NOT READY** until owner-PC independent backup and full restore are demonstrated.

**DB-001 disposition:** dormant, historical and non-critical; it is not the next engineering target or a V1 blocker. The Founder confirms it occurred during offline event-creation/calendar-related task testing and has not recurred after subsequent offline-architecture work. Do not request diagnostic captures or run DB-001 scripts unless it recurs on current `main`. Installation backup/restore remains a separate operational gate.

---

## Director security review — offline session and trusted-device transitions — 2026-11-22

**Reviewed source/test head:** [8803746](https://github.com/Leano-Jordan/ZazuEMP/commit/88037465ddacbc4b60a81efe343e4210b6b1dc02). Exact-head Laravel/browser/PostgreSQL [44987764883](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44987764883) **PASS**.

- Laravel: **679 passed / 1 skipped / 4,665 assertions**.
- Offline browser: **63 desktop + 7 mobile viewport + 1 host-outage + 1 phone-local attachment/reconnect passed**.
- PostgreSQL migration/recovery, Zazu Quality, PHPMD, Director Health and Push-on-main: **PASS**. SonarCloud: **SKIPPED**.
- Audited expiry/re-authentication, logout failure, owner/manager transition, business switching, late bootstrap, re-pair token replacement, in-flight push/attachment responses, owner revocation and token expiry. Existing tests and generation/token guards cover these boundaries; **no new confirmed defect**.
- DEC-019 intentionally retains business-scoped trusted-device credentials across ordinary web-account logout. This is not per-staff offline isolation; owner revocation is the device-removal control.
- Evidence maturity remains **77/100**. V1 remains **NOT READY**; automated browser tests do not replace physical Android or lost-device acceptance.

**Next engineering target:** recovery integrity review — business archive export/import versus installation-wide backup/restore, including trusted-device safeguards and private media.

---

## Director acceptance checkpoint — offline expense identity isolation — 2026-11-22

**Source/test head:** [8803746](https://github.com/Leano-Jordan/ZazuEMP/commit/88037465ddacbc4b60a81efe343e4210b6b1dc02) on `main`. Exact-head Laravel + browser + PostgreSQL: [44987764883](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44987764883). Zazu Quality: [44987764894](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44987764894). PHPMD: [44987764850](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44987764850). Director Health: [44987764840](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44987764840). Push-on-main: [44987765335](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44987765335).

- Laravel: **679 passed / 1 skipped / 4,665 assertions**; `OfflineExpenseReferenceIsolationTest` passed.
- Offline browser: **63 desktop Chromium + 7 mobile viewport + 1 mobile host-outage + 1 phone-local attachment/reconnect passed**. Public/auth accessibility acceptance passed.
- PostgreSQL migration, rollback/reapplication, registration and backup/restore/private-file recovery: **PASS**.
- Zazu Quality (incremental Pint, application checks, Composer security audit and secret scan), PHPMD, Director Health and Push-on-main: **PASS**. SonarCloud: **SKIPPED**.
- Added an adversarial test proving corrupted business-local identity rows cannot bind an offline expense to a foreign event, supplier or purchase order. All three mutations remain pending, no expense is written, and foreign records remain unchanged. Existing resolver logic correctly fails closed; no application-code change was needed.
- Evidence maturity remains **77/100**; no score uplift is claimed. V1 remains **NOT READY** pending physical Android acceptance, intended-PC recovery, live-business commercial reconciliation, POPIA operations and ZAZU name clearance.

**Next engineering target:** continue the offline account/session transition adversarial review, preserving the approved business-trusted device model and durable local queue while verifying expiry, re-authentication and stale in-flight request handling.

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

**Superseded target:** archive restore rollback and cleanup on mid-write failure is now covered by an exact-head regression; see the checkpoint above.

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

**Superseded target:** trusted-device restore rejection is now covered by an exact-head regression; see the Director acceptance checkpoint above.

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

**Superseded target:** offline expense capture and reconciliation is now verified; see the subsequent expense acceptance record above.

---

## Offline quote acceptance and invoice sync — 2026-11-21

**Application/test source:** `36a2f605481e3985b647dda710ead7bef157b70f` on `main`.  
**Exact-head Laravel / browser / PostgreSQL workflow:** [44898943264](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44898943264).

- The disconnected quote workflow now has an exact-head regression for local quote acceptance, offline invoice creation, reconnect push, quote and linked Work revision acknowledgements, invoice server-identity reconciliation, retained quote-local relationship and durable queue drain.
- Corrected a test fixture that had acknowledged a different, bootstrap-seeded invoice ID instead of the newly created invoice's actual local ID. This was an F2/F3 test/fixture issue; no production source change was required.
- Offline browser: **60 desktop Chromium passed**; mobile viewport **7 passed**; phone-local Work/attachment journey **1 passed**.
- Laravel: **649 passed / 1 skipped / 4,456 assertions**.
- PostgreSQL migration, account/workspace registration, backup/restore and private-file recovery smoke: **PASS**.
- Zazu Quality, Composer audit, secret scan, application and Actions/Python/JavaScript analysis: **PASS**. Documentation-head Zazu Quality, Director Health, Director Contract Lint and Push-on-main also passed.
- CASE-ZAZU-0037 / ENG-FIXTURE-007: **CLOSED**.

**Evidence boundary:** the browser test uses deterministic mocked sync acknowledgements. It proves the offline client queue and acknowledgement reconciliation; it does not independently prove a live server invoice was created. The wider offline sync matrix and live-business commercial reconciliation remain OPEN. Physical Android cold launch/restart/reconnect, intended-PC recovery and POPIA operational sign-off remain separate gates. V1 remains **NOT READY**; no evidence-maturity uplift is claimed.

**Superseded target:** purchase-receipt acknowledgement reconciliation was implemented and its exact-head checks passed; see the later receipt acceptance record in this status history.

---

# ZAZU EMP — ENGINEERING ERROR INDEX

This is the normalized engineering diagnostic vocabulary for recurring failures.

## Two error layers

1. Runtime error catalog — user-facing codes such as DB-001, AUTHZ-001 and APP-001.
2. Engineering diagnostic codes — ENG-* codes used by Director to identify the actual failure mechanism, regardless of how the user-facing error is rendered.

Do not collapse these layers.

## Recognition rule

OBSERVE → SEARCH ERROR INDEX → LOAD CASE → FINGERPRINT → CLASSIFY F1–F8 → REVIEW HISTORY → DIAGNOSE → CORRECT → REGRESS → RECORD

A recurring symptom reuses its engineering code and CASE-ZAZU ID unless materially new evidence proves a different mechanism.

## Engineering code format

ENG-FAMILY-NNN

Codes are permanent once assigned. Never recycle a code.

## Core diagnostic families

| Family | Meaning | Typical signatures |
|---|---|---|
| SYNTAX | Source does not parse | unmatched delimiter, parse error, class discovery failure |
| SCHEMA | Runtime database differs from repository contract | missing table/column, migration drift, schema-version mismatch |
| ROUTE | Route registration/ownership/state mismatch | 404, wrong owner, inactive route, navigation dead-end |
| AUTH | Authentication/session failure | missing/expired session, auth-state mismatch |
| AUTHZ | Authorization/permission mismatch | wrong role, direct bypass, missing permission mapping |
| CONTRACT | Implementation and test/UI/API contract disagree | stale assertion, selector drift, response contract, semantic state |
| FIXTURE | Seed/test data violates a real contract | wrong type, UUID, default, sequence, invalid state |
| RUNTIME | Environment/runtime causes application behavior | extension mismatch, cache state, asset/runtime mismatch |
| CI | Workflow/runner execution issue | YAML, job, permission, runner-state failure |
| TOOL | Static analysis/tooling failure | PHPMD, Psalm, lint, dependency/tool version |
| JS | Client bootstrap/interaction defect | stale initializer, undefined function, event wiring |
| CSS | Visual rule ownership/cascade defect | duplicate declaration, cascade conflict, token drift |
| UI | Interaction/layout/visual state defect | hit-test, clipping, overlap, responsive state |
| DATA | Data/invariant corruption or unsafe mutation | duplicate mutation, ownership, partial write |
| RECOVERY | Backup/restore/update safety failure | restore path, rollback, incomplete recovery |
| FLAKE | Non-deterministic or load-sensitive outcome | timeout, race, concurrency, worker sensitivity |

## Current named failures

| Engineering code | Case | Name | F1–F8 | Status |
|---|---|---|---|---|
| ENG-SCHEMA-001 | CASE-ZAZU-0001 | Local schema migration drift | F4 | CLOSED |
| ENG-FIXTURE-001 | CASE-ZAZU-0002 | Offline/restore contract mismatch | F1/F2 | CLOSED |
| ENG-JS-001 | CASE-ZAZU-0003 | Stale shared JavaScript initializer | F1 | CLOSED |
| ENG-SYNTAX-001 | CASE-ZAZU-0004 | Restore command PHP parse failure | F1 | CLOSED |
| ENG-CONTRACT-001 | CASE-ZAZU-0005 | E2E accessible-name/label mismatch | F2 | CLOSED |
| ENG-FIXTURE-002 | CASE-ZAZU-0006 | Seeded idempotency key violates UUID contract | F3 | CLOSED |
| ENG-UI-001 | CASE-ZAZU-0007 | Closed inspector intercepts mobile input | F1 | CLOSED |
| ENG-CONTRACT-002 | CASE-ZAZU-0008 | Browser verification setup/locator drift | F2 | VERIFIED |
| ENG-FLAKE-001 | CASE-ZAZU-0009 | Parallel browser load timeout | F8 | CLOSED |
| ENG-ROUTE-001 | CASE-ZAZU-0010 | Navigation active-route ownership drift | F1 | VERIFIED |
| ENG-UI-002 | CASE-ZAZU-0011 | Calendar runtime lockout/reachability failure | F1/F2 | VERIFIED — browser suite green |
| ENG-CONTRACT-003 | CASE-ZAZU-0012 | PHPUnit 12-failure stale/brittle contract batch | F2/F7 | VERIFIED — current suite green |
| ENG-CONTRACT-004 | CASE-ZAZU-0013 | PHPUnit 269-pass/7-fail stale-checkout batch | F2/F7 | VERIFIED — current suite green |
| ENG-DATA-002 | CASE-ZAZU-0017 | Finance payment replay returns 422 instead of idempotent redirect | F1/F2/F3 | VERIFIED — current browser suite green |
| ENG-DATA-003 | CASE-ZAZU-0033 | Stale offline snapshot overwrites an intervening record update | F1 | CLOSED — exact-head paired-device, causal-chain, conflict and writer-audit evidence passed on 51a75eb |
| ENG-DATA-004 | CASE-ZAZU-0034 | Receiver bootstrap acknowledges an incomplete bounded snapshot | F1/F7 | CLOSED — exact-head CI verified on 559e66b |
| ENG-AUTH-001 | CASE-ZAZU-0035 | Bootstrap trusts a stale authenticated device snapshot after revocation or credential replacement | F1/F7 | CLOSED — exact-head CI verified on 559e66b |
| ENG-UI-003 | CASE-ZAZU-0018 | Calendar agenda hidden on desktop/tablet | F1/F2 | VERIFIED — current browser suite green |
| ENG-CSS-002 | CASE-ZAZU-0019 | Horizontal overflow at 1280px | F1/F2 | VERIFIED — current browser suite green |
| ENG-CSS-003 | CASE-ZAZU-0020 | Theme token state differs by browser/viewport | F1/F2/F4 | VERIFIED — current browser suite green |
| ENG-CSS-004 | CASE-ZAZU-0032 | Dashboard dark-theme token alias defeated by selector specificity | F1 | CLOSED — exact-head light/dark browser and CI verification passed |
| ENG-CONTRACT-005 | CASE-ZAZU-0021 | Quote latest-version assertion calls fresh on relation | F2 | OPEN — corrected, current-head CI pending |
| ENG-CONTRACT-006 | CASE-ZAZU-0022 | Stale service-worker cache test assertion | F2/F7 | VERIFIED — Laravel + Zazu quality passed on 3f5d1f1 |
| ENG-RUNTIME-002 | CASE-ZAZU-0023 | Offline launch relies on insecure PC-hosted LAN origin | F4/F7 | OPEN — delivery architecture and device proof required |
| ENG-FIXTURE-003 | CASE-ZAZU-0028 | MySQL restore rollback failure fixture emits escaped newlines | F2 | VERIFIED — corrected fixture executed in exact-head Laravel suite |
| ENG-FIXTURE-004 | CASE-ZAZU-0029 | Offline sync browser tests race boot-time registration and retry scheduling | F2/F3 | CLOSED — exact-head browser and Laravel verification passed |
| ENG-FIXTURE-005 | CASE-ZAZU-0030 | Offline startup readiness test accesses ZazuOffline before runtime exposure | F2/F3/F8 | CORRECTED — exact-head rerun pending |
| ENG-FIXTURE-006 | CASE-ZAZU-0031 | Offline customer edit fixture coupled to seeded/cached directory assumptions | F2/F3 | CLOSED — exact-head browser and supporting CI passed |
| ENG-FIXTURE-007 | CASE-ZAZU-0037 | Offline invoice sync acknowledgement targets the seeded invoice instead of the newly created local invoice | F2/F3 | CLOSED — exact-head browser/Laravel/PostgreSQL/quality verification passed |


## Latest browser failure fingerprints — 2026-10-06 — reconciled

- CASE-ZAZU-0016 through CASE-ZAZU-0020 are historical failure fingerprints. Owner-reported current-head browser execution is green; retain the fingerprints for recurrence detection and do not reopen without a new failing fingerprint.

**Rule:** capture exact response/DOM/computed-style/layout evidence before another patch.

## Known related mechanisms

Secondary codes may be attached when evidence shows they contribute to the same case:

- ENG-CALENDAR-001 — linked to CASE-ZAZU-0012 as the Calendar semantic active-state contract mechanism.
- ENG-CALENDAR-002 — linked to CASE-ZAZU-0011 as the corrected Calendar month/holiday rendering mechanism; CASE-ZAZU-0011 remains BLOCKED pending runtime evidence.
- ENG-CSS-001 — linked to CASE-ZAZU-0012 where canonical visual ownership was the affected contract.
- ENG-AUTHZ-001 — RESERVED; no confirmed current case linkage.
- ENG-RUNTIME-001 — RESERVED; no confirmed current case linkage.
- ENG-CI-001 — RESERVED; no confirmed current case linkage.
- ENG-DATA-001 — RESERVED; no confirmed current case linkage.
- ENG-RECOVERY-001 — RESERVED; no confirmed current case linkage.

These codes are not interchangeable with the primary case code and must only be attached when evidence supports them.

## F1–F8 mapping

| Class | Meaning |
|---|---|
| F1 | Application defect |
| F2 | Verification/test contract defect |
| F3 | Fixture/data defect |
| F4 | Environment/runtime state defect |
| F5 | CI/workflow defect |
| F6 | Tooling/static-analysis defect |
| F7 | Contract drift |
| F8 | Flaky/non-deterministic verification |

The F1–F8 class says where the failure originates. ENG-* says what mechanism it is.

## Fingerprint standard

Build a normalized fingerprint from:

target | layer | workflow phase | expected | actual | assertion/signature | relevant runtime/data state

A changed file, changed line number, or different wording does not create a new failure by itself.

## New-code procedure

When a confirmed mechanism has no existing code:
1. choose the narrowest existing family;
2. assign the next unused number in that family;
3. record the code in the case;
4. add the case to this index;
5. add a regression control when the mechanism can recur;
6. never silently repurpose an older code.

## Runtime catalog boundary

The runtime catalog remains authoritative for safe operator-facing errors. Engineering codes belong in internal diagnostics and repository records unless a deliberate product decision exposes them.
