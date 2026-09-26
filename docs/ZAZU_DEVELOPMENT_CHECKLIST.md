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
- [x] Quote centre connected to real quote records
- [ ] Contextual activity/timeline
- [ ] Documents attached to Work

## 03 Commercial
- [x] Quote identity and lifecycle foundation
- [x] Quote versions with immutable commercial snapshots
- [x] Quote line items
- [x] Requirement/capability snapshot inputs
- [x] Draft pricing calculations
- [x] Quote totals
- [ ] Customer-facing quote presentation
- [ ] Quote acceptance/deposit workflow
- [x] Historical reproducibility coverage

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

## Current execution position — 2026-09-26

Completed slices: **Commercial → Quote foundation** and **Planning & Resources → Travel costing foundation**

Implemented in this cycle:
- Quote identity and version tables
- Quote line snapshots
- Draft quote creation from Work requirements
- Server-side line validation
- Server-side subtotal/total calculation
- Quote revision cloning with preserved historical snapshot
- Revision concurrency protection
- Quote centre and Work-specific quote views
- Work → Quote navigation
- Quote workflow tests
- Laravel 13 compatibility checked against current official documentation for Eloquent revision lookup and pessimistic locking

Not yet proven locally:
- PHPUnit execution
- fresh migration execution
- browser traversal
- rendered responsive/light/dark verification
- customer-facing quote document generation

Current execution slice: **Planning & Resources → Travel costing foundation** is implemented.

Next execution slice: **Planning & Resources → Projected vs actual costs / preparation workspace**, unless repository evidence changes that sequence.


Travel costing foundation implemented in this cycle:
- Work-specific travel calculation workspace
- route label + provider boundary
- origin/destination
- distance and travel time inputs
- configurable fuel price and vehicle consumption
- one-way / round-trip handling
- customer rate per kilometre
- authoritative calculated distance, fuel litres, fuel cost and customer charge
- historical calculation snapshot
- contextual Work → Travel navigation
- automated calculation coverage

## Operating rule
Execute the smallest complete outcome that advances the current stage. Protect future scale without building speculative infrastructure. Every meaningful change ends with verification, regression checking and updated project memory.


## Foundation sweep - 2026-09-26

Completed:
- [x] Customer edit workflow
- [x] Customer profile photo capture/update/remove
- [x] Optional Day and Night customer contacts during creation
- [x] Additional customer contact create/edit/remove workflow
- [x] Primary-contact protection during removal
- [x] Work edit and active-record removal controls
- [x] Work Day and Night contact assignment
- [x] Work soft deletion with historical data retained
- [x] Historical Event lookup preserved for Quotes after Work removal
- [x] Dashboard connected to live Work/Customer/Quote signals
- [x] Calendar connected to live Work dates
- [x] Shared customer/profile avatar component
- [x] User/staff profile photo field prepared in the data model
- [x] Expected-route registration test
- [x] Blade named-route integrity test
- [x] Customer/contact/profile-photo workflow tests
- [x] Work edit/removal/day-night tests
- [x] Privacy engineering baseline documented

Commercial/security boundaries recorded:
- Profile photos are validated to approved image formats and a size limit.
- Development photo delivery currently uses Laravel's public disk so the feature can be exercised.
- Production must use authenticated/business-authorized media access for identifiable customer and staff images.
- Authentication, active business context, business isolation, roles/permissions, auditability and retention/deletion workflows remain production gates.
- Soft deletion preserves history; it is not a substitute for a lawful retention schedule.
- Local PHPUnit/migrations/browser rendering remain unverified in this environment.
