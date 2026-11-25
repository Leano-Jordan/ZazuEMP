# Changelog

## 2026-11-25 — Repository cleanup, event-management identity and offline navigation correction

- Consolidated the Director identity across the remaining active capability contracts and owner-communication rules; marked DEC-002 superseded and removed the obsolete Morpheus command hierarchy.
- Superseded DB-001-first instructions in the development checklist, commercial backlog and 2027 hardening log; clarified that the SaaS architecture note is a future reference, not a hosting mandate. Retained dated material for traceability.
- Removed retired Render/Docker deployment files, the Docker-only Hadolint workflow, unused `public/offline-attachments.js`, generated placeholder tests, an obsolete Claude handover, and the vendor-media workflow that fetched third-party images and auto-committed them.
- Simplified `.ai/engineering/STATE.md` from roughly 331 KB to a concise live evidence register; retained detailed failure cases, decisions and dated audits as historical records.
- Reconciled product definition across README, product specification, Composer metadata, manifest, app footer and Director mandate: Zazu is an event management platform, with service types as configurable capabilities rather than a catering-only category.
- Corrected stale operating guidance that still treated DB-001 as an active blocker and named Morpheus as the controlling engine; one accountable Director remains.
- Fixed the Pint incremental workflow to ignore deleted PHP paths. Removed the CSS rule that hid the offline workspace's own navigation; browser and feature regressions now require Customers, Jobs and Quotes navigation to stay available offline.
- Laravel run [45651931394](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/45651931394): **761 passed, 1 skipped, 5,443 assertions**; offline browser acceptance **93 passed**; PostgreSQL migration/recovery smoke and coverage passed.
- Windows package run [45651931423](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/45651931423): package build, encrypted/legacy archive tests and Windows PowerShell fresh-install/launcher check passed.
- Current-main Zazu Quality [45653237320](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/45653237320), Director Contract Lint [45653237123](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/45653237123), and CodeQL [45653236491](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/45653236491) passed. Psalm/PHPMD analysis steps ran, but their SARIF publication jobs failed; SonarCloud remains skipped. These are not independent penetration-test results.
- Automated offline browser navigation is closed; physical Android Phase 2, the failed owner archive restore, intended-PC full recovery, commercial reconciliation, POPIA operations and ZAZU name clearance remain open.

## 2026-11-25 — Offline Dashboard navigation and pairing scope

- Removed the unrequested phone pairing-code entry field from the shared app layout and removed its Dashboard/mobile form handler.
- Kept phone pairing owner-controlled in Settings → Phone sync; the eight-digit code contract and server authorization were not removed.
- Reused the Dashboard controller's business-scoped permission snapshot for the offline workspace navigation.
- Corrected offline browser/feature-test contracts for the Jobs title and Settings-only pairing controls.
- Exact-source result on `95975c61215373b5796e1adcdfddd70370fc91b5`: Zazu Quality, Hadolint, PHPMD, Director Health, PostgreSQL migration/recovery smoke and Windows V1 package/fresh install passed. Laravel reported 762 passed / 1 skipped / 5,445 assertions but offline browser acceptance failed on missing Customers and Quotes navigation links. SonarCloud was skipped. The subsequent docs-only main commit `52f81df` passed Zazu Quality and Hadolint; this does not close the application browser failure or physical Android acceptance.

## 2026-11-25 — Dashboard pairing prompt removal and offline acceptance corrections

- Removed the unsolicited 8-digit **Pair this phone** field from the shared app shell so it no longer appears on the Dashboard. The owner-side Phone sync controls and server-side pairing authorization remain unchanged; pairing is not being reopened as a V1 scope item.
- Removed the app-shell-only prompt handler and updated the feature contract to prevent the field from silently returning.
- Corrected offline browser expectations to match the real route-specific screen: the offline Dashboard is titled **Offline workspace**, a cached Work detail URL resolves to the Jobs offline view, and the outage notice does not show a redundant Dashboard link when already on Dashboard.
- Scoped the Customers heading assertion to the page title, avoiding collision with the Customers collection heading.
- Made quote acceptance wait for the actual offline transition and visible Quotes navigation before clicking.
- Exact-head CI is pending. These test-contract corrections do not certify physical Android offline acceptance.

## 2026-11-23 — Encrypted business archive V2

