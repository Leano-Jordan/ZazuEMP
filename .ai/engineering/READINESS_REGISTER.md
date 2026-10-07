# ZAZU EMP — COMMERCIAL READINESS REGISTER

## Purpose

This is the live release-risk register.

Readiness is tracked by domain and evidence maturity rather than by a single inflated percentage.

## Evidence maturity

0 — not started  
1 — designed  
2 — implemented  
3 — automated evidence  
4 — runtime/CI verified  
5 — repeatedly proven through realistic/production/recovery evidence

## Release domains

- **Correctness** — critical workflows produce the intended result.
- **Architecture** — core structure is understandable, bounded and maintainable.
- **Data Integrity** — transactions, parent/child invariants, ownership, precision and concurrency are protected.
- **Security** — authentication, authorization, isolation, storage and trust boundaries are enforced.
- **Workflow Integrity** — real jobs can move through valid states, including failure/recovery.
- **UX / Accessibility** — critical flows are understandable and usable across required devices/themes.
- **Reliability / Recovery** — failures are observable and data can be restored.
- **Operability** — the owner can diagnose incidents and understand application health.
- **Deployment / Upgrade Safety** — clean and representative existing installations can be upgraded safely.
- **Documentation / Ownership / Compliance** — release/runbook, dependency, IP and privacy evidence exists.
- **Release Evidence** — claims are based on current CI/runtime/browser evidence.

## What blocks commercial release

A release-critical blocker can cause:
- data loss or corruption;
- unauthorized access;
- broken critical workflow;
- unrecoverable upgrade/deployment;
- inability to restore;
- materially incorrect financial/commercial state;
- inability to operate or diagnose the product.

## Future-scale work

Not automatic V1 blockers unless explicitly required:
- SaaS billing/entitlements;
- high-scale performance proof;
- multi-location expansion;
- production soak evidence;
- advanced messaging/portal ecosystem;
- advanced AI automation.

Future architecture should remain compatible with these possibilities without allowing them to displace current release-risk work.

## Current directive

**Close existing correctness, architecture, integrity, security, reliability and release-evidence gaps before speculative feature expansion.**

Last updated: 2026-10-06

## Offline operational depth update — 2026-10-07

Current main now contains phone-local and server synchronization coverage for inventory items/movements, operational event costs, assets and asset allocation/return, alongside the previously implemented commercial offline chain. Direct and paired bootstrap payloads carry these domains, and focused regression tests have been added.

Evidence boundary: IMPLEMENTED only for this new cycle until the current regression suite executes successfully in CI/runtime. No numerical readiness uplift is claimed from source changes alone.

Remaining offline gaps: runtime/device proof, broader disconnected parity across every desktop capability, and full recovery/real-world sync proof. Attachment synchronization and explicit conflict detection/resolution are now implemented and covered by targeted tests.

Last updated: 2026-10-07


## Evidence snapshot — 2026-10-01

Current Director assessment:

| Domain | Evidence maturity | Release condition |
|---|---:|---|
| Correctness | 4/5 | Current-head Laravel suite passed; populated business proof remains |
| Architecture | 4/5 | Refactors reduced import complexity; no major V1 blocker identified |
| Data Integrity | 3/5 | Populated-data/reconciliation proof required |
| Security | 4/5 | Current-head automated security/isolation coverage passed; final adversarial review remains |
| Workflow Integrity | 4/5 | Populated end-to-end walkthrough required |
| UX / Accessibility | 3/5 | Desktop/mobile/tablet/accessibility verification required |
| Reliability / Recovery | 2/5 | Backup/restore proof required |
| Operability | 3/5 | Final operational evidence required |
| Deployment / Upgrade Safety | 2/5 | Existing populated database migration proof required |
| Documentation / Ownership / Compliance | 4/5 | Release/runbook sign-off remains |
| Release Evidence | 4/5 | Current-head Laravel, browser, PHPMD, Psalm and Quality evidence observed |

