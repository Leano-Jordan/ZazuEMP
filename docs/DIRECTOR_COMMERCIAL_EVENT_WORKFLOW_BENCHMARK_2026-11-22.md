# Director Commercial Event Workflow Benchmark — 2026-11-22

**Repository:** `Leano-Jordan/ZazuEMP`  
**Branch:** `main`  
**Research date:** 2026-11-22  
**Scope:** Commercial event/catering product flows, public product documentation, publicly visible UI evidence, practitioner discussions, and direct comparison with Zazu's current source.  
**Decision:** Apply a small, low-risk correction to the new-job intake sequence and its explanatory copy. Do not perform a broad navigation redesign until the requirement lifecycle and job-home journey are resolved.

## 1. Executive conclusion

The user's problem is real and aligns with recurring product patterns in established event and catering software: access to individual features is not enough. People need a visible path through a job, an event-centred home, and state-aware explanations of what is waiting or should happen next.

Across Caterease, Total Party Planner, Planning Pod and HoneyBook, the strongest common patterns are:

1. **A guided start:** collect client/contact and core event facts before deeper operational detail. Use a wizard or a process map to guide the user.
2. **One event/project home:** keep related records, tools, activity and current state accessible from the event itself.
3. **Visible progress:** show upcoming, waiting, overdue and completed work rather than requiring the operator to remember where to look.
4. **Contextual hand-offs:** documents and downstream work are created from the event's existing information rather than asking users to rebuild the same facts.
5. **Flexible operational work:** tasks, vendors, menus, budgets, payments and execution details are available when relevant; the software should not impose the same rigid sequence on every event.
6. **Role-appropriate simplicity:** advanced capability must not turn the default path into an overwhelming feature catalogue.

The products differ in market and process. Their commonality is not a universal linear checklist; it is a coherent event context and visible orientation.

## 2. Product evidence reviewed

### Caterease — catering-specific event booking wizard

**Official product features:** https://www.caterease.com/features/  
**Official onboarding timeline PDF:** https://help.caterease.com/wp-content/uploads/2026/01/Caterease-Onboarding-Timeline.pdf

The Caterease onboarding document lists the booking path in this order:
- Access the Event Wizard
- Add a new client
- Add contact people
- Enter event details
- Build the event menu
- Enter general event comments
- Generate and email a print
- Post a payment

It separately teaches event management tools such as event timeline, event checklist, future payment scheduling, cost/profit evaluation, attachments and event portal sharing.

Its product feature page says event booking wizards can be customized per event type and fields can be made conditionally required. Menu building includes menu packages, linked/minimum quantities; recipes and packing lists can be compiled from event menu items; task management includes reminders, history notes and checklists.

**What this proves:** a catering-specific product deliberately puts client/contact and event facts before menu construction, and uses event-type-specific intake rather than treating every booking as the same generic form.

**Evidence limitation:** the public page and onboarding PDF document the product and training sequence; they do not expose the full authenticated wizard screen-by-screen. No claim of a complete live-app usability test is made.

### Total Party Planner — catering sales and back-of-house operations

**Official features:** https://totalpartyplanner.com/features/

The official feature page groups capabilities into event management, sales/lead management, financial/profit analysis, staff/task management, back-of-house management and payments. It explicitly describes:
- custom event creation, banquet management, calendar/event tracking and reporting;
- menu, beverage and rental packages;
- proposals, BEOs, invoices and express order forms;
- message templates and a vision board;
- manual or automated packing lists;
- menu costing and profit analysis;
- deposits and partial payments;
- task templates, user permissions and mobile access.

**What this proves:** a catering event is treated as the shared source for commercial documents and operational outputs, including the back-of-house detail that a generic event calendar would miss.

**Evidence limitation:** the public feature page is a vendor feature overview, not a complete event-creation walkthrough. Claims about integration and efficiency are vendor claims, not independent measurements.

### Planning Pod — event dashboard and task visibility

**Official event overview/help:** https://planningpod.com/help-center/overview-how-do-events-work  
**Official checklist/task management:** https://planningpod.com/checklists-and-task-management  
**Official platform:** https://planningpod.com/platform

Planning Pod documents a clear Events-tab entry point. Creating an event automatically creates an event sub-calendar. The Event Dashboard provides attendee, productivity, financial, collaboration and additional tools. It surfaces recent activity, overdue items, waiting items and upcoming items, and includes an activity log, date/time conflicts, contacts, vendors and venue spaces.

Its task system supports due dates, descriptions, notes, status, attachments, assignments, reminders, sublists and reusable templates. Template due dates are calculated backwards from the event date.

