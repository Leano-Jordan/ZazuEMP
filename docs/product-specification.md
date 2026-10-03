# Zazu EMP — Product Specification

**Status:** V1 Foundation  
**Purpose:** Product, UX, architecture and development source of truth

## 1. Product Definition

Zazu EMP is a business operations platform for event and catering businesses, managing the lifecycle from:

**Client → Event → Planning → Work → Resources → Purchasing → Costs → Finance → Completion**

Zazu replaces fragmented spreadsheets, paper processes and disconnected tools with one coherent operational system.

The product must remain practical for small businesses while supporting increasingly structured operations.

## 2. Product Principles

### Simplicity without sacrificing capability
Zazu hides unnecessary complexity without weakening the underlying system.

### One product, multiple experience levels
Zazu provides **Basic, Intermediate and Advanced** experiences. These are experience levels, not separate products. Users select a level during onboarding and can change it later. Changing level must not delete, corrupt or unnecessarily restructure data.

### Mobile and desktop are first-class
Neither platform is secondary. Zazu must provide professional experiences on phones, tablets and desktops. Interfaces adapt to context rather than simply shrinking or expanding another layout.

### Professional, not decorative
Prioritize hierarchy, clarity, consistency, useful information density, accessibility and fast recognition. Avoid decoration that does not improve usability.

### Business truth must remain consistent
Connected domains must not drift into contradictory states, especially **Work → Costs → Purchasing → Finance**.

### Assist, don't overwhelm
Zazu should guide users without constantly interrupting them.

## 3. Primary Users

Zazu primarily serves:
- Event management businesses
- Catering businesses
- Event service businesses
- Food/event production businesses
- Small and growing event businesses

## 4. Experience Levels

### Basic
Essential operational experience:
- Clients
- Events
- Basic products/services
- Basic planning
- Basic work/tasks
- Essential financial information
- Essential documents
- Essential reporting

### Intermediate
Broader operational control:
- Expanded event planning
- Resources
- Purchasing
- Cost tracking
- Supplier management
- More detailed financial workflows
- Expanded reporting

### Advanced
Full operational experience:
- Complete operational lifecycle
- Detailed purchasing and costs
- Financial controls
- Advanced reporting
- Complex workflows
- Deeper configuration
- Advanced search
- Operational analysis
- Expanded assistance and automation

**Critical distinction:** Experience level is not permission. Roles/permissions determine what a user can do; experience level determines what functionality Zazu presents.

## 5. Onboarding

Onboarding is a core capability.

Target journey:

**Registration → Business → Experience Level → Essential Setup → First Meaningful Action**

Onboarding must:
- Ask only what is necessary at each stage
- Allow appropriate setup to be deferred
- Put products/services setup close to registration where relevant
- Establish the selected experience level
- Avoid overwhelming new users with full configuration

Principle: **progressive configuration over configuration overload.**

## 6. Zazu Helper

Zazu Helper is a core product capability.

### Guide
Explains functions, fields and workflow requirements.

### Coach
Identifies what needs attention and useful next actions.

### Search Assistant
Helps users locate business information using natural language where practical.

### Operational Assistant
Identifies operational conditions, for example upcoming events with incomplete purchasing.

### Approved Automation
Future capability for controlled, user-approved actions. Autonomous business decisions are not a V1 requirement.

## 7. Advanced Search

Search is a core capability, not merely a database search box.

Search should cover permitted business domains including:
- Clients
- Events
- Products/services
- Suppliers
- Purchases
- Invoices
- Payments
- Documents
- Work/tasks

Structured filtering should support relevant fields such as:
- Date
- Status
- Type
- Client
- Supplier
- Event
- Financial state

Search must respect permissions.

Natural-language search can be layered over structured search as the capability matures.

## 8. Core Business Domains

V1 establishes:
- Business & Configuration
- Users & Access
- Clients
- Events
- Products & Services
- Work & Process Management
- Resources
- Suppliers
- Purchasing
- Costs
- Finance
- Documents
- Reporting

## 9. Event Lifecycle

Events are central operational objects.

A typical lifecycle is:

**Draft → Planned → Confirmed → In Progress → Completed**

with appropriate cancellation or exceptional states where required.

The exact state model may evolve, but events must never enter states that contradict dependent operational or financial records.

State transitions must be deliberate and controlled.

## 10. Operational Chain

Zazu must establish a coherent relationship between:

**Event → Work → Resources → Purchasing → Costs → Finance**

The system should make it possible to determine:
- What work is required
- What resources are required
- What must be purchased
- What activities cost
- What financial records result
- Whether the event is operationally and financially complete

The same business fact should not be independently recreated in multiple domains when that creates contradictory sources of truth.

## 11. Financial Integrity

Financial information is business-critical.

