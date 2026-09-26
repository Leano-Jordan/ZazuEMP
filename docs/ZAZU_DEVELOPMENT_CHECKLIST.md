# Zazu EMP — Development Control Checklist

This is the living execution map for Zazu EMP. Work it in order unless repository evidence changes the sequence.

## 01 Foundation
- [x] Laravel application and frontend foundation
- [x] Event/Work persistence
- [x] Business/Customer relationship foundation
- [x] Customer contacts
- [x] Work creation and editing
- [x] Work requirements
- [x] Business capabilities catalogue
- [x] Shared Zazu UI shell
- [x] Complete top-level UI skeleton/navigation
- [x] Shared colour/accessibility corrections
- [ ] Authentication and active business context
- [ ] Server-side business isolation
- [ ] Roles and permissions

## 02 Core Workspaces
- [x] Work workspace
- [x] Customer workspace
- [x] Requirements workspace
- [x] Capability catalogue
- [ ] Calendar connected to real Work records
- [ ] Dashboard connected to real operational signals
- [ ] Contextual activity/timeline
- [ ] Documents attached to Work

## 03 Commercial
- [ ] Quote identity and lifecycle
- [ ] Quote versions with immutable commercial snapshots
- [ ] Quote line items
- [ ] Requirement/capability snapshot inputs
- [ ] Pricing calculations
- [ ] Quote totals
- [ ] Customer-facing quote presentation
- [ ] Quote acceptance/deposit workflow
- [ ] Historical reproducibility tests

## 04 Planning & Resources
- [ ] Travel costing
- [ ] Replaceable route provider
- [ ] Distance/time evidence
- [ ] Configurable fuel and vehicle assumptions
- [ ] Projected versus actual costs
- [ ] Staff costing
- [ ] Suppliers and purchasing
- [ ] Inventory
- [ ] Asset accountability
- [ ] Preparation/readiness workspace

## 05 Finance & Completion
- [ ] Payments
- [ ] Expenses
- [ ] Deposits and balances
- [ ] Invoice/document generation
- [ ] Reconciliation
- [ ] Work completion
- [ ] Financial history

## 06 Control & Security
- [ ] Authentication
- [ ] Business membership context
- [ ] Roles/permissions
- [ ] Server-side authorization tests
- [ ] Audit/activity trail
- [ ] Reminders/notifications
- [ ] Data export/recovery
- [ ] Privacy/security hardening

## 07 Reporting
- [ ] Operational reporting
- [ ] Commercial reporting
- [ ] Resource reporting
- [ ] Finance reporting
- [ ] Dashboard signals backed by authoritative records

## 08 Verification & Release
- [ ] Full feature test suite passes
- [ ] Fresh migration verification
- [ ] Browser workflow verification
- [ ] Responsive/mobile verification
- [ ] Light/dark UI verification
- [ ] Regression verification
- [ ] Dependency/configuration review
- [ ] Backup/restore evidence
- [ ] Release candidate review

## Operating rule
Execute the smallest complete outcome that advances the current stage. Protect future scale without building speculative infrastructure. Every meaningful change ends with verification, regression checking and updated project memory.
