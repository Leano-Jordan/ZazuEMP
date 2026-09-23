# ZAZU EMP — NEW CHAT CONTEXT

Read this file after memory.md at the start of every Zazu EMP chat.

## OWNER
Rosscore Labs Pty Ltd owns Zazu EMP.
Isaac Junior Lehlogonolo Maluleka is the founder, creator, lead developer and final decision-maker.

AI is the engineering/research partner. Do not make consequential product or architecture decisions silently.

## PRODUCT
Zazu EMP = reusable Event Management Platform.

It is NOT:
- a bespoke Sindi-only system
- SwiftOrder
- catering-only software

Target businesses include catering, equipment hire, tents/chairs, sound/DJ, baking, decor, rentals and combinations of event services.

Core:
Business -> Customer -> Event/Job -> Services -> Quote -> Confirmation/Deposit -> Buying/Hiring -> Preparation -> Event -> Payment/Completion

## CURRENT DEVELOPMENT STATE
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
The owner created a local branch named:
laravel-foundation

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
Event controller scaffold: DONE
Event model: NEXT
Event routes: NEXT
Event CRUD UI: NEXT
Event tests: NEXT
Application shell: NOT STARTED
Authentication/business isolation: NOT STARTED
Quotation/travel costing: NOT STARTED

## IMMEDIATE COMMAND
Continue from the Event model. Do not restart setup.
