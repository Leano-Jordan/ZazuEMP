# Zazu EMP — Living Project Context

**Product:** Zazu – Event Management Platform (Zazu EMP)
**Owner:** Rosscore Labs Pty Ltd
**Founder / creator / lead developer:** Isaac Junior Lehlogonolo Maluleka
**Repository:** Leano-Jordan/ZazuEMP
**Default branch:** main
**Context updated:** 2026-09-23 16:08 SAST

> NEW-CHAT BOOTSTRAP: Read this file first. It is the canonical project memory. Then read docs/ZAZU_CHAT_CONTEXT.md. Inspect the current repository state before acting. Repository state outranks stale conversation memory.

## 1. Product identity

Zazu EMP is a reusable commercial event-management platform owned by Rosscore Labs Pty Ltd.

It is NOT a bespoke Sindi system and NOT SwiftOrder.

Target businesses include:
- catering
- tents/chairs and event equipment hire
- sound/DJ
- baking/cookies
- decor
- rentals
- combinations of event services
- other small event-related businesses

Core model:

Business -> Customer -> Event/Job -> Services -> Quote -> Confirmation/Deposit -> Buying/Hiring -> Preparation -> Event -> Payments/Completion

The Event/Job is the central operational record.

## 2. Owner authority

Isaac is the product owner and final decision-maker.

For consequential product, UX, architecture, infrastructure, spending, licensing, branding or scope decisions:
- inspect evidence
- present viable options and trade-offs
- identify risks/costs/dependencies
- let the owner decide unless already explicitly decided

Do not silently convert an AI preference into a project decision.

## 3. Operating engines

Keep these three guidance systems aligned:
- Project Genesis & Scale Engine
- Human-First Discovery Mode
- RossCore Engineering Command Engine

Operating principles:
- evidence before assumptions
- small useful solutions
- no unnecessary abstractions
- inspect current implementation before changing it
- implement, test and verify
- record meaningful project state
- keep discovery questions short and high-value
- UI/UX is an engineering concern, not a final coat of paint

## 4. Commercial constraints

Current budget: R0.

Prefer free/open-source technologies with commercial-use-friendly licensing. Paid services are possible later but require an explicit cost/value decision.

Zazu must support multiple independent businesses. Business data isolation is foundational.

## 5. Discovery evidence: Sindi

Sindi Sithole is a real discovery/pilot operator. Her workflow is evidence about her business, not a universal market specification.

Validated signals:
- quotation work is a major pain point
- she currently uses Excel for quotations/invoices
- she manually researches prices from shops, online or physically
- menu is agreed before quotation
- ingredients, time and effort are considered
- client confirmation can fail after quote effort
- distance is checked with Google Maps
- she described kilometre-based travel costing and mentioned R14/km as an example from her current practice
- round-trip travel is charged
- calculating cost per kilometre is the slowest quotation task for her
- after deposit, controlling spending against agreed scope matters
- she uses owned resources and hires what she lacks for a job
- separate events use different invoices/references
- event folders may be named using the address
- reminders/keeping track are a problem
- she would like a dedicated system
- map, WhatsApp and Excel connections sounded useful to her

Do not treat R14/km as a universal Zazu rule.

## 6. Product hypothesis

The product should reduce manual coordination around an event without forcing a small operator into enterprise software.

Useful event context may include:
- customer
- date/time
- venue/address
- services
- quote
- travel
- deposit/payment status
- expenses
- hired/bought items
- documents
- reminders
- activity/history
- contact actions

The goal is faster, clearer, less error-prone real work, not feature volume.

## 7. UX direction

Core principle: WORKSPACE OVER SCREEN.

Prefer a small number of rich contextual workspaces using tabs, panels, drawers, contextual modals, split views and inline editing where useful.

Desktop/laptop is important because operators already use Excel, folders and calculators. Phone use also matters. Zazu is desktop + mobile, not mobile-only.

The Event Workspace is expected to be the most important operator surface and should eventually bring relevant customer, quote, travel, spending, payment, preparation, documents and history context together.

Design quality bar:
professional, fast, clear, calm, information-dense without clutter, accessible, responsive, maintainable and commercially credible.

Avoid card soup, button soup, unnecessary animation, decorative UI and excessive whitespace.

## 8. Security / business isolation

Conceptual access model:

User -> Business membership -> Role -> Permission -> Allowed action

Do not rely on a global role alone.

Every business-owned record needs a clear business boundary.

Likely roles are business owner, manager, staff/operator plus Rosscore platform control, but exact permissions remain an implementation decision.

