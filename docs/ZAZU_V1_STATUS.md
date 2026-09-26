# Zazu EMP — V1 Product / Software Status

Assessment date: 2026-09-26
Repository: Leano-Jordan/ZazuEMP
Branch: main
Verified application head: 0826b6fa435afa3f3a8eafb9c4d276057ff1443e
Latest CI result: PASS
CI tests: 51 passed, 266 assertions
CI frontend: npm ci + npm run build passed
CI database: fresh SQLite database created and application test suite passed

## 1. Current product position

Zazu EMP has moved beyond a disconnected foundation prototype.

The current application has a connected operational chain:

Authentication → Business membership → Active workspace → Dashboard/Calendar → Customer → Job → Services/Requirements → Quote → Travel / Costs / Preparation → Resource planning → Reporting

The Job/Work workspace remains the central operational record. Related customer, requirement, commercial and planning data are connected to that record rather than being implemented as isolated screens.

The foundation phase is now sufficiently integrated to support the next V1 implementation tranche.

It is not yet a V1 release candidate because several transactional, security, recovery and customer-facing layers remain unfinished or require runtime evidence.

## 2. Implemented and connected

### Identity and workspace foundation
- Authentication and registration
- Owner/staff membership model
- Server-side owner boundary
- Explicit session-backed active business context
- Secure workspace switching limited to active memberships
- Owner access selects an owned workspace correctly when the account has multiple memberships
- Staff-visible navigation respects owner-only controls

### Core operations
- Customer directory and relationship workspace
- Customer contacts and primary-contact protection
- Customer profile images
- Job creation, editing and removal
- Job lifecycle transitions
- Day/night contact assignment
- Soft deletion and historical Work references
- Calendar connected to live Work dates
- Dashboard connected to live operational signals

### Services and commercial flow
- Business service/capability catalogue
- Service/product/rental definitions
- Work-specific requirements
- Reusable capability selection
- Quote creation from requirements
- Integer-cent quote arithmetic
- Versioned quote history
- Immutable historical line snapshots
- Quote revision cloning and concurrency protection
- Work → quote navigation

### Planning
- Travel costing
- Route evidence and calculation snapshots
- Projected vs actual operational costs
- Preparation/readiness tracking
- Overdue preparation workload
- Resource planning foundations
- Inventory demand view
- Rental/asset demand view
- Supplier planning context

### Reporting
- Live operational metrics
- Job status reporting
- Quote totals grouped by currency
- Operational cost summaries grouped by currency
- Business isolation in reporting

### UX and commercial presentation
- Shared visual system
- Responsive shell
- Light/dark theme
- Icon-only theme control
- Compact account menu
- Consistent action hierarchy
- Constrained form control sizing
- Improved image inputs
- Guided selections where finite vocabularies exist
- Reduced implementation language on user-facing surfaces
- Owner Administration overview
- Foundation resource pages brought to the same structural level as the core Work/Customer pages

## 3. Architecture and security improvements completed in this sweep

- Foundation resource pages changed from static views to business-scoped controller-backed views.
- Active business context is now explicit and membership-bound.
- Owner role checks are centralized through CurrentBusiness.
- Quote revision authorization occurs before closed-work lifecycle checks, reducing cross-business information disclosure.
- Resource joins are explicitly business-scoped.
- New branding/customer/catalogue image files are cleaned up when database writes fail, reducing orphaned storage.
- EventCost category validation now uses the actual cost-category vocabulary.
- PHP runtime declaration is aligned with the PHP 8.4 CI/dependency baseline.
- Controller route integrity checks now handle invokable controllers.
- Foundation integration tests use RefreshDatabase so test data cannot contaminate later suites.
- Duplicate migration timestamp prefix was identified and retained as historical migration structure rather than destructively rewriting applied migration history.

## 4. Remaining V1 implementation gates

### Customer-facing commercial layer
- Customer-facing quote presentation/document
- Quote delivery/share workflow
- Quote acceptance
- Deposit workflow
- Commercial status model beyond internal draft/revision handling

### Finance
- Payments
- Deposits and balances
- Invoice generation
- Expenses as finance records rather than operational cost records
- Reconciliation
- Financial history and reporting

### Resource transactions
- Supplier records
- Supplier terms/contact data
- Purchasing
- Purchase orders
- Receiving/GRN
- Inventory movement history
- Stock availability
- Physical asset register
- Asset assignment/availability
- Return/condition/damage/loss records

### Control and security
- Granular roles and permissions
- Authorization policy coverage for every protected action
- Authenticated/private media delivery for identifiable customer/staff images
- Audit/activity trail
- Notifications/reminders
- Data export
- Backup/restore evidence
- Retention/deletion lifecycle controls

### Release verification
- Browser end-to-end workflow traversal
- Responsive desktop/tablet/mobile verification
- Light/dark rendered verification
- Accessibility runtime verification
- Fresh migration verification on the owner's Windows checkout
- Upgrade/migration test using the populated development database
- Release-candidate review

## 5. Known architectural debt to manage

1. Several controllers repeat small business-context helper methods. The current behaviour is safe, but the next architecture pass can reduce duplication through shared authorization/context infrastructure.
2. BelongsToBusiness automatically stamps missing business IDs but does not by itself reject an explicitly supplied incorrect business ID. Current controller boundaries protect the implemented workflows; stronger domain-level invariants should be considered before broadening write paths.
3. The migration history contains two migrations with the same 2026_09_26_000015 timestamp prefix. They are structurally distinct and should not be rewritten casually once development databases may have applied them.
4. Public-disk media remains a production security boundary. The current development path is usable, but identifiable production media should move behind an authenticated/business-authorized delivery path.

## 6. Verification evidence

The repository's Laravel CI workflow currently performs:

- PHP 8.4 environment setup
- Composer dependency installation
- Node setup
- npm dependency installation
- frontend production build
- application key generation
- fresh SQLite database creation
- full Laravel test execution

The latest exact application head passed:

51 tests / 266 assertions

This proves the current automated application suite and fresh CI database path.

It does not prove visual browser rendering, local populated-database compatibility, or production media/security configuration. Those require runtime evidence from the owner's environment.

## 7. V1 position

Foundation: integrated and ready for the next implementation tranche.

Automated application verification: green.

Core operational workflow: connected.

Commercial release: not yet ready.

The next V1 work should therefore concentrate on completing the missing transaction layers rather than adding more cosmetic foundation pages.

Recommended architectural progression:

Quote customer delivery → Acceptance/deposit → Payments/invoices → Supplier/purchasing → Inventory movement → Asset accountability → Audit/notifications → Private media → Export/recovery → Release candidate verification