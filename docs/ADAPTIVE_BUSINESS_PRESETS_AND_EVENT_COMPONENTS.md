# Adaptive Business Presets and Event-Component Experience

**Status:** Product requirement — mandatory design and implementation constraint  
**Authority:** Founder direction, recorded 2026-11-23  
**Applies to:** onboarding, business settings, capability catalogue, navigation, dashboards, event intake, requirements, quotes, operations, reports, Helper, desktop, mobile and offline behaviour.

## Product promise

Zazu is one event-management platform that deliberately adapts to the business using it. It must not present every business with the same generic catalogue of industries, services, fields, workflows and operational prompts.

The business choices made during setup and later configuration must have meaningful, consistent effects throughout the product. A business should feel that Zazu understands what it does, while retaining the ability to handle the event components it actually offers.

**Core principle: one event platform; business-specific working environments.**

## Preset examples

Presets are starting configurations, not hard-coded product editions:

- **DJ / Sound provider:** sound systems, DJ services, playlists/programme details, setup and breakdown, equipment, crew, transport and relevant deposits/payments.
- **Tent and chair hire:** inventory/assets, quantities, delivery/collection, setup/strike, condition, availability, transport, damage/loss and hire periods.
- **Catering:** menus, food/beverage quantities, dietary requirements, preparation, packing, purchasing, suppliers, delivery/service and food-related costs.
- **Photography / camera hire:** coverage, schedules, deliverables, equipment, storage/media handoff, editing or return/condition workflows as applicable.
- **Decor / styling:** design scope, items, quantities, setup/strike, venue constraints, sourcing and labour.
- **Baking / cakes:** products, portions, flavours, dietary/allergen details, production, packaging, delivery and collection.
- **Multi-service / full event provider:** combine selected capabilities into one coherent workspace and event process.

These examples define intent, not a demand to build every specialist feature in one pass. Existing application capabilities and V1 scope remain authoritative for implementation sequencing.

## Required behaviour

### 1. Business choices configure the workspace

During onboarding and in business settings, an authorised owner can select one or more business capabilities. The selection must configure relevant:

- service and product catalogue starting points;
- terminology and relevant form fields;
- navigation visibility and workspace entry points;
- dashboard summaries and attention signals;
- event intake choices and requirement templates;
- operational actions and lifecycle guidance;
- Helper explanations and recommendations;
- relevant reports and settings.

A preset should establish sensible defaults quickly. The owner must be able to review, customise and extend those defaults without being trapped in a rigid industry template.

### 2. Event components are first-class scope

An event can contain multiple components, and its requirements determine which operational work is relevant. A DJ supplying sound at a wedding and a tent-hire provider supplying seating for the same wedding are different business scopes, even though both manage an event.

The system must distinguish:
- the business's enabled capabilities (what this business offers);
- its catalogue (the specific products/services, prices and units it maintains);
- the components selected for an individual event (what this customer booked);
- downstream work actually required by those components.

Do not assume every event requires every module. Do not equate an event type (for example, wedding) with the services a provider sells.

### 3. Remove irrelevant clutter without removing capability

When a business does not offer catering or camera hire, those options should not dominate its default navigation, dashboards, intake forms, catalogue suggestions or Helper guidance.

Use contextual visibility and progressive disclosure:
- show relevant, enabled capabilities by default;
- keep genuinely cross-business essentials available;
- place optional/advanced capabilities behind a deliberate add/enable path;
- explain why a newly enabled capability adds relevant workspace options;
- avoid dead-end links and empty modules presented as required work.

Hiding a capability from a business's normal workspace is not deletion, data loss, or an authorization boundary. Server-side authorization and business isolation must remain authoritative.

### 4. Multi-service businesses remain coherent

A business may select several presets or individual capabilities. The result must be a unified workspace, not several disconnected mini-apps or duplicate navigation entries.

Shared event facts, customer records, quotes, invoices, payments, suppliers and activity remain connected to the existing event/business records. Shared concepts should be reused; specialist details should appear only when relevant.

### 5. Changes must be safe and reversible

Changing a business's enabled capabilities must:
- preserve existing customers, events, requirements, quotes, invoices, payments, inventory, attachments and history;
- never silently delete or rewrite historical event scope, accepted quote snapshots or financial records;
- distinguish disabling future use from deleting catalogue data;
- warn before a change that affects active workflows;
- retain access to historical records and their original meaning;
- define safe behaviour for offline edits and later synchronization.

