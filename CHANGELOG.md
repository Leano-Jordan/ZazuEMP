# Changelog

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
