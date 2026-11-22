# Director Workflow Discovery Review — 2026-11-22

**Repository:** `Leano-Jordan/ZazuEMP`  
**Branch:** `main`  
**Review type:** Source-backed workflow discoverability review  
**Disposition:** Confirmed UX concern; implementation deliberately deferred pending human-centred validation.

## Founder clarification

The concern is not that Customers, Jobs, Quotes, Finance, Suppliers, Purchasing or Inventory are necessarily broken or incorrectly integrated. The Founder describes a discovery problem: a user moves between pages, clicks around, and cannot readily see the intended sequence or know whether they are doing things in the right order.

The workspace data count is corrected to **4 customers and 2 jobs**. The earlier dashboard-derived count of 2 customers is superseded. The Founder confirms the records are fictional and reports normal connected use on both PC and linked mobile.

Do not reinterpret this as a request to merge modules, move navigation destinations, create a second source of truth, or redesign the entire interface.

## Source inspection

Reviewed current `main` source for:
- `resources/views/work/show.blade.php`
- `resources/views/components/zazu-helper.blade.php`
- `resources/js/app.js` — Helper and navigation initialization
- `docs/ZAZU_UI_DISCLOSURE_STANDARD.md`
- `docs/DIRECTOR_NAVIGATION_AND_RELIABILITY_AUDIT_2026-10-01.md`
- `docs/DIRECTOR_WORKFLOW_UX_OFFLINE_AUDIT_2026-10-04.md`
- `docs/ZAZU_HELPER_SEMANTIC_ANCHORS.md`
- `docs/ZAZU_2027_COMPETITIVE_BENCHMARK.md`

### What already exists

1. The Job detail page contains a **Next action** panel.
2. The Job page has a five-part progress strip: Job → Services → Quote → Prepare → Complete.
3. The Job page has an **Operational chain** with contextual links to Services, Preparation, Purchasing, Costs and Finance.
4. Job detail also exposes customer information, location, job tools and attachments.
5. The Zazu Helper contains route-specific guidance for Dashboard, Customers, Jobs, Job detail, Quotes, Finance, Purchasing and Inventory.
6. Existing design standards explicitly call for context-first presentation and a job-centred relationship chain. The navigation audit also says findability is a reliability concern.

### Verified discoverability weaknesses

**1. The Job progress strip is not a complete explanation of the business journey.**  
It displays five broad stages, while other consequential work—customer decision, invoicing/payment, purchasing/receiving and costs—is presented separately as links. A user can see that these destinations exist without understanding when they matter or how they relate to the current job.

**2. The Job's Next action logic is narrow.**  
In the inspected view, it selects:
- Add services when no quote exists;
- Review quote when the latest quote no longer matches current requirements;
- Otherwise Open quote whenever a quote exists.

This does not yet express the next useful action across the full lifecycle after quote creation. It does not, in this view, distinguish quote awaiting customer decision, accepted work awaiting invoice, outstanding payment, procurement/receipt, preparation readiness, cost close-out or completion.

This is a source-level finding about the visible decision logic, not a claim that the underlying modules fail to support those operations.

**3. The Helper is guidance, not a visible workflow map.**  
The Helper is rendered as a floating control with a hidden panel. The user must open it to see its route-specific stepper. Most guide steps are explanatory copy rather than direct links to the next task. It can help explain a page, but it does not currently provide a continuously visible, state-aware path through the complete job lifecycle.

**4. Existing links provide access, but access alone does not communicate sequence.**  
The Operational chain is a set of useful destinations. It does not tell the user which action is relevant now, which steps are conditional, which can happen in parallel, or what constitutes completion.

## Human-centred interpretation

The user needs orientation and confidence, not more destinations.

The experience should answer, in plain language:
- Where am I in this job?
- What has already been done?
- What should I do next, and why?
- What can wait, and what depends on something else?
- Where do I go to complete that action?
- How do I know that part is finished?

Do not force every job into one rigid linear sequence. Some work is conditional or parallel: purchasing may be unnecessary for a job; preparation may begin while a customer decision or supplier process is pending; invoices and payments have their own states. The interface must reflect actual business state and dependencies rather than impose a generic checklist.

## Director decision

**Do not move, rename, merge or reorder existing modules or primary navigation based on this source inspection alone.**

The next safe product increment is a state-aware, job-contextual workflow orientation layer built over existing records and routes:
- retain the current module boundaries and route authority;
- make the Job the place to understand the job's overall progress;
- expose one evidence-backed recommended next action when one is genuinely available;
- explain why it is next and what record/state makes it relevant;
- show completed, waiting, blocked and not-applicable states distinctly;
- link directly to the existing destination while preserving job context;
- support conditional and parallel work rather than pretending every task is mandatory;
- keep existing module pages usable directly from primary navigation.

Before implementation, trace the actual state transitions and permission rules for quote decision, invoice/payment, purchasing/receipt, preparation, costs and completion. Verify the current controllers/services and tests; do not infer business rules from labels alone.

## Acceptance criteria for a later implementation

1. A non-technical user can identify the current job stage and the next useful action without opening several unrelated modules.
2. Every recommendation is grounded in persisted state and permission-aware.
3. No recommendation appears if its prerequisites are missing, ambiguous or inaccessible; explain the prerequisite instead.
4. Conditional work is marked optional/not applicable rather than left looking incomplete.
5. Actions navigate to existing routes and preserve the current job context.
6. No duplicate records, parallel workflow authority, route relocation or destructive data migration is introduced.
7. The same meaning is retained on desktop and mobile; mobile presentation is adapted, not merely compressed.
8. Tests cover state transitions, permissions, missing prerequisites, optional work, stale/changed quote requirements and completed/cancelled jobs.
9. A human walkthrough validates comprehension and confidence; automated route tests alone do not prove the journey is understandable.

## Evidence boundary

This is a source and documentation review. No runtime usability observation, screen recording, task-completion study or full desktop/mobile visual inspection was available in this review. Therefore no claim is made that a particular layout change is already validated.

The Founder-confirmed workspace population is **4 customers and 2 jobs**. The Founder reports normal connected PC and linked-mobile operation. This correction supersedes the earlier dashboard count of 2 customers. The fictional records are accepted as test data.

No V1 readiness score change is justified by this UX finding alone.


## Follow-up lifecycle trace

A second source pass checked how far the existing hand-offs already go:

- The accepted-quote detail page explicitly offers **Create invoice** when no invoice exists, or links the existing invoice when one does. The hand-off exists, but it appears after the user has navigated from the Job page into the Quote page.
- The event lifecycle service prevents completion while linked preparation remains open/blocked, a linked cost remains planned, or a linked purchase order remains draft/sent/ordered. These are real prerequisites, not merely suggested labels.
- The quote and job surfaces therefore contain useful pieces of the journey, but the guidance is distributed across pages. The Job page's own next-action panel does not currently surface the accepted-quote-to-invoice hand-off, nor does its five-step progress strip explain the lifecycle blockers.
- This confirms that the correct next design task is to consolidate *orientation* at the Job context, not to move the underlying modules.

Important nuance: do not imply every job must have purchasing, costs or an invoice in every circumstance. The lifecycle service's completion guards establish specific blockers; they do not prove a universal mandatory ordering for all other work. The workflow layer must reflect actual persisted conditions and applicable rules.

The existing `WorkCompletionGuardTest` covers open/blocked preparation, planned costs and active purchase orders as completion blockers. Preserve and extend these domain tests if the orientation logic is later implemented; do not duplicate lifecycle rules in Blade or JavaScript.
