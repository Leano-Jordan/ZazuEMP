# Zazu EMP — Director Navigation & Reliability Hardening
**Date:** 2026-10-01

## Director finding

Navigation is currently a release-risk because the product can contain the correct functionality while still making users struggle to find it.

The clearest evidence is the supplier case: Suppliers existed, but the user had to hunt for the module. That means the information architecture was technically functional but operationally unclear.

For Zazu, **findability is reliability**. A business owner cannot reliably use a system they cannot reliably navigate.

## Navigation decision

Primary navigation now exposes real destinations instead of category links that silently land on one representative module.

### Workspace
- Dashboard
- Jobs
- Customers

### Sales & operations
- Services & prices
- Quotes
- Calendar

### Purchasing & resources
- Purchasing
- Suppliers
- Inventory
- Assets

### Money & control
- Finance
- Search
- Reports

### System
- Settings
- Platform admin (where authorised)

This removes the previous ambiguity where:
- Operations could land on Jobs, Services, Quotes or Calendar depending on permission.
- Resources could land on Purchasing, Suppliers, Inventory or Assets depending on permission.

Those patterns were compact but made the destination model invisible.

## Navigation principles

1. **If a destination matters to daily work, expose its name.**
2. Category labels group destinations; they do not replace destinations.
3. Do not require users to understand Zazu's internal terminology before finding a module.
4. Mobile uses the same information architecture as desktop; the drawer changes presentation, not meaning.
5. Contextual section tabs remain secondary navigation, not the only way to reach a child module.
6. Search remains a first-class navigation path, but search is not a substitute for discoverable navigation.
7. Active route state must make the current location obvious.
8. Avoid duplicate routes or parallel navigation authorities.
9. Navigation should be permission-aware without changing the meaning of the remaining destinations.
10. A module that exists but cannot be found during ordinary use is treated as a navigation defect.
11. A long navigation set must remain fully reachable on small screens; the mobile drawer may scroll its destination region without changing the information architecture.

## Reliability hardening direction

Director will now audit reliability across five layers:

- **Discoverability:** Can a normal user find the function?
- **Workflow integrity:** Does the action produce the correct domain state?
- **Data integrity:** Can incorrect or conflicting data be prevented?
- **Recovery:** Can the user safely correct normal mistakes?
- **Verification:** Is there evidence from tests or observed runtime behaviour?

A module is not considered hardened merely because its controller and database operation exist.

## Current hardening priority

1. Navigation and route discoverability.
2. Core business workflow integrity.
3. Cross-module relationships.
4. Populated-data correctness.
5. Recovery and correction paths.
6. Runtime/browser verification.
7. Only then cosmetic expansion.

## Mobile foundation

Mobile is a first-class product surface. Navigation therefore cannot depend on desktop-only information density.

The mobile drawer should present the same destination names and hierarchy as desktop. Future mobile/offline work must preserve this information architecture.

Current offline boundary remains unchanged: true phone-only offline business operation is future architecture, developed incrementally.

## Stop rule

Do not add navigation items merely because a route exists.

Every visible destination must represent a user-understandable business area or an essential control surface.



## 2026-10-01 execution — navigation hardening

The explicit-destination navigation change is now treated as the current control state rather than an experimental compact-navigation pattern.

Additional hardening:
- the mobile destination region now owns vertical scrolling when the navigation set exceeds the available viewport;
- the drawer shell remains fixed and the brand/close control and account area are not lost behind an overflowing destination list;
- feature destinations remain named directly: Suppliers is not hidden under Resources, and the same rule applies to Purchasing, Inventory, Assets, Quotes and other daily-use areas;
- regression coverage now protects the visible destination set from accidental removal.

This cycle deliberately does not add new modules, new navigation categories or feature breadth.


## Navigation acceleration — 2026-10-01
The previous explicit-destination navigation corrected hidden modules but still left a usability defect: the sidebar remained a long list, and the owner had to scroll to reach less-prominent destinations such as Suppliers.

### Director decision
Treat navigation as a reliability surface. The user must be able to reach common destinations without scrolling or remembering Zazu's internal grouping.

### Executed
- Added a persistent Quick access rail for Jobs, Customers, Quotes, Purchasing, Suppliers and Calendar.
- Added a visible Find control beside All areas.
- Wired the existing workspace command surface to actually function: focus/open, filtering, keyboard navigation, Enter activation, Escape close and Ctrl/Cmd+K.
- Kept the full permission-aware destination list intact so no module is hidden or removed.
- Added regression coverage for quick-access and command-navigation hooks.

### Design basis
Current SaaS navigation patterns increasingly combine a concise personalized/sidebar surface with command navigation and favorites/recent access. Atlassian documents customization, starred items, recent items, collapsible sidebars and command navigation; Linear documents favorites, recent access and command/search shortcuts. These patterns support Zazu's direction without copying either product's information architecture.

### Boundary
Quick access is intentionally a bounded first acceleration layer. Personal favorites/reordering and richer recent-record navigation can be added later if actual usage evidence shows they are needed.

### Financial conversion re-audit
Invoice creation already requires an accepted quote and accepted latest version for quote-based conversion. The quote-version foreign key is unique at database level. Payments are business-scoped, idempotent, invoice-locked, lifecycle-checked, prevented from exceeding balance, and deposit payments are capped to the quoted deposit requirement. No new financial mutation was justified in this sweep; end-to-end acceptance evidence remains the next proof target.
