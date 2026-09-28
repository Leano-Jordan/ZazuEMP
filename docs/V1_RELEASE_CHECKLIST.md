# Zazu EMP — V1 Release Checklist
## Director baseline — 2026-09-28

Checked means implemented and source-reviewed. Runtime-only items remain unchecked until executed in the repository environment.

### Gate 1 — Platform
- [x] Registration/authentication
- [x] Active business context
- [x] Workspace switching
- [x] Business isolation
- [x] Role/permission enforcement
- [x] Auditability
- [x] Experience level
- [ ] Final runtime regression suite

### Gate 2 — Onboarding
- [x] Registration redirects into setup
- [x] Services/products setup follows registration
- [x] Catalogue deferral
- [x] Basic / Intermediate / Advanced selection
- [x] Business identity setup
- [x] Business setup deferral
- [x] Resumable setup
- [x] Change experience level later
- [ ] Fresh desktop registration walkthrough
- [ ] Fresh mobile registration walkthrough

### Gate 3 — Operations
- [x] Customers
- [x] Contacts
- [x] Events/jobs
- [x] Lifecycle control
- [x] Work/preparation
- [x] Requirements/services
- [x] Suppliers
- [x] Purchasing
- [x] Receiving
- [x] Assets
- [x] Inventory
- [x] Event costs
- [x] Finance
- [x] Documents
- [x] Reports

### Gate 4 — Connected business truth
- [x] Event → Work
- [x] Work → Resources
- [x] Work → Purchasing
- [x] Work → Costs
- [x] Work → Finance
- [x] Completion protection
- [x] Cancellation protection
- [x] Operational chain visibility
- [ ] Populated-data end-to-end walkthrough

### Gate 5 — Search
- [x] Header search
- [x] Customer search
- [x] Job/event search
- [x] Service search
- [x] Supplier search
- [x] Purchase-order search
- [x] Quote search
- [x] Invoice search
- [x] Cost search
- [x] Asset search
- [x] Inventory search
- [x] Expense search
- [x] Type filter
- [x] Status filter
- [x] Date filter
- [x] Business isolation
- [x] Permission-aware result selection
- [ ] Runtime search suite
- [ ] Desktop search QA
- [ ] Mobile search QA

### Gate 6 — Zazu Helper
- [x] Contextual guides
- [x] Workflow explanations
- [x] Experience awareness
- [x] Setup attention
- [x] Overdue preparation attention
- [x] Open purchasing attention
- [x] Draft-job attention
- [x] Next-action links
- [ ] Broader natural-language business queries
- [x] No autonomous V1 decisions

### Gate 7 — UX
- [x] Coherent Zazu visual system
- [x] Desktop shell
- [x] Mobile shell
- [x] Mobile navigation
- [x] Professional forms
- [x] Professional lists/tables
- [x] Empty/loading/error states
- [x] Meaningful toast feedback
- [x] Consequential-action confirmation
- [ ] Rendered desktop QA
- [ ] Rendered mobile QA
- [ ] Tablet QA
- [ ] Accessibility smoke

### Gate 8 — Security
- [x] Authentication boundary
- [x] Server-side authorization
- [x] Business isolation
- [x] Permission-aware search
- [x] Secure attachment foundation
- [x] Validation
- [x] Auditability
- [ ] Final release security review

### Gate 9 — Database and recovery
- [x] Experience-level migration
- [x] Existing memberships backfilled to Intermediate
- [ ] Populated production-like migration run
- [ ] Backup creation
- [ ] Restore
- [ ] Representative record verification after restore
- [ ] Private media verification after restore

### Gate 10 — Scope control
- [x] No enterprise workflow engine
- [x] No autonomous AI employee
- [x] No predictive finance
- [x] No mature BI expansion
- [x] No warehouse-management expansion
- [x] No enterprise asset-management expansion
- [x] No speculative external search infrastructure
- [x] No automatic Planned event state

### Release-candidate finish line
- [ ] Laravel tests green
- [ ] Browser smoke green
- [ ] Static analysis green
- [ ] Populated migration verified
- [ ] Backup/restore proven
- [ ] Desktop critical workflows verified
- [ ] Mobile critical workflows verified
- [ ] Operational chain verified end-to-end
- [ ] Search verified
- [ ] Helper verified
- [ ] No release-blocking authorization or data-integrity defects remain

### Post-V1 hold
- Autonomous AI actions
- Predictive analytics
- Advanced BI
- Enterprise workflow design
- Heavy integration expansion
- Warehouse-scale inventory
- Enterprise asset management
- Broad collaboration suite
- Unlimited UI configurability

Rule: finish the product before expanding the product.
