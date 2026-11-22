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

## Implementation sequencing

This is a product-wide requirement, but implementation must be incremental:

1. Inventory the current business capability/preset configuration, catalogue, navigation, intake, event requirements, Helper sources and offline sync.
2. Identify the existing authoritative source (or smallest safe extension) for enabled business capabilities. Do not add a parallel configuration system.
3. Define a capability-to-surface matrix before changing visibility or workflows.
4. Implement and test one narrow vertical slice for a representative preset, including Helper relevance.
5. Extend to multi-capability composition and other presets only after the first slice proves safe.
6. Verify historical-data preservation, business isolation, permissions, PC/mobile and offline behaviour.

**Release discipline:** this requirement is recorded and mandatory for product direction. It is not a claim that adaptive presets are already implemented or that every specialist workflow belongs in V1. Prioritize the smallest coherent V1 slice against current readiness gates; do not silently expand V1 into a rewrite.