- Added owner passphrase-protected business archive export and restore using streaming Sodium secretstream XChaCha20-Poly1305 with Argon2id key derivation.
- Added authenticated chunk/final-tag verification, private temporary decryption, passphrase confirmation, independent export/restore rate limits and backward-compatible import of plaintext V1 business archives.
- Added Sodium to the Windows installer prerequisite check and all PHP acceptance workflow extension lists.
- Kept installation-wide scheduled backups unattended and unencrypted; documented encrypted off-disk storage as an operator requirement. The runner verifies SHA-256 copy integrity but does not encrypt or attest secondary destination encryption.
- Added multi-chunk, wrong-passphrase, tamper, legacy compatibility and controller flow regression coverage.

## 2026-11-21 — Offline sync authorization and recovery hardening

- The installed offline runtime now checks the Zazu host while Wi-Fi remains connected, surfaces a non-destructive outage notice and links to the cached local workspace.
- Backup and restore now share archive-entry and 2 GiB uncompressed-size limits; backup verifies the complete ZIP, including its manifest, before publishing.
- Restore now replaces private storage from the manifest even when the backup's private directory is empty, preventing stale files from surviving a restore.
- The sync push applier now revalidates the original device credential in the domain-application transaction, closing the gap between recording and applying an offline mutation.
- Added desktop/mobile outage acceptance, backup-limit boundary tests and an empty-private-storage restore integration regression. Exact-head run 44937037910 passed 673 Laravel tests (1 skipped, 4,575 assertions), PostgreSQL recovery smoke and desktop/mobile browser acceptance.
- Windows V1 package workflow now also verifies populated private-media recovery and an empty-private-storage restore that removes stale target files; package, Windows PowerShell fresh install and both recovery cases passed in run 44937732880.

