# Director Commercial Audit — Zazu EMP

**Assessment date:** 2026-10-03
**Repository:** `Leano-Jordan/ZazuEMP`
**Branch:** `main`
**Latest application-changing candidate after the landing visual/media cycle:** `33ec396f69ddcf50b217f986611b5120e9a58d4d` (bundled stock photography, landing composition/nav refinement, offline precache, and regression coverage)
**Application assessment baseline:** the canonical release checklist still records `52d9b26bc17ddee8d1427032ecaf2fb8fc0b6350` as the last application assessment candidate; later commits observed in this audit are primarily control/documentation changes and must not be treated as fresh application verification.

## 1. Executive health position

**Commercial-readiness maturity: 67/100**

**Maturity remaining: 33/100 points**

That 67/100 is a weighted readiness maturity score, not a claim that 67% of the source code is complete. The remaining work is disproportionately concentrated in proof, recovery, populated-data safety, mobile/device acceptance, legal/compliance operations and release certification rather than basic CRUD feature construction.

**Release status: NOT CERTIFIED.**

Zazu already has a substantial operational foundation. The commercial risk is now mainly whether the existing system can be demonstrated as safe, recoverable, understandable and legally/commercially deployable in realistic conditions.

## 2. Director evidence boundary

Repository source and control documents were inspected first.

Observed:
- current product specification and Director direction records;
- current release checklist and readiness register;
- current Laravel/Vite manifests;
- major route, authorization, finance, attachment, backup/restore, offline and Helper implementations;
- relevant migrations and business-ownership constraints;
- mobile/responsive CSS and JavaScript structure;
- current commercial/legal/IP registers;
- recent Director commits and historical release gates.

Not observed in this audit:
- a fresh successful GitHub Actions run for the latest observed main ref;
- a live mobile/tablet browser session against the latest release candidate;
- a real populated backup/restore drill;
- a populated existing-database upgrade and rollback exercise;
- a production deployment exercise.

Therefore those items remain **unproven**, even where source code and older test evidence exist.

## 3. Scorecard — mobile-readable

| Area | Score /100 | Work left | Status | Main evidence / gap |
|---|---:|---:|---|---|
| Product specification alignment | 88 | 12 | 🟢 strong | Broad product, lifecycle and UX direction is documented; some requirements remain only partially surfaced |
| Product completeness | 80 | 20 | 🟡 | Major operational domains exist; the connected lifecycle still needs populated end-to-end proof |
| Direction / strategy | 84 | 16 | 🟢 | Strong product direction, offline path and progressive-disclosure rules; stale Helper wording must be reconciled |
| UI | 76 | 24 | 🟡 | Strong shared visual system and responsive structure; rendered current-head proof is missing |
| UX | 74 | 26 | 🟡 | Workspace model and progressive disclosure are solid; broader page-level adoption and real-user validation remain |
| Mobile | 65 | 35 | 🟠 | Mobile shell/nav exists; full critical workflows and device verification are still outstanding |
| Accessibility | 68 | 32 | 🟡 | ARIA/focus/reduced-motion foundations exist; current rendered WCAG-oriented verification is incomplete |
| Database architecture | 78 | 22 | 🟡 | Business ownership, foreign keys, soft deletes and parent/child structure are strong; populated upgrade proof is missing |
| Data integrity | 74 | 26 | 🟡 | Transactions, idempotency, locking and audit foundations exist; realistic reconciliation and failure proof remain |
| Code quality / maintainability | 76 | 24 | 🟡 | Architecture is a coherent modular monolith; some complexity suppression/large UI files need continued governance |
| Security | 76 | 24 | 🟡 | Server-side authorization, isolation and private media controls exist; final adversarial certification is not current |
| Commercial / finance truth | 72 | 28 | 🟡 | Quote → invoice → payment safeguards exist; populated reconciliation and tax-invoice presentation verification remain release gates |
| Offline/local-first capability | 52 | 48 | 🟠 | Strong architecture and server-side sync foundation; no complete phone-local store/queue/domain sync handlers yet |
| Backup / restore / recovery | 42 | 58 | 🔴 | Real commands exist, but real recovery evidence is still absent and release-critical |
| Deployment / upgrade / rollback | 44 | 56 | 🔴 | Fresh install/tooling exists; populated upgrade and rollback exercises remain open |
| Legal / privacy operations | 44 | 56 | 🔴 | Ownership baseline exists; POPIA operational controls, privacy notice, terms and incident procedure are incomplete |
| IP / brand protection | 58 | 42 | 🟠 | Founder ownership is documented; formal trademark clearance is not completed and current Zazu-name collisions exist |
| Dependency / licensing control | 68 | 32 | 🟡 | Third-party register exists; release-level transitive audit still required |
| Observability / operations | 58 | 42 | 🟠 | Error/incident foundations and audit logging exist; production runbooks and recovery operations remain |
| Zazu Helper implementation | 42 | 58 | 🔴 | New architecture is documented, but current component still uses the legacy hard-coded bird implementation |
| Commercial feasibility | 86 | 14 | 🟢 | Modular monolith + R0 native web animation + staged offline architecture keep the plan technically feasible |
| Documentation / governance | 84 | 16 | 🟢 | Director control system is mature; several historical/current HEAD references need reconciliation |
| **Overall commercial readiness** | **67** | **33** | **NOT CERTIFIED** | Proof/recovery/legal/mobile gates dominate the remaining work |

