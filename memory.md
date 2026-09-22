# Zazu EMP — Living Project Context

**Product:** Zazu – Event Management Platform  
**Short name:** Zazu EMP  
**Company / product owner:** Rosscore Labs Pty Ltd  
**Founder / company owner / creator / lead developer:** Isaac Junior Lehlogonolo Maluleka  
**Repository:** `Leano-Jordan/ZazuEMP`  
**Default branch:** `main`  
**Project state:** Definition / foundation build  
**Context last updated:** 2026-09-23

> **BOOTSTRAP RULE FOR EVERY NEW PROJECT CHAT:** Read this file first. Treat it as the current project context and living memory. It records current decisions, validated discovery evidence, unresolved questions, architecture direction, and build state. It may change when the owner gives new direction or when new evidence changes what we know.

---

## 1. What Zazu EMP is

Zazu EMP is a Rosscore Labs Pty Ltd software product for small businesses that provide event-related services.

The product is **not Sindi's personal system** and is **not limited to catering**.

Target businesses can include:
- catering
- tents and chairs / event equipment hire
- sound / DJ
- baking / cookies
- decor
- other event services
- businesses combining several of these services

A business may use only one service or several on the same event.

The central model is:

**Business → Customer → Event / Job → Services → Quote → Confirmation / Deposit → Buying / Hiring → Preparation → Event → Payments / Completion**

The event/job is the centre of the product, not the industry category.

---

## 2. Owner and decision authority

Zazu EMP is owned and controlled by **Rosscore Labs Pty Ltd**.

**Isaac Junior Lehlogonolo Maluleka** is the owner of the company, creator and lead developer of Zazu EMP.

The engineering/discovery assistants work for the owner.

### Decision rule
Do not silently turn an assistant preference into a project decision.

For major product, UX, architecture, infrastructure, spending, licensing or scope decisions:
1. inspect the current project/evidence
2. present viable options and meaningful trade-offs
3. identify risks/dependencies/cost implications
4. let the owner decide unless the owner has already decided

The project memory is adaptable. It is not a rigid specification.

---

## 3. Operating engines

Three project engines must stay aligned on meaningful Zazu EMP work:

### Project Genesis & Scale Engine
Purpose: understand reality, reduce uncertainty, define the product, sequence work, and scale from evidence.

Core loop:
**EXTRACT → UNDERSTAND → QUESTION → VALIDATE → DEFINE → SCOPE → PLAN → BUILD / JOIN / INTERVENE → VERIFY → LEARN → ADAPT → SCALE / PRODUCTIZE / PIVOT / STOP**

### Human-First Discovery Mode
Purpose: learn from real operators without wasting their time.

Rules:
- ask short, high-value questions
- ask one useful question at a time when interviewing
- follow the pain rather than completing a template
- distinguish known facts from assumptions
- stop questioning when another question will not materially change the next decision

### RossCore Engineering Command Engine
Purpose: produce software that is correct, maintainable, secure, usable and verifiable.

Core engineering discipline:
**INSPECT → PLAN → AUTHORIZE → IMPLEMENT → TEST → VERIFY**

UI/UX is a first-class engineering concern.

---

## 4. Commercial position

### Current budget
**R0.**

Explore free and open-source technologies that permit commercial use before introducing paid services.

Paid infrastructure or services are not forbidden forever, but they must be presented as an option with cost/value implications and require owner approval.

### Productisation
Zazu EMP should be designed from the beginning as a reusable commercial product for multiple independent businesses.

Sindi is a real discovery/pilot user. She is not the sole customer and not the product owner.

Other operators, including the owner's neighbour and additional catering/event businesses, are valid discovery and pilot users.

---

## 5. Real-world discovery: Sindi

Sindi Sithole is an actual small event-business operator whose workflow is being used for product discovery.

She is not being treated as a proxy for every business. Her answers are evidence about **her workflow** and signals about problems worth investigating with other operators.

### Confirmed quotation findings
- Quotation is a major pain point.
- Before quoting, she may need to source prices from different shops.
- She personally goes online or physically to look for ingredients/items.
- This is an owner-operated, ad-hoc sourcing process, not a large-company supplier-chain workflow.
- Menu is already agreed before the quote.
- Ingredients are accounted for.
- Time and effort are included.
- Prices are already set when preparing the quote.
- She currently uses Excel for quotations and invoices.
- Some clients do not confirm after substantial quotation work has already been done.
- Deposit/payment is the commitment point.

### Confirmed distance/travel findings
- Distance charge is calculated by kilometre.
- She checks Google Maps.
- She referred to government pricing available online as a reference.
- Example mentioned: R14/km.
- She charges the trip in and out.
- She confirmed that calculating the cost per kilometre is the part of quotation work that takes the most time.

### Confirmed changes/additions
- Additions can be added to the existing work or handled as a separate quote.
- Invoice number and amount may be changed when the work changes.
- Keeping related work together is preferable.

### Confirmed buying/hiring model
- She uses what she already owns.
- She hires what she does not have for a particular job.
- Sourcing can therefore vary from job to job.
- The product should not assume every operator owns all equipment or has a fixed supplier catalogue.

### Confirmed cost-control issue
- After deposit, she wants to avoid buying things outside the agreed scope.
- Time and distance can be missed in costing.
- Finance is currently manual using Excel and calculators.

### Confirmed event organisation
- Separate events can be separated with different invoices and references.
- She creates a folder for a wedding, funeral or other event and names the folder using the address.
- Reminders / keeping track are a problem and built-in reminders sounded useful.
- Before an event she checks that everything agreed with the client is prepared and ready.
- That preparation check is ordinary operational common sense and is not itself being treated as a major validated pain point.

### Confirmed technology preferences/signals
- She currently has no dedicated system.
- She said many businesses also do not have one.
- She would like a dedicated system.
- She mentioned a map, WhatsApp connection and possible Excel connection as useful.

### Current strongest discovery signals
1. Faster, easier quotation work.
2. Automatic or assisted distance-cost calculation.
3. Clear approved scope so spending stays within the job.
4. Simple event/job organisation.
5. Practical reminders and follow-up.
6. Less manual Excel/calculator work.
7. The ability to work with owned, hired and bought resources in one job.

---

## 6. Product hypothesis

The product should reduce the manual coordination around an event without forcing a small operator into enterprise software.

A useful event record should bring related data together:

- customer
- event date/time
- venue/address
- services
- quote
- travel
- deposit/payment status
- expenses
- hired items
- documents
- reminders
- activity/history
- contact actions

The goal is not “more features”.

The goal is to make the operator's real work faster, clearer and less error-prone.

---

## 7. UX / information architecture

### Core UX principle
**Workspace over screen.**

Do not create one narrow CRUD screen for every data type simply because the database has separate tables.

Current design direction is a small number of primary workspaces containing rich, contextual records using:
- tabs
- cards
- side panels
- drawers
- contextual modals
- split views
- inline editing where useful

Related information should remain together when the user is performing one job.

Example:

When editing an event quote, the user should not need to bounce through separate screens to see the customer, venue, distance, travel cost and payment context.

### Candidate primary operator navigation
This is a working proposal, not a final locked decision:

1. **Home** — today's work, upcoming events, reminders, outstanding quotes/payments and attention items.
2. **Events** — combined list/calendar with search and filtering.
3. **Event Workspace** — central operational record for one event.
4. **Customers** — customer records and event history.
5. **Quotes / Work** — quotation pipeline and active work.
6. **Finance** — payments, expenses and business summaries.
7. **Settings** — business profile, services, rates, users and integrations.

The exact navigation and screen count remain subject to design and owner approval.

### Event Workspace
This is expected to be the most important screen/workspace.

It should be able to show, in one contextual view:
- customer
- date/time
- event type
- address
- status
- quote value
- deposit/balance
- services
- travel calculation
- spending
- hired/bought items
- reminders
- documents
- activity/history
- WhatsApp actions
- map/route

---

## 8. Two connected product experiences

Zazu EMP has two faces over the same product core.

### Business Operator App
For each customer business:
- strong desktop/laptop experience because operators already use Excel, MS tools, folders and calculators
- strong phone experience for work away from the desk and at events
- responsive web application
- eventual PWA capabilities

This is **desktop + mobile**, not mobile-only.

### Rosscore Control
For Isaac / Rosscore Labs:
- cross-business operational visibility
- product usage and support visibility
- event/activity overview
- alerts and exceptions
- account/business administration
- system health

Access to customer business data must be deliberate, permissioned and auditable.

---

## 9. Role-based access and business boundaries

Security and authorization are foundational.

Use a business-aware access model:

**User → Business membership → Role → Permission → Allowed action**

Do not use a simple global user role as the whole authorization model.

Every business-owned record should have a clear business boundary so independent businesses cannot see each other's data.

Initial conceptual roles may include:
- platform owner / Rosscore control
- business owner
- manager
- staff / operator
- other restricted roles as evidence requires

The exact role catalogue and permissions are subject to implementation design and owner approval.

Core requirements:
- authentication
- registration
- session security
- authorization
- business isolation
- password recovery
- email verification where appropriate
- auditability for important actions
- server-side authorization, not just hidden UI

---

## 10. Quotation model

Quotation is a key entry point.

A quote may combine:
- services
- products/items
- quantities
- prices
- labour/time
- travel
- hired items
- other charges
- discount/adjustment where applicable
- total
- deposit
- payment status

### Travel calculation
Validated pain point.

Conceptual flow:
**origin + destination → route distance → one-way/round-trip → km rate → calculated travel charge**

The rate is configurable. Sindi's R14/km is an example from her current practice, not a universal Genesis rule.

A manual override may be allowed with a reason/history record.

The route provider should sit behind a replaceable map/distance service boundary.

---

## 11. Scope, changes and money

An accepted/approved quote should create a clear historical boundary for the job.

Changes/additions should be represented as versions or change records rather than silently destroying the prior agreed state.

The event should be able to distinguish:
- quoted amount
- deposit received
- additional charges
- actual expenses
- hire costs
- balance outstanding

This is particularly relevant to Sindi's concern about avoiding spending outside agreed scope.

The first financial model is operational visibility, not a replacement for every accounting package.

---

## 12. Buying, hiring and sourcing

Genesis must support real small-operator behaviour.

For a particular event, an item/service may be:
- already owned
- bought for the event
- hired for the event
- sourced ad-hoc from a shop/supplier

A fixed supplier network is **not** a prerequisite.

Potential future structures:
- simple supplier/source records
- reusable known prices
- event-specific purchase/hire records
- actual-vs-quoted cost comparison

These remain to be validated.

---

## 13. Maps, WhatsApp and Excel

### Maps
Current user evidence supports map-assisted distance calculation.

Candidate approach:
- address fields
- route/distance lookup
- configurable km rate
- round-trip calculation
- generated travel line item

Map provider choice remains open.

### WhatsApp
First useful capability:
- stored customer phone number
- click-to-chat
- pre-filled quote/reminder/payment messages

Deep API automation is a later decision based on demand, cost and feasibility.

### Excel
First useful capability:
- import existing business data where practical
- export quotes, invoices and operational/financial information
- ease migration from current Excel workflows

Live two-way Excel synchronisation is not yet a validated requirement.

---

## 14. Technical direction

### Current preferred foundation
This is the current technical proposal, not an irreversible commitment:

- **PHP 8.4** as the practical local development target because that version is already available on the owner's machine
- **Laravel 13**
- **MySQL 8.x**
- **Livewire 4**
- **Tailwind CSS 4**
- **Vite**
- minimal JavaScript for browser capabilities where useful
- automated tests appropriate to critical business/security workflows

The reason for using PHP 8.4 rather than forcing another installation is practicality. The owner's current CLI is still resolving AMPPS PHP 7.4 and must be switched to the existing PHP 8.4 installation before Laravel 13 creation.

### Architecture
Current direction:
- modular monolith
- recognisable Laravel conventions
- reusable business logic
- replaceable integration boundaries
- multi-business data boundaries from the start
- simple infrastructure appropriate to R0
- avoid architecture that assumes one business only

Do not inherit SwiftOrder's architecture automatically. Genesis has different product requirements.

---

## 15. Current development environment / build state

Owner is developing in **VS Code on Windows**.

Working project folder:
**Zazu EMP** under the owner's projects directory.

GitHub repository exists:
**Leano-Jordan/ZazuEMP**

Repository is currently a public GitHub repository and includes project-control documentation.

Current machine state:
- PHP CLI: **7.4.33**, supplied by AMPPS
- Composer: **2.10.2**
- Node: **26.9.0**
- npm: **11.19.1**
- PHP 8.4 is already available separately on the owner's machine
- current task is to make the development shell resolve PHP 8.4 instead of AMPPS PHP 7.4

Do not delete or break AMPPS PHP 7.4 while switching the Zazu development environment.

### Immediate build sequence
1. Correct the PHP CLI resolution.
2. Create the Laravel application in the Zazu EMP project.
3. Establish authentication and polished first impression.
4. Establish business creation/ownership and role-based access.
5. Build the shared application shell and workspace navigation.
6. Build Event/Job + Customer foundation.
7. Build Quote + travel-cost workflow.
8. Build deposits/payments/scope/change tracking.
9. Add reminders, documents, WhatsApp actions and Excel support.
10. Build Rosscore Control.
11. Test with multiple real businesses and adapt from evidence.

---

## 16. UI/UX quality bar

A beautiful UI is not a finishing coat.

Zazu should feel:
- modern
- clear
- calm
- fast
- professional
- approachable
- information-dense without being cluttered
- usable on laptop/desktop
- excellent on phone when needed

The product should feel like a **purpose-built modern operations workspace**, not an Excel spreadsheet wearing a web skin.

---

## 17. Brand

Product name:
**Zazu – Event Management Platform**

Short form:
**Zazu EMP**

Zazu is a personal name connection used by Sindi for Isaac, and the product brand can use an original bird mascot.

Mascot direction:
- create an original bird character
- may draw on broad African bird/hornbill inspiration
- must not copy the appearance, costume, artwork or distinctive character design of Disney's Zazu
- treat brand/trademark questions separately from copyright
- complete appropriate clearance before commercial brand expansion

---

## 18. Current discovery questions

Discovery should continue only where another answer can change what we build.

Useful future questions should focus on concrete mechanics such as:
- how quote revisions are actually handled
- how event spending is recorded
- what information an owner needs at a glance
- how different businesses organise services/items
- how customers communicate changes
- what multiple events look like in practice
- what operators would pay or what commercial model is realistic

Ask real operators short questions. Do not waste their time.

---

## 19. Known facts vs hypotheses

### VERIFIED / REPOSITORY
- Zazu EMP repository exists.
- Rosscore Labs owns the product.
- Isaac is the owner/creator/lead developer.
- This is a multi-business product direction.
- Project uses three operating engines.
- R0 is the current budget.
- Desktop/laptop is an important primary workspace.
- Workspace-over-screen is a current UX principle.
- Role/business isolation is foundational.

### REPORTED BY SINIDI
- quotation work is time-consuming
- per-kilometre calculation is the slowest part for her
- sourcing is done personally online/physically
- Excel + calculator is current workflow
- deposit is commitment point
- time/distance can cause costing losses
- scope control matters after deposit
- events are separated by invoice/reference
- folders by event/address are used
- reminders/keeping track are problematic
- map, WhatsApp and Excel connections sound useful
- she would use a dedicated system

### INFERRED
- quote + travel costing is a plausible initial product wedge
- approved scope linked to expenses may reduce out-of-scope spending
- event workspace can replace multiple disconnected manual records

### ASSUMED / NEEDS MORE VALIDATION
- exact common workflow across the broader market
- universal role structure
- universal accounting requirements
- universal need for inventory management
- preferred map provider
- depth of WhatsApp integration
- commercial pricing model
- exact minimum screen/workspace count

---

## 20. Major rules for future project chats

1. Read this file before acting.
2. Check current repository/runtime evidence before assuming state.
3. Treat owner instructions as authoritative unless they conflict with safety/legal constraints.
4. Do not treat stale AI output as a decision.
5. Do not make a major architecture/spending/product decision without presenting options unless the owner has already decided.
6. Keep the three engines together.
7. Keep discovery evidence separate from assumptions.
8. Prefer useful action over long speeches.
9. Do not build features merely because they are possible.
10. Update this file when project reality, owner decisions or validated evidence changes.
11. Keep documentation aligned with repository reality.
12. Zazu EMP is a Rosscore Labs product for multiple businesses, not a bespoke Sindi application.

---

## 21. Current next action

**Get the development environment onto PHP 8.4, then create the real Laravel application and begin the authentication/registration foundation with a polished Zazu EMP UI.**
