# Zazu EMP — Director Deep Software Audit

> **Current authority:** This dated audit is historical evidence. Current product strategy, commercial positioning, V1 scope, scorecard and release-gate order are governed by [Zazu Engine Director — Commercial V1 Mandate](ZAZU_ENGINE_DIRECTOR_COMMERCIAL_V1_MANDATE.md). Current implementation status is recorded in `.ai/engineering/STATE.md`; do not treat this audit's status snapshot as current.

**Audit date:** 2026-11-22  
**Repository:** `Leano-Jordan/ZazuEMP`  
**Audited branch:** `main`  
**Repository HEAD inspected at audit start:** `18eb37327a39e60f18a27391ab02b96e4d10655a`  
**Latest application implementation baseline:** `c992b986497ca1c233437212d55b3e93ad29c455`  
**Disposition:** V1 NOT READY — engineering foundation is strong; operational and security evidence remains incomplete.

## 1. Scope and method

This review examined the current repository tree (931 entries), project and engineering control documents, current V1 status and release checklist, quality-gate definitions, CI workflows, key application services/controllers, backup/restore implementation, offline synchronization, commercial reconciliation tests, Windows packaging and customer-facing limitations.

The current HEAD is 14 commits ahead of the cited application baseline. The Git comparison shows those intervening changes touch documentation/control records only; no application source files changed in that interval. The current HEAD has 8 successful check runs. The latest recorded application suite on the implementation baseline reports 679 passed, 1 skipped and 4,671 assertions (Laravel workflow 44989474410).

This is a repository/source/CI audit. It is not a penetration test against a running production installation, physical-device certification, or proof of the owner's local recovery drill. No source-code change or readiness-score increase is claimed by this audit.

Excluded: hosting procurement, storage purchasing, visual branding, logo and photography.

## 2. Executive assessment

Zazu has progressed beyond a typical early-stage CRUD application. It has a coherent event-centred domain model, substantial business-isolation controls, an offline mutation/synchronization architecture, financial workflow tests, Windows packaging, database recovery logic and a useful automated quality pipeline.

The strongest engineering decision is to keep the event/work record as the operational anchor. Customers, requirements, quote versions, purchasing, preparation, costs, invoices, payments, attachments and audit activity should remain connected to that record rather than become competing copies of commercial truth.

The most important remaining weaknesses are not missing feature volume. They are:
1. Confidentiality of backup archives.
2. Real-world proof of recovery on the intended installed PC.
3. Physical Android offline acceptance.
4. Reconciliation using a real populated business, rather than relying only on seeded CI data.
5. Runtime performance evidence on supported low-resource devices.
6. A documented security-verification baseline appropriate for business and personal information.

No new exploitable application-code defect was confirmed in the bounded source review. The findings below distinguish verified implementation, documented limitations, and recommended work.

## 3. Current evidence

### Automated evidence

The latest application baseline records:
- Laravel: 679 passed / 1 skipped / 4,671 assertions.
- Offline browser acceptance: 63 desktop cases, 7 mobile viewport cases, 1 host-outage case and 1 phone-local attachment/reconnect case.
- Zazu Quality, PHPMD, Psalm, PostgreSQL migration/recovery smoke, populated upgrade/rollback and Windows fresh-install acceptance: recorded PASS.
- Windows PowerShell 5.1 backup-runner metadata/failure-preservation acceptance: PASS.
- Current repository HEAD: 8/8 reported check runs successful.

SonarCloud is skipped and must not be represented as passed. The mobile browser cases are emulation, not physical-device certification. CI recovery is not the owner's installed-PC recovery drill.

### Release evidence still open

The current status identifies:
- DB-001 owner-installation diagnostics and successful repetition of the affected installed workflow.
- Owner-PC scheduled backup observation, independent off-disk copy and full restore including representative private media.
- Physical Android cold launch, host outage, offline work, force-close/restart and reconnect.
- Populated live-business quote-to-payment-to-cost/profit reconciliation.
- POPIA operational controls and sign-off.

The ZAZU name-clearance item is outside this software audit.

## 4. Findings by engineering area

### A. Architecture and business workflow — strong foundation

