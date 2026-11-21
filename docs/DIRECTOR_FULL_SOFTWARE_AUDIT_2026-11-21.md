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
