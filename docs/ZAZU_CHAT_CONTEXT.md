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

Verified:
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

Do not derail development to fix those warnings unless they cause a real Zazu failure.

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

## SECURITY / PRIVACY / IP

Use:
User -> Business membership -> Role -> Permission -> Allowed action

Business isolation is foundational.
Server-side authorization is mandatory before production use.

Never commit secrets, production exports or customer personal information.

Relevant project controls:
- SECURITY.md
- IP_OWNERSHIP.md
- THIRD_PARTY_NOTICES.md
- docs/COMPLIANCE_BASELINE.md
- docs/ZAZU_FILE_MAP.md

The compliance baseline is an engineering control document, not a declaration of legal compliance.

## CURRENT WORKING FILE MAP

Start with docs/ZAZU_FILE_MAP.md for the plain-English explanation of the Laravel files.

Core workflow:
- routes/web.php = URL -> controller map
- app/Http/Controllers/WorkController.php = Work workflow
- app/Http/Controllers/CustomerController.php = Customer workflow
- app/Models/Event.php = Event database model
- app/Models/Customer.php = Customer database model
- app/Models/CustomerContact.php = contact database model
- resources/views/components/app-layout.blade.php = shared browser shell
- resources/views/work/*.blade.php = Work browser screens
- resources/views/customers/*.blade.php = Customer browser screens
- database/migrations/*.php = versioned schema changes
- config/database.php + local .env = database connection configuration

## DATABASE REALITY

The migration files are not the live database.

Migration files describe schema changes.
The migration repository table records which migrations ran.
The actual local records live in the database selected by the local .env.

For this foundation, the important flow is:

Browser -> route -> controller -> model -> database -> controller redirect -> Blade view

Customer creation now saves the customer and primary contact inside one database transaction.

Work creation writes the Event record after validating the selected customer/contact relationship.

## DISCOVERY SIGNALS

Sindi is a real discovery/pilot operator, not the product definition.

Validated pain signals:
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

## BATCH 01 — DATA FOUNDATION

DONE.

## BATCH 02 — CUSTOMER + WORK WORKFLOW

DONE.

Implemented:
- Customer CRUD
- primary contact creation
- create Work/Event from selected customer
- event-day contact selection
- validation
- contact ownership validation
- Work index/show/edit
- workspace shell
- workflow tests

## BATCH 03 — APPLICATION SHELL + STABILITY

IMPLEMENTED on laravel-foundation:
- responsive mobile navigation in the shared application shell
- clearer Work overview metrics
- defensive rendering for optional/legacy Event values
- dynamic Work status badge
- visible validation error list
- atomic Customer + primary contact creation
- safe event-day contact reset when changing Customer during edit
- owner file map
- compliance/privacy baseline
- core customer/work workflow tests

LOCAL VERIFICATION IS STILL REQUIRED.

## NEXT

Batch 04:
Requirements versus business capabilities.

Do not jump into quotes, Maps, WhatsApp, suppliers, finance, dashboards, roles or dozens of tables without the workflow dependency being established.

Authentication/business isolation remains a production-critical stage.

## DELIVERY LOOP

DISCOVER -> DEFINE -> SCOPE -> INSPECT -> DESIGN -> IMPLEMENT -> TEST -> VERIFY -> RECORD -> NEXT

After a meaningful batch:
- verify behavior
- update memory/context
- commit with a meaningful message

Repository state wins over old plans.

## VERIFICATION

After refreshing the local branch:

git status
git log -5 --oneline
php artisan migrate:status
php artisan test
npm.cmd run build

Then refresh:
http://localhost:8000

Walk through:
1. Work
2. Customers
3. New customer
4. New work
5. Work workspace
6. Edit work
7. Change customer and confirm the contact resets
8. Try an invalid submission and confirm validation errors are visible

Do not declare the batch verified until the local browser and tests confirm it.
