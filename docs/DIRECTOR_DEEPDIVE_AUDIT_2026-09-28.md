# Zazu EMP — Director Deep-Dive Audit
## 2026-09-28

### Purpose

This audit maps the current main codebase against docs/product-specification.md, docs/v1-capability-map.md, the existing architecture, current Laravel 13 patterns, current event-management product patterns, and current server-side authorization guidance.

The goal is commercial V1 readiness, not maximum feature count.

### Research basis

Laravel 13 documentation confirms that Form Requests are the framework pattern for encapsulating validation and authorization logic. Laravel Scout also provides a built-in database engine using MySQL or PostgreSQL full-text indexes and LIKE clauses without external search infrastructure.

OWASP guidance continues to require server-side authorization and fail-safe access control.

Current event-management references reinforce the chosen Zazu direction: the event/project is the operational context, with tasks, files, payments and related activity kept close to that context. Current HoneyBook guidance also supports deferring non-essential setup and completing it later. Current Tripleseat guidance treats the event as the central record for details, documents, payments and task completion.

Sources:
- https://laravel.com/framework/docs/validation
- https://laravel.com/framework/docs/scout
- https://cheatsheetseries.owasp.org/cheatsheets/Authorization_Cheat_Sheet.html
- https://help.honeybook.com/en/articles/11116252-create-your-first-project
- https://help.honeybook.com/en/articles/11648189-navigate-a-project
- https://support.tripleseat.com/hc/en-us/articles/22437492617495-First-Event-Checklist
- https://support.tripleseat.com/hc/en-us/articles/1500008939721-How-to-close-out-an-event

---

## Executive finding

Zazu is not missing its major business domains. The current codebase already contains a substantial commercial foundation.

### Strong and already present

- registration and authentication
- business isolation and active business context
- workspace switching
- business setup
- customers and contacts
- events/jobs
- products/services/capabilities
- work and preparation
- suppliers
- purchasing and receiving
- assets and inventory foundations
- event costs
- invoices, payments and expenses
- documents/attachments
- reports
- roles and permissions
- auditability
- controlled lifecycle transitions
- transaction/idempotency protections
- responsive application shell
- desktop workspace navigation
- mobile navigation foundation
- branded Zazu feedback/toast surface
- contextual Zazu Helper guide
- command-palette navigation surface

### Gaps confirmed by the deep dive

1. Experience-level state was missing.
2. The header command palette searched navigation surfaces but not business records.
3. Zazu Helper explained screens but did not surface live operational attention.
4. Dashboard presentation was essentially one fixed detail level.
5. The Job workspace contained the connected domains but did not make the end-to-end operational chain sufficiently visible.

These have now been moved materially toward the V1 baseline.

---

# Capability audit

| Domain | Finding | Director action | V1 state |
|---|---|---|---|
| Registration/auth | Mature foundation | No unnecessary redesign | Exists |
| Business setup | Mature | Reconnected into onboarding sequence | Tightened |
| Experience level | Missing | Added per-business membership state and UI | Implemented |
| Clients | Mature | No broad change | Exists |
| Events | Mature | Preserved current lifecycle | Exists |
| Work/process | Strong | Added connected-chain visibility | Tightened |
| Products/services | Mature | No broad change | Exists |
| Resources | Existing assets/inventory/requirements | Kept contained | Exists |
| Suppliers | Mature | Added to record search | Tightened |
| Purchasing | Mature | Added to record search and Helper attention | Tightened |
| Costs | Mature | Added to record search and Job chain | Tightened |
| Finance | Mature | Added finance records to search, retained safeguards | Tightened |
| Documents | Existing | No full DMS expansion | Contained |
| Reporting | Existing | No BI expansion | Contained |
| Global search | Partial | Added database-backed cross-domain search | Implemented |
| Zazu Helper | Partial | Added operational attention layer | Implemented foundation |
| Responsive UX | Strong foundation | No broad redesign | Verification remains |
| Security | Strong foundation | Search remains server-side permission aware | Exists |

---

# Experience levels

Experience level is now stored on the business_user relationship and has three values:

- Basic
- Intermediate
- Advanced

This is explicitly a presentation preference. It is not a role and does not grant or remove permission.

