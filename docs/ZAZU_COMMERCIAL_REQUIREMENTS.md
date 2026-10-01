# Zazu EMP — Commercial Requirements & Tier Readiness

**Status:** Director commercial baseline  
**Date:** 2026-10-02  
**Authority:** Zazu EMP repository + Product Specification  
**Purpose:** Consolidate the working commercial packaging model, current capability position and build requirements without turning pricing into a premature feature factory.

## 1. Commercial model

Zazu is one operational product with increasing levels of sophistication.

| Tier | Working price | Promise |
|---|---:|---|
| Basic | R299/month | Keep my business organised |
| Professional | R499/month | Run my jobs and money |
| Business | R799/month | Run my people and operations |
| Business Plus | R1,299/month | Control a larger, more complex business |

These are **working pricing hypotheses**, not final contractual entitlements. Final pricing requires customer validation, cost analysis and a production entitlement/licensing model.

## 2. Tier requirements

### R299 — Basic

**Must provide**
- Business/workspace and authentication
- Customers/contacts
- Products/services
- Events/jobs and requirements
- Calendar
- Quotes and versions
- Quote acceptance
- Deposits
- Invoices
- Payments
- Basic expenses
- Dashboard
- Essential reports
- Search
- Printable documents
- Core security, auditability and data protection
- Proven backup/restore

**Outcome:** the owner can replace the main spreadsheet/admin workflow.

**Current position:** source capability is materially present. Release proof remains the gate.

### R499 — Professional

**Must provide**
- Everything in Basic
- Suppliers
- Purchase orders
- Receiving/goods received
- Cost capture
- Inventory foundations
- Product/service costing
- Event profitability
- Customer/event history
- Spreadsheet import preview and matching
- Spreadsheet export
- Stronger operational reporting

**Commercial spine:**

**Event → Quote → Deposit → Invoice → Payment → Purchasing → Receiving → Costs → Profit → Export**

**Outcome:** Zazu runs the operational and financial side rather than merely recording it.

**Current position:** foundations exist, but the integrated populated workflow, export, profitability clarity and remaining import coverage need closure.

### R799 — Business

**Must provide**
- Everything in Professional
- Staff roles and permissions
- Explicit manager policy
- Event/work assignment
- Workload visibility
- Staff activity
- Operational attention
- Approval/control workflows
- Stock/purchasing alerts
- Notifications/reminders
- Management dashboard
- Stronger resource/equipment controls

**Outcome:** a team can operate in Zazu while the owner/manager controls the operation.

**Current position:** access-control foundations exist; operational coordination features remain a material build area.

### R1,299 — Business Plus

**Must provide when validated**
- Everything in Business
- Advanced profitability by event/service/customer
- Resource capacity and allocation
- Advanced purchasing/stock controls
- Advanced management reporting
- Multi-location or equivalent multi-unit control
- Advanced import/migration
- Higher-order automation and cross-team coordination

**Outcome:** Zazu controls materially more complex operations.

**Current position:** intentionally future work. Do not build merely to justify the price.

## 3. Current capability/gap register

| Capability | R299 | R499 | R799 | R1,299 | Current Director position |
|---|---|---|---|---|---|
| Customers | MUST | MUST | MUST | MUST | Present |
| Products/services | MUST | MUST | MUST | MUST | Present |
| Events/work | MUST | MUST | MUST | MUST | Present |
| Calendar | MUST | MUST | MUST | MUST | Present |
| Quotes/acceptance | MUST | MUST | MUST | MUST | Present/further runtime proof |
| Deposits/invoices/payments | MUST | MUST | MUST | MUST | Present/further reconciliation proof |
| Expenses | MUST | MUST | MUST | MUST | Present/further reconciliation proof |
| Suppliers | Basic | Full | Advanced | Advanced | Present/foundational |
| Purchasing | — | MUST | MUST | Advanced | Present/foundational |
| Receiving | — | MUST | MUST | Advanced | Present/foundational |
| Inventory | — | MUST | MUST | Advanced | Present/foundational |
| Assets/resources | — | Useful | Full | Advanced | Present/foundational |
| Costing | Basic | MUST | Full | Advanced | Partial / needs commercial proof |
| Profitability | Basic | MUST | Management | Advanced | Partial |
| Search | MUST | MUST | MUST | MUST | Present |
| Excel import | Basic | Core | Advanced | Migration | Customer path present; product/supplier expansion remains |
| Excel export | MUST | MUST | MUST | MUST | Missing |
| Reports | Basic | Full | Management | Advanced | Present/partial |
| Roles/permissions | Core | Core | MUST | MUST | Present; runtime challenge still matters |
| Staff assignment | — | — | MUST | MUST | Missing/partial |
| Notifications/reminders | — | — | MUST | Advanced | Missing |
| Backup/restore | MUST | MUST | MUST | MUST | Tooling present; proof gate |
| Mobile | MUST | MUST | MUST | MUST | Foundation present; critical-path proof required |
| Offline/local-first | Core direction | Core direction | Core direction | Core direction | Foundation only; full offline operation is separate scope |
| Multi-location | — | — | — | MUST | Missing |
| Advanced automation | — | Useful | MUST | MUST | Partial/future |

