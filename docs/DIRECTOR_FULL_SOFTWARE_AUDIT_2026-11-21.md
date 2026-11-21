# Zazu EMP — Director Full Software Inspection & V1 Commercial Readiness Audit

**Assessment date:** 2026-11-21  
**Repository:** `Leano-Jordan/ZazuEMP`  
**Branch:** `main` (source of truth)  
**Audited repository head:** `a5561f90df9f54f426300b296cac9ca3abab9dd8`  
**Latest documented application/test head:** `2d0269b00a3a94ecfb392f04c06438ece6eda603`  
**Disposition:** **V1 NOT READY**  
**Evidence maturity:** **77/100** (existing Director score; not independently recalculated here)

## 1. Scope and exclusions

Reviewed product specification, V1 scope/readiness documentation, offline architecture and workflow matrix, release checklist, QA matrix, security/recovery runbooks, recent commit history, current GitHub Actions results, selected Laravel sync/security code, test configuration, and public competitor/review evidence.

Explicitly excluded: Render, hosting procurement, storage procurement, logo/brand design, photography and launch artwork. No production deployment or customer data was changed. No branch was created.

This is a risk-led repository inspection and evidence review, not a claim that every line in all 912 tracked paths has undergone manual line-by-line review or that an independent penetration test has been performed.

## 2. Current engineering evidence

The latest application/test source baseline documented in `docs/V1_RELEASE_CHECKLIST.md` is `2d0269b`:

- Laravel: **649 passed / 1 skipped / 4,456 assertions**.
- PostgreSQL migration and recovery smoke: PASS.
- Offline browser acceptance: 59 desktop Chromium, 7 mobile viewport and 1 phone-local Work/attachment/reconnect journey passed.
- Windows V1 package/fresh-install acceptance: PASS.
- PHPMD, Psalm, Zazu Quality and populated upgrade/rollback: PASS.
- SonarCloud: SKIPPED; it is not evidence of a successful scan.

The subsequent repository head `a5561f9` is documentation/control-plane reconciliation. Its Director Contract Lint, Zazu Quality, Director Health and Push-on-main workflows passed. These documentation workflows do not replace a new application test run.