Existing memberships are migrated to Intermediate.

Onboarding now follows:

Registration → Services & prices → Experience level → Business identity → Dashboard

The existing ability to defer business setup or catalogue setup is preserved.

Users can change their level later from the workspace preference screen.

The dashboard now uses the level to reduce or increase visible operational detail without changing access.

---

# Search

### Previous state

The topbar command palette was useful for navigation but only searched static workspace destinations.

### Current state

The new WorkspaceSearchService searches inside the active business context across:

- customers
- jobs/events
- services
- suppliers
- purchase orders
- quotes
- invoices
- costs
- assets
- inventory
- expenses

Supported controls:

- text search
- record type
- status where the record supports it
- date range
- server-side permission filtering
- direct navigation

The topbar search now has a path to the real record-search surface.

### Architecture decision

No external search engine was added for V1.

The first implementation stays inside Laravel and the existing database architecture. This keeps deployment and recovery simple while leaving room for Laravel Scout or another indexed engine if actual production scale requires it.

### Security boundary

The search service checks the authenticated user's membership and role for the active business. Hidden UI controls are not treated as authorization.

---

# Zazu Helper

### Previous state

The Helper already provided route-specific workflow guidance.

### Current state

The Helper now combines:

Guide:
- explains the current screen
- explains the workflow
- offers useful next-step links

Attention:
- unfinished workspace setup
- overdue preparation
- open purchase orders
- draft jobs

Attention count is adapted to experience level:

- Basic: up to 2
- Intermediate: up to 3
- Advanced: up to 4

No autonomous approval, mutation or business decision engine was introduced.

---

# Event lifecycle

The implementation currently uses:

Draft → Confirmed → In Progress → Completed

with cancellation handling.

The specification uses Planned as a descriptive lifecycle concept, but the code does not have a real planned state.

### Director decision

Do not create a migration and UI change just to make a diagram match the software.

A planned state should only be introduced if a real business requirement proves that Draft and Confirmed do not represent the needed distinction.

The existing centralized transition service already protects completion and cancellation against open operational, procurement and financial states.

---

# Operational chain

The major business chain is:

Event → Work → Resources → Purchasing → Costs → Finance

The current domain relationships and lifecycle services already support this chain. The Job workspace has now been given an explicit Operational chain surface showing:

Services → Preparation → Purchasing → Costs → Finance

with live linked-record counts and navigation.

This is a visibility layer only. It does not create a second source of truth.

Existing completion/cancellation protections remain the authority.

---

# Contained areas

No major working domain was deleted.

Keep these contained for V1:

### Compliance
Retain evidence and business documentation. Do not turn this into a full regulatory platform.

### Inventory
Retain stock and movement foundations. Do not build warehouse-management complexity.

### Assets
Retain equipment tracking. Do not build enterprise asset management.

### Travel
Retain event-linked travel cost support. Do not build logistics optimisation.

### Reporting
Retain operational and financial reporting. Do not build mature BI or predictive analytics.

### AI
Build Helper guidance and search assistance. Do not build an autonomous AI employee or autonomous finance.

---

# Deep-dive checklist

## Platform

- [x] Registration/authentication
- [x] Active business context
- [x] Workspace switching
- [x] Business isolation
- [x] Role/permission enforcement
- [x] Auditability
- [x] Experience-level storage
- [x] Experience-level self-service

## Onboarding

- [x] Registration enters setup
- [x] Services/products setup follows registration
- [x] Catalogue can be deferred
- [x] Experience level is selected
- [x] Business identity follows experience selection
- [x] Business setup can be deferred
- [x] Setup can be resumed
- [ ] Fresh-account desktop browser walkthrough
- [ ] Fresh-account mobile browser walkthrough

## Search

- [x] Global search surface
- [x] Real record search
- [x] Cross-domain records
- [x] Permission-aware results
- [x] Type filter
- [x] Status filter where valid
- [x] Date filter
- [x] Direct navigation
- [ ] Desktop rendered QA
- [ ] Mobile rendered QA
- [ ] Runtime test execution

## Helper

- [x] Contextual guide
- [x] Workflow explanation
- [x] Experience awareness
- [x] Setup attention
- [x] Overdue preparation attention
- [x] Open purchasing attention
- [x] Draft-job attention
- [x] Next-action links
- [ ] Broader natural-language business queries
- [x] No autonomous decision engine

