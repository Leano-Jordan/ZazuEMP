# Zazu EMP — Three-Tier Capability Matrix

**Status:** Founder-approved product direction; capability allocation is a design baseline, not an enforcement specification  
**Date:** 2026-11-27  
**Authority:** Founder direction in the Zazu Engine Director session  
**Canonical tiers:** Basic · Intermediate · Professional  
**Scope:** Product capability progression only. No prices, billing rules or numerical quotas are approved here.

## 1. Product principle

Zazu is one event-management and business-operations product with three levels of operational capability:

- **Basic:** a small business can genuinely run on Zazu.
- **Intermediate:** a growing business can coordinate more of its operation in Zazu.
- **Professional:** an established or more complex business can manage scale, oversight and advanced control.

The tiers differentiate business capacity and operational sophistication. They must not create three separate products or cripple the Basic experience.

## 2. Non-negotiable foundation shared by every tier

These are product-quality guarantees, not premium features:

- Supported functionality remains dependable online and offline.
- Offline operation does not require a remote entitlement check.
- Core reliability, security, data integrity and recovery safeguards are not reduced by tier.
- Users retain access to their own existing business records.
- Users can export their business data.
- Existing records remain readable after downgrade or entitlement expiry.
- A tier change never changes a user's role, permissions or business-data ownership.
- Desktop and mobile remain first-class experiences.
- Business presets keep irrelevant service categories and workflows out of the user's way without deleting or fragmenting shared capabilities.
- No tier is allowed to silently lose or corrupt queued offline work.

A feature may have a tier-specific capacity or advanced workflow, but its underlying reliability and data-safety contract remains shared.

## 3. Capability progression

| Capability area | Basic | Intermediate | Professional |
|---|---|---|---|
| Business workspaces | One operating business as the initial target | Expanded business operations | Complex business structures and multiple locations |
| Customers | Core customer records and history | Expanded customer operations and workflows | Advanced customer oversight and consolidated views |
| Events and jobs | Create, plan, manage and complete everyday jobs | Higher operational capacity and richer coordination | Complex, high-volume and cross-location oversight |
| Quotes and invoices | Essential quote and invoice lifecycle | More connected commercial workflows and controls | Advanced commercial controls and oversight |
| Payments and expenses | Record and review essential financial activity | Connected costs, purchasing and profitability | Deeper financial analysis and consolidated performance |
| Products and services | Essential business catalogue | Expanded catalogue and operational costing | Advanced catalogue governance across complex operations |
| Suppliers | Essential supplier details where needed for core work | Supplier management and supplier workflows | Advanced supplier oversight and cross-location coordination |
| Purchasing | Essential visibility needed to complete supported jobs | Purchase orders, receiving and purchasing controls | Advanced procurement oversight and controls |
| Inventory and assets | Essential visibility needed by included workflows | Operational inventory and asset management | Advanced resource, asset and capacity oversight |
| Reporting | Essential business and job summaries | Connected operational and profitability reporting | Advanced analytics, comparisons and consolidated reporting |
| Team access | Owner-led operation | Team members, roles and collaboration | Advanced team structures, controls and oversight |
| Devices and sync | Sensible owner PC + primary phone continuity | Broader multi-device synchronisation and collaboration | Larger operational device/team footprint |
| Automation and integrations | Core built-in workflows | Selected productivity automation where justified | Advanced automation, integrations and API capabilities |
| Business locations | Single-location operation | Stronger operational coordination | Multiple locations and consolidated oversight |
| Support and administration | Self-service essentials and portable local backup | Expanded operational controls and managed-service candidates | Advanced administration and enterprise-oriented controls |

This table expresses intended progression, not a promise that every row is fully implemented today. Each capability must be mapped to actual source behaviour and acceptance evidence before it is advertised or enforced.

## 4. Basic — genuine small-business operation

Basic must be useful indefinitely, not a demo or a temporary trial shell.

It should let an owner-led small business:

- Maintain customers and business details.
- Create and manage events/jobs.
- Manage essential requirements and preparation.
- Create quotes and invoices.
- Record payments and essential expenses.
- Use the calendar and core dashboard.
- Maintain products/services relevant to its business preset.
- Access essential documents and business history.
- Work offline across the supported Basic workflows.
- Create and retain local backups and export business data.
- Use a primary desktop and phone workflow without making connectivity a prerequisite.