## 4. Severity scale

**🔴 Release-critical:** can cause data loss, incorrect commercial state, unrecoverable deployment, serious privacy/security exposure or inability to operate safely.

**🟠 High:** materially weakens commercial acceptance or creates a credible operational risk.

**🟡 Medium:** significant quality/maintainability/usability gap that should be closed before broad release.

**🟢 Controlled:** direction/source foundation is substantially established; normal verification or refinement remains.

## 5. Critical findings

### AUD-CRIT-01 — Populated end-to-end workflow is not proven

**Severity:** Critical  
**Fix effort:** High  
**Release impact:** Blocks V1 certification

Source supports the core operational chain, but the release ledger still requires a realistic populated traversal from customer/job through requirements, quote, purchasing, preparation, costs, invoice, payment and completion.

**What remains:** one controlled populated business scenario, source-to-screen reconciliation, expected financial totals, state transitions, audit trail and failure-path checks.

### AUD-CRIT-02 — Backup / restore is implemented but not commercially proven

**Severity:** Critical  
**Fix effort:** High  
**Release impact:** Blocks V1 certification

`zazu:backup` and `zazu:restore` exist with archive/path/size/symlink protections and rollback staging. That is a good engineering foundation, but source existence does not prove a customer's business can be recovered.

**What remains:** real backup creation, restore into a clean target, record verification, private-media verification and failure/rollback evidence.

### AUD-CRIT-03 — Existing populated database upgrade and rollback remain unproven

**Severity:** Critical  
**Fix effort:** High  
**Release impact:** Blocks V1 certification

Zazu contains multiple repair/reconciliation migrations because historical schema drift has been encountered. That makes representative upgrade testing especially important.

**What remains:** upgrade a realistic populated database through the migration path, verify counts/relationships/financial totals/media, then exercise rollback/recovery procedure.

### AUD-CRIT-04 — Current-head runtime evidence is stale/unobserved

**Severity:** Critical  
**Fix effort:** Medium  
**Release impact:** Blocks final certification

Older green figures are recorded, but the latest observed GitHub main ref did not return an associated workflow run in the connector during this audit.

**What remains:** current-head CI, browser, static-analysis and runtime evidence; classify all failures rather than carrying forward historical green numbers.

### AUD-HIGH-01 — Mobile critical-path acceptance is incomplete

**Severity:** High  
**Fix effort:** High  
**Release impact:** Blocks mobile release acceptance

Responsive shell, mobile navigation and reduced-motion CSS/JS exist. The repository itself explicitly says full mobile workflow verification remains open.

**What remains:** registration/onboarding, customer/job, quote, purchasing, finance/payment, documents and Helper interactions on representative phone widths plus tablet checks, including overflow/occlusion.

### AUD-HIGH-02 — Legal/compliance operations are not launch-complete

**Severity:** High  
**Fix effort:** Medium/High  
**Release impact:** Commercial launch gate

