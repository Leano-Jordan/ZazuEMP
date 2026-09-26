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
- [x] Calendar connected to real Work records
- [x] Dashboard connected to real operational signals
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
- [x] Travel costing
- [x] Replaceable route provider
- [x] Distance/time evidence
- [x] Configurable fuel and vehicle assumptions
- [x] Projected versus actual costs
- [ ] Staff costing
- [ ] Suppliers and purchasing
- [ ] Inventory
- [ ] Asset accountability
- [x] Preparation/readiness workspace

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

Current execution slice: **Planning & Resources → Projected vs actual costs / preparation workspace** is implemented.

Next execution slice: **Control & Security → Authentication, active business context and server-side business isolation**, unless repository evidence changes that sequence.


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

## Work recovery and capability update - 2026-09-26

- [x] Event schema recovery migration added for existing development databases missing `events.deleted_at` and/or `event_night_contact_id`.
- [x] Work creation pre-save variable defect fixed.
- [x] Work Edit night-contact selector restored and covered by existing Work workflow tests.
- [x] Photography and Photographer capability direction recorded.
- [x] Camera hire capability direction recorded.
- [x] Current owner documentation aligned to Isaac Junior Lehlogonolo Maluleka as solo developer/owner.
- [x] Rosscore Labs documented as future business identity, not present legal owner.

**Runtime gate:** the owner must run `php artisan migrate`, `php artisan view:clear`, and `php artisan test` on the Windows checkout. GitHub source inspection cannot prove the state of the local database or browser runtime.

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


## Planning & resources cycle - 2026-09-26

Completed:
- [x] Work-specific projected vs actual cost records
- [x] Currency-safe cost summaries without mixing currencies
- [x] Cost status lifecycle: planned, incurred, cancelled
- [x] Preparation/readiness item records
- [x] Preparation status transitions: open, blocked, ready
- [x] Preparation due dates, quantities and notes
- [x] Work workspace navigation to costs and preparation
- [x] Business ownership fields on planning records
- [x] Server-side protection against updating a preparation item through the wrong Work route
- [x] Automated workflow coverage for cost persistence and preparation status transitions

Architecture boundary:
- Cost records are operational planning records, not payment/accounting records.
- Preparation records are Work-scoped readiness records and do not yet pretend to be staff, supplier or inventory allocation.
- Business IDs are derived from the Work record rather than accepted from user input.
- Authentication and server-side business membership authorization remain the next foundation gate.


## UX navigation and job-creation hardening - 2026-09-26

Completed:
- [x] Guided job creation instead of category/event-type free typing
- [x] South Africa-oriented service choices for Catering, Decor, Sound & Entertainment, Furniture & Equipment, Photography & Video, Baking, Transport, Staff and Venue
- [x] "Other" path for services not listed
- [x] Job creation creates selected services and redirects directly to the new Job workspace
- [x] Job workspace now presents a visible next action
- [x] Job progress is visible from Job → Services → Quote → Prepare → Complete
- [x] Work-scoped pages keep a direct Job workspace action available
- [x] Adding a service returns to the Job workspace instead of leaving the user on a dead-end form
- [x] Service catalogue redesigned around visual cards
- [x] Catalogue preview images
- [x] Reusable usual prices and charging methods
- [x] Saved catalogue services appear visually when adding services to a Job
- [x] Quote price fields prefill from saved service prices
- [x] Signed-in user is explicitly shown with avatar and name
- [x] User-facing wording changed toward literal "Jobs", "Services", "Prices", "Prepare" and "Job workspace"
- [x] Form choices favour recognition over recall and reduce unnecessary typing

UX verification basis:
- Jobber's current workflow keeps clients, quotes, jobs and invoices connected and provides creation from multiple contextual locations. citeturn0search1turn0search3turn0search8
- HoneyBook's current product flow centralises service selection, project management and payment-related stages and uses reusable services rather than repeatedly defining the same offering. citeturn0search9turn0search15turn2search6
- WCAG guidance requires clear labels/instructions and supports reducing repeated entry; Nielsen Norman Group guidance supports recognition over recall and reducing cognitive load in forms. citeturn1search2turn1search6turn1search7turn1search16

Next architectural gate remains:
- [ ] Authentication and active business context
- [ ] Server-side business isolation
- [ ] Roles and permissions

## Execution cycle - 2026-09-26: UX + business-control hardening

- [x] Guided currency selection
- [x] Guided category selection where a finite vocabulary exists
- [x] Guided unit selection with flexible legacy/custom storage support
- [x] Explicit record/column hierarchy across major lists
- [x] Clickable workload quick views
- [x] Overdue preparation workload view
- [x] Central active business resolver
- [x] Model-level business ownership guard for direct business records
- [x] Business-scoped reads/writes across touched modules
- [x] Closed-work mutation protection
- [x] Central Work lifecycle transitions
- [x] Cross-business regression coverage

Still required before production security acceptance:
- [ ] Authentication
- [ ] Explicit active-business selection
- [ ] Roles and permissions
- [ ] Authorization policy coverage for every protected action
- [ ] Authenticated/private media delivery
- [ ] Full PHPUnit execution
- [ ] Fresh migration verification
- [ ] Browser/light-dark/responsive traversal