**What this proves:** the event dashboard is not just a page of links. It gives the user a context-rich event home, with progress and activity signals, while the calendar brings date-based work together.

### HoneyBook — client/project journey and process mapping

**Official getting-started guide:** https://www.honeybook.com/lp/getting-started  
**Official process mapping help:** https://help.honeybook.com/en/articles/9124063-set-up-your-business-process-in-honeybook  
**Official project/client relationship help:** https://help.honeybook.com/en/articles/9055403-manage-your-client-relationships-in-honeybook

HoneyBook's setup guide walks a new business through settings, project pipeline, files/templates, lead forms, automations and payments. Its process mapping tool asks how the business works, creates a visual process map, and links each block to the corresponding product feature. It explicitly tells users to work through the map column by column, while noting that not every business needs every step.

The client/project model distinguishes leads from booked clients and uses projects as hubs for communication, files, participants and event details. Pipeline stages track where a client relationship stands.

**What this proves:** a product can help users understand the intended sequence before they begin, and can connect process stages directly to the feature that completes them. It also demonstrates why a single rigid workflow is inappropriate: the map is tailored to the service process.

## 3. Practitioner evidence and cautions

Public discussions show both demand for centralization and the risk of overbuilding:

- In an EventProduction discussion, practitioners described using a mixture of Asana, Basecamp, Excel, Airtable, Notion and Trello. One operator said the ability to access all event details in one place, including on a phone, was invaluable. Another explicitly said they did not want event software to reinvent project management; they wanted finalized vendor details, change logs and useful reports.  
  Source: https://www.reddit.com/r/EventProduction/comments/1ijfh8p

- In another event-software discussion, a practitioner called event software clunky and too difficult to customize, while others described mixing Airtable, Google Sheets, Notion and Asana.  
  Source: https://www.reddit.com/r/EventProduction/comments/1dfd3b4

- A Caterease user described its extensive customization as overwhelming when the organization underused features and staff lacked training; another user said an enterprise system was poorly laid out and staff reverted to Excel for schedules. These are individual experiences, not a representative satisfaction survey.  
  Source: https://www.reddit.com/r/restaurant/comments/1m5jh5m

- A 2025 discussion about switching event software included complaints about tools being too robust/complicated, unclear to use, and not adopted by operations staff; the same thread includes positive experience with Caterease.  
  Source: https://www.reddit.com/r/EventProduction/comments/1jlxdyg

**Interpretation:** centralization helps only when the interface makes the information usable. More features, more customization and more navigation do not automatically make the operator's work easier. Do not copy a competitor's feature count or assume that one process fits every event business.

## 4. Common workflow shape — not a mandatory linear sequence

The combined evidence supports this high-level pattern:

1. **Capture the relationship:** identify or create the customer/contact and understand the request.
2. **Establish the event facts:** name/type, date/time, location, event size or other relevant facts.
3. **Define the scope:** services/menu/packages, quantities, units, special requirements and customer-specific details.
4. **Agree the commercial offer:** proposal/quote, terms, customer decision, contract where applicable, deposit/invoice.
5. **Prepare and coordinate:** tasks, suppliers, purchase orders, stock/assets, staffing, venue logistics and event-specific timelines. Some work is optional or parallel.
6. **Deliver and close:** execution, final charges/payments, actual costs, profitability and completion.
7. **Retain the record:** files, communication, change history and repeat-event context.

The sequence changes by business type and job. A simple delivery may not need venue planning, staffing or purchasing. A large wedding may need those activities in parallel. The interface should expose dependencies and conditional work, not mark every possible module as a mandatory step.

## 5. Direct Zazu source inspection

Reviewed current `main` source:
- `resources/views/work/create.blade.php`
- `app/Http/Controllers/WorkController.php`
- `resources/views/requirements/create.blade.php`
- `resources/views/requirements/index.blade.php`
- `app/Http/Controllers/RequirementController.php`
- `resources/views/work/show.blade.php`
- `resources/views/quotes/create.blade.php`
- `resources/views/quotes/show.blade.php`
- `app/Services/EventLifecycleService.php`
- `routes/web.php`
- `tests/Feature/WorkWorkflowTest.php`
- `docs/DIRECTOR_WORKFLOW_DISCOVERY_REVIEW_2026-11-22.md`

### What Zazu already gets right

- The Event/Job record is already the operational relationship anchor. Do not introduce another event/order record.
- The quote builder consumes saved requirements rather than asking users to re-enter all service descriptions.
- Accepted quote detail has a direct invoice hand-off.
- The Job page exposes contextual links to requirements, preparation, purchasing, costs and finance.
- The lifecycle service enforces real completion blockers for open/blocked preparation, planned costs and active purchase orders.
- Quote versions preserve historical commercial snapshots and stale-requirement detection exists.

### Confirmed problems and friction

**A. Intake order was backwards relative to the strongest catering reference.**

Before this change, the new-job form order was:
1. Customer
2. Job name/type
3. Services being provided
4. When and where (dates, times, setup/collection dates and location)

Caterease's published booking sequence places event details before menu construction. Zazu asked the user to select service scope before completing the event's scheduling/location facts. That is not a data-integrity defect, but it is a weaker mental sequence for a new booking.

**Action taken:** the Job creation form now presents customer → job name/type → when/where → service scope. The backend payload and record creation behavior are unchanged.

**B. The form did not disclose what the service selection actually did.**

The inspected `WorkController::store()` immediately creates an `EventRequirement` for each selected service, using quantity `1` and unit `service`. The user could reasonably interpret the selection as a preliminary category choice, while Zazu had already persisted operational requirement rows.

The separate Requirements page then invites the user to add a service with a quantity, unit and special details. This is a different level of detail, but the distinction was not made explicit at initial selection.

**Action taken:** the creation form now explains that the selections create starting requirements and that the user should review quantity, unit and special details before preparing the quote. The next-step panel and stage labels were clarified accordingly. No data behavior was changed.

**C. Requirement correction is not visibly supported by the inspected online UI.**

The inspected `RequirementController` exposes index, create and store. The relevant `routes/web.php` section exposes GET index, GET create and POST store; no requirement update or delete route was found. The list view presents rows without edit/delete controls.

This creates a material correction risk: a user can create an initial service requirement (including through the new-job form) but cannot correct or remove it from the normal online Requirements page. The inspected online `WorkController::update()` also does not apply a submitted `services` list. The offline `OfflineDomainMutationHandler::syncOfflineJobRequirements()` does support reconciling a submitted service list, including removing descriptions no longer present while preserving matching rows' quantity, notes, status and capability links. That is a meaningful existing capability, but it does not provide a clearly discoverable online correction path.

Because quote snapshots and stale-quote logic exist, any correction workflow must be version-aware and must not silently rewrite accepted quotes. Online and offline correction semantics must be reconciled rather than implementing two different rules.

**Disposition:** high-priority workflow defect to resolve after tracing quote status, offline mutation support, audit expectations and lifecycle constraints. Do not bolt on an unguarded delete button.

**D. The visible Job progress model remains too coarse.**

The Job page's five stages are Job → Services → Quote → Prepare → Complete. It does not clearly show the commercial decision, invoice/payment state, procurement/receiving, actual cost close-out or conditional work. Its Next action selection is still narrow after quote creation, as documented in the earlier Workflow Discovery Review.

**Disposition:** replace neither the modules nor the lifecycle. Improve the Job home with state-derived orientation and clear hand-offs after the full state graph has been mapped and tested.

**E. The vocabulary is inconsistent.**

The creation experience mixes "New job", "Work / event name", "job type", "event type", "Job start date" and "Event start time". This is a source-visible terminology inconsistency. A user should not have to infer whether Job, Work and Event are different record types.

**Disposition:** establish a single visible primary term and a short glossary before doing a product-wide rename. Do not perform a blind global string replacement.

## 6. Director product decisions

1. **Preserve the event-centred data model and existing module destinations.** Do not merge modules or rebuild navigation from scratch.
2. **Treat the Job home as the orientation surface.** It should answer: where am I, what is done, what is waiting, what is blocked, what is optional, and what is the next useful action?
3. **Make recommendations state-aware and evidence-backed.** They must derive from persisted records and existing domain rules, not from a hard-coded universal checklist.
4. **Use event-type-aware intake progressively.** Gather essential event facts first; reveal optional details when relevant. Keep the form usable for simple jobs.
5. **Resolve requirement correction safely.** Users need a clear way to correct mistaken scope before quote creation and a controlled revision path after a quote exists. Preserve accepted quote snapshots.
6. **Make the commercial chain visible.** Quote decision → invoice → deposit/final payment must be understandable without forcing users to remember separate module destinations.
7. **Keep operational work conditional/parallel.** Purchasing, inventory, assets, travel, staffing, preparation and costs should appear when relevant; don't make every module mandatory.
8. **Expose event activity and due/waiting work.** Use a compact activity/attention summary, not another generic to-do list.
9. **Retain direct module navigation.** The Job home is an orientation layer, not a new gatekeeper.

## 7. Proposed target experience (wireframe-level, not a final visual design)

### New Job — first capture

```
NEW JOB
Who is this for?
  Customer / Add customer

What are we organising?
  Job name
  Event type

When and where?
  Start/end date
  Start/end time
  Setup / collection dates
  Location

What services are involved? (optional starter scope)
  Select service categories
  "These become starting requirements; review quantity and details next."

[Create job]  [Create job & continue to quote]
```

The new-job form has been adjusted to this sequence without changing the underlying record creation behavior.

### Job home — orientation, not another module menu

```
JOB: [Name]                     [Status] [Edit]

CUSTOMER · DATE/TIME · LOCATION

NEXT USEFUL ACTION
  [Action derived from current records]
  Why this is next
  [Open existing destination]

PROGRESS
  Scope → Quote & customer decision → Delivery readiness → Delivery → Close-out
  Each stage shows complete / waiting / blocked / optional / not applicable.

THIS JOB
  Commercial: quote versions · acceptance · invoice · payments
  Delivery: requirements · preparation · assets · purchasing · stock
  Control: suppliers · travel · costs · attachments

ATTENTION
  Overdue · waiting on customer · supplier receipt · blocked prerequisites

RECENT ACTIVITY
  What changed, when and where it happened
```

This is a conceptual arrangement. It is not permission to move existing routes or to make every group a mandatory stage. The next step is to derive the actual state model and test the recommendation logic.

## 8. Execution completed in this research cycle

- Reordered the new-job form so scheduling/location facts appear before service scope.
- Removed a repeated "Choose the job type" heading and added an explicit "Event type" field legend.
- Clarified that selected services are persisted as starter requirements.
- Updated the "What happens next" guidance to explain review of quantities, units and special details.
- Added a feature test for the new intake order and the persisted starter-requirement behavior.

Application changes are intentionally narrow. No navigation destinations, route names, database schema, existing records or commercial rules were changed.

## 9. Next implementation priorities

1. **Requirement correction workflow:** inspect the full online/offline requirement lifecycle and quote revision rules; add edit/replace/cancel support with tests only after defining safe behavior before and after quote acceptance.
2. **Job home state model:** derive state and next action from actual quote, invoice, payment, preparation, purchasing, receiving, cost and event lifecycle states.
3. **Human task walkthrough:** validate real comprehension on PC and phone using an existing populated fictional job. Measure whether the user can complete a quote, accept it, invoice, record payments, record purchasing/costs and understand completion without exploratory clicking.
4. **Terminology pass:** settle Job vs Event vs Work and apply consistently to the main journey.
5. **Only then** consider broader navigation adjustments.

## 10. Evidence limits

- Vendor pages are official product descriptions/help documents; they are not independent product audits.
- Caterease's onboarding PDF gives an explicit sequence. Other vendors' public documentation describes capabilities and dashboards; it does not fully expose every authenticated event-creation screen.
- Reddit posts are self-selected practitioner anecdotes and are not statistically representative.
- No competitor account was created and no authenticated product workspace was accessed.
- No full user observation/session recording of Zazu's current UI was available. The Zazu findings above are based on source code, route definitions, views and tests.
- The small UI correction is grounded in the source sequence and published Caterease booking order; it is not yet validated by a fresh owner walkthrough.

## 11. Sources

- Caterease Features: https://www.caterease.com/features/
- Caterease Onboarding Timeline (PDF): https://help.caterease.com/wp-content/uploads/2026/01/Caterease-Onboarding-Timeline.pdf
- Total Party Planner Features: https://totalpartyplanner.com/features/
- Planning Pod Event Overview: https://planningpod.com/help-center/overview-how-do-events-work
- Planning Pod Checklists: https://planningpod.com/checklists-and-task-management
- Planning Pod Platform: https://planningpod.com/platform
- HoneyBook Getting Started: https://www.honeybook.com/lp/getting-started
- HoneyBook Process Mapping: https://help.honeybook.com/en/articles/9124063-set-up-your-business-process-in-honeybook
- HoneyBook Client Relationships: https://help.honeybook.com/en/articles/9055403-manage-your-client-relationships-in-honeybook
- Reddit — event-production workflow preferences: https://www.reddit.com/r/EventProduction/comments/1ijfh8p
- Reddit — event planning software clunkiness and tool sprawl: https://www.reddit.com/r/EventProduction/comments/1dfd3b4
- Reddit — Caterease/enterprise software usability and training: https://www.reddit.com/r/restaurant/comments/1m5jh5m
- Reddit — event software complexity and adoption: https://www.reddit.com/r/EventProduction/comments/1jlxdyg