The repository documents ownership and a POPIA engineering baseline, but its own legal register marks the privacy notice, customer terms, POPIA operational documentation, PAIA documentation, incident procedure and production media controls as incomplete.

POPIA is South African law governing personal-information processing by public and private bodies. The Act includes requirements around safeguards, operator relationships and compromise notification. urlProtection of Personal Information Act — South African Governmenthttps://www.gov.za/documents/protection-personal-information-act

CPA and ECTA may also apply depending on Zazu's eventual commercial model and contract structure. urlConsumer Protection Act — South African Governmenthttps://www.gov.za/documents/consumer-protection-act urlECTA — South African Governmenthttps://www.gov.za/documents/electronic-communications-and-transactions-act

### AUD-HIGH-03 — Zazu name has meaningful brand-clearance risk

**Severity:** High  
**Fix effort:** Medium  
**Release impact:** Commercial/branding gate

Current public evidence shows a South African company trading as **Zazu SA (Pty) Ltd** operating a business-finance product, and an unrelated **Zazu Events** event-management/catering business. This does not establish infringement or determine whether Zazu EMP can use the name, but it makes formal South African trademark and market-clearance work materially important.

CIPC provides a free public preliminary trademark search and a more detailed paid professional search path. urlCIPC Intellectual Property Online — free trademark searchhttps://iponline.cipc.co.za/IPOnlineTest/Trademarks/Search/FreeTMSearchNotice.aspx

Current public evidence for the other businesses: urlZazu South Africa business finance platformhttps://www.get-zazu.com/ urlZazu Eventshttps://zazuevents.com/


### AUD-HIGH-05 — Tax-invoice presentation still needs release verification

**Severity:** High  
**Fix effort:** Medium  
**Release impact:** Finance/document release gate

The current invoice view conditionally presents 'Tax Invoice', supplier/customer tax identifiers, serialised invoice number, issue/due dates, line details, tax treatment, tax and total. That is a strong implementation foundation.

SARS states that a valid tax invoice must contain prescribed supplier/recipient information, serial number/date, description, quantity/volume and value/tax/consideration fields, with the exact requirements depending on the invoice type. The current standard VAT rate is 15%, and SARS currently states a compulsory VAT registration threshold of R2.3 million in taxable sales. urlSARS Tax Invoiceshttps://www.sars.gov.za/businesses-and-employers/government/tax-invoices/ urlSARS VAT FAQhttps://www.sars.gov.za/faq/what-is-vat-and-who-needs-to-register/

**What remains:** render and verify representative Zazu invoices against the applicable SARS requirements, including VAT/non-VAT presentation and stored tax snapshots. Do not hard-code tax registration assumptions that belong to the customer's actual tax status.

### AUD-HIGH-04 — Helper product direction is ahead of implementation

**Severity:** High  
**Fix effort:** Medium/High  
**Release impact:** Medium for V1 core app; High for Helper rollout

The documentation now defines one shared Helper engine with selectable skins, semantic states, anchors and a free native SVG/CSS/WAAPI baseline. The current live component still renders the older hard-coded bird and route-specific guide logic.

**What remains:** implement the semantic Helper engine, target resolver and skin boundary, then replace the legacy presentation without leaving competing systems behind.

## 6. Contradictions and stale truth

### CONTRA-01 — Helper identity wording
Director Product Definition still describes the Helper as a bird, while the current character foundation defines Gecko, Ant and Chameleon as selectable skins under one character framework.

**Action:** reconcile the living direction to the skin-neutral Helper model.

### CONTRA-02 — Repository HEAD references
Several status/control files contain different historical HEAD values. The evidence rule already says historical figures must not be reused as current proof, but inconsistent headers still create avoidable ambiguity.

**Action:** update living status files to explicitly distinguish current main ref from last application-assessment ref.

### CONTRA-03 — “Offline-first” wording versus actual capability
The architecture correctly uses “offline-first” as the target direction but explicitly states that phone-local data, durable offline writes and sync handlers are not yet implemented.

**Action:** preserve the term as product architecture direction, but never market the current release as fully disconnected phone-capable until the stated gates pass.

## 7. Potential

Zazu has several commercially useful foundations already in place:

