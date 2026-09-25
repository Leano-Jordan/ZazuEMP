# Zazu EMP — Living Project Context

**Product:** Zazu – Event Management Platform (Zazu EMP)
**Owner:** Rosscore Labs Pty Ltd
**Founder / creator / lead developer:** Isaac Junior Lehlogonolo Maluleka
**Repository:** Leano-Jordan/ZazuEMP
**Default branch:** main
**Active development branch:** laravel-foundation
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

## Security / privacy direction

Conceptual access model:
User -> Business membership -> Role -> Permission -> Allowed action

Server-side authorization and business isolation are mandatory before production use.

Never commit secrets, production data or customer records.

Compliance baseline is recorded in:
docs/COMPLIANCE_BASELINE.md

Ownership/provenance is recorded in:
IP_OWNERSHIP.md

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
- Customer CRUD
- primary contact creation
- create Work/Event from selected customer
- event-day contact selection
- validation and contact ownership validation
- Work index/show/edit
- workspace shell
- core workflow test coverage

## Batch 03 — Application shell + stability pass

IMPLEMENTED on laravel-foundation:
- responsive mobile navigation in the shared application shell
- clearer Work overview metrics
- defensive rendering for optional/legacy event values
- dynamic Work status badge instead of always showing Draft
- visible validation error list in the shared shell
- customer + primary-contact creation wrapped in a database transaction
- edit workflow now clears an old event-day contact when the customer changes
- owner-oriented file map: docs/ZAZU_FILE_MAP.md
- compliance/privacy engineering baseline: docs/COMPLIANCE_BASELINE.md
- core customer/work workflow tests: tests/Feature/WorkWorkflowTest.php
- additional model property documentation was started to reduce IDE undefined-property noise

The current batch is still locally UNVERIFIED until the owner pulls/refreshes the branch and runs the verification commands below.

## Later batches

Batch 04: requirements versus business capabilities.

Batch 05: versioned quote engine with historical calculation inputs.

Batch 06: travel costing with replaceable route provider, two routes, distance/time, configurable fuel price, vehicle consumption, round trip, customer charge and historical evidence.

Batch 07: projected versus actual costs, staff costing, receipt proof plus AI extraction with human verification, reusable asset accountability and damage/loss evidence.

Batch 08: payments, expenses, documents, activity/audit trail and reminders.

Batch 09: authentication, active business context, roles/permissions, server-side business isolation and authorization tests.

The order can change when verified dependencies or evidence justify it.

## Verification scoreboard

IMPLEMENTED:
- Laravel foundation
- environment/application key
- database infrastructure
- original events migration
- controller scaffold
- frontend dependency/build
- Batch 01 schema foundation
- Batch 01 Eloquent relationships
- Batch 01 relationship test added
- Batch 02 customer/work workflow
- Batch 03 application shell/stability pass

LOCAL VERIFICATION REQUIRED:
- pull/refresh current branch
- git status review
- migration status
- full feature test run
- npm production build
- browser walkthrough of Work, Customers, Create Work and Edit Work
- confirm the local database connection in .env matches the intended development database

PRODUCTION BLOCKERS STILL OUTSTANDING:
- authentication
- active business context
- roles/permissions
- server-side business isolation
- production backup/restore evidence
- complete security review
- legal/compliance review for the actual deployment and processing model

## Owner orientation

When a batch changes files, explain:
1. which working files changed,
2. what each file does,
3. whether the database schema changed,
4. whether live data changes are expected,
5. what the owner should inspect in VS Code,
6. what browser behaviour should visibly improve.

Do not treat the owner as a passenger.

## Immediate verification commands

After refreshing the local branch:

git status
git log -5 --oneline
php artisan migrate:status
php artisan test
npm.cmd run build

Then refresh:
http://localhost:8000

Verify:
- Work page loads without undefined errors
- mobile/desktop navigation is usable
- New customer saves
- customer primary contact is saved
- New work saves
- event-day contact follows the selected customer
- Edit Work changes customer/contact safely
- Work status displays correctly