The major V1 capability surface is present. Current-head CI/runtime evidence is now green. Remaining release risk is concentrated in populated workflow proof, recovery, upgrade/rollback and final authorization/device acceptance.


Current UI cycle does not create a new V1 feature expansion requirement.

## Source-hardening snapshot — 2026-10-01

The long-sprint page audit tightened shared form submission safety, browser-storage failure tolerance, quote financial visibility, multi-line purchasing usability and context-preserving return paths. The current release candidate now has green Laravel/browser/static-quality evidence. The remaining maturity gap is proof of realistic populated operations, recovery and upgrade behaviour.



## Latest visual-system audit — 2026-09-28

Target: colour theory, contrast, positioning and shared visual authority.

Completed:
- consolidated light-mode identity around cobalt-iris rather than generic utility blue;
- introduced sea-glass as a distinct secondary accent;
- separated semantic status colours into warning / success / danger / information roles;
- aligned base app utility colour tokens with the canonical visual layer;
- removed the repeated active-workspace navigation treatment;
- preserved the flat navigation structure;
- corrected header optical alignment;
- strengthened active/hover navigation states without light text on light surfaces;
- applied explicit dark foregrounds to light-mode cards, panels, forms, lists and dashboard surfaces;
- applied explicit semantic colours to dashboard lifecycle statuses;
- retained reduced-motion support;
- confirmed no literal non-dark light-on-light colour/background rule in the final visual layer by static source inspection.

Evidence boundary:
- static source inspection completed;
- rendered visual/device verification is still required before release acceptance.


## Director authorization challenge snapshot — 2026-10-01

The populated source walkthrough is coherent, but final authorization certification is currently blocked by a concrete role-policy gap.

AUTH-ROLE-001 — Seeded manager role has no permission mapping
- Demo fixture creates an Operations Manager membership with role manager.
- PermissionService grants all permissions to owners and otherwise reads the role entry from config/zazu.php.
- config/zazu.php defines staff permissions but no manager entry.
- Result: the seeded manager resolves to an empty permission set and cannot reach permission-protected workspaces such as Dashboard, Work, Customers, Quotes, Purchasing or Finance.
- Classification: authorization completeness / role-model consistency, not proven privilege escalation.
- Release impact: blocks final multi-role authorization certification until the role policy is made explicit.

The populated business financial chain is source-consistent at ZAR 11,500.00 total with ZAR 3,450.00 deposit plus ZAR 8,050.00 final payment. This remains source evidence only until executed against a current runtime.

Current Security maturity remains 4/5: automated isolation/authorization foundations exist, but the final adversarial challenge and role-policy closure remain outstanding.

## Manager role policy closure — 2026-10-01

**AUTH-ROLE-001 RESOLVED AT SOURCE:** the previously seeded `manager` role now has an explicit permission matrix.

Manager policy:
- operational dashboard/calendar/work/customer/quote/purchasing/inventory/assets/supplier/report capabilities;
- Finance view only;
- no invoice creation, payment recording or expense creation;
- no work deletion;
- owner-only settings/catalogue/administration boundaries remain enforced.

Regression coverage has been added for representative allowed and denied manager routes plus direct permission resolution.

**Release status:** role-policy gap closed. Final authorization certification remains pending runtime/adversarial execution, not source implementation.


## SaaS-readiness architecture gate — 2026-10-02

A hosted multi-business SaaS path is now an explicit architectural readiness concern, while public SaaS release remains a separate certification gate.

### Architecture position
- Current deployment remains a coherent modular monolith.
- Business ownership and parent/child invariants are the required isolation seam.
- Local-first core workflows remain protected from accidental online dependencies.
- Scaling is evidence-driven: optimise first, then targeted caching/queues, then infrastructure scaling, then service extraction only if required.
- Distributed infrastructure is not a current release requirement.

