# Director Capability-to-Surface Map

**Date:** 2026-11-23  
**Source of truth:** current `main` implementation, inspected at `c34853197b2d79b1936a95f042f082941f2d0b16`.  
**Purpose:** identify where business capability configuration already affects Zazu, where it does not, and the safest next implementation seam. This is an implementation map, not a claim that adaptive workspace acceptance is complete.

## Current source ownership

| Concern | Current source | What it owns | Boundary |
|---|---|---|---|
| Business focus | `business_user.business_focuses` plus legacy `primary_niche` | Owner-selected business activities and the separate guidance priority | Membership-scoped; legacy primary focus remains supported |
| Reusable offerings | `business_capabilities` / `BusinessCapability` | Actual sellable, rentable, packaged or supplied catalogue records, including category and capability type | Business-scoped; active state controls future selection, not historical deletion |
| Event scope | `event_requirements` / `EventRequirement` | What this customer actually booked for this event | Event-scoped; accepted quote locks agreed scope |
| Permissions | `PermissionService`, role config and route middleware | Whether the current member may view or mutate a surface | Must remain authoritative regardless of capability visibility |
| Experience preference | `ExperienceLevel` and `experience_levels` config | Amount of detail presented | Presentation preference only; not capability or authorization |

## Surface map: observed implementation

| Surface | Current behaviour | Capability relationship | Gap / safe next step |
|---|---|---|---|
| Onboarding | Captures multiple business focuses before catalogue setup; primary focus remains distinct | Focus selects relevant catalogue category suggestions | Owner can identify activities; full downstream workspace configuration is not yet implemented |
| Catalogue | Lists business-owned active/inactive capabilities; supports rental and non-rental types; category filters derive from catalogue records | Actual offerings are stored as capabilities | Good source for enabled service mix; avoid introducing another enabled-capability flag |
| Event intake / requirements | Requirements can link to an active capability in the same business; event-specific description/category/quantity/notes are stored | Event requirements are the authoritative booked components | Use requirement categories and linked capabilities for event-specific relevance |
| Navigation | `app-layout.blade.php` groups fixed routes into Work, Sales, Resources, Finance and System; each link is permission-gated. A compact “Your business mix” panel now shows derived focus labels, active categories and active offering count. | The panel is derived from selected membership focuses and active catalogue records; it links to Services & prices only when the member has `capabilities.view`. | Navigation remains intentionally discoverable and route permissions remain authoritative. Dynamic module prioritisation is still a follow-up; no module is hidden or access granted by capability. |
| Dashboard | `DashboardController` provides shared event/customer/quote/purchasing/asset metrics; experience level controls some additional metric detail | Primary niche and supporting catalogue focus are passed to the dashboard | Dashboard is partly focus-aware, but metrics/tools are still broad rather than capability-composed |
| Helper | `zazu-helper.blade.php` derives business guidance from active catalogue categories and saved focuses; Job detail uses that event's requirement categories | Both business-wide mix and current event scope already influence guidance | Existing narrow adaptation is present; next refine only where records and permissions justify it |
| Job operational summary | Existing event preparation, purchasing, cost, travel and asset-allocation records are shown when present and permitted | Event context plus actual operational records drive the summary | Preserve this state-derived approach; do not create a parallel task engine |
| Offline bootstrap | `OfflineWorkspaceController` and `SyncBootstrapPayloadBuilder` send business catalogue and operational records to the local client | Catalogue records are included; the inspected bootstrap business envelope does not yet expose the multi-focus membership configuration | Capability-aware offline presentation needs an explicit, versioned bootstrap contract and physical PC/phone acceptance |
| Authorization / isolation | Route middleware, permission checks and business-scoped queries protect surfaces and records | Independent of capability selection | Capability configuration must never grant access or weaken tenant isolation |

## Existing focus-to-category vocabulary

The current `config/zazu.php` and `NicheFocus::matchingCategories()` map:

| Focus | Catalogue categories |
|---|---|
| Chairs & tents | Furniture & equipment; Rentals |
| Catering & baking | Catering; Baking |
| Sound & DJ | Sound & entertainment; Audio & entertainment |
| Decor & styling | Decor |
| Photography & video | Photography & video |
| Transport & delivery | Transport |
| Mixed event services | No category restriction |

The category match is guidance metadata. It is not a complete module-dependency map and must not be treated as one.

## Architectural findings

1. There are three distinct layers: selected business focus, reusable catalogue capabilities, and event-specific requirements. They must remain separate.
2. The catalogue is the best existing source for what the business currently offers. Event requirements are the best source for what a particular booking actually includes.
3. The current primary navigation is static apart from role/permission checks. Capability-aware navigation is not yet implemented.
4. Helper already adapts to the business catalogue mix and, on Job detail, the event's requirement categories. Its operational recommendations also reuse existing records and permission checks.
5. The offline client receives catalogue and operational records, but this inspection did not find a complete capability/focus profile in the offline bootstrap business envelope. This is an acceptance gap, not proof that no related offline metadata exists elsewhere.
6. Do not hide core shared surfaces solely because a focus category is absent. Suppliers, purchasing, finance, costs, search and reports can support multiple business types. Any contextual navigation should use progressive disclosure and preserve discoverability.
7. Never infer that an event needs a module merely because the business offers a capability. Event requirements and actual operational records determine event-level work.

## Recommended implementation sequence

1. **Capability profile resolver:** derive a read-only workspace profile from the active business's selected focuses and active catalogue records. Do not persist duplicate booleans or add a second source of truth.
2. **Navigation presentation:** keep shared essentials visible; group capability-relevant tools first and move optional specialist tools into a discoverable secondary area. Apply the same permission checks already used by the layout.
3. **Event-level relevance:** continue using event requirements and permission-filtered operational records; only surface specialist actions when the booked component and supporting record justify them.
4. **Offline contract:** expose the same derived profile or its source inputs in bootstrap, version the payload if necessary, and test restart/reconnect behaviour on PC and phone.
5. **Acceptance:** test DJ-only, hire-only, catering-only and mixed businesses; owner/staff permission differences; disabled catalogue items; historical/accepted quote preservation; offline startup and synchronization.

## Explicit non-goals

- No capability-based authorization.
- No deletion or hiding of historical records.
- No second workflow/task engine.
- No forced industry templates.
- No assumption that all events of the same event type require the same services.
- No claim of mobile/PC offline parity until physically demonstrated.

## Current disposition

**Mapped from source; implementation partial.** Onboarding, Helper, the read-only workspace profile and compact sidebar business-mix panel now use the existing focus/catalogue model. Dynamic module prioritisation and offline workspace profile parity remain open. The next code slice should focus on explicit offline contract parity and then carefully prioritise navigation without hiding shared tools.