Basic may have a smaller operational allowance than higher tiers once the exact limits and downgrade behaviour are approved. Historical records and existing active work must not be disabled merely because an allowance is reached.

## 5. Intermediate — connected business operations

Intermediate should remove the operational fragmentation that appears as a business grows.

In addition to Basic, it is intended to provide:

- Expanded customer and job capacity.
- More complete supplier management.
- Purchasing and purchase-order workflows.
- Receiving and stock movement workflows where implemented.
- Inventory and asset management.
- Product/service costing and event cost capture.
- Connected profitability and operational reporting.
- Team collaboration, membership and permissions.
- Broader device synchronisation.
- Stronger operational controls and workflow visibility.

Intermediate is the natural level for a business that needs Zazu to coordinate not just customer jobs, but also people, suppliers, resources and costs.

## 6. Professional — advanced business control

Professional should serve established businesses with greater operational complexity.

In addition to Intermediate, it is intended to provide:

- Multiple business locations or operational units.
- Consolidated oversight across locations.
- Advanced financial, operational and profitability analytics.
- Advanced resource and capacity management.
- More sophisticated team administration and approval controls.
- Automation and integrations.
- API access where securely implemented.
- Higher operational and storage allowances, subject to infrastructure and cost validation.
- Advanced administration and reporting.

Professional must not be used as a dumping ground for features that are merely unfinished in lower tiers. Every Professional capability must provide a real advantage to a more complex business.

## 7. Capacity limits — design rules, not approved numbers

Potential capacity dimensions include:

- Number of customer records.
- Number of concurrently active jobs.
- Number of users or team members.
- Number of paired/synchronised devices.
- Number of business locations.
- Attachment and backup storage.
- Automation and integration volume.

No numerical thresholds are approved by this document. Thresholds require usage evidence, infrastructure/cost analysis and explicit Founder approval.

Apply limits safely:

1. Prefer limits on new capacity creation over restrictions on existing records.
2. Count active jobs separately from completed/archived history.
3. Reaching a limit must not prevent users from viewing, exporting or completing existing work.
4. Do not require deletion of customer records to downgrade.
5. Do not discard offline queued mutations when a limit is reached.
6. Explain the specific limit and the next available action without interruptive advertising.
7. Enforce any approved limit consistently on the server and in offline-capable client workflows; client-side hiding is not security.
8. A limit must never create contradictory online/offline states or duplicate records on reconnect.

## 8. Separate commercial entitlement from presentation preference

These are distinct concepts:

- **Commercial entitlement** determines which tier capabilities and approved capacity allowances the business can use.
- **Experience preference** determines how much detail, guidance and operational context an individual member sees.
- **Role and permissions** determine which records and actions that member is authorised to access.

A Basic member may prefer a detailed interface. A Professional member may prefer a simplified interface. Neither preference changes the commercial entitlement or authorisation.

The existing experience-level implementation currently uses the identifiers `basic`, `intermediate` and `advanced`. The customer-facing name for the third level is now **Professional**. Preserve the internal `advanced` identifier as a compatibility mapping until a separately tested migration is justified; do not break signed licences, stored preferences or existing tests merely to change a label.

## 9. Offline and downgrade contract

- Offline reliability is shared across tiers for each supported workflow.
- An offline installation resolves its entitlement locally; it must not require a network call to keep operating.
- Entitlement expiry or downgrade resolves to Basic continuity.
- Existing records remain accessible and exportable.
- Existing active jobs can be completed.
- Existing offline queue entries remain durable and reconcile safely.
- Higher-tier capabilities that are no longer entitled may become read-only for new creation, but only after the exact affected workflows and downgrade behaviour are approved and tested.
- No blanket entitlement middleware may block customer-data routes.

## 10. Implementation and release boundary

This matrix does not authorise immediate feature gating, numerical quotas, billing automation, production licensing changes or removal of existing functionality.

Implementation sequence:

1. Reconcile this matrix with current source, routes, models, navigation and offline workflows.
2. Mark each capability as implemented, partial, missing, or future.
3. Identify the smallest server-enforced entitlement boundaries for capabilities that are genuinely tier-specific.
4. Specify safe online/offline limit behaviour and downgrade handling for each boundary.
5. Add regression tests for ownership, permissions, data preservation, queue durability and reconnect.
6. Obtain Founder approval for exact numerical limits and any irreversible or material capability restriction.
7. Implement only the approved boundaries and verify on an exact commit.

