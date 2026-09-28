# Zazu EMP — Commercial Engineering Gate Map

This document is the current execution map. Dated implementation records remain historical evidence.

## 01 Core product workflow

- [x] Authentication/business foundation implemented
- [x] Customer/work foundation
- [x] Requirements/capability foundation
- [x] Quotes and quote revisions foundation
- [x] Travel/cost planning foundation
- [x] Preparation/readiness foundation
- [x] Finance foundation
- [x] Purchasing foundation
- [x] Inventory transaction foundation
- [x] Physical asset foundation
- [x] Operational reporting foundation
- [ ] Customer-facing commercial completion
- [ ] Final end-to-end workflow closure

## 02 Architecture & correctness

- [ ] Remove remaining high-risk coupling/duplication
- [ ] Enforce parent/child invariants consistently
- [ ] Verify critical state transitions under concurrency
- [ ] Confirm historical/immutable commercial snapshots
- [ ] Confirm money/quantity precision across every financial path
- [ ] Verify migration compatibility with representative existing data

## 03 Security & control

- [x] Authentication foundation
- [x] Active business context
- [x] Core business isolation
- [x] Owner/staff boundary foundation
- [ ] Complete granular permission coverage
- [ ] Full authorization-policy coverage
- [ ] Authenticated/private media verification
- [ ] Privacy/retention operational controls
- [ ] Security response procedure

## 04 Commercial completion

- [ ] Quote customer-facing presentation/delivery
- [ ] Quote acceptance
- [ ] Deposit lifecycle
- [ ] Invoice/document generation
- [ ] Payment/reconciliation completion
- [ ] Complete purchasing/receiving workflow
- [ ] Inventory reservation/allocation where required
- [ ] Asset condition/damage/loss evidence
- [ ] Activity/audit continuity
- [ ] Reminders/notifications

## 05 UX & operability

- [ ] Critical workflow browser traversal
- [ ] Desktop runtime verification
- [ ] Mobile runtime verification
- [ ] Light/dark runtime verification
- [ ] Accessibility runtime verification
- [ ] Error/recovery state verification
- [ ] Navigation and workflow continuity review
- [ ] Owner-facing diagnostics/health visibility

## 06 Recovery & release

- [ ] Backup drill
- [ ] Restore drill with representative business data/media
- [ ] Rollback procedure
- [ ] Fresh-install verification
- [ ] Existing-database upgrade verification
- [ ] Dependency/license review
- [ ] Release configuration review
- [ ] Operational runbooks
- [ ] Final release-candidate audit

## Execution rule

Do not work down this list mechanically.

Morpheus selects the next target based on:
1. release risk;
2. dependency order;
3. evidence available;
4. blast radius;
5. smallest safe action that materially advances readiness.

Tests are supporting evidence, not the objective.

Source of truth: `.ai/engineering/STATE.md` + `.ai/engineering/READINESS_REGISTER.md`.