Zazu must prioritize:
- Accurate calculations
- Controlled state transitions
- Traceability
- Auditability
- Consistent relationships
- Appropriate permissions
- Reconciliation
- Historical integrity

Financial records must not silently change because an unrelated operational record changes.

## 12. Documents & Attachments

Zazu should support contextual business documents and attachments including:
- Receipts
- Photos
- Messages
- Supplier documents
- Event documentation
- Financial evidence

Documents should remain associated with relevant business context rather than becoming an uncontrolled file dump.

## 13. Dashboard & Navigation

The dashboard is an operational control surface, not a collection of decorative widgets.

It should communicate:
1. Where am I?
2. What needs attention?
3. What is happening next?
4. What is the current business position?
5. Where can I go next?

Dashboard complexity should adapt to experience level.

## 14. User Experience Standard

Zazu must maintain a coherent visual language across:
- Typography
- Spacing
- Buttons
- Forms
- Tables
- Cards
- Navigation
- Status indicators
- Toasts
- Dialogues
- Icons
- Empty states
- Error states
- Loading states
- Responsive behaviour

The goal is not identical screens. The goal is that every screen clearly belongs to **Zazu**.

### Design correction rule
Existing UI must not automatically be changed merely because it differs from another screen. Changes should improve usability, consistency, accessibility, hierarchy, responsive behaviour or commercial quality. Intentional differences should remain where they serve a legitimate purpose.

## 15. Responsive Product Behaviour

### Mobile
Prioritize:
- Quick actions
- Clear navigation
- Touch-friendly controls
- Important information first
- Efficient forms
- Event execution
- Search
- Notifications
- Fast status updates

### Desktop
Prioritize:
- Information density
- Multi-column workflows
- Tables
- Planning
- Financial work
- Reporting
- Administration
- Side-by-side information

Both are first-class experiences.

## 16. Notifications & Feedback

System feedback should explain:
- What happened
- Whether it succeeded
- What changed
- What the user can do next, where relevant

Avoid meaningless messages such as "Success."

Consequential or destructive actions require appropriate confirmation.

## 17. Architecture Requirements

Architecture must support:
- Clear domain boundaries
- Controlled state transitions
- Consistent data ownership
- Permission enforcement
- Auditability
- Maintainability
- Secure data handling
- Reliable financial calculations
- Extensible workflows
- Responsive interfaces
- Search
- Zazu Helper
- Experience-level configuration

Business logic should not be unnecessarily duplicated across controllers, views and frontend code.

## 18. Security & Access

Zazu must enforce:
- Authentication
- Authorization
- Role-based permissions
- Server-side permission checks
- Appropriate data isolation
- Secure document handling
- Input validation
- Protection of sensitive business information
- Auditability of important actions

UI visibility is never the security boundary.

## 19. V1 Scope

V1 must provide a reliable, coherent operational foundation covering:
- Registration
- Onboarding
- Business setup
- Basic / Intermediate / Advanced experience levels
- Users and permissions
- Clients
- Events
- Products/services
- Work/process management
- Suppliers
- Purchasing
- Costs
- Core finance
- Documents/attachments
- Reporting
- Search
- Responsive mobile experience
- Responsive desktop experience
- Zazu Helper foundation
- Notifications and meaningful system feedback

The major domains must work together without contradictory lifecycle states, particularly:

**Event → Work → Purchasing → Costs → Finance**

## 20. V1 Does Not Require

V1 must not expand indefinitely.

The following are not automatic V1 requirements:
- Full AI autonomy
- Autonomous business decision-making
- Complex enterprise workflow engines
- Unlimited automation
- Predictive financial systems
- Large-scale third-party integrations
- Advanced AI forecasting
- Fully mature analytics/BI
- Every conceivable catering workflow
- Unlimited UI customisation
- Enterprise-scale multi-company complexity unless required by the architecture

These remain potential post-V1 expansion.

## 21. V1 Finish Line

V1 is ready when:
- The core event/catering business lifecycle works coherently end-to-end.
- The application feels like one professional product.
- Basic users are not overwhelmed.
- Advanced users can access necessary operational depth.
- Critical workflows work properly on mobile.
- Complex operational, administrative and financial workflows work properly on desktop.
- Domain boundaries are clear.
- Business and financial information remains internally consistent.
- Permissions cannot be bypassed through alternative interfaces.
- Important operations fail safely.
- Zazu Helper provides useful contextual guidance.
- Users can reliably find business information.

## 22. Post-V1 Expansion

Potential expansion includes:
- Deeper automation
- Advanced AI assistance
- Natural-language business intelligence
- Predictive planning
- Advanced forecasting
- More integrations
- Advanced financial intelligence
- Deeper inventory/resource management
- Industry-specific workflows
- Enterprise capabilities
- Advanced collaboration

Expansion must be driven by validated product needs rather than feature accumulation.

## 23. Specification Governance

Every proposed feature should be classified as:

**V1 Core** — required for the V1 product.

**V1 Supporting** — required to make V1 reliable, usable or commercially credible.

**Post-V1** — valid Zazu functionality that should wait.

**Out of Scope** — does not belong in the current product direction.

**Architectural Foundation** — not necessarily visible to users but necessary to prevent future rework.

## 24. Product North Star

Zazu should make running an event and catering business feel:

**Clearer. More controlled. More connected. Less administrative. Less fragmented.**

Zazu's job is not to show users how much software it contains.

**Its job is to help them run the business.**


## 25. Director implementation baseline — 2026-09-28

The V1 specification has been checked against the current implementation and the main missing capabilities have been moved materially toward baseline.

### Experience levels

Zazu now defines Basic, Intermediate and Advanced as presentation levels stored per business membership. They change the amount and order of surfaced information and guidance. They do not change roles, permissions or business-data access.

### Onboarding

The operational onboarding sequence is now:

**Registration → Services & prices → Experience level → Business identity → Dashboard**

Catalogue and business setup deferral remain supported.

### Search

A real cross-domain record search foundation now exists within the Laravel/database architecture. The header command palette can hand a query into record search. Results remain business-scoped and permission-aware.

### Zazu Helper

The Helper now combines route-specific guide content with a small live attention layer for setup, overdue preparation, open purchasing and draft jobs. The amount of attention shown adapts to experience level.

### Operational chain

The Job workspace now makes the intended operational path more visible:

**Services → Preparation → Purchasing → Costs → Finance**

This is a visibility layer over existing domain relationships and lifecycle controls, not a new source of truth.

### Event lifecycle note

The implementation remains:

**Draft → Confirmed → In Progress → Completed**

with cancellation handling.

The specification does not require a new Planned state merely because the word appears in a descriptive lifecycle. Adding a state will require an explicit business rule and impact assessment first.

### V1 scope control

No major existing domain has been removed. Compliance, inventory, assets, travel, reporting and AI remain deliberately bounded so V1 can finish as a coherent product rather than expanding indefinitely.

### Release implication

The work is now moving from broad capability construction toward:

**runtime verification → populated-database verification → desktop/mobile QA → targeted defect correction → release candidate**



## 26. Director UX revelation — progressive disclosure

Zazu must be capable without being visually overwhelming. Simplicity is achieved through **progressive disclosure**, not capability removal.

### Information layers
1. Primary: what the user needs for the current task.
2. Expandable: useful detail available on demand.
3. Advanced: deeper operational, financial or configuration information.
4. Specialist: rare compliance, tender, administration and power-user controls.

### Preferred interaction patterns
- Accordion for grouped forms and settings.
- Expandable row for record detail.
- Expandable card/summary for dashboard or event/job context.
- Sticky summary for quotes and other workflows where a consequential total should remain visible.

Basic/Intermediate/Advanced remains a presentation/guidance preference, not a literal per-page mode switch and never a permission boundary.

### Context rule
Where possible, users should work from the business object they understand — especially the event/job — rather than navigating through implementation-shaped domain boundaries.

## 27. Director landing-page visual direction

The landing page is a brand/identity surface and may be visually expressive during product development. Custom artwork, photography, mascot concepts, illustration, texture and visual experimentation are legitimate design inputs.

Release preparation must separately verify ownership, licensing, trademark/brand permissions and replacement requirements for every externally sourced asset. Development visuals must not be removed merely because release clearance has not yet occurred.

## 28. Local-first capability integration

Zazu may use open-source libraries and external APIs to enhance the product, including OCR, document viewing, scanning, signatures, search, charts, mapping and voice input. These are not automatically V1 core requirements.

Integration rule: **core business operations first; optional intelligence/enrichment second.**

An external service must not become a hidden dependency for essential offline-capable workflows. Each dependency is governed through docs/ZAZU_EXTERNAL_CAPABILITY_REGISTER.md.


## 29. Commercial packaging requirements — Director consolidation 2026-10-02

Zazu is one product with increasing levels of operational sophistication. Commercial tiers must not become four separate codebases or artificially cripple basic users.

The working commercial packaging hypothesis is:

| Tier | Working price | Product promise |
|---|---:|---|
| Basic | R299/month | Keep my business organised |
| Professional | R499/month | Run my jobs and money |
| Business | R799/month | Run my people and operations |
| Business Plus | R1,299/month | Control a larger, more complex business |

These prices are **commercial hypotheses, not fixed entitlements**. Final pricing requires customer validation, operating-cost analysis and a release-ready entitlement/licensing model.

### R299 — Basic

The minimum credible paid product should replace the operator's core spreadsheet/admin workflow.