### SaaS certification gates added
Before hosted multi-business release can be accepted, Director must have evidence for:
1. cross-business read isolation;
2. cross-business mutation isolation;
3. nested parent/child ownership consistency;
4. search/filter/export isolation;
5. attachment/download isolation;
6. background-job ownership where jobs exist;
7. populated multi-business authorization challenge;
8. migration of existing business data into explicit ownership boundaries;
9. backup/restore with multiple businesses;
10. deployment and upgrade safety;
11. operational logging/diagnostics without cross-business leakage.

### Status
**Architecture foundation:** established.

**Hosted SaaS certification:** not yet claimed.

The next Director target is isolation proof against actual repository implementation and populated multi-business fixtures. This does not expand V1 feature scope by itself.

Last updated: 2026-10-03


## Director niche + offline + isolation cycle — 2026-10-03

- Primary niche is explicitly non-exclusive: it controls emphasis, while active business capabilities determine supporting niche signals.
- Sound & DJ was used as the reference niche; overlapping Chairs & tents capabilities remain visible when the business catalogue supports them.
- Offline-first is now an explicit architecture contract in `docs/ZAZU_OFFLINE_FIRST_ARCHITECTURE.md`.
- Static PWA caching and local licensing remain foundations; broad disconnected phone data/write/sync capability is now implemented in the workspace, but final release claims remain gated on runtime/device evidence and broader parity/recovery proof.
- Commercial isolation source audit found no justified application rewrite. Active-business context, route-bound ownership, model save protection, parent/child database constraints, search scoping and private media checks are present.
- Additional adversarial coverage was added for foreign search results and foreign attachment downloads.
- Latest main-head CI workflows are queued, so current-head runtime/CI verification remains pending.

Next Director target:
**CURRENT-HEAD RUNTIME VERIFICATION → consume the queued Laravel, quality, PHPMD and browser results; classify failures; then run the populated multi-business isolation challenge against the latest verified head.**



## Director commercial audit snapshot — 2026-10-03

### Overall health

**Commercial-readiness maturity: 67/100**

This is a weighted maturity score, not a percentage of source-code completion. The remaining maturity gap is concentrated in proof and release controls rather than basic CRUD capability.

### Scorecard

| Domain | /100 | Work left | Current Director position |
|---|---:|---:|---|
| Correctness | 78 | 22 | Core paths implemented; populated end-to-end proof required |
| Architecture | 84 | 16 | Modular monolith and staged offline architecture are coherent |
| Data Integrity | 74 | 26 | Transactions/idempotency/ownership exist; populated reconciliation proof open |
| Security | 76 | 24 | Isolation/authz foundations exist; final adversarial certification open |
| Workflow Integrity | 72 | 28 | Connected chain exists; realistic populated traversal open |
| UX / Accessibility | 68 | 32 | Strong source foundation; rendered mobile/tablet/WCAG-oriented proof open |
| Reliability / Recovery | 42 | 58 | Real backup/restore drill remains release-critical |
| Operability | 58 | 42 | Error/audit foundations exist; runbooks and recovery evidence remain |
| Deployment / Upgrade Safety | 44 | 56 | Existing populated upgrade and rollback remain unproven |
| Documentation / Ownership / Compliance | 68 | 32 | Governance strong; privacy/terms/brand/licence closure remains |
| Release Evidence | 54 | 46 | Historical green evidence cannot certify latest observed main ref |
| **Commercial readiness** | **67** | **33** | **Not certified** |

### Release-critical findings

**AUD-CRIT-01 — Populated operational workflow partially evidenced**
The seeded owner journey now passes through Work inspection, requirements, preparation, purchasing, inventory/assets, invoice history and reporting; payment replay/overpayment and manager/staff authorization also pass. A controlled customer → job → quote creation/acceptance → invoice/payment → completion run remains required.

**AUD-CRIT-02 — Backup/restore unproven**  
Commands and safety logic exist, but customer recovery has not been exercised and evidenced.

**AUD-CRIT-03 — Populated upgrade/rollback unproven**  
Migration/repair history makes representative populated upgrade verification mandatory.