The existing 30-day trial and Basic fallback remain in place unless separately changed by the Founder. Trial expiry does not start billing and does not lock the workspace.

## 11. Acceptance standard

The tier model is ready for implementation only when:

- The capability-to-source map is complete.
- No Basic workflow is misrepresented as offline-supported without evidence.
- Higher-tier boundaries are enforced server-side, not by navigation visibility alone.
- Roles and business isolation remain independent from tier checks.
- Online and offline behaviour is consistent.
- Downgrade preserves customer data, active work and queued changes.
- Capacity thresholds have explicit owner approval.
- Automated regression and physical device acceptance cover the affected workflows.

**North star:** Basic lets a small business run. Intermediate helps it grow. Professional helps it operate at scale. All three remain Zazu.


## 12. Current source reconciliation — 2026-11-27

This is a first-pass source map, not a full acceptance sign-off. "Implemented" below means a meaningful source path exists; it does not mean the complete workflow has passed current exact-head or physical-device acceptance.

| Capability area | Current source evidence | Current disposition |
|---|---|---|
| Basic customer and job operations | `WorkController`, `DashboardController`, customer/event models and authenticated routes | IMPLEMENTED online; full offline parity remains PARTIAL / acceptance open |
| Basic quotes, invoices, payments and expenses | `FinanceController`, `Quote`, `Invoice`, `Payment`, `FinanceExpense` and finance routes | IMPLEMENTED online; commercial reconciliation and offline route coverage remain separate acceptance gates |
| Basic calendar, dashboard, reports and search | Existing calendar/dashboard/report surfaces and business-scoped search foundation | IMPLEMENTED foundation; reporting depth remains basic |
| Basic offline continuity | `OfflineWorkspaceController`, IndexedDB mutation queue, service worker and native page integration | PARTIAL by supported workflow; exact route parity, restart, attachments, sync and physical acceptance remain open |
| Intermediate suppliers and purchasing | `Supplier`, `PurchaseOrderController`, `PurchaseOrderReceivingService` and purchasing routes | IMPLEMENTED online foundation; end-to-end operational and offline acceptance required |
| Intermediate inventory and assets | `InventoryController`, `AssetController`, `InventoryItem`, `Asset`, movement/allocation records | IMPLEMENTED online foundation; offline write/reconciliation coverage remains partial |
| Intermediate costing and profitability | Finance and event-cost records; reporting surface includes operational costs and profitability summaries | IMPLEMENTED foundation; source-record-to-report reconciliation remains an acceptance gate |
| Intermediate team collaboration | Business membership, roles and permission middleware | PARTIAL; role/permission foundations exist, but assignment, workload visibility and advanced management controls are not established as complete |
| Professional multiple locations | No verified multi-location business model/controller was identified in this first-pass search | NOT EVIDENCED; treat as deferred, not shipped |
| Professional advanced analytics and capacity planning | Current reporting surface is a lean operational summary; no verified advanced BI/capacity engine identified | NOT EVIDENCED; future capability |
| Professional automation, integrations and API access | No verified customer-facing automation/integration/API capability identified in this first-pass search | NOT EVIDENCED; future capability |

### Entitlement and enforcement baseline

- `app/Support/BusinessEntitlement.php` resolves the business's current tier and 30-day trial fallback.
- `app/Support/ExperienceLevel.php` clamps each member's selected presentation level to the business ceiling.
- `app/Models/BusinessLicense.php` resolves local signed entitlement state.
- Current behaviour enforces the selectable-level ceiling; it does **not** implement the module-level capability matrix or commercial customer/job/user/storage quotas.
- The internal third-level identifier remains `advanced`; `config/zazu.php` now presents it as **Professional**.
- The offline bootstrap has bounded snapshot queries (for example, a customer batch limit). Such technical payload bounds are not approved commercial quotas and must not be reused as tier policy.

### Evidence and next action

The current repository state records native offline parity and physical PC/Android acceptance as open. The source paths above must be challenged with exact-head tests and owner-device workflows before any capability is described as fully accepted.

Next: complete the source/test mapping for each row, identify the specific offline support contract per feature, and classify each proposed boundary as shared foundation, capacity candidate, tier-specific capability or deferred. Do not introduce feature gates or quotas in this reconciliation pass.