Required capability:
- business/workspace and authentication;
- customers and contacts;
- products/services;
- events/jobs and requirements;
- calendar;
- quotes and quote versions;
- quote acceptance;
- deposits;
- invoices;
- payments;
- basic expenses;
- dashboard and essential reports;
- search;
- printable business documents;
- essential audit/security controls;
- reliable data protection and recovery.

The customer outcome is: **"I no longer need my main workbook to run the business."**

### R499 — Professional

This tier must add a clear operational and financial control loop rather than merely more screens.

Required commercial spine:
**Event → Quote → Deposit → Invoice → Payment → Purchasing → Receiving → Costs → Profit → Export**

Required capability:
- supplier workflow;
- purchase orders;
- receiving/goods received;
- cost capture tied to operational records;
- usable inventory foundations;
- product/service costing;
- event profitability;
- customer/event history;
- spreadsheet import with preview and matching;
- spreadsheet export;
- stronger operational reporting.

The customer outcome is: **"Zazu is running the operational side of the business, not just recording it."**

### R799 — Business

This tier is for businesses with staff and meaningful operational coordination.

Required capability:
- staff roles and permissions;
- manager access with explicit permission boundaries;
- event/work assignment;
- workload visibility;
- operational attention dashboard;
- staff activity;
- approval/control workflows where consequential;
- stock and purchasing alerts;
- operational notifications/reminders;
- management-level financial and operational visibility;
- stronger resource/equipment control.

The customer outcome is: **"My team can use Zazu and I can manage the operation."**

### R1,299 — Business Plus

This tier is for materially more complex businesses. It should not be built simply to justify a price.

Required capability when validated by real customer need:
- advanced profitability by event/service/customer;
- resource capacity and allocation;
- advanced purchasing/stock controls;
- advanced management reporting;
- multi-location or equivalent multi-operational-unit controls;
- advanced import/migration;
- higher-order automation and cross-team coordination.

The customer outcome is: **"I can control a larger, more complex operation from one system."**

### Commercial entitlement rule

Do not create arbitrary record-count limits as the primary upgrade mechanism.

Upgrade boundaries should be based on meaningful operational sophistication, such as team management, approvals, advanced reporting, resource control and multi-unit operation.

### Commercial readiness order

The engineering sequence is:

1. Close release blockers and prove the current core.
2. Complete the R299 foundation.
3. Complete the R299 → R499 operational/financial bridge.
4. Validate the commercial workflow with real businesses.
5. Build R799 capabilities from demonstrated team-management needs.
6. Build R1,299 capabilities only where complexity and willingness-to-pay are evidenced.

This protects V1 from feature sprawl while giving pricing a concrete product architecture.

## 19. Zazu Helper Character System

Zazu Helper is a core product character and interaction layer. It is not merely decorative artwork.

The system uses **one Helper character framework with selectable mascot skins**. Initial candidates are Gecko, Ant and Chameleon. The skin changes the physical expression of the character, not its personality, intelligence, permissions or experience level.

Basic / Intermediate / Advanced remains a presentation and guidance setting. The same selected Helper skin operates across all three levels.

The Helper must use semantic behaviour and responsive workspace targets rather than screen-specific animation logic. Its foundation includes reusable states such as idle, notice, approach, point, explain, think, working, warning, error, offline, syncing, return and sleep.

Production weight is a hard design constraint. The preferred direction is lightweight 2D/vector assets with reusable animation/state-machine behaviour. Avoid 3D models, game engines, video-based ordinary interactions and duplicated per-page animation systems.

Movement should be purposeful and event-driven. The Helper should normally remain quiet/resting and move only when relevant to the user or an active workflow. It must never obscure controls or become a substitute for ordinary UI.

The final visual design is intentionally deferred. The character-system foundation must exist independently of final artwork so that visual skins can be designed and attached later without changing the underlying product behaviour.

See docs/ZAZU_HELPER_CHARACTER_SYSTEM.md for the detailed foundation.

## 20. Helper implementation and R0 technology constraint

The Zazu Helper architecture is intentionally designed for a zero-budget V1.

The Helper must operate without a paid animation service, paid runtime, animation-hosting subscription or commercial character platform.

The V1 renderer baseline is:

Original Zazu artwork → SVG → CSS / Web Animations API

The semantic Helper engine must remain separate from the renderer so that future technology changes do not change application behaviour.

The final mascot visual design remains an owner/design decision. The implementation must not hard-code the product around a particular animal skin.

See:
- docs/ZAZU_HELPER_ENGINE_SPECIFICATION.md
- docs/ZAZU_HELPER_SKIN_DESIGN_BRIEFS.md
- docs/ZAZU_HELPER_FIRST_PRODUCTION_BEHAVIOURS.md
- docs/ZAZU_HELPER_ANIMATION_TECHNOLOGY.md

Commercial release proof remains separate from architecture: the Helper is not considered production-complete until its behaviour, mobile placement, accessibility, interruption handling and reduced-motion/static states are verified in the actual Zazu interface.