- a central job/event operational record tying major domains together;
- business-scoped ownership and isolation across the application and data model;
- transactional and idempotent controls around critical commercial mutations;
- private attachment handling rather than public-by-default business media;
- a modular-monolith architecture that is easier to run at the early customer scale than premature distributed infrastructure;
- a staged offline architecture rather than pretending static PWA caching equals offline business operation;
- an owner-controlled offline licensing foundation;
- a reusable Helper architecture that can become a differentiating interaction layer without paying for an animation platform.

The R0 Helper decision is especially feasible: native SVG + CSS + browser WAAPI avoids adding a paid animation dependency.

## 8. Risk register

| Risk | Score /100 | Why it matters |
|---|---:|---|
| Data recovery risk | 90 | Customer trust collapses if restore is unproven |
| Upgrade risk | 88 | Existing customers must survive schema evolution |
| Mobile workflow risk | 82 | Mobile is a first-class product surface |
| Legal/privacy risk | 82 | Zazu will handle names, contacts, documents and media |
| Brand risk | 78 | Current public Zazu-name usage creates clearance work |
| Offline overclaim risk | 78 | Present implementation is not yet full phone-local offline |
| Commercial reconciliation risk | 76 | Finance/quote correctness needs realistic proof |
| Current-head evidence risk | 74 | Historical green runs cannot certify the current candidate |
| Helper implementation drift | 70 | Documentation is ahead of the live Helper component |
| Dependency/licensing risk | 54 | Register exists; transitive release audit remains |
| CSS/UI complexity risk | 50 | Large/fragmented visual files increase regression surface |

## 9. Commercial standards alignment

Zazu should use **WCAG 2.2** as the accessibility target for web/mobile UI. W3C identifies WCAG 2.2 as a current Recommendation and notes that it is intended for desktops, laptops, tablets and mobile devices. urlW3C WCAG 2.2 overviewhttps://www.w3.org/WAI/standards-guidelines/wcag/

For application security verification, Zazu can use **OWASP ASVS 5.0.0** as the engineering benchmark. OWASP currently lists 5.0.0 as the latest ASVS version and provides the standard freely. urlOWASP ASVShttps://owasp.org/projects/asvs

These are benchmarks, not certification claims. Zazu still needs actual test evidence against the controls relevant to its deployment and data model.

## 10. Landing-page imagery

The landing page now uses six real stock photographs vendored into `public/images/landing/stock/`. The active runtime path is repository-local; it no longer fetches landing photography from Pexels or another image host.

The temporary library covers reception/venue, catering, outdoor sound/stage, tent setup and decor. The files are optimized JPEGs and are included in the service worker's explicit precache list for offline-first rendering after service-worker installation.

Pexels permits commercial website/app use under its license, but Pexels also notes separate rights considerations for people, trademarks, logos and brands. The current temporary library is therefore suitable for visual evaluation, not a final rights-clearance conclusion. urlPexels commercial-use guidancehttps://help.pexels.com/hc/en-us/articles/360042295214-Can-I-use-the-photos-and-videos-for-a-commercial-project


## 11. Feasibility

**Technical feasibility: high.**

The system is already a modular Laravel monolith with a relational database, server-side business isolation, transactional financial safeguards and a staged offline architecture.

**Commercial deployment feasibility: conditional.**

The remaining gates are realistic, but the product is not yet ready to be represented as fully recoverable, fully offline on phone, legally operationalised or independently release-certified.

## 12. Work remaining — release-focused

### Release-blocking package
1. Current-head CI/runtime verification.
2. Populated end-to-end business workflow and commercial reconciliation.
3. Final authorization/security challenge.
4. Mobile + tablet critical workflow acceptance.
5. Real backup/restore + private-media recovery.
6. Populated-database upgrade + rollback exercise.
7. Legal/privacy/operator/customer documentation closure.
8. Final licensing/trademark/dependency clearance.
9. Final Director re-audit and release certification.

### Non-blocking future work
- Full phone-local offline store and durable mutation queue.
- Local host/device synchronization.
- Advanced Helper agent behaviour.
- Deeper niche-specific adaptation.
- Broader natural-language commands.
- Enterprise-scale infrastructure.