Observed:
- The event/work record is the relationship anchor.
- Quote versions and customer acceptance are explicit.
- Financial and purchasing records use business/event relationships.
- Purchase order receiving supports partial receipts and idempotency.
- Event contribution reporting separates invoiced net sales from actual event costs.
- Commercial reconciliation tests cover quote acceptance, invoice creation, deposit/final payment, purchase receipt, incurred costs, completion and contribution reporting.

Recommendation:
- Preserve this model. Do not introduce a second event/order record or duplicate commercial source of truth.
- Make every commercial transition traceable to the accepted quote version and its downstream invoice/purchase/cost records.
- Keep the real populated-business reconciliation as a release gate. The seeded integration test is valuable but does not prove the owner's actual installation data reconciles correctly.

### B. Business isolation and authorization — strong automated coverage

Observed:
- Dedicated business-isolation tests cover cross-business reads and writes.
- Offline mutations validate business-scoped record identities and foreign references.
- Trusted-device authorization and stale-credential races have regression coverage.
- Private media is served through authorized application paths.
- Public quote acceptance is signed and rate-limited.

Known limitation:
- A trusted offline device represents a business-approved shared device. The server attributes its mutations to the approving owner; it does not independently authenticate each staff member for every offline mutation or re-evaluate each staff member's role offline.
- iOS offline operation is not certified for V1.

Recommendation:
- Keep the limitation visible in onboarding, device-pairing and security documentation.
- Require physical device lock, controlled custody and immediate revocation for a lost/reassigned device.
- Do not claim per-staff offline permissions or iOS offline parity.

### C. Offline reliability — sophisticated, bounded implementation

Observed:
- IndexedDB workspace and durable mutation queue.
- Business-scoped stable identity, mutation replay protection, ordered delivery, atomic apply/publication, revision-aware stale-write handling and conflict resolution.
- Phone-local Work attachment capture and reconnect upload.
- Service-worker authentication/cache race tests.
- Browser host-outage and reconnect acceptance.

Recommendation:
- Keep offline scope explicit. Do not market app-wide offline parity.
- The remaining decisive proof is physical Android: install from the supported HTTPS origin, cold launch with the host unavailable, create supported records and attachments, force-close/restart, reconnect and verify exactly-once reconciliation.
- Add storage quota/eviction and low-device-storage failure acceptance if not already represented in the physical runbook.

### D. Backup and restore — strong integrity, confidentiality gap

Observed:
- Backup includes installation database and private storage.
- A manifest records archive contents and checksums.
- Backup reopens and verifies the completed ZIP before publishing.
- Restore checks archive structure, payload membership, size/entry limits, checksums, symlinks and supported database driver before mutation.
- Restore has staged database/private-storage rollback paths.
- Empty private-storage restore and failed mid-restore cleanup have regression coverage.

**Historical finding — initial business and installation archives were unencrypted.**

Business archive confidentiality is now remediated for new exports: V2 .zazu exports use a passphrase-derived Argon2id key and authenticated Sodium secretstream encryption. Legacy plaintext V1 business archives remain importable for migration compatibility and should be re-exported.

Installation-wide scheduled backups intentionally remain unattended ZIP files so a recovery operation never depends on a human passphrase. Their off-disk copies must be stored on BitLocker-protected removable media or in an encrypted, access-controlled cloud vault. The scheduled runner verifies SHA-256 copy integrity only; it does not encrypt the secondary copy or verify destination encryption.

The original APP_KEY is not used as the business archive passphrase or encryption key. The business passphrase is supplied by the owner, is not persisted, and must be stored separately. A forgotten passphrase cannot be recovered by Zazu.

The actual owner-PC scheduled backup, encrypted independent copy and controlled restore remain mandatory.

### E. Performance and resource use — measurement gap

Observed:
- CI builds production assets and enforces compressed JavaScript/CSS size budgets.
- The existing quality documentation explicitly states these budgets do not measure browser execution time, interaction latency, CPU, memory, SQL query time or performance on a low-powered PC/phone.

