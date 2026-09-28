# Zazu EMP — Hardening & Commercial Readiness Scorecard

## Purpose

Current working gate for development toward a commercially usable release.

This scorecard separates implementation from evidence maturity and separates **V1 release requirements** from **future scale programmes**.

## Evidence maturity

0 — not started  
1 — designed  
2 — implemented  
3 — automated evidence  
4 — runtime/CI verified  
5 — repeatedly proven through realistic/production/recovery evidence

## V1 gate domains

| Domain | What must be true |
|---|---|
| Correctness | Critical workflows produce the intended result |
| Architecture | Core structure is understandable, bounded and maintainable |
| Data integrity | Transactions, ownership, parent/child rules, precision and concurrency are protected |
| Security | Authentication, authorization, isolation and sensitive-media boundaries are enforced |
| Workflow integrity | Jobs can move through valid operational/commercial states |
| UX/accessibility | Critical workflows are understandable and usable across required contexts |
| Reliability/recovery | Failure paths are controlled and restore has been demonstrated |
| Operability | The owner can diagnose and respond to incidents |
| Upgrade/deployment safety | Clean installs and representative existing databases can be upgraded safely |
| Documentation/compliance | Required runbooks, dependency/IP/privacy evidence exist |
| Release evidence | Current CI/browser/runtime evidence supports the release claims |

## Release blocker definition

A blocker is release-critical when it can cause:
- data loss/corruption;
- unauthorized access;
- broken critical workflow;
- unrecoverable deployment/upgrade;
- inability to restore;
- materially incorrect financial/commercial state;
- inability to operate or diagnose the product.

## Future-scale gates

These are **not automatic V1 blockers** unless product scope explicitly requires them:
- SaaS billing/entitlements
- large-data performance proof
- production soak/long-term reliability evidence
- multi-location expansion
- advanced messaging/client portal ecosystem
- advanced AI automation

The architecture should preserve future options without allowing future-scale work to displace current release-risk closure.

## Operating rule

Readiness increases when meaningful engineering risk is removed **and evidence matures**.

It does not increase merely because:
- more files were created;
- more tests were added;
- another dashboard/report was written;
- an incomplete workflow was given a new UI.

Source of truth for live status: `.ai/engineering/READINESS_REGISTER.md`.
