# Zazu EMP — V1 Product / Software Status

Assessment date: 2026-09-26
Repository: Leano-Jordan/ZazuEMP
Branch: main
Current application head: 112dd4e4680fd887b23ab89dbed4bebb86bbce58
Latest CI for current head: queued at time of this update

## 1. Current position

Zazu EMP has a connected operational foundation and now has live transaction layers for finance, purchasing, inventory and physical assets.

Core chain:

Authentication → Business → Customer → Work → Requirements → Quote → Finance → Purchasing → Inventory / Assets → Preparation → Execution

The system is still not a V1 release candidate because customer-facing quote acceptance/deposits, control/audit features, recovery/export and final runtime verification remain.

## 2. Implemented

### Identity and workspace
- Authentication and registration
- Owner/staff membership
- Server-side owner boundary
- Session-backed active business context
- Business-scoped navigation and writes
- Registration now detects an outdated local schema and returns a usable validation error instead of an uncaught database exception

### Core operations
- Customers and contacts
- Work/job lifecycle
- Calendar and dashboard
- Requirements and capability catalogue
- Quotes, revisions and historical snapshots
- Travel, operational costs and preparation

### Finance
- Business-scoped invoices
- Invoice totals linked to quote versions
- Customer payments
- Payment methods and references
- Outstanding balance calculation
- Overpayment rejection
- Automatic invoice paid/issued status
- Finance expenses
- Finance overview for invoiced, received and expense totals

### Purchasing
- Supplier register
- Supplier contact details
- Purchase orders
- Purchase order line items
- Supplier/business isolation
- Purchase order status
- Receiving through purchase-order status

### Inventory
- Inventory item register
- SKU/unit/reorder level
- Auditable movement history
- Receipts, issues, returns and adjustments
- Negative-stock protection
- Purchase-order receipt automatically creates inventory stock movements
- Stock-on-hand derived from movement history rather than job demand

### Physical assets
- Individual asset register
- Asset tags
- Condition, location and acquisition data
- Availability status
- Job allocation
- Return/release workflow
- Business-scoped allocation protection

### UX
- Shared Zazu visual system
- Responsive shell
- Light/dark theme
- Existing navigation retained and extended with Finance and Purchasing
- Login visual now uses the supplied repository catering image with controlled cover cropping and a readable overlay

## 3. Remaining V1 gates

### Customer-facing commercial layer
- Quote delivery/share
- Customer acceptance
- Deposit workflow
- Commercial status lifecycle

### Finance completion
- Reconciliation workflow
- Finance-grade reporting/history
- Invoice document rendering/download
- Deposit-specific lifecycle if required by final commercial rules

### Purchasing/inventory/assets completion
- Multi-line purchase-order editor rather than the initial one-line form
- Receiving/GRN detail and partial receipt handling
- Inventory allocation/reservation against Work
- Asset damage/loss evidence and condition history
- Supplier terms and richer purchasing history
- Stronger inventory costing rules if required for accounting-grade reporting

### Control layer
- Granular permissions
- Complete authorization policy coverage
- Audit/activity trail
- Notifications/reminders
- Export
- Backup/restore evidence
- Retention/deletion controls
- Authenticated/private production media delivery

### Release proof
- Browser end-to-end traversal
- Desktop/tablet/mobile runtime verification
- Light/dark runtime verification
- Accessibility runtime verification
- Fresh migration verification on the owner's Windows checkout
- Upgrade test against populated development database
- Dependency/license review
- Final release-candidate audit

## 4. Known architectural debt

1. Several controllers still repeat small business-context checks.
2. Some child models rely on controller-level business authorization rather than enforcing every parent/child invariant independently.
3. Two migrations share the 2026_09_26_000015 timestamp prefix. They should not be rewritten casually after development databases have applied them.
4. Some reporting/UI summaries still use PHP float conversion for decimal monetary values. Finance transaction validation now uses integer cents; broader reporting should follow the same rule.
5. Public-disk identifiable media remains a production security boundary.

## 5. Verification

The Laravel CI workflow runs on pushes to main and performs PHP 8.4 setup, Composer installation, Node/npm build, fresh SQLite creation and the full Laravel test suite.

Current head CI run: 36265411148
Status at documentation update: queued.

The implementation must not be treated as verified until this run completes successfully. The repository has previously demonstrated a green CI baseline of 75 tests / 367 assertions before this transaction-layer tranche.

## 6. V1 position

Foundation: complete.

Finance: implemented foundation.

Purchasing: implemented foundation.

Inventory: implemented transaction foundation.

Assets: implemented accountability foundation.

Commercial completion: still required.

Control/recovery/release proof: still required.

The next work should deepen transaction correctness and customer-facing commercial completion, not create more placeholder resource screens.
