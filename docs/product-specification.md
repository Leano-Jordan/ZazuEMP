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