Recommendation:
- First capture a repeatable baseline; do not set arbitrary thresholds.
- Use fixed small-business and populated datasets.
- Record cold/warm launch, dashboard, customer search, Work detail, quote creation, reports and offline queue/reconnect.
- Record p50/p95 interaction and request timings, slow SQL/query count, CPU and peak memory.
- Profile the supported Windows PC and physical Android device.
- Add regression thresholds only after a stable baseline exists.

### F. Database compatibility and installation

Observed:
- SQLite is the current full-suite V1 target.
- PostgreSQL has migration and recovery smoke coverage, not the full commercial and synchronization suite.
- Windows packaging builds from an exact main commit and runs fresh-install/recovery acceptance.
- The package expects a compatible PHP runtime and does not bundle PHP.

Recommendation:
- This is acceptable for the current SQLite-first installation target.
- If the first hosted deployment uses PostgreSQL, make the full commercial, tenant-isolation and sync feature suite run against PostgreSQL before that deployment is accepted.
- Continue testing clean install, populated upgrade, rollback, schema diagnostics, private-file permissions and recovery using the exact release artifact.

### G. Security assurance

Observed:
- Composer and npm dependency audits.
- Full-history secret scanning.
- CodeQL analysis.
- Psalm, PHPMD and changed-file Pint checks.
- Security headers/CSP, throttling, signed public quote routes and private media authorization.
- Security and incident-response documentation.

Recommended standard:
- Use OWASP ASVS 5.0 as the application-security verification catalogue.
- Apply a risk-adapted Level 2 baseline to authentication, authorization, tenant isolation, session/device lifecycle, sensitive data, file handling, cryptography, logging and business transactions.
- Maintain a control-to-test/evidence matrix. Black-box testing alone is not sufficient for the important authorization and data-integrity controls.
- Align development, dependency and vulnerability-response practices with NIST SSDF.

### H. Privacy operations and data lifecycle

Observed:
- Business-scoped records, private storage, authorization and audit foundations exist.
- The data-subject request procedure states that dedicated self-service export/deletion is not implemented.
- Retention/disposal remains a provisional operational policy rather than automated enforcement.

Recommendation:
- Keep the software honest about the manual boundary.
- Before commercial operation, ensure an authorized operator can locate, export, correct and securely dispose of customer data where appropriate, while preserving legally required accounting/audit records.
- Verify that deletion/retention includes attachments, derived exports and backup expiry—not just database rows.
- Keep legal sign-off distinct from software capability.

## 5. Market research — patterns worth adopting

Reviewed product material and public customer feedback for Tripleseat, HoneyBook and Planning Pod.

Patterns that recur:
- A single event record connects proposal, contract, customer communication, payments and on-site operations.
- Reusable templates and flexible service/catalogue configuration help small and specialist businesses.
- Clear customer-facing proposals and payment steps reduce email and administrative back-and-forth.
- Mobile usability matters when operators are away from a desk.
- Large feature sets can overwhelm clients and staff.
- Reporting flexibility, calendar/communication reliability and mobile responsiveness appear in customer criticism.

Examples:
- Tripleseat reviews praise centralized event information and proposals, while some reviewers mention cumbersome complex changes, reporting flexibility and mobile responsiveness.
- Planning Pod reviews praise broad event coverage and consolidation, while one reviewer describes client-facing complexity and calendar-sync problems.
- HoneyBook positions itself around the small-business client journey, communication, proposals and payments.

These are directional observations from vendor pages and self-selected review samples, not controlled market research or proof of universal customer sentiment.

### Zazu's strongest competitive position

Do not try to match enterprise vendors feature-for-feature. Focus on:
1. A dependable event-to-profit operating record.
2. Offline continuity for supported workflows.
3. Multi-service flexibility for catering, rentals, sound/DJ, baking, decor and related operators.
4. Accurate quote, payment, purchasing and actual-cost handoffs.
5. A mobile experience that remains usable under real connectivity constraints.

The differentiator must be proven in customer workflows, not just described in marketing.

## 6. Commercial software QA checklist

Use the following as the release acceptance baseline. A check is complete only when its result is evidenced against the relevant release commit or real installation.

### Business and tenant integrity
- [ ] Cross-business read/write/reference attempts fail closed.
- [ ] Business switching cannot leak cached data, queues or attachments.
- [ ] Soft-deleted, archived and closed records follow defined lifecycle rules.
- [ ] Duplicate/retried requests do not duplicate business mutations.
- [ ] Audit events identify the business, actor/device and affected record.

