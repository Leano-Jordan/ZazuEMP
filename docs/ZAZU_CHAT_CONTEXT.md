# ZAZU EMP — NEW CHAT CONTEXT

Read this file after memory.md at the start of every Zazu EMP chat.

## ENGINE / OPERATOR MODE

Master ENGINE identity: **Morpheus**. Owner-facing nickname: **Jarvis**.

This project is **Zazu EMP only**. Never mix it with SwiftOrder or store-ordering.

When the owner says **execute**, act on the repository. Do not answer with a plan instead of doing the work, and do not spend a reply discussing token usage or explaining that a report is unnecessary.

While executing:
- Inspect the current repository, not stale chat assumptions.
- Check the requested area plus nearby high-impact defects that affect correctness, accessibility, reliability, maintainability or workflow integrity.
- Fix clearly justified defects within the active scope.
- Do not invent placeholder routes or future modules solely to fill navigation gaps.
- Verify available evidence and clearly leave runtime-only checks as unverified.
- Default to a compact result. Give a detailed report only when explicitly requested.

## OWNER
Isaac Junior Lehlogonolo Maluleka is the current owner, founder, creator, solo developer and final decision-maker for Zazu EMP.
Rosscore Labs is a planned future business identity and is not yet a registered company or the present legal owner.

AI is the engineering/research partner. Do not make consequential product or architecture decisions silently.

## PRODUCT
Zazu EMP = reusable Event Management Platform.

It is NOT:
- a bespoke Sindi-only system
- SwiftOrder
- catering-only software

Target businesses include catering, equipment hire, tents/chairs, sound/DJ, baking, decor, rentals, photography, camera hire and combinations of event services.

Core:
Business -> Customer -> Event/Job -> Services -> Quote -> Confirmation/Deposit -> Buying/Hiring -> Preparation -> Event -> Payment/Completion

## CURRENT DEVELOPMENT STATE

Recovery note 2026-09-26: repository source uses Event soft deletes, but an owner development database reported a missing `events.deleted_at` column. A repair migration has been added to reconcile schema drift. Work creation also had a source-level pre-save `$event` reference, and Work Edit now includes the previously missing night-contact field.
Windows + VS Code.

Verified 2026-09-23:
- PHP 8.4.26
- Laravel 13.33.0
- Composer dependencies installed
- .env exists and APP_KEY generated
- database migrations work
- npm.cmd install completed
- npm.cmd run build succeeds

Non-blocking warnings:
- missing pdo_firebird PHP extension
- optional Vite fontaine optimization

Do not derail development to fix those warnings.

Temporary Laravel skeleton:
C:\Projects\ZazuEMP-LaravelTemp

Real working project:
C:\Projects\ZazuEMP

Git history must remain protected.

## CURRENT GIT STATE

Current repository focus: Customer + Work workflow.
`main` is the GitHub source-of-truth branch. Historical branch notes may be stale.

Before any new implementation:
- inspect git status
- inspect current branch/HEAD
- inspect changed files
- never assume old chat state is current

## DISCOVERY SIGNALS
Sindi is a real discovery/pilot operator, not the product definition.

Her validated pain signals:
- quotation is time-consuming
- Excel is used for quotes/invoices
- prices are researched manually
- travel distance is checked with Google Maps
- per-kilometre calculation is her slowest quote task
- R14/km was an example from her practice, not a universal Zazu rate
- round-trip travel is charged
- time/distance can cause costing losses
- scope control after deposit matters
- owned and hired resources can be mixed per job
- reminders/keeping track are difficult
- event folders/references/invoices are used to separate jobs
- map, WhatsApp and Excel connections are potentially useful

Use evidence labels:
VERIFIED = directly established
INFERRED = plausible but needs validation
UNKNOWN = not established

## PRODUCT / UX DIRECTION
Build the smallest useful solution.

Core UX principle:
WORKSPACE OVER SCREEN.

Zazu should provide rich contextual workspaces rather than turning every table into a separate CRUD screen.