- The authenticated mutation applier now checks the original credential and mutation ownership inside the domain-application transaction; conflict recording, bootstrap, pull and acknowledgement share the same business-first/device-row credential guard.
- Added a stale-credential conflict-write regression and strengthened attachment reload acceptance to verify offline state, durable pre-reload storage and business-scoped IndexedDB continuity.
- Exact-head [04db872](https://github.com/Leano-Jordan/ZazuEMP/commit/04db8722a66ff8c97d62fef855f7ba07073934c7): Laravel **675 passed / 1 skipped / 4,581 assertions**; PostgreSQL migration/recovery, 63 desktop + 7 mobile + 1 host-outage + 1 phone-local attachment/reconnect browser journeys, PHPMD, Psalm, Zazu Quality, Director Health, populated upgrade/rollback, Windows package/fresh install and Push-on-main passed. No browser retries or flaky tests. SonarCloud skipped.



## 2026-11-21 — Windows scheduled backup runtime acceptance

- Windows Ops CI now creates and inspects a real temporary Task Scheduler task under Windows PowerShell 5.1, verifying S4U/limited privileges, daily 02:00 trigger, catch-up/wake settings and overlap protection.
- The same workflow runs the backup runner against a temporary fixture, checks 14-archive local and secondary retention, SHA-256 copy equality, and preservation of the previous verified timestamp when the secondary drive is unavailable.
- [Windows Ops workflow 44941196865](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44941196865) passed on [db5f25e](https://github.com/Leano-Jordan/ZazuEMP/commit/db5f25e1f6b54fb28909738508ef935792a419cb). The intended owner PC's real scheduled run, off-disk copy and controlled restore remain open.



## 2026-11-21 — Transactional trusted-device authorization

- Added a shared transactional device guard that locks business then device, rechecks active status and verifies the authenticated token hash against persisted credentials.
- Applied the guard to conflict listing/resolution and attachment upload/list/download so revocation cannot race these operations after middleware authentication.
- Device revocation now rejects a stale authenticated snapshot if the installation has already been re-paired with a replacement credential.
- Added five focused stale-device authorization regression tests. On code head `7d4b097`, Laravel passed **670 / 1 skipped / 4,552 assertions**, PostgreSQL migration/recovery, PHPMD, Psalm, populated upgrade/rollback and Director Health passed. Offline-browser acceptance passed: **62 desktop Chromium**, **7 mobile viewport**, and **1 phone-local Work/attachment/reconnect** journey. Windows V1 package passed. SonarCloud was skipped.


## 2026-11-21 — Receiver sync completeness and bootstrap authorization

- Fixed a receiver reconciliation defect where a delivered aggregate outside a bounded bootstrap collection could be omitted from the local snapshot while its delivery cursor was still acknowledged.
- Receiver bootstrap now accepts the exact delivery sequence prefix, resolves primary and related aggregates for the authenticated business/device, and includes those records before acknowledgement.
- Normal bounded collections prioritize recently revised aggregates while keeping their existing display order and 500-record working-set limit.
- Preserved explicitly pinned pairing selections; delivery-driven Customer/Work/Quote/Preparation entries cannot silently broaden a restricted workspace.
- Hardened bootstrap against stale device-authentication snapshots: business-first and device-row locks, active-status revalidation, persisted token-hash comparison and 401 rejection after revocation or credential replacement.
- Advanced the precached offline client to service-worker cache v58.
- Exact-head source: 559e66b87de8617f967b722b55b0fbfd9b116495. Laravel: 632 passed / 1 skipped / 4,388 assertions; desktop Chromium: 59 passed; mobile viewport: 7 passed; phone-local attachment journey: 1 passed; PostgreSQL recovery, PHPMD, Zazu Quality, Composer audit, secret scan, Director Health and Push-on-main: PASS. SonarCloud skipped.
- V1 remains NOT READY; evidence maturity is 75/100 (+2). Physical Android restart/reconnect, intended-installation recovery, live-business financial reconciliation, POPIA controls and the wider offline sync matrix remain open.


## 2026-11-17 — Job date rules, safer Windows startup and plain English

- Centralised job date/time relationship checks for online saves and offline sync. Invalid date order is rejected; multi-day jobs may end at an earlier clock time on their final day.
- Reused the same date/time fields on job creation and editing, with clearer labels, short explanations and changing date/time limits in the form.
- Added regression coverage for online/offline date conflicts and the form's labels and date limits. Exact-head CI is pending.
- Added a product-wide plain-English writing standard and simplified the DB-001 and account-error screens.
- Windows startup now checks for pending database migrations. It creates and verifies a full database/private-files backup before applying updates, then verifies the migration status. Startup stops if any safety step fails.
- Owner-reported DB-001 remains open. Local schema drift is the leading hypothesis; the actual database error has not yet been confirmed from the installed application.

## 2026-11-17 — Dashboard monthly payments KPI

- Added a live “Payments received (this month)” dashboard KPI for users with finance access. It sums recorded payment rows for the active business, the current calendar month and that business's configured currency; it does not mistake quote totals or unpaid invoices for cash received.
- Kept “Next 14 days” as the fallback KPI for users without finance access who have calendar access. Added regression coverage for business, currency and month boundaries.


## 2026-11-17 — Windows recovery proof and frontend dependency advisory

- Extended the Windows V1 package workflow to perform a Windows-native SQLite and private-media backup/restore round-trip. The CI drill creates disposable sentinels, backs them up, mutates both, restores the archive and verifies the original database value and file bytes.
- Windows V1 Package run 82 at fd82f6a passed the package build, PowerShell fresh installation and the new recovery drill. This is supporting CI evidence; the owner-run recovery drill on the intended installation remains open.
- The quality workflow now fails at npm audit because GitHub advisory GHSA-fc5q-39xc-vjpf / CVE-2026-112366 flags picomatch versions through 4.0.7. The advisory currently lists no patched version. The affected packages are in the development/build toolchain; no advisory suppression or breaking Vite downgrade was applied pending a verified fix.
- Confirmed that LAN asset delivery fixes already in main post-date the supplied 13 Nov phone screenshot: remote requests bypass stale Vite hot URLs and receive compiled same-origin CSS/JS; Windows LAN startup validates compiled assets. The current HTTPS physical-device check remains open.


## 2026-11-17 — Rental asset date availability verified
- Closed the serialized-asset booking defect: non-overlapping periods can be booked while the asset has other active allocations; overlapping and open-ended conflicts are rejected.
- Added per-allocation return handling, end-date entry and consistent asset status across online and offline mutation paths.
- Verified online/offline regression coverage and exact-head CI at 00af9adda: 523 passed, 1 skipped, 3,653 assertions; desktop/mobile offline and phone-local attachment acceptance passed; Windows fresh-install candidate passed.
- Physical return remains manual; event completion or a passed date alone does not prove equipment has been returned.

## Unreleased — 2026-11-17 (Normalize optional allocation end date)
- Normalized an omitted allocated_until value before date-order validation, overlap checking, persistence and audit to prevent the open-ended booking path from returning HTTP 500.
- Exact-head CI pending; regression remains unchanged.

## Unreleased — 2026-11-17 (Nullable open-ended allocation handling)
- Persisted and audited the normalized nullable allocation end date when the optional field is omitted, preventing an HTTP 500 on open-ended bookings.
- The failed online regression remains intact; exact-head CI pending.

## Unreleased — 2026-11-17 (Regression test maintainability)
- Split online and offline open-ended allocation regression coverage into focused methods after PHPMD flagged the previous test methods over the 100-line limit; no threshold or test was weakened.
- Exact-head CI pending.

## Unreleased — 2026-11-17 (Follow-up test correction)
- Corrected the fully-qualified HttpException namespace in the open-ended allocation regression test; exact-head CI remains pending.

## Unreleased — 2026-11-17 (Open-ended rental overlap hardening)
- Extended online and offline allocation overlap checks so an allocation without a return date is treated as open-ended and cannot overlap a later booking.
- Added regression coverage for bounded non-overlapping bookings, overlapping bookings, independent returns and open-ended overlap protection.
- Exact-head CI for this follow-up is pending.

## Unreleased — 2026-11-17 (Rental allocation correction candidate)
- Reproduced the non-overlapping asset booking rejection (HTTP 422) at c7be735; added online and offline regression coverage.
- Enabled multiple non-overlapping date allocations for serialized assets, added allocation end-date entry, per-allocation return handling and consistent asset status while other bookings remain active.
- Kept physical return operator-confirmed; no automatic release on event completion or date passage.
- Exact-head CI for this correction is pending; no readiness score uplift.

## Unreleased — 2026-11-17 (Rental and invoice-number reproduction)
- Added a focused regression test for two non-overlapping bookings of one serialized rental asset; exact-head CI is pending before defect confirmation or correction.
- Added an assertion documenting the existing month-prefixed random online invoice-number format; offline UUID numbering remains unchanged pending SA tax-adviser guidance.

## Unreleased — 2026-11-17 (Director audit reconciliation)
- Reconciled Director state, readiness register and V1 release checklist to live main HEAD ac795437 and exact-head CI evidence.
- Archived prior long-form state, readiness register and memory snapshots without discarding history.
- Recorded Claude's eight findings in the existing readiness register; no duplicate backlog or scorecard.
- Confirmed root ROSCORE_PROJECT_MANIFEST.md as canonical; docs/ROSCOR_PROJECT_MANIFEST.md is now a legacy pointer.

## Unreleased — 2026-11-17

- Corrected offline purchase-order decimal handling to use integer-cent arithmetic and reject precision loss.
- Added explicit business permission gating for offline bootstrap and corrected staff/manager work-delete authority.
- Removed the generated Vite manifest from source control; build assets are generated by installation and CI.
- Built frontend assets in PostgreSQL acceptance before exercising dashboard registration and recovery.
- Reconciled offline V1 scope resumption and current exact-head acceptance evidence.
- Added a device-local customer relationship view with offline customer/contact/billing edits, linked Work/quote visibility, durable queued updates and acknowledgement-based sync status; covered by Playwright acceptance.
- Added device-local Work inspection across customer, services, quotes, preparation, costs, purchase orders and invoices, with desktop/mobile offline acceptance.
- Added queued offline Work edits for job name, date, venue/address and notes, while preserving lifecycle status and customer identity.
- Removed the unrelated Render-labelled Docker preview workflow; no Render deployment is part of Zazu.


## 2026-11-16 — Windows V1 installation candidate and commercial architecture research

- Added a Windows first-run installer and one-click installation launcher. It verifies PHP 8.4+ and required extensions, creates a private environment only when absent, generates the application key and runs pending migrations without demo seeding.
- Added a production ZIP build containing production Composer dependencies, compiled Vite assets, Windows startup helpers, customer installation instructions and operator runbooks; the package excludes `.env`, databases, private uploads, internal engineering files and test suites.
- Added SHA-256 checksum generation and a 30-day GitHub Actions artifact.
- Kept the loopback Vite development path separate from the LAN/compiled-assets startup path.
- Added current commercial architecture research covering Tripleseat, Event Temple, Perfect Venue, HoneyBook and CaterZen patterns, mapped to Zazu's event-centred model.
- Package status is an installable fresh-install candidate, not a hosted SaaS release or an in-place updater. Physical-device acceptance, installed-target recovery, live-business reconciliation and POPIA operational sign-off remain release evidence gates.

## 2026-09-27 — Development hardening
- Added business-scoped idempotency protection for invoices, payments, expenses, purchase orders and stock movements.
- Added operational audit logging and owner Activity Audit view.
- Added centralized staff permissions with server-side route enforcement for finance, purchasing and inventory.
- Hardened purchase-order receipt concurrency and parent/child integrity checks.
- Added SQLite/MySQL backup and restore commands covering private storage.
- Added Playwright registration-to-dashboard browser smoke coverage and CI workflow.


All notable Zazu EMP project changes will be recorded here.

## 2026-09-27

### Security / Correctness Sweep

- Revalidated quote status transitions after row locking to close a concurrent state-change race.
- Serialized primary customer-contact promotion per customer to prevent multiple primary contacts under concurrent writes.
- Revoked prior database-backed sessions after password reset and enabled Laravel session-integrity middleware for authenticated application routes.
- Moved business branding uploads and delivery to private storage.
- Added an upgrade migration to move existing business-branding files out of the public disk.
- Updated privacy, development-checklist and V1 status records to reflect the private branding boundary.
- Recorded the repository-wide sweep in `docs/ZAZU_SWEEP_AUDIT_2026-09-27.md`.

## 2026-09-26

### Fixed / Updated

- Added a conditional Work event-schema repair migration for existing databases missing `events.deleted_at` and/or the Work night-contact column.
- Fixed Work creation code that referenced `$event` before the event record existed.
- Restored the Work Edit night-contact selector so the matching JavaScript and submitted field are present together.
- Added photography/Photographer and camera-hire capability direction for event and wedding workflows.
- Corrected project ownership records so Isaac Junior Lehlogonolo Maluleka is the current solo developer and owner.
- Recorded Rosscore Labs as a future business identity only, pending registration and any later written IP transfer.
- Added the commercial/legal protection register and event-media privacy controls.
- Populated the third-party licence register with top-level dependency information and a release-level transitive-audit gate.

## 2026-09-23

### Updated

- Established `memory.md` as the living Zazu EMP project context and bootstrap document for future project sessions.
- Recorded Rosscore Labs Pty Ltd as product owner and Isaac Junior Lehlogonolo Maluleka as founder, company owner, creator and lead developer.
- Recorded the multi-business product direction across catering and event-related services.
- Recorded real operator discovery findings from Sindi Sithole.
- Recorded desktop/laptop and mobile field-use requirements.
- Recorded workspace-first UX and reduced-primary-navigation direction.
- Recorded the current R0 budget constraint and free/open-source-first infrastructure evaluation.
- Recorded role-based access and business-data isolation as foundational requirements.
- Recorded the current Laravel/Livewire/MySQL technical direction as a proposal subject to owner approval.
- Updated README, IP ownership and licence notices to match current project reality.

## 2026-09-22

### Added

- Repository ownership and IP-control documentation.
- Proprietary project licence.
- Security policy.
- Contribution controls.
- Third-party licence register.
- Project memory.
- Project Genesis framework.
- Human-First Discovery framework.
- RossCore Engineering Command Engine framework.

## 2026-09-26

### UX / Architecture hardening cycle

- Reworked operational list hierarchy so page titles, section labels, column headers, records and row actions are visually distinct.
- Replaced avoidable typed currency/category/unit fields with guided choices and centralized configuration.
- Added a business default currency setting and reused it in quote, cost and travel workflows.
- Added Work quick-view workload navigation and overdue preparation visibility.
- Centralized active business resolution and added model-level business ownership protection.
- Scoped affected operational queries and writes to the active business context.
- Added lifecycle rules preventing invalid Work status movement and mutation of closed Work records.
- Prevented downstream requirements, quotes, costs, travel and preparation changes after Work closes.
- Hardened quote monetary arithmetic around integer cents and historical snapshots.
- Moved quote construction and revision logic into a transaction-aware QuoteService so Work requirements, quote versions and historical snapshots stay aligned.
- Added stale-quote detection and an explicit draft-revision review/edit workflow when Work services change.
- Attached currency to reusable catalogue prices and prevented catalogue defaults from silently crossing quote currencies.
- Added exact two-decimal validation for requirement quantities and commercial prices.
- Fixed catalogue service selection so choosing a saved service does not clear its capability binding.
- Kept customer profile-photo replacements on private local storage, with cleanup of legacy public paths.
- Added regression coverage for quote revision integrity, currency-aware pricing, exact Money arithmetic, private media replacement and route integrity.
- Added BusinessIsolationTest coverage for cross-business access, workload isolation and lifecycle controls.



- Backup creation now reopens and validates the completed ZIP, manifest, payload membership and SHA-256 checksums before publishing it; a regression covers tampered private payload rejection.