A capability change must not retroactively alter what a customer accepted or what an event historically contained.

### 6. Helper must understand the configured business

Helper recommendations must be relevant to both the current event state and the business's enabled capabilities. It should guide the operator through the next meaningful action for the work they actually sell and the event components actually booked.

Examples:
- A tent-and-chair hire business should receive guidance about availability, quantities, delivery, setup, collection and asset condition—not catering preparation or camera editing.
- A DJ business should receive relevant equipment, programme, setup, crew and event timing guidance.
- A caterer should receive relevant menu, dietary, purchasing, preparation and delivery guidance.

Helper must continue to use existing business-scoped, permission-filtered state and route sources. It must not invent a second workflow authority, recommend unavailable modules, or treat every possible lifecycle step as mandatory. When the current state does not support a useful recommendation, it should explain the actual blocker or remain quiet rather than fabricate work.

## UX and architecture guardrails

1. **Progressive disclosure, not feature destruction.** Reduce irrelevant default complexity while preserving discoverability for capabilities the owner may enable later.
2. **Configuration is not authorization.** UI visibility never replaces server-side policies, tenant/business isolation or permission checks.
3. **One source of truth.** Capability configuration, catalogue records and event requirements must have explicit ownership; avoid duplicated flags and competing workflow state.
4. **Offline parity.** Business capability selections and their consequences must behave consistently on PC and phone, including disconnected use, restart and synchronization. Do not claim parity until physically accepted.
5. **Accessible and explainable.** Owners must understand what a preset enables, what it changes, and how to amend it. Staff see only options permitted by their role.
6. **No forced linearity.** Conditional and parallel event work remains conditional and parallel.
7. **No premature redesign.** Trace existing configuration, navigation, domain models, permissions, routes and offline sync before changing implementation. Prefer surgical changes that reuse current architecture.

## Acceptance criteria

The requirement is not complete merely because a preset selector exists.

- [ ] An owner can select one or multiple business capabilities and review the resulting configuration.
- [ ] A DJ-oriented business receives relevant catalogue/intake/workspace/Helper defaults without unrelated catering or camera-hire clutter.
- [ ] A tent-and-chair hire business receives relevant inventory, quantity, logistics, setup/collection and condition workflows without unrelated service prompts.
- [ ] A catering business receives relevant menu, dietary, purchasing and preparation workflows.
- [ ] A multi-service business can combine capabilities without duplicate records, navigation or conflicting workflow state.
- [ ] Event requirements expose only relevant defaults while allowing legitimate event-specific exceptions.
- [ ] Disabling a capability preserves historical and active records and does not rewrite accepted commercial snapshots.
- [ ] Enabling a capability makes its relevant functions discoverable without granting permissions the user does not have.
- [ ] Helper recommendations respect business configuration, event scope, permissions and actual lifecycle state.
- [ ] PC, phone, offline, restart and reconnect behaviour is consistent and covered by automated plus physical acceptance.
- [ ] Regression tests cover business isolation, staff permissions, configuration changes, historical records, multi-capability combinations and offline synchronization.

## Current implementation evidence — 2026-11-23

The first Helper adaptation slice now reads the existing saved primary business focus and changes guidance on Dashboard, Jobs and Job detail for chairs/tents, sound/DJ and catering/baking. Automated feature coverage has been added for those three focus values.

This is deliberately partial. The current focus is a single presentation preference, not a complete multi-capability configuration. Active catalogue categories can identify supporting areas, but they are not yet a unified capability-selection contract for navigation, event intake, event components and downstream operations. The slice does not prove that irrelevant modules are hidden or that every specialist workflow exists.

## Current implementation evidence — event-aware Helper context

The Helper now reads the saved requirements belonging to the current Job detail page and displays a concise “This booking includes” list. It verifies the event belongs to the active business and scopes capability-name fallback data to that same business. Feature coverage asserts that a tent/chair booking displays its actual saved components without unrelated catering or camera prompts.

This is a context-display slice only. It does not yet calculate component-specific operational recommendations, alter navigation visibility or establish complete preset behaviour.

## Implementation sequencing

This is a product-wide requirement, but implementation must be incremental:

1. Inventory the current business capability/preset configuration, catalogue, navigation, intake, event requirements, Helper sources and offline sync.
2. Identify the existing authoritative source (or smallest safe extension) for enabled business capabilities. Do not add a parallel configuration system.
3. Define a capability-to-surface matrix before changing visibility or workflows.
4. Implement and test one narrow vertical slice for a representative preset, including Helper relevance.
5. Extend to multi-capability composition and other presets only after the first slice proves safe.
6. Verify historical-data preservation, business isolation, permissions, PC/mobile and offline behaviour.

**Release discipline:** this requirement is recorded and mandatory for product direction. It is not a claim that adaptive presets are already implemented or that every specialist workflow belongs in V1. Prioritize the smallest coherent V1 slice against current readiness gates; do not silently expand V1 into a rewrite.


## Current implementation evidence — linked booking operations

The Job-detail Helper now reads counts from the current business-owned Event's existing relationships and, when present, lists linked preparation items, purchase orders, asset allocations, recorded costs and travel costs. It does not infer missing work from an empty relation, create tasks, or replace the Job's existing next-action logic. A feature test seeds a tent/chair booking with a linked preparation item and purchase order and asserts both appear in the Helper context. This remains an evidence/context slice; it is not yet a component-to-operation recommendation engine or full adaptive workspace.


## Permission-aware Helper context — 2026-11-23

The linked-booking summary now checks the active user's existing workspace permissions before rendering each record count: preparation/cost/travel context requires `work.view`, purchase-order context requires `purchasing.view`, and asset-allocation context requires `assets.view`. The event remains constrained to the active business. A feature test covers a staff member with `work.view` but without purchasing access: preparation context remains visible while the purchase-order count is withheld. This is presentation-layer minimization; the existing route middleware and server authorization remain the security boundary.


## Helper interaction and validation model — 2026-11-23

The Helper should behave like a dependable operations coach, not a generic chatbot. Commercial guidance products such as Intercom Fin separate focused guidance from procedures, encourage precise conditions and concrete outcomes, and provide a preview/testing step before publishing changes. Zazu applies the useful product lessons without copying the AI-agent architecture:

1. **One current context:** use the active business, current route, signed-in member permissions and (on Job detail) that Job's saved requirement categories.
2. **One best next action:** prefer the Job's existing state-derived next-action source. Elsewhere, choose the highest-priority permission-filtered attention item.
3. **A visible reason:** explain why the action matters using the same authoritative state that triggered it.
4. **A permitted destination:** link only to an internal action/page the member can access. UI guidance must never become an authorization bypass.
5. **No fabricated work:** do not recommend a specialist workflow because an event type sounds like it might need it. Use saved requirement components and actual linked records.
6. **Preview before activation:** future changes to Helper rules should be tested against representative DJ, tent/chair, catering and mixed-service fixtures before being treated as ready.

The current implementation now combines the member's selected primary focus with up to two additional focus areas inferred from active business catalogue categories. On Job detail, the saved requirement categories for that business-owned Job take precedence over the general business mix. Inactive catalogue items are excluded. This affects guidance only; it does not enable modules, hide records, grant access or change historical data.

Attention ordering is now explicit: overdue preparation (priority 10), open purchase orders (20), draft Jobs (30), then optional owner setup reminders (40). Members without the corresponding permission do not receive those records as Helper attention. The exact priority order and multi-capability/event-specific focus have feature tests.

