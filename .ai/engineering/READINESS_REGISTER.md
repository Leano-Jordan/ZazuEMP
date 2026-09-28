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

Last updated: 2026-09-28


## Evidence snapshot — 2026-09-28

Current Director assessment:

| Domain | Evidence maturity | Release condition |
|---|---:|---|
| Correctness | 3/5 | Runtime suite still required |
| Architecture | 4/5 | No major V1 blocker identified |
| Data Integrity | 3/5 | Populated-data/reconciliation proof required |
| Security | 4/5 | Final release security review required |
| Workflow Integrity | 4/5 | Populated end-to-end walkthrough required |
| UX / Accessibility | 3/5 | Desktop/mobile/tablet/accessibility verification required |
| Reliability / Recovery | 2/5 | Backup/restore proof required |
| Operability | 3/5 | Final operational evidence required |
| Deployment / Upgrade Safety | 2/5 | Existing populated database migration proof required |
| Documentation / Ownership / Compliance | 4/5 | Release/runbook sign-off remains |
| Release Evidence | 1/5 | Current-HEAD CI/browser/security evidence required |

The major V1 capability surface is present. Remaining release risk is concentrated in proof, recovery, device QA and defects exposed by those checks.

Current UI cycle does not create a new V1 feature expansion requirement.


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

