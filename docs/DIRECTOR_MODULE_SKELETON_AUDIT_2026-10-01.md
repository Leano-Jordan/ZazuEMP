# Zazu EMP — Director Module Skeleton Audit
**Date:** 2026-10-01
**Scope:** source-level structural audit of existing V1 modules; no runtime claims.

## Director finding

Zazu does not have a simple “everything is incomplete” problem. The codebase has a mix of:
- **Fit modules** — meaningful workflow, persistence, controls and surrounding UI already exist.
- **Lean modules** — useful and intentional, but deliberately narrow.
- **Skeleton modules** — a route/view/controller exists, but the domain surface is too thin relative to its V1 role or its dependencies.

The Director should **not fatten every module**. The target is to bring skeletons up to the minimum coherent shape required by the existing product workflow.

## Mobile is a first-class foundation

Zazu must be designed and documented as a product that can be used by a business owner or staff member whose only computing device may be a phone.

Therefore:
- Mobile is not merely a responsive QA target.
- Desktop is not a prerequisite for the product to be considered usable.
- New workflows must have a mobile-safe interaction path unless there is a documented reason they are inherently desktop-oriented.
- Desktop can remain the efficient surface for dense setup, bulk work, reporting and administration.
- The architecture may later add offline-first/sync capability incrementally; that is **not** the same thing as pretending the current web app is fully offline.
- Load-shedding constraints are now part of product reality: when the PC is unavailable, the phone may be the only active device.
- Each future mobile/offline change should be a small foundation brick: installability → local assets → resilient reads → queued actions → synchronization/conflict handling, only when evidence justifies the next layer.

Current boundary:
**online mobile use is V1; true phone-only offline business operation is a future architectural capability.**

## Skeleton audit

### 1. Suppliers — SKELETON / highest-value small brick
**Evidence:** supplier controller currently provides list/create/store; the index shows supplier relationships and demand; purchasing depends on supplier records. There is no supplier edit surface.

**Problem:** supplier data is a persistent operational record used by purchasing, but correction/maintenance is incomplete.

**Severity:** High  
**Fix effort:** Small  
**Release impact:** High — purchasing data quality depends on maintainable supplier records.

**Director action:** add business-scoped supplier edit/update without introducing supplier deletion or a new supplier domain.

### 2. Calendar — LEAN, not broken
The calendar provides month navigation, date grouping, job links and an agenda representation. It is intentionally a view over Event records rather than a second scheduling authority.

**Severity:** Low  
**Fix effort:** Do not expand now  
**Release impact:** Low

**Decision:** keep lean. Do not turn it into a scheduling platform.

### 3. Reports — LEAN FOUNDATION
Reports currently provide operational counts, job status, quote totals by currency and cost summaries. The view explicitly keeps finance/profitability reporting behind the underlying transaction model.

**Severity:** Medium  
**Fix effort:** Medium/large if expanded  
**Release impact:** Medium

**Decision:** do not inflate it. Strengthen only when the underlying commercial/financial workflow closes.

### 4. Compliance — SUPPORTING MODULE
Compliance has a real document register, profile-driven prompts, private document storage/download and a generated pack surface.

**Severity:** Medium  
**Fix effort:** Already substantial  
**Release impact:** Supporting, not core

**Decision:** stop adding compliance breadth. It should not outrun the commercial workflow.

### 5. Assets — LEAN/FIT FOUNDATION
Assets already support registration, editing, allocation, release, business isolation, lifecycle protection and audit entries.

**Severity:** Medium  
**Fix effort:** Targeted later  
**Release impact:** Medium

**Decision:** do not add an asset-management product. Later focus only on the existing V1 requirement for condition/damage/loss evidence.

### 6. Inventory — LEAN/FIT FOUNDATION
Inventory has stock creation, movement types, quantity guards, idempotency, event linkage and audit entries.

**Severity:** Medium  
**Fix effort:** Targeted later  
**Release impact:** High where stock is operationally relevant.

**Decision:** no warehouse expansion. Later reconcile inventory with purchasing/receiving and event demand.

### 7. Capabilities/catalogue — FIT
The catalogue has business scoping, pricing, categories, imagery, rental/product/service distinctions and UI separation between equipment and catering.

**Decision:** avoid cosmetic expansion. Its next value comes from deeper connection to quote/requirements/resource workflows.

### 8. Work/Event — FIT / central authority
This remains the product's strongest structural anchor. Do not duplicate its authority elsewhere.

### 9. Quotes — FIT architecture but commercially incomplete
Versions, items, tax snapshots and calculations exist. The missing weight is customer-facing delivery/acceptance/deposit closure, not another internal quote feature.

### 10. Finance — FIT foundation but release-critical incomplete
Invoices/payments/expenses and idempotency exist. The remaining gap is end-to-end commercial reconciliation and document workflow.

## Module obesity rule

A module is **not** improved merely by adding screens, filters or configuration.

Director must ask:
1. Does the module own a real business decision?
2. Does it connect to the central Job/Event record where appropriate?
3. Does it preserve data integrity?
4. Can the user correct normal mistakes?
5. Does it have a clear completion boundary?
6. Does adding more make the core workflow safer, or merely make the module bigger?

If the answer is “bigger only”, stop.

## Next execution order

1. **Supplier skeleton → coherent maintenance surface.**
2. **Re-audit supplier against purchasing.**
3. **Inventory/purchasing reconciliation skeleton.**
4. **Asset condition/damage/loss gap only if existing V1 requirement still demands it.**
5. **Commercial quote → acceptance → deposit skeleton remains the major release-critical branch.**
6. **Mobile/offline foundation grows incrementally alongside real workflow work; never as a parallel feature programme.**

This audit intentionally does not promote deferred Excel/WhatsApp/OCR/integration ideas into current implementation scope.