**Commercial references:** [Intercom Fin Guidance best practices](https://www.intercom.com/help/en/articles/10560969-fin-guidance-best-practices), [Provide Fin AI Agent with specific guidance](https://www.intercom.com/help/en/articles/10210126-provide-fin-ai-agent-with-specific-guidance), and [Use Fin previews](https://www.intercom.com/help/en/articles/12599471-use-fin-previews). These are product-design references only; Zazu's workflow state and permissions remain authoritative.


## Director source-to-surface map — 2026-11-23

This map is based on the current implementation, not an assumed architecture:

| Surface | Existing authority | Current behaviour / gap |
|---|---|---|
| Business focus | Membership pivot `primary_niche`; `NicheFocus` | One explicit primary focus; `activeFor()` supplements guidance from active catalogue categories. This is not yet a full capability selection model. |
| Catalogue | Business-scoped `BusinessCapability` records (`category`, `capability_type`, `is_active`) | The catalogue is the strongest existing business-specific data source. Rental items were separated correctly, but the second stream was hard-coded to catering/food/per-person categories, excluding valid DJ, decor, photography and general service capabilities. |
| Onboarding | `OnboardingController::storeExperience()` | Saves one primary niche and experience tier. It does not yet offer multi-capability composition. |
| Navigation | `resources/views/components/app-layout.blade.php` plus role permissions | Navigation is permission-driven and largely static; it does not yet adapt to the business catalogue. Do not use capability visibility as an authorization boundary. |
| Dashboard | `DashboardController::workspaceToolsFor()` and `NicheFocus` | Tool visibility follows permissions/tier; Helper focus is adapted, but dashboard module composition is not capability-driven. |
| Event scope | Business-owned `EventRequirement` records | The actual selected components are already available as the event-specific source of truth and should determine downstream relevance. |
| Helper | `NicheFocus`, `ZazuHelperService`, current Event requirements and linked operational records | Business and event context are partially reflected; further recommendations must remain grounded in permitted linked records. |
| Offline | Business capability aggregate revision/sync support and offline bootstrap | Capability records participate in synchronization, but parity of future multi-capability configuration and resulting UI state still needs explicit proof. |

### First implementation slice

The catalogue's second stream now includes all non-rental capability types (`service`, `package`, `product`, `other`) rather than only hospitality/catering categories. Its label and pricing display are business-neutral. Empty streams are not rendered, so a tent/chair-only business does not see an empty catering tab, while a DJ/decor/photography business can see its own service and product catalogue entries. Rental capabilities remain in their own asset-linked stream.

This is an incremental catalogue correction, not completion of adaptive workspace behaviour. The authoritative capability records remain unchanged; no records are created, deleted or reclassified by this presentation change.

### Next vertical slice

Use the existing active `BusinessCapability` catalogue as the read model for an owner-facing capability summary in business settings/onboarding, then test the effect of adding/deactivating a capability on catalogue discoverability and Helper guidance. Do not add a parallel preset table or mutate historical EventRequirement/quote snapshots. After that, derive only low-risk navigation and dashboard progressive disclosure from the same active capability set, preserving permission middleware and cross-business essentials.


## Catalogue visibility and role refinement — 2026-11-23

The business catalogue now treats the existing `BusinessCapability` records as the authoritative set of offerings:

- Rental capabilities render in a dedicated hire/rental stream.
- Active non-rental capabilities of type service, package, product or other render in the business-neutral services/products stream.
- Empty streams are omitted; this prevents an empty catering or rental section from being presented as required work.
- Owners see inactive catalogue entries marked HIDDEN so they can edit/reactivate them.
- Staff and managers see active offerings only. The controller filters inactive entries and their categories before constructing the page.
- Asset allocation actions are rendered only when the member has `assets.view`; the Assets route remains independently protected by its permission middleware.

Regression tests cover a mixed DJ/sound/rental/product provider, a DJ-only business, owner visibility of hidden entries, staff suppression of hidden entries and missing asset permission. This is a catalogue/workspace-discoverability slice, not proof of complete adaptive navigation, dashboard or offline intake.

## Next vertical slice — active catalogue to online/offline Job intake

The current New Job form still renders `config('zazu.service_categories')` and submits the legacy `services[]` field. Offline job creation and synchronization also reconcile this existing service list. The next implementation must derive choices from the active business-owned `BusinessCapability` catalogue while preserving one shared online/offline contract, business isolation, requirement quantities/units and accepted quote history. Do not replace the online form field with an unrelated IDs-only contract or leave phone-local creation using the old generic list.



## Catalogue-driven Job intake — implementation checkpoint 2026-11-23

New Job now presents active offerings from the current business's existing `BusinessCapability` records instead of the generic static service-category configuration. Disabled offerings are excluded. When no active offerings exist, the form explains how to add one; one-off custom services remain a separate option.

Online creation validates the selected offering names against the active business catalogue within the creation transaction and stores the matching capability relationship, category and default unit on each starter `EventRequirement`. The existing `services[]` field is retained so the offline client contract does not change. Offline server application resolves those names within the mutation's business and links matching capability records. Existing legacy queued service names retain the prior category fallback, and accepted-quote scope protection is unchanged.

**Scope of this slice:** catalogue-driven Job intake and capability-linked starter requirements. It does not complete multi-capability onboarding, adaptive navigation/dashboard visibility, specialist operational workflows or physical offline parity.

**Verification:** feature tests were added for active/disabled offering visibility and online capability linkage. Exact-head CI and physical PC/phone acceptance are pending; this checkpoint must not be treated as a passing release gate until those results are recorded.