Desktop/laptop is important.
Phone use is also important.
Target is desktop + mobile responsive web, with PWA capabilities later if justified.

UI quality:
professional, clear, calm, fast, dense without clutter, accessible, responsive, maintainable and commercially credible.

Avoid:
card soup, button soup, decorative UI, excessive animation, random spacing, excessive whitespace.

## SECURITY DIRECTION
Use:
User -> Business membership -> Role -> Permission -> Allowed action

Business isolation is foundational.

Server-side authorization is mandatory.

Exact role catalogue remains to be designed and validated.

## FIRST BUILD SLICE
Do not build the whole platform at once.

Current target:

CREATE EVENT
-> SAVE EVENT
-> SEE EVENT
-> EDIT EVENT
-> SEE EVENT IN ZAZU UI

The Event/Job record is the first central database object.

Migration:
database/migrations/2026_09_23_000000_create_events_table.php

Current events fields:
id
reference unique
name
event_type nullable
customer_name
customer_phone nullable
customer_email nullable
event_date nullable
event_address nullable
notes nullable
status default draft
timestamps

Migration has been executed successfully.

Controller scaffold exists:
app/Http/Controllers/EventController.php

## NEXT FILES
Start with:
app/Models/Event.php

Then:
app/Http/Controllers/EventController.php
routes/web.php
resources/views/events/index.blade.php
resources/views/events/create.blade.php
resources/views/events/edit.blade.php
resources/views/events/show.blade.php

Add validation and tests as part of the slice.

Do not add quotations, Maps, WhatsApp, suppliers, finance, dashboards, roles or dozens of tables until this slice is working and verified.

## IMPLEMENTATION STYLE
Use standard Laravel conventions.
Do not manufacture framework internals.
Do not create abstractions without demonstrated repetition.
Do not replace working code just because another structure looks cleaner.
Give the owner exact paths and exact code when asking them to implement.
Explain only what is necessary to keep the owner oriented.

## DELIVERY LOOP
DISCOVER -> DEFINE -> SCOPE -> INSPECT -> DESIGN -> IMPLEMENT -> TEST -> VERIFY -> RECORD -> NEXT

After a meaningful batch:
- verify behavior
- update memory/context
- commit with a meaningful message

Repository state wins over old plans.

## CURRENT SCOREBOARD
Foundation: DONE
Laravel: DONE
Events migration: DONE
Customer workflow: IMPLEMENTED
Work index/show/edit/create workflow: IMPLEMENTED
Work creation hardening + feature tests: IMPLEMENTED
Application shell: PARTIAL
Authentication/business isolation: NOT STARTED
Quotation/travel costing: NOT STARTED

## CURRENT WORKFLOW STATUS
Customer creation has been exercised successfully by the owner.
Work dashboard is present and the Work index is functional.
The Work create source had a historical compiled Blade parse error: `Unclosed '[' on line 94 does not match ')'`. The source view has since been corrected and generated `storage/framework/views` artifacts were removed from Git. A local `php artisan view:clear` is required to discard any stale compiled view on the owner's machine.

The current Work creation implementation includes customer selection, event-day contact validation, event creation, redirect to the created work record, and a transactional write.

## IMMEDIATE NEXT
Verify the Work creation path locally after clearing compiled views, then continue with the next smallest operational workspace slice.


## Critical regression lesson — 2026-09-26

A generated write corrupted PHP namespace/import backslashes in `DashboardController.php`, producing `AppHttpControllers` / `AppModels...` and causing the controller-backed dashboard to fail even though the file existed.

Root cause:
- write-integrity failure during automated repository editing.

Required execution guard:
1. After every automated PHP write, immediately re-fetch the exact repository file.
2. Verify namespace, imports, class declaration and obvious syntax-sensitive constructs.
3. Verify the route -> controller -> view chain for touched pages.
4. Run or leave an explicit runtime/test verification state before calling the batch complete.
5. Treat regression prevention as higher priority than the next feature.

A new route-integrity test now checks that controller-backed route actions resolve to loadable PHP classes.