## 4. Release blockers vs commercial gaps

### Release blockers

These must be closed before claiming production readiness regardless of price:

1. Populated end-to-end business workflow proof.
2. Real backup/restore and private-media recovery evidence.
3. Representative populated-database upgrade proof.
4. Rollback evidence.
5. Final authorization/security challenge.
6. Desktop/mobile critical workflow acceptance.
7. Financial reconciliation evidence.
8. Business-isolation evidence against realistic populated data.

### R299 commercial completion

After release blockers, confirm:

- core event/job workflow is genuinely usable;
- essential documents are reliable;
- financial records reconcile;
- import path is safe;
- recovery is understandable and repeatable;
- mobile critical path is usable.

### R499 commercial completion

Then close:

- purchasing → receiving → cost linkage;
- event profitability;
- spreadsheet export;
- products/services import;
- supplier import;
- populated Excel migration workflow;
- reporting that makes the commercial result understandable.

### R799 commercial completion

Only after the R499 bridge is stable:

- staff assignment;
- manager/owner operating views;
- workload/attention;
- notifications/reminders;
- approval controls;
- resource coordination.

### R1,299 commercial completion

Only after real usage demonstrates the need:

- advanced profitability;
- resource capacity;
- advanced reporting;
- multi-location/multi-unit controls;
- advanced migration;
- cross-team automation.

## 5. Pricing/entitlement rules

Zazu should not primarily upsell through arbitrary record counts.

Preferred upgrade triggers:
- number and sophistication of team roles;
- management controls;
- approvals;
- advanced reports;
- resource coordination;
- multi-unit operations;
- advanced migration;
- higher-order automation.

The underlying data model should not be deliberately weakened for lower tiers.

## 6. Excel continuity requirement

Excel continuity is a core commercial differentiator.

The intended journey is:

**Excel → Zazu import → preview → clean/match → approve → records → calculations → visual understanding → Zazu workflow → export back to Excel**

Import must be non-destructive and explicit:
- New
- Existing
- Duplicate
- Needs review

Existing records must not be silently overwritten.

The long-term import engine should extend consistently to:
- Customers
- Products/services
- Suppliers
- Assets/equipment where justified

Export is required so businesses do not feel trapped inside Zazu.

## 7. Director build order

The current engineering priority is not four-tier feature expansion.

**Priority 1:** close release evidence.

**Priority 2:** complete the R299 foundation.

**Priority 3:** complete the R299 → R499 bridge:

**Event → Quote → Deposit → Invoice → Payment → Purchasing → Receiving → Costs → Profit → Export**

**Priority 4:** validate with real businesses.

**Priority 5:** build R799 team/management depth from demonstrated demand.

**Priority 6:** build R1,299 complexity controls only where customer evidence supports them.

## 8. Definition of commercial readiness

A tier is commercially defensible when:
- its promised outcome is actually achievable;
- the critical workflow works with populated data;
- failure/recovery behaviour is proven;
- security/isolation is proven;
- the UX is credible on required devices;
- the tier does not depend on hidden manual intervention;
- the upgrade boundary reflects real additional value.

Feature count alone is not evidence of commercial value.