## Core business chain

- [x] Event lifecycle controls
- [x] Work/preparation linkage
- [x] Resource foundations
- [x] Purchasing linkage
- [x] Cost linkage
- [x] Finance linkage
- [x] Completion guards
- [x] Cancellation guards
- [x] Operational-chain visibility
- [ ] Final decision on whether Planned becomes a real event state
- [ ] Populated-data end-to-end walkthrough

## Finance and recovery

- [x] Invoice integrity
- [x] Payment controls
- [x] Expense capture
- [x] Idempotency controls
- [x] Mixed-currency handling
- [x] Auditability
- [ ] Runtime reconciliation drill
- [ ] Real backup/restore drill
- [ ] Populated production-like migration check

## Responsive product

- [x] Responsive shell
- [x] Mobile navigation
- [x] Desktop workspace
- [x] Responsive forms
- [x] Responsive calendar
- [ ] Rendered desktop release QA
- [ ] Rendered mobile release QA
- [ ] Tablet QA
- [ ] Accessibility smoke

---

# Release risks

1. Runtime certification is still the largest immediate gap. The environment could not clone the public repository because outbound DNS/network access was unavailable, so the Laravel and browser suites were not executed locally during this audit.

2. Search is intentionally database-backed for V1. Real production-like data volumes should be used to determine whether indexing changes are necessary later.

3. Planned remains a product decision, not a missing code checkbox.

4. Responsive code is strong, but rendered QA still matters.

---

# Director release position

The fastest route to commercial release is no longer another broad feature-building sweep.

The remaining critical path is:

Runtime verification → populated-database migration check → desktop/mobile browser QA → targeted defect fixes → release candidate

Feature additions should now be challenged against the primary product chain and release criteria.

### Final conclusion

The V1 gaps identified in the product specification have been materially advanced through:

- Basic / Intermediate / Advanced experience-level state
- onboarding experience selection
- real cross-domain record search
- permission-aware record search
- operational Helper attention
- experience-aware Dashboard detail
- visible Job operational chain
- regression tests for experience/search paths

No major existing domain was deleted.

No autonomous AI system was added.

No speculative enterprise workflow engine was added.

No Planned event state was invented without a business reason.

The remaining work is predominantly verification, targeted correction and release assurance.

## Director visual hierarchy correction — 2026-09-28

### Light mode

The previous visual sweep contained a base zazu-sidebar rule that forced a dark navigation rail even when the application theme was light. The shell has now been corrected so light mode explicitly uses a bright blue/white navigation and header treatment.

Light mode now has:
- bright near-white page surface
- blue-tinted navigation rail
- white active navigation states
- vibrant blue primary actions
- readable navy/blue typography
- stronger but restrained structural borders

Dark mode is scoped separately and remains unchanged by the light-mode correction.

### Structural visibility

The Reports page was reviewed as the internal visibility reference because its sections have clear light borders, explicit panel boundaries and readable internal separation.

That visibility standard is now applied across shared Zazu structural surfaces, including cards, panels, forms, command bands, documents, metrics, route cards, context cards, next-action blocks, dashboard surfaces, calendar/register surfaces and operational rows.

Dashboard-specific surfaces also receive stronger light-mode boundaries so the user can immediately distinguish primary work, metrics, commercial action, resource information and quick access.

This is a structural visibility improvement, not a return to noisy grid-heavy UI.

### Public entry

The public root remains the landing page.

The landing page now provides explicit guest actions for:
- Register
- Log in

Logout now returns to the public landing page rather than directly to the login screen.

A feature test locks this behavior.

### Landing imagery

The landing page now uses replaceable image placeholders from free Unsplash-licensed event/catering photography for:
- the hero workspace preview
- operations
- resources

These are deliberately temporary visual assets and can be replaced by the product owner later.

### Research note

The light-theme decision aligns with current interface guidance that treats light and dark themes as separate surface systems rather than simple color inversion. Current SaaS references also emphasize clear navigation, structural hierarchy and restrained use of accents. The Reports-page reference is retained as the Zazu-specific visual authority for operational visibility.