Server-side authorization is mandatory.

## 9. Integrations

Maps:
- address -> route/distance -> configurable km rate -> travel charge
- provider choice remains open
- use a replaceable integration boundary

WhatsApp:
- first useful capability can be click-to-chat and pre-filled messages
- deep API automation is a later decision

Excel:
- import/export is useful for migration and existing workflows
- live two-way sync is not yet validated

## 10. Technical foundation

Verified on 2026-09-23:
- Windows + VS Code
- PHP 8.4.26 CLI
- Laravel 13.33.0
- Composer dependencies installed
- Node/npm installed
- npm.cmd is used because PowerShell blocks the npm.ps1 shim
- .env exists
- application key generated
- migrations execute
- npm.cmd install completed
- npm.cmd run build succeeds

A clean Laravel skeleton was temporarily created at:
C:\Projects\ZazuEMP-LaravelTemp

The Laravel foundation was then merged into the real project:
C:\Projects\ZazuEMP

The temporary folder is not the product and should not become the working project.

Known non-blocking warnings:
- PHP reports missing pdo_firebird. Leave it alone unless it causes a real Zazu failure.
- Vite reports optional fontaine optimization. Leave it alone unless the owner chooses to address it.

Do not restart PHP setup because of either warning.

Architecture direction:
- Laravel modular monolith
- normal Laravel conventions
- reusable business logic
- replaceable integration boundaries
- multi-business boundaries from the start
- infrastructure appropriate to R0
- avoid abstractions for their own sake

## 11. Current Event/Job implementation

The first database object is Event/Job because other operational information hangs from it.

Migration:
database/migrations/2026_09_23_000000_create_events_table.php

Fields:
- id
- reference, unique
- name
- event_type, nullable
- customer_name
- customer_phone, nullable
- customer_email, nullable
- event_date, nullable
- event_address, nullable
- notes, nullable
- status, default draft
- timestamps

Verified:
php artisan migrate created events successfully.

Controller scaffold:
app/Http/Controllers/EventController.php

Verified:
php artisan make:controller EventController succeeded.

The Event model, routes and UI still need to be implemented unless current repository inspection shows otherwise.

## 12. First vertical slice

Do not jump directly into quotations, suppliers, WhatsApp, Maps, finance, dashboards or dozens of tables.

Current target:

CREATE EVENT
-> SAVE EVENT
-> SEE EVENT
-> EDIT EVENT
-> SEE EVENT IN ZAZU UI

Expected next implementation files:
1. app/Models/Event.php
2. app/Http/Controllers/EventController.php
3. routes/web.php
4. resources/views/events/index.blade.php
5. resources/views/events/create.blade.php
6. resources/views/events/edit.blade.php
7. resources/views/events/show.blade.php

Use normal Laravel conventions. Keep the first slice small and verifiable.

## 13. Current verification scoreboard

DONE:
- PHP/Laravel foundation
- .env and app key
- database migration infrastructure
- events table migration
- controller scaffold
- frontend dependency installation
- frontend production build

IN PROGRESS:
- Event/Job vertical slice

NOT YET VERIFIED:
- Event model behavior
- validation
- controller behavior
- routes
- create/edit/show/index UI
- automated tests for Event slice
- polished Zazu application shell
- authentication/registration
- business membership/isolation implementation

## 14. New-chat operating rules

1. Read memory.md and docs/ZAZU_CHAT_CONTEXT.md before acting.
2. Inspect current Git branch, HEAD and changed files.
3. Treat GitHub/current repository as source of truth.
4. Do not repeat completed setup work.
5. Do not ask the owner to re-investigate problems already verified.
6. Give exact file paths and exact code when implementation is requested.
7. Keep responses focused and actionable.
8. For major decisions, show options and let Isaac decide.
9. Keep discovery evidence separate from hypotheses.
10. Update project memory after meaningful implementation state changes.
11. Preserve working behavior and avoid unnecessary rewrites.
12. Every meaningful implementation batch should have a clear verification step and meaningful commit.
13. The temporary Laravel folder is not the working project.
14. Never treat Sindi's workflow as the universal market specification.
15. Do not invent business rules, pricing, legal claims, branding claims or integrations without evidence/owner approval.

## 15. Immediate next action

Complete and verify the Event/Job vertical slice.

Start with:
app/Models/Event.php

Then wire:
controller -> routes -> validation -> Blade UI -> create/save -> show -> edit/update -> tests.

After the slice works, move to the next highest-value dependency based on evidence rather than building the entire platform at once.