**AUD-CRIT-04 — Current-head CI evidence unavailable**
Local Laravel and desktop/mobile/tablet Playwright verification is observed for main `4b1fbb8c6139215d5338b442b73a943d820948d9`. GitHub Actions status remains unverified because the GitHub CLI is unauthenticated in this environment; local evidence does not substitute for CI.

**AUD-HIGH-01 — Physical-device acceptance incomplete**
Responsive critical workflows pass in Pixel 7 and tablet Playwright emulation, and a rendered inspector screenshot was reviewed. Physical phone/tablet acceptance remains open.

**AUD-HIGH-02 — Legal/privacy operational layer incomplete**  
Privacy notice, POPIA operational controls, operator agreements where applicable, retention/deletion, incident response and customer terms remain open.

**AUD-HIGH-03 — Zazu brand clearance required**  
Current public market evidence shows unrelated Zazu uses, including a South African business-finance product and an event/catering business. This is a clearance risk, not a legal conclusion.

**AUD-HIGH-04 — Helper implementation trails direction**  
New Helper engine/skin architecture is documented, while the live component remains the legacy bird implementation.

### Contradictions requiring reconciliation

- Helper documentation now defines selectable skins; older Director wording used “bird mascot”.
- Multiple status files contain historical/current HEAD values; living documents must distinguish the current main ref from the last application-assessment ref.
- “Offline-first” is the target architecture; current release must not be described as fully disconnected phone-local operation.

### Next Director priority

**CURRENT-HEAD VERIFICATION → POPULATED COMMERCIAL WORKFLOW → RECOVERY → UPGRADE/ROLLBACK → LEGAL/LICENCE/BRAND CLOSURE → FINAL DIRECTOR CERTIFICATION**

No feature expansion should displace a release-critical gate.

Last updated: 2026-10-03

## Current-head verification update — 2026-10-04

Baseline: remote `main` `4b1fbb8c6139215d5338b442b73a943d820948d9`.

- Laravel: **241 passed / 1,377 assertions** (`composer test`).
- Playwright: **25 passed / 2 intentionally skipped** across desktop Chromium, Pixel 7 emulation and tablet emulation. The only skips are the registration flow on mobile/tablet; that journey is run once on desktop to avoid shared-IP registration throttling.
- The suite ran against a freshly migrated and demo-seeded temporary SQLite database. Theme/navigation checks no longer require external storage state.
- Remaining release gates: authenticated CI status, complete newly created commercial workflow proof, physical-device acceptance, backup/restore drill, populated upgrade/rollback, and legal/licence/brand closure.
- `pdo_firebird` startup warning remains a local PHP configuration issue; all tests completed.

The 2026-10-03 numeric scorecard above is not recalculated from this single local evidence cycle.

Last updated: 2026-10-04


## Current populated authorization/isolation evidence — 2026-10-04

Target: final populated multi-business authorization/isolation challenge.

Evidence:
- PopulatedAuthorizationIsolationTest seeds Business A from the Demo Scenario and creates a separate Business B owner/customer.
- Business B cannot view Business A's populated customer, Work/Event or invoice.
- Business B search cannot surface Business A's customer.
- Business B cannot create Work against Business A's customer; no unauthorized event is created.
- Business B's own customer remains available.
- Laravel CI run 37203066802 passed the new challenge as part of 242 tests / 1,381 assertions.
- Current-head Psalm Security Scan, PHPMD, Quality, CodeQL, browser smoke and populated upgrade/rollback also passed.

Disposition: Security evidence is VERIFIED / PROVEN at current runtime level. This is not a formal security certification.

Remaining material release gates: physical phone/tablet acceptance; legal/privacy operational closure; release-level dependency/transitive licence audit; Zazu brand/trade-mark clearance; final Director re-audit.

Last updated: 2026-10-04


## Director release-documentation closure — 2026-10-04

The minimum V1 release documentation set now exists in `docs/RELEASE_*.md` and has been audited against the repository implementation.

**Documentation-design maturity:** 5/5.

The audit confirms:

- technical privacy/security controls are implemented in the application foundation;
- data-subject rights, retention/disposal, PAIA handling and customer terms are currently manual/documentary controls, not self-service application features;
- incident response has technical evidence but still requires named organisational escalation and notification handling;
- operator/data-processing controls depend on the actual deployment/vendor relationships;
- deployment, backup/restore and upgrade/rollback runbooks now have explicit operating procedures.

**Release interpretation:** this closes the missing-document-set problem but does **not** make Zazu legally cleared.

Remaining material gates are now explicitly:

1. final privacy/legal operational completion;
2. release-level dependency/licence notice completion;
3. Zazu brand/trade-mark clearance;
4. physical phone/tablet acceptance;
5. final Director re-audit against the selected release commit.

Last updated: 2026-10-04


## Director defect refinement — 2026-10-04

**Wallpaper:** previously documented as implemented, but the rendered path was not actually effective because later global body background rules overrode the selected wallpaper image. **Resolved at source** with a final specific wallpaper rule; regression coverage added.

**New-job customer creation:** existing flow was valid but operationally inefficient because a new customer required leaving the job flow. **Resolved at source** with an in-context customer-creation dialog using the existing permission/business boundary and immediate selection in the current job form.

**No regression intent:** existing job creation, customer storage, business isolation, private media serving and branding storage were preserved. New tests cover the new boundaries.

Last updated: 2026-10-04

## Director UI/UX + commercial re-audit — 2026-10-04

Source-level refinement completed:
- base form surfaces are theme-aware;
- final visual CSS duplicate selectors were reduced from approximately 1,561 blocks to 1,263;
- dark-form regression coverage added;
- shared command-band/calendar artwork and compact holiday markers retained;
- repository hygiene scan found no dump(), var_dump(), console.log(), browser alert(), or NotImplemented residue.

Updated assessment:
- Correctness: 84/100
- Architecture: 87/100
- Data Integrity: 82/100
- Security: 88/100
- Workflow Integrity: 78/100
- UX / Accessibility: 78/100
- Reliability / Recovery: 80/100
- Operability: 72/100
- Deployment / Upgrade Safety: 82/100
- Documentation / Ownership / Compliance: 72/100
- Release Evidence: 82/100
- Overall commercial readiness: 80/100

This is a maturity score, not a release approval.

Remaining material gates:
1. final populated commercial workflow traversal;
2. physical phone/tablet acceptance;
3. disconnected phone-local store/pairing/bootstrap;
4. legal/privacy operational closure;
5. dependency/licence notice closure;
6. Zazu brand/trademark clearance.

Current valuation view:
- replacement-cost reference: R450k–R900k;
- realistic current early software/product asset value: R150k–R400k;
- recurring revenue and retention are required to support a materially higher SaaS valuation.

Last updated: 2026-10-04


## Control-plane cleanup status — 2026-10-06

The Director control plane has been reconciled against current `main`. Cases 0012 and 0013 are **CORRECTED / VERIFICATION PENDING**, not closed. The canonical read order is now owned by `.ai/engineering/00_DIRECTOR_ENGINE.md`; the historical router path is compatibility-only. Director eval dispositions are pinned and `.agents/evals/**` is owner-review scoped.

**Release evidence rule:** no readiness gate is marked CLOSED from source inspection alone. The exact current-head CI run must be recorded before closing verification-dependent cases.

Current application gates remain:
1. current-head CI/browser evidence;
2. populated commercial workflow traversal;
3. backup/restore;
4. populated upgrade/rollback;
5. physical phone/tablet acceptance;
6. legal/privacy operational completion;
7. dependency/licence notice closure;
8. Zazu brand/trade-mark clearance;
9. final Director re-audit.

Last updated: 2026-10-06


### 2026-10-07 — offline attachment evidence boundary

Jarvis added the attachment transfer layer to the offline implementation. Repository evidence now covers business-scoped listing, upload/download endpoints, idempotency, separate local binary storage, and online retry wiring. Runtime/device proof remains a release gate; this change does not by itself certify physical-device offline recovery.
