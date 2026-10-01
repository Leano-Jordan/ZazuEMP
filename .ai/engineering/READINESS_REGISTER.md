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

Last updated: 2026-10-01


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

Last updated: 2026-10-02