### Commercial correctness
- [ ] Quote calculations, revisions, tax snapshots and rounding reconcile.
- [ ] Customer acceptance applies to the exact version sent.
- [ ] Changed requirements cannot silently overwrite accepted scope.
- [ ] Invoice totals and payment balances reconcile under partial and repeated payments.
- [ ] Voids/cancellations do not inflate revenue or profit.
- [ ] Partial purchase receipts cannot over-receive or duplicate stock.
- [ ] Actual costs, planned costs and cancelled costs remain distinct.
- [ ] Multi-currency totals are never combined as if they share one currency.
- [ ] Event contribution matches independently calculated source records.

### Offline and synchronization
- [ ] Supported records persist through reload and cold launch.
- [ ] Queue survives host outage, app restart and reconnect.
- [ ] Duplicate delivery/retry remains idempotent.
- [ ] Stale revisions and conflicting edits are surfaced safely.
- [ ] Logout, expiry, business switching, device revocation and re-pairing do not apply stale mutations.
- [ ] Attachment capture, upload, download and authorization survive reconnect.
- [ ] Storage quota/eviction failures never falsely report a successful local save.
- [ ] Physical Android acceptance is recorded separately from emulation.

### Recovery and data protection
- [ ] Backup rejects incomplete, oversized and malformed source data.
- [ ] Restore rejects corrupt, tampered, duplicate, traversal and symlink entries.
- [ ] Database and private files restore to the same consistent point.
- [ ] Failed restore rolls back both database and files.
- [ ] Empty private storage restores correctly.
- [ ] Off-device backups are encrypted with a separately recoverable key.
- [ ] A clean installation can be recovered without relying on the lost machine.
- [ ] Owner/operator restore drill is recorded with representative data and media.

### Security and privacy
- [ ] Authorization is enforced server-side for every sensitive operation.
- [ ] Tenant isolation is tested with adversarial foreign identifiers.
- [ ] Session expiry, token rotation, revocation and recovery are tested.
- [ ] Uploads and downloads enforce type, size, ownership and private access.
- [ ] Rate limits, CSRF, signed links and security headers are regression-tested.
- [ ] Logs and diagnostics do not disclose passwords, tokens, raw SQL bindings or customer records.
- [ ] Dependency, secret and static security checks pass.
- [ ] Data export, correction, retention and disposal procedures are operable and evidenced.

### Performance, accessibility and usability
- [ ] Representative datasets have repeatable cold/warm timings.
- [ ] p50/p95 interaction and request latency are recorded.
- [ ] CPU, peak memory, database query count and slow queries are profiled.
- [ ] Dashboard, search, Work, quotes, finance and reports remain responsive on supported hardware.
- [ ] Keyboard operation, focus visibility, labels, contrast, zoom and mobile layouts are checked.
- [ ] Offline/online status and save/sync outcomes are unambiguous.
- [ ] Errors tell users what happened and the safe next action.

### Installation and release
- [ ] Clean installation succeeds from the exact release package.
- [ ] Populated upgrade and rollback succeed.
- [ ] Database schema and runtime prerequisites are diagnosed safely.
- [ ] Existing customer data and private media survive upgrades.
- [ ] Release notes, installation/recovery instructions and known limitations match the actual artifact.
- [ ] Release evidence identifies exact source SHA, package SHA and test run IDs.

## 7. Prioritized engineering plan

### P0 — release-critical
1. Resolve the open DB-001 owner-installation diagnostic using the read-only scripts and matching safe incident report. Do not reset or migrate the owner database during diagnosis.
2. Design and implement recoverable encrypted installation backups; preserve compatibility with existing archives.
3. Complete the actual owner-PC scheduled backup, independent copy and full restore drill with representative private media.
4. Complete physical Android installed-PWA offline/restart/reconnect acceptance.
5. Complete populated live-business quote → invoice → payment → purchase/cost → event contribution reconciliation.