Evidence links:
- [V1 release checklist](https://github.com/Leano-Jordan/ZazuEMP/blob/main/docs/V1_RELEASE_CHECKLIST.md)
- [Laravel run 44892813216](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44892813216)
- [Psalm run 44892813233](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44892813233)
- [PHPMD run 44892813263](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44892813263)
- [Windows package run 44892813237](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44892813237)
- [Current-head Director Contract Lint](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44893188267)

## 3. Confirmed product and architecture strengths

- Laravel modular monolith with established business-domain workflows rather than a collection of disconnected CRUD modules.
- Transactional/idempotent domain mutation paths, business scoping, audit events, revision-aware synchronization, stale-write rejection and conflict preservation.
- IndexedDB workspace and durable offline mutation/file queues, paired-device sync and session-generation boundaries.
- Tenant-scoped business archive export/import distinct from installation-wide operator backup/restore.
- Windows installation package, daily backup scheduling, missed-run catch-up, retention, backup health warnings and optional verified secondary-copy support.
- Populated upgrade/rollback and PostgreSQL migration/recovery smoke.
- Extensive Laravel feature tests, Playwright offline coverage, static analysis, dependency audit and Windows package checks.

## 4. Release-blocking findings

### P0 — Physical installed Android acceptance is still unproven

Browser emulation is not installed-device evidence. The release runbook still requires cold launch from the installed app icon with PC/server and internet unavailable, force-close/reopen, durable local records/attachments, and reconnect with exactly-once synchronization.

**Exit evidence:** actual supported Android device/browser, secure origin, offline launch and second launch, local create/edit, attachment persistence, reconnect, no duplicate records, no lost queue entries, and captured results.

### P0 — Actual owner-PC recovery drill is still open

The daily backup implementation is present, but the intended installation has not yet demonstrated a complete restore. Same-disk archives are not protection against disk failure.

**Exit evidence:** scheduled backup observed on the actual owner PC; archive copied off-disk; restore into a controlled target; database and private files verified; representative business records and relationships checked; operator steps and elapsed recovery time recorded.

### P0 — Live-like commercial reconciliation is still open

Seeded tests prove a useful quote/invoice/payment/cost calculation path. They do not prove the operator's real records reconcile end to end.

**Exit evidence:** one populated business scenario reconciled across accepted quote/version, tax, invoice, deposits, partial/final payments, purchase orders, receiving, actual costs, stock effects, event contribution/profit and exports. Compare source records and rendered documents, not just aggregate totals.

### P0 — Trusted-device authority boundary needs an explicit release decision

The current design records the owner who approved a trusted device, but a trusted device is a shared business-scoped workspace and normal staff permissions are not rechecked for each offline mutation. Audit attribution is therefore the approving owner, not necessarily the human operating the device.

This is documented, not a newly discovered defect. It is a material commercial/security limitation if staff are expected to use shared paired devices.

**Exit evidence:** either restrict V1 trusted offline devices to owner-controlled use and disclose the limitation clearly, or implement and test per-user device identity/authorization and audit attribution. Do not imply per-staff accountability where it does not exist.

### P0 — Offline V1 scope is not yet fully proven

The offline architecture matrix correctly distinguishes local mutation handlers from complete local read/render/navigation. The current client remains a hybrid cache-and-queue experience, not a complete disconnected replacement for every Laravel screen.

Known boundaries include:
- Customers and Work have the strongest local directory/inspection coverage.
- Quotes have a local dashboard detail panel and queued acceptance, but not a complete independent quote register/route.
- Several modules have local collection panels, while many domain forms and full detail routes remain server-rendered or unavailable offline.
- Invoice creation depends on a locally available accepted quote.
- Some media changes remain online-only.
- The complete bidirectional sync matrix and consequential workflow reconciliation remain open.

**Exit evidence:** freeze a truthful, bounded list of V1 offline-capable workflows and test every declared read, create/update, attachment, reload, restart, retry, conflict and reconnect path. Every unsupported action must fail safely and explain the connectivity requirement. Do not advertise full offline parity.

## 5. Important engineering/QA gaps

### P1 — CI does not enforce formatting

The QA matrix explicitly records Laravel Pint as installed but not a CI gate. Add a non-mutating Pint check to the quality workflow so formatting drift is caught before merge. This is a low-risk process improvement, not a functional release blocker.

### P1 — Full application test suite is SQLite-backed

The CI workflow runs the complete Laravel suite against SQLite. PostgreSQL receives migration, registration and backup/private-file recovery smoke coverage, not the complete commercial/sync feature suite. This is acceptable while the actual V1 target is SQLite; if PostgreSQL becomes the pilot/production database, the full domain and sync suite must run against it before that target is accepted.

### P1 — Performance and capacity evidence is thin

The reviewed release evidence does not establish representative concurrent-user, large-business dataset, slow-device, low-memory, attachment-size or sync-backlog performance. Define realistic V1 workload limits and measure dashboard/search/report latency, sync batch throughput, queue recovery and memory/storage behaviour. Avoid arbitrary enterprise-scale load tests; use expected customer volumes.

### P1 — Independent application security assessment is absent

Psalm, dependency auditing, secret scanning and regression tests are useful but are not a penetration test. Map the security acceptance suite to [OWASP ASVS 5.0.0](https://asvs.owasp.org/), focusing on authentication/session management, access control, tenant isolation, API/device credentials, data protection, file access, security logging and business-logic abuse. Test the offline trust boundary explicitly.

### P1 — Migration and upgrade safety need realistic historical data coverage

The project already has populated upgrade/rollback automation. Extend it only where evidence shows a gap: representative legacy database snapshots, failed/interrupted migration recovery, idempotent rerun behaviour, private media integrity and downgrade/rollback data-loss boundaries.

### P1 — Test quality and traceability

Keep the existing large suite, but maintain a concise requirement → risk → test → evidence mapping for every V1 critical workflow. Record exact source SHA and run IDs. A skipped test must have a reason and owner; flaky tests must be fixed, not repeatedly rerun until green.

## 6. Commercial QA checklist for V1

Use risk-based acceptance, not a checklist-count target.

- **Functional:** onboarding, permissions, customer lifecycle, event/work lifecycle, quote versions/acceptance, invoices, deposits, partial/final payments, purchasing, receiving, stock, costs, profitability, exports and document rendering.
- **Data integrity:** duplicate submissions, retries, idempotency, transaction rollback, concurrent edits, stale revisions, parent/child ownership, delete/archive/restore, import/export round trips and money/date/quantity precision.
- **Offline:** supported-route launch, local reads, local writes, attachments, queue durability, logout/re-pair isolation, server unavailable/restarted, token revocation, reconnect, delivery acknowledgement, conflicts and exactly-once effects.
- **Security:** authentication, password/session lifecycle, authorization on every route/action, tenant isolation, IDOR/BOLA, CSRF, XSS, SQL injection, mass assignment, rate limiting, file upload/download, token storage/revocation, audit attribution and backup confidentiality.
- **Reliability/recovery:** backup integrity, off-disk copy, restore drill, corrupt/missing archive handling, interrupted operations, migration recovery, rollback and clear user-facing failure states.
- **Compatibility:** supported Android installed PWA, desktop browsers, tablet layout, Windows installer/launcher, clean install and populated upgrade.
- **Usability/accessibility:** task completion without training, validation recovery, keyboard/focus, contrast, readable mobile density, clear pending/synced/conflict states and safe destructive actions.
- **Performance:** dashboard, search, reporting, large lists, attachments and sync backlog under representative business data.
- **Operations:** actionable logs, bounded log retention, visible backup health, useful diagnostic export and documented support/recovery procedure.

Quality-model reference: [ISO/IEC 25010:2023](https://www.iso.org/standard/78176.html). Use it as a product-quality evaluation model, not as a claim of certification.

## 7. Market and competitive research

### South African direct competitor: EventSync

[EventSync catering workflow](https://eventsync.co.za/solutions/catering) markets a local all-in-one flow: enquiry/headcount → package/per-head quote → accepted order → inventory/load-out checklist → invoice/pay link. It also describes catalogue/packages, customer records, checklists and finance/event profitability. Its wider platform covers venue, restaurant, bar/POS, ticketing and staff operations. Public pricing is presented through a plan comparison and demo funnel.

**Implication:** Zazu cannot compete merely by having customers, quotes, invoices and inventory. A local competitor already describes the same core chain. Zazu needs stronger execution, reliable offline continuity, accessible small-business onboarding, accurate event profitability and a visibly simpler mobile workflow.

### Global reference competitors

- [Tripleseat](https://www.capterra.com/p/118047/Tripleseat/): Capterra lists 4.7/5 from 576 reviews. Its positioning includes event sales, proposals, deposits/payments, guest communication, reports and hospitality integrations. Reviews identify strengths in central workflow and event coordination, alongside complaints about email deliverability, technical glitches, mobile limitations, complex last-minute changes, pricing and integration reliability.
- [Perfect Venue](https://www.capterra.com/p/266878/Perfect-Venue/): Capterra lists 4.8/5 from 85 reviews in the retrieved listing. Small catering/event operators praise usability, phone-based edits, proposals, BEO automation and support. Reported gaps include large-file attachments, proposal policy flexibility, recurring-event handling, mobile sorting and occasional navigation/bug issues.

Review counts/ratings are listing snapshots, not independent market-share evidence. Some reviews are vendor-invited or incentivized, so treat them as directional customer feedback rather than unbiased population statistics.

## 8. Competitive strategy — what Zazu should win on

Do not attempt to reproduce the entire feature surface of well-funded venue platforms. Compete on a narrower customer and a more dependable operating loop:

1. **Offline operational continuity:** real local work when internet or the shared server disappears, with explicit safe-sync semantics.
2. **One event's financial truth:** quote → acceptance → invoice → payment → purchasing → receiving → actual cost → contribution, with no contradictory totals.
3. **Fast owner-operated usability:** low setup burden, readable mobile workflows, clear next actions and minimal duplicate entry.
4. **Local fit:** ZAR and South African tax/accounting workflows, local business realities and practical migration from spreadsheets/paper.
5. **Trust:** predictable backups, transparent recovery, clear permissions, tenant isolation and explainable audit trails.

Future competitive opportunities, not V1 blockers: reusable package/per-head pricing, polished BEO/production/packing documents, client collaboration/approval, event-aware communication, and operational action recommendations. Validate demand before adding them.

## 9. Execution order

1. Close physical Android offline acceptance against the declared V1 workflow matrix.
2. Complete the actual owner-PC off-disk backup and restore drill.
3. Complete populated commercial reconciliation.
4. Resolve trusted-device staff-authority policy for V1.
5. Close any defects uncovered by the full offline sync matrix and targeted security review.
6. Add Pint CI enforcement and define measurable V1 performance baselines.
7. Complete POPIA operational sign-off and remaining release governance.

No feature expansion is justified until these gates are closed. Hosting and storage procurement remain outside this engineering audit.

## 10. Director conclusion

Zazu has a serious engineering base: broad domain coverage, strong automated regression, offline queue/sync foundations, recovery tooling and a disciplined release evidence trail. That is a credible starting point, not proof of a commercially ready product.

The largest gap is no longer raw feature count. It is proving that the whole supported product behaves predictably when the network, device, data, user session or business workflow does not behave as expected.

**Current disposition: NOT READY.** The shortest route to a credible V1 is to close the five operational/security gates above, add the small CI/quality controls, and resist feature expansion until real acceptance evidence supports release.

---

## 11. Current-main reinspection addendum — 2026-11-21

This section supersedes the earlier evidence snapshot above where the two differ. It does not erase the earlier dated history.

### 11.1 Exact current engineering evidence

The reinspection reached application/test commit `6b3ddb5af18dd992037caf57037cf20d72ac3757` on `main`. On that exact head:

- Laravel: **655 passed / 1 skipped / 4,513 assertions**.
- PostgreSQL migration, registration and backup/restore/private-file recovery smoke: **PASS**.
- Offline Chromium acceptance: **61 passed / 1 failed**.
- Zazu Quality, Composer security audit, secret scan, PHPMD, Director Health and Push-on-main: **PASS**.
- SonarCloud: **SKIPPED**, not a successful scan.
- The failed browser job prevented the subsequent mobile-viewport and phone-local attachment steps from running on that exact head.

Evidence:
- [Laravel run 44905870260](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44905870260)
- [Zazu Quality run 44905870250](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44905870250)
- [PHPMD run 44905870220](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44905870220)
- [Director Health run 44905870177](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44905870177)
- [Push-on-main run 44905870766](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44905870766)

### 11.2 Browser failure classification and correction

The repeated failure was:

`e2e/pwa-offline.spec.js` — `event cost form saves locally and reconciles its job identity after reconnect`, failing at the assertion that a locally cached server-backed job exists.

**Confirmed root cause — test precondition, not a production bootstrap defect established by this failure:**

- The failing event-cost test sets `window.__ZAZU_OFFLINE_DEFER_INITIAL_SYNC = true` before page boot.
- It then logs in, waits only for local IndexedDB readiness, and immediately reads `state.jobs`.
- It neither trusts the browser device nor explicitly starts workspace synchronization before requiring a server-backed Work record.
- With initial sync deliberately deferred, local readiness correctly does not imply that server-backed Work records have been bootstrapped.
- The failing assertion is therefore premature. The event-cost mutation itself has not yet been exercised by this failing run.

The earlier correction `1f658f4` added trust/sync/wait logic to a different test (`a rejected sync mutation and its recovery conflict persist together` around line 1194), not the failing event-cost test (around line 1804). The diagnostic commit `4272f71` instrumented that same unrelated test, so its printed workspace snapshot was not evidence about the failing event-cost scenario. This explains why those attempts did not move the actual failure.

**Corrective implementation:** commit [4971ce1](https://github.com/Leano-Jordan/ZazuEMP/commit/4971ce12c7c968edf5fcdae4d269f4c0bb079aa2) now explicitly trusts the device, calls `ZazuOffline.sync()`, and waits for a server-backed job in the event-cost test before selecting the job. The misplaced diagnostic logging was removed in [86f0386](https://github.com/Leano-Jordan/ZazuEMP/commit/86f03861cd640aaefa2a6b08f022d0a5b1f17e45).

**Verification status:** the correction is committed on `main`, but no GitHub Actions run or commit status was available for `4971ce1` at the time of this audit update. The original business acceptance assertion remains intact. Do not mark this failure closed or claim the test passes until the exact-head offline browser acceptance executes successfully.

**First attempted correction (not accepted as fixed):** `1f658f4abb98dbe47325255caa330c6057594927`. It explicitly trusted the device, invoked synchronization and waited for a server-backed job, while retaining the assertion. Exact-head run [44907862242](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44907862242) still failed at the same missing-job assertion. Therefore this change did not close the failure and must not be reported as a successful fix.

**Diagnostic follow-up:** commit `4272f7157b62d88d6c534f577b739f5f3b231dda` adds a bounded diagnostic record for active business ID/name, local-dirty state, pairing state, cached job identities and pending queue count immediately before the assertion. [Exact-head workflow 44908353460](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44908353460) was still in progress at the time of this update. No pass is claimed. The diagnostic output is required before selecting the next correction.

### 11.3 Documentation and decision-control defects

The repository contains a material documentation-drift problem. Current state snapshots still cite `e45cab9` and 653 passing tests, while the inspected application head had advanced to `6b3ddb5` and 655 passing tests. The latest application test correction is now `1f658f4`.

Files requiring current-state reconciliation include:
- `.ai/engineering/STATE.md`
- `.ai/engineering/READINESS_REGISTER.md`
- `docs/ZAZU_V1_STATUS.md`
- `docs/V1_RELEASE_CHECKLIST.md`
- `docs/ZAZU_2027_HARDENING_SCORECARD.md`
- `docs/RELEASE_BACKUP_RESTORE_RUNBOOK.md`
- `docs/ZAZU_TEST_AND_QUALITY_MATRIX.md`
- `docs/V1_KNOWN_LIMITATIONS.md`

Specific conflicts:
1. `docs/product-specification.md` section 4 reflects the current Founder decision DEC-027: exactly three tiers, Basic (permanent Free), Intermediate (paid), Advanced (paid). However, section 29 still contains the old four-tier R299/R499/R799/R1,299 hypothesis.
2. `docs/ZAZU_COMMERCIAL_REQUIREMENTS.md` still presents that four-tier pricing model as its commercial baseline.
3. `docs/v1-capability-map.md` still refers to the old R299→R499 and R799/R1,299 sequencing.
4. `.ai/engineering/READINESS_REGISTER.md` still says the tier conflict is awaiting an owner decision, despite DEC-027 being active.
5. `docs/ZAZU_TEST_AND_QUALITY_MATRIX.md` says latest main commits lack usable workflow-run evidence, which is no longer accurate.
6. `docs/V1_KNOWN_LIMITATIONS.md` uses an older evidence baseline and describes a narrower offline test boundary than the later quote, payment, expense and event-cost acceptance work now present.

**Risk:** these are not cosmetic inconsistencies. They can cause future engineering, licensing, sales and release decisions to be made against superseded pricing, entitlements or offline capabilities. DEC-027 is authoritative: do not infer prices, feature gates or quotas from the old tables.

### 11.4 Additional engineering observations

- The V1 capability map describes a broad event-business operating loop across customers, events, resources, purchasing, costs, finance, documents and reporting. The core architecture direction is sound; current readiness is limited by proof, not a reason to rewrite the application.
- The offline bootstrap controller applies `limit(500)` to multiple workspace collections. This may be an intentional V1 bound, but the product must not silently imply unlimited offline history. Validate representative record volumes and define clear truncation/pagination or supported offline-history behaviour before larger businesses rely on it.
- The current trusted-device limitation remains material: a paired device is a shared business data surface, and offline audit attribution identifies the approving owner rather than independently authenticating each staff operator. This must be a deliberate V1 owner-operated policy or a separately implemented and tested per-user authority model.
- The current SQLite full-suite / PostgreSQL smoke split is acceptable for the documented SQLite Windows V1 target. It is not evidence of full PostgreSQL commercial/sync parity.
- Pint remains installed but is not a CI formatting gate. SonarCloud remains skipped. Neither should be counted as passing evidence.
- Physical Android acceptance, the installed owner-PC recovery drill, populated live-business reconciliation, privacy operations and final trusted-device authority acceptance remain open.

### 11.5 Market evidence refinement

The South African competitor review confirms that EventSync is a direct competitor, not merely an adjacent venue tool. Its published catering workflow already covers enquiry/headcount, reusable packages and per-head quoting, accepted orders, inventory/load-out checklists, invoicing and pay links: [EventSync catering](https://eventsync.co.za/solutions/catering).

Additional customer evidence:
- [Perfect Venue Capterra reviews](https://www.capterra.com/p/266878/Perfect-Venue/reviews/) include praise for mobile edits, proposal workflows, ease of use and BEO automation, alongside complaints about attachment size, proposal flexibility and occasional bugs.
- [CaterZen Capterra reviews](https://www.capterra.com/p/82293/Restaurant-Catering-System/reviews/) emphasize accounts receivable, customer history, ease of use and support; some reviewers identify menu setup and group communication limitations.
- [Tripleseat Capterra reviews](https://www.capterra.com/p/118047/Tripleseat/) show a mature review base and strengths in central event coordination, with reported learning-curve, pricing and workflow-complexity trade-offs.
- A recent [r/catering software discussion](https://www.reddit.com/r/catering/comments/1wzkxwq/building_a_catering_software/) describes an operator's use of spreadsheets, calendars and QuickBooks. This is a useful qualitative signal, not statistically representative demand evidence.

The competitive recommendation remains differentiation, not feature copying: dependable offline continuity, trustworthy event-level financial truth, fast owner-operated workflows, local fit and recovery confidence.

### 11.6 Standards-based QA target

Use the following as the verification baseline, not as certification claims:

- **ISO/IEC 25010:2023** — assess functional suitability, performance efficiency, compatibility, interaction capability, reliability, security, maintainability, flexibility and safety. [ISO standard](https://www.iso.org/standard/78176.html)
- **OWASP ASVS 5.0.0** — map relevant application controls to a practical Level 2 verification target, especially authentication, session management, authorization, tenant isolation, API/device credentials, file handling, data protection and security logging. [OWASP ASVS](https://asvs.owasp.org/)
- **NIST SSDF 1.1** — maintain secure development, review, vulnerability response and release provenance practices. [NIST SP 800-218](https://csrc.nist.gov/pubs/sp/800/218/final)
- **WCAG 2.2 AA** — verify keyboard access, focus visibility, contrast, target size, form error recovery, accessible authentication and mobile interaction. [W3C WCAG 2.2](https://www.w3.org/TR/WCAG22/)

### 11.7 Updated execution order

1. Obtain green exact-head evidence for commit `1f658f4`, including the full desktop, mobile-viewport and phone-local attachment jobs. Do not claim a pass before the run completes.
2. Reconcile current engineering state and commercial documents against DEC-027 and the latest exact-head CI evidence.
3. Complete the real Android installed-PWA cold-launch, restart, offline-write, attachment and reconnect acceptance. Browser emulation is not a substitute.
4. Complete the intended Windows owner-PC off-disk backup and full restore drill, including private media and representative records.
5. Reconcile one populated business through quote, acceptance, invoice, deposit/payment, purchasing/receiving, actual costs, event contribution and export.
6. Explicitly accept or narrow the shared trusted-device authority boundary.
7. Complete risk-based ASVS security verification, WCAG 2.2 AA checks and representative performance/capacity tests.

No new feature expansion or architecture rewrite is justified before these gates close.

**Evidence maturity remains 77/100 as the last documented score; this audit does not independently recalculate it. V1 remains NOT READY.**