## 13. Director engine meeting brief

### Morpheus / Director
**Position:** Release not certified.  
**Current job:** reconcile one shared state, remove stale truth, prioritise release-blocking proof.  
**Handoff:** highest-risk gate only; no broad feature expansion while recovery/upgrade evidence is open.

### Discovery & Design
**Position:** Product architecture is coherent.  
**Focus:** validate the populated operational journey against the product specification; keep progressive disclosure and event-as-workspace principles intact.  
**Handoff:** concrete acceptance scenarios, not speculative features.

### Builder
**Position:** Most core capability is already implemented.  
**Focus:** only build what closes a verified gap: recovery instrumentation/tests, populated upgrade safety, mobile defects, legal/operator product surfaces, Helper migration.  
**Handoff:** no duplicate subsystem or competing Helper implementation.

### Guardian
**Position:** Isolation/security foundations are strong but final proof is open.  
**Focus:** adversarial business isolation, role boundaries, private media, destructive failures, duplicate submission, stale-device/offline cases and recovery rollback.  
**Handoff:** challenge every “works” claim that lacks current evidence.

### UI/UX Improvement
**Position:** Visual system and responsive shell are substantially established, but rendered acceptance is outstanding.  
**Focus:** mobile-first human-eye review, overflow, navigation, forms, tables, Helper placement, accessibility and commercial polish.  
**Handoff:** findings with observable evidence and regression risk.

### Verification & Quality
**Position:** historical automated evidence is useful but not current certification.  
**Focus:** current-head CI/browser/static-analysis/runtime evidence and evidence classification.  
**Handoff:** current candidate results only.

### Failure Case / Forensics
**Position:** previous recurring backup/restore and environment failures have been recorded and corrected.  
**Focus:** repeated or ambiguous failures in current recovery, upgrade and offline sync tests.  
**Handoff:** persistent case ID for repeat failures; no cycling on rejected hypotheses.

### Release
**Position:** commercial gate is downstream of proof, not feature count.  
**Focus:** backup/restore, upgrade/rollback, deployment configuration, dependency/licence audit, legal evidence, runbooks and release package integrity.  
**Handoff:** final release checklist state to Director.

## 14. Director conclusion

Zazu is **past the “is there a real product here?” stage**. It has a credible operational software foundation.

The current commercial question is different: **can the owner prove that this software survives real customer data, real mobile use, real failure/recovery conditions, and the legal/commercial obligations of launch?**

At present, the answer is **not yet proven**.

The next work should therefore be proof-heavy and release-controlled. More feature breadth is not the priority.

## 15. Audit references

- `docs/product-specification.md`
- `docs/ZAZU_DIRECTOR_PRODUCT_DEFINITION.md`
- `docs/V1_RELEASE_CHECKLIST.md`
- `.ai/engineering/READINESS_REGISTER.md`
- `docs/ZAZU_COMMERCIAL_LEGAL_REGISTER.md`
- `docs/ZAZU_OFFLINE_FIRST_ARCHITECTURE.md`
- `docs/ZAZU_OFFLINE_LICENSING_SPEC.md`
- `resources/views/components/zazu-helper.blade.php`
- `app/Support/ZazuHelperService.php`
- `app/Console/Commands/ZazuBackupCommand.php`
- `app/Console/Commands/ZazuRestoreCommand.php`
- `app/Services/FinanceTransactionService.php`
- `tests/Feature/BusinessIsolationTest.php`
- `tests/Feature/ZazuBackupRestoreTest.php`
- `tests/Feature/UiAccessibilityTest.php`

## 16. Audit control update — 2026-10-03

The landing visual/media cycle changed the application candidate. Real stock photographs were downloaded from individually selected Pexels source URLs by a controlled GitHub Actions vendor job and committed under `public/images/landing/stock/`. The landing page was then tightened to reduce dead whitespace, enlarge the visual field, add service coverage, improve the public navigation hierarchy, and precache the images through the service worker.

The current application candidate is `33ec396f69ddcf50b217f986611b5120e9a58d4d`. Fresh runtime certification is still not inferred from source-only inspection; the current-head workflows are the evidence gate.
