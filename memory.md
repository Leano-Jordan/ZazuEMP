# Zazu EMP — Living Project Context

**Product:** Zazu – Event Management Platform (Zazu EMP)
**Owner:** Rosscore Labs Pty Ltd
**Founder / creator / lead developer:** Isaac Junior Lehlogonolo Maluleka
**Repository:** Leano-Jordan/ZazuEMP
**Default branch:** main
**Active development branch:** main
**Context updated:** 2026-09-26

Repository state outranks stale conversation memory. Inspect the current repository before acting.

## Product direction

Zazu EMP is a reusable commercial event-management platform.

It is not catering-only software and not funeral-specific software. Target businesses include catering, event equipment hire, sound/DJ, baking, decor, rentals, combinations of event services, and funeral parlours whose work can use the same operational core.

Core model:
Business -> Customer -> Event/Job/Case -> Requirements/Capabilities -> Quote -> Confirmation/Deposit -> Buying/Hiring -> Preparation -> Execution -> Payments/Completion

The operational workspace is the common product engine. Industry-specific capabilities should plug into it later.

## Owner authority

Isaac is the product owner and final decision-maker.

Use evidence before assumptions. Do not silently turn an AI preference into a product or architecture decision.

## Engineering rules

Operating engines:
- Project Genesis & Scale Engine
- Human-First Discovery Mode
- RossCore Engineering Command Engine

Delivery loop:
DISCOVER -> DEFINE -> SCOPE -> INSPECT -> IMPLEMENT -> TEST -> VERIFY -> RECORD -> COMMIT -> NEXT

Repository/GitHub is the primary source of truth.

## UX direction

WORKSPACE OVER SCREEN.

Main navigation target:
Dashboard / Work / Customers / Quotes / Calendar / Suppliers / Inventory / Assets / Reports / Settings

The Work Workspace is the operational centre.

Quality target:
professional, clear, calm, fast, dense without clutter, accessible, responsive and maintainable.

## Security direction

Conceptual access model:
User -> Business membership -> Role -> Permission -> Allowed action

Server-side authorization and business isolation are mandatory before production use.

## Technical foundation

Verified:
- PHP 8.4.26
- Laravel 13.33.0
- Composer dependencies installed
- npm.cmd install completed
- npm.cmd run build succeeds
- migrations execute
- .env and application key exist

Known non-blocking warnings:
- missing pdo_firebird PHP extension
- optional Vite fontaine optimization

Do not derail development for those warnings unless they cause a real Zazu failure.

## Batch 01 — Data foundation

IMPLEMENTED on laravel-foundation:

New migrations:
- businesses
- business_user membership
- customers
- customer_contacts
- business/customer/event-day-contact links on events

New/updated models:
- Business
- Customer
- CustomerContact
- Event relationships
- User businesses relationship

Test added:
- tests/Feature/FoundationRelationshipsTest.php

The original events migration remains unchanged. Legacy customer snapshot fields remain temporarily so migration/backfill can be handled safely later.

Current foreign keys for business/customer/event context are nullable because authentication and active-business authorization are not implemented yet. Production enforcement belongs to the authorization hardening stage.

## Batch 02 — Customer + Work workflow

IMPLEMENTED:
- Customer creation workflow
- primary customer contact creation
- Work index/show/edit/create workflow
- customer selection when creating Work
- event-day contact selection and ownership validation
- Work creation transaction
- Work creation feature coverage
- Work action renamed from `New work` to `Create work`

REMAINS TO VERIFY LOCALLY:
- fresh Work creation after clearing compiled Blade views
- full feature test suite
- fresh migration run against the current local database

## Batch 04 — Business capabilities foundation

IMPLEMENTED:
- business_capabilities migration
- BusinessCapability model
- Business -> capabilities relationship
- BusinessCapabilityController
- capability index/create/edit screens
- capability routes and navigation
- Work workspace link into the capability catalogue
- BusinessCapability feature coverage for create/update

Foundation fields:
- business_id nullable
- name
- category
- capability_type
- pricing_basis
- default_unit
- description
- is_active

The capability catalogue is intentionally foundational. Work-specific quantities/pricing and quote history are not coupled to it yet. Tightening/scoping belongs to later architecture hardening.

## Work creation hardening

IMPLEMENTED:
- moved nested customer/contact data preparation out of the Create Work Blade template
- Create Work now uses prepared JSON data, removing the fragile nested Blade/PHP expression that caused the recurring compiled-view parse failure
- contact selector clearly handles customers with no contacts
- validation errors are shown beside the relevant Create Work fields
- ownership test payload now includes the required event date

## Later batches

Batch 03: application shell and responsive workspace UI.

Batch 04: requirements versus business capabilities.

Batch 05: versioned quote engine with historical calculation inputs.

Batch 06: travel costing with replaceable route provider, two routes, distance/time, configurable fuel price, vehicle consumption, round trip, customer charge and historical evidence.

Batch 07: projected versus actual costs, staff costing, receipt proof plus AI extraction with human verification, reusable asset accountability and damage/loss evidence.

Batch 08: payments, expenses, documents, activity/audit trail and reminders.

Batch 09: authentication, active business context, roles/permissions, server-side business isolation and authorization tests.

The order can change when verified dependencies or evidence justify it.

## Verification scoreboard

DONE:
- Laravel foundation
- environment/application key
- database infrastructure
- original events migration
- controller scaffold
- frontend dependency/build
- Batch 01 schema foundation
- Batch 01 Eloquent relationships
- Batch 01 relationship test added

NOT YET VERIFIED locally after Batch 01:
- fresh migration run
- feature test execution
- compatibility with any existing local event data
- Customer/Event CRUD: owner has manually verified customer creation and current Work UI
- Work creation: source-level fix implemented; local runtime verification pending
- workspace UI
- authentication/business authorization

## Navigation + workflow connectivity pass — 2026-09-26

IMPLEMENTED:
- Customer directory rows now open a real customer relationship workspace.
- Customer workspace exposes contacts, work history and Start Work.
- Work workspace links directly to Customer and Requirements.
- Requirements register is now a real workflow page under a Work record.
- Requirement creation supports optional reusable BusinessCapability selection plus work-specific quantity/unit/category/notes.
- Requirement routes/controller/model/migration added.
- Requirements page links back to Work and into Add Requirement.
- Work progression now makes Requirements the first connected downstream stage.
- Feature coverage updated for the Requirements redirect and Customer relationship workspace.

Navigation rule:
- Every implemented page must have a valid path forward and a valid path back to its parent workflow.
- Do not render a clickable UI element for a workflow stage until its destination route exists, unless it is explicitly styled as a non-interactive future stage.
- Contextual workflow pages should link back to their parent workspace rather than becoming isolated global navigation items.

## Immediate next action

On the owner's Windows ZazuEMP checkout:

php artisan migrate
php artisan view:clear
php artisan test

Then manually verify:
1. Customers -> open a customer -> Start Work -> Work.
2. Work -> Customer -> back to Work.
3. Work -> Requirements -> Add Requirement -> save -> Requirements -> Work.
4. Work -> Capability catalogue -> capability edit -> back to catalogue.
5. Work edit -> Workspace.
6. Theme toggle remains functional across all connected pages.

If those pass, proceed to the next roster slice after Requirements, which is the versioned Quote foundation, while keeping all existing navigation connected.