### P1 — commercial hardening
6. Add a risk-based OWASP ASVS 5.0 control/evidence matrix and close any applicable high-risk gaps.
7. Establish repeatable runtime performance/resource baselines on supported Windows and Android hardware.
8. Strengthen privacy data lifecycle operations so export, correction, retention and deletion cover both records and files.
9. Reconcile current-state documentation and ensure only one active release ledger is treated as authoritative.

### Conditional
10. If PostgreSQL is selected for the first hosted release, run the full commercial, authorization, offline-sync and recovery suites against that engine before acceptance.

## 8. Final Director decision

Zazu has a credible technical foundation and a stronger-than-usual automated regression system for a solo-developed product. The remaining work is concentrated around proof under real operating conditions, backup confidentiality, financial reconciliation and measurable runtime quality.

Do not expand V1 scope to imitate every competitor. Close the identified reliability and data-protection gaps, prove the existing workflows on real devices and real business data, and ship a smaller dependable product.

**Current readiness remains 77/100 evidence maturity; V1 remains NOT READY.** This audit does not award a score increase.

## 9. Source references

### Zazu repository evidence
- [Current repository HEAD](https://github.com/Leano-Jordan/ZazuEMP/commit/18eb37327a39e60f18a27391ab02b96e4d10655a)
- [V1 status](https://github.com/Leano-Jordan/ZazuEMP/blob/main/docs/ZAZU_V1_STATUS.md)
- [V1 release checklist](https://github.com/Leano-Jordan/ZazuEMP/blob/main/docs/V1_RELEASE_CHECKLIST.md)
- [Quality gates](https://github.com/Leano-Jordan/ZazuEMP/blob/main/docs/QUALITY_GATES.md)
- [Backup/restore runbook](https://github.com/Leano-Jordan/ZazuEMP/blob/main/docs/RELEASE_BACKUP_RESTORE_RUNBOOK.md)
- [Known limitations](https://github.com/Leano-Jordan/ZazuEMP/blob/main/docs/V1_KNOWN_LIMITATIONS.md)
- [Commercial reconciliation test](https://github.com/Leano-Jordan/ZazuEMP/blob/main/tests/Feature/CommercialReconciliationTest.php)

### External standards and market evidence
- [OWASP ASVS 5.0](https://asvs.owasp.org/)
- [NIST Secure Software Development Framework](https://csrc.nist.gov/projects/ssdf)
- [ISO/IEC 25010:2023](https://www.iso.org/standard/78176.html)
- [Tripleseat](https://tripleseat.com/)
- [Tripleseat customer reviews](https://www.capterra.com/p/118047/Tripleseat/reviews/)
- [HoneyBook vs Tripleseat comparison](https://www.honeybook.com/blog/honeybook-vs-tripleseat)
- [Planning Pod customer reviews](https://www.capterra.com/p/125947/Planning-Pod/reviews/)


## Backup confidentiality follow-up — 2026-11-23

The initial finding was valid: the installation archive and business .zazu exports were plain ZIP files. The decision has now been implemented with separate controls for the two recovery paths.

**Implemented on main:**
- Installation backup staging, published backup archives and restore/rollback staging use owner-only POSIX permissions (0700 directories / 0600 files), with Windows staging under the installer's restricted backup directory.
- New business V2 .zazu exports use streaming Sodium secretstream XChaCha20-Poly1305 with Argon2id-derived keys and a confirmed owner-supplied passphrase.
- Restore requires the correct passphrase, authenticates every chunk and the final tag, decrypts to private temporary storage, then applies the existing strict archive schema, tenant and empty-workspace checks.
- Legacy plaintext V1 business archives remain importable for migration compatibility; new exports are encrypted.
- Installation-wide scheduled backups remain unattended plain ZIP files so recovery does not depend on a human passphrase. Off-disk copies must be encrypted and access-controlled.
- Sodium is now an explicit Windows installer prerequisite and is included in Laravel and Windows package CI extension lists.

**Verification boundary:** the Sodium streaming algorithm passed a local PHP 8.4 multi-chunk and wrong-password smoke test. Full Laravel/ZipArchive CI and actual Windows installed-PC export/restore acceptance remain open. Do not claim those gates passed until the exact head is green and the installed workflow is exercised.
