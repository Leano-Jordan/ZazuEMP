# Zazu EMP — Living Project Context

**Product:** Zazu – Event Management Platform (Zazu EMP)
**Current owner:** Isaac Junior Lehlogonolo Maluleka
**Development model:** Solo developer
**Future business identity:** Rosscore Labs, not yet registered
**Repository:** Leano-Jordan/ZazuEMP
**Default branch:** main
**Active development branch:** main
**Context updated:** 2026-09-26

Repository state outranks stale conversation memory. Inspect the current repository before acting.

## Owner execution directive — 2026-09-26

Morpheus is the Master ENGINE identity. The owner refers to the engine as Jarvis.

When the owner explicitly says **execute**, execution is the default response. Do not substitute a plan, progress speech, token warning, or unsolicited report for the requested work.

During execution:
- Inspect the current Zazu repository before changing it.
- Work the repository, not only the defects the owner happened to name.
- Inspect adjacent high-impact correctness, accessibility, reliability, maintainability and workflow defects in the touched surface.
- Fix directly related defects when the safe scope is clear.
- Do not invent future routes or placeholder navigation merely to make the interface look complete.
- Do not silently turn future product ideas into implementation scope.
- Verify what can be verified from available evidence and mark runtime-only checks as unverified.
- Return a compact completion result unless the owner explicitly asks for a report.

The owner's time and response budget are part of the operating constraints. Do not waste replies explaining that the response is being kept short or that tokens are limited.

## Product direction

Zazu EMP is a reusable commercial event-management platform.

It is not catering-only software and not funeral-specific software. Target businesses include catering, event equipment hire, sound/DJ, baking, decor, rentals, photography, camera hire, combinations of event services, and funeral parlours whose work can use the same operational core.

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


## Master ENGINE identity + UI audit — 2026-09-26

**Master ENGINE name:** Morpheus  
**Owner-facing nickname:** Jarvis  
**Project boundary:** This audit is for **Zazu EMP / Leano-Jordan/ZazuEMP only**. Do not mix findings, files, architecture or memory from SwiftOrder / store-ordering into Zazu EMP.

### Current verified findings

1. **[HIGH] Dark-mode primary button contrast was wrong and is now fixed**
   - Dark mode now uses a semantic `--zazu-primary-ink` token for primary button text.
   - The dark primary background `#6ca7a2` now uses dark ink `#122125` for readable button text.
   - The shared button rule was fixed rather than patching individual pages.

2. **[MEDIUM] Duplicate application layout was removed**
   - `resources/views/components/app-layout.blade.php` is the active application shell.
   - The unused duplicate `resources/views/layouts/app.blade.php` was deleted after repository-wide usage inspection found no `layouts.app` consumers.

3. **[MEDIUM] Navigation is structurally incomplete versus the recorded product navigation target**
   - Project memory records: Dashboard / Work / Customers / Quotes / Calendar / Suppliers / Inventory / Assets / Reports / Settings.
   - Current primary navigation exposes only Work / Customers / Capabilities.
   - This is not a broken-link finding by itself. Missing destinations should remain non-clickable until their routes exist, per the navigation rule.
   - The issue is therefore recorded as **navigation hierarchy/information architecture incomplete**, not as permission to invent placeholder links.

4. **[MEDIUM] Color hierarchy was formalized in the shared token layer**
   - Added semantic tokens for primary action text, links, navigation active state and focus.
   - Dark-mode links now use a dedicated readable link token instead of inheriting the primary action color on every surface.
   - Active navigation state and keyboard focus now reference semantic tokens rather than component-specific colour meaning.

5. **[LOW] Link and template correctness issues found during the deeper pass**
   - The customer directory contained an invalid `IlluminateSupportStr` class reference; this was corrected to Laravel's real `Illuminate\Support\Str` class.
   - Customer creation is now atomic so the customer record and primary contact cannot be split by a partial write.
   - Requirement creation now rejects inactive capabilities even when a crafted request bypasses the rendered selector.
   - Existing named route targets remain structurally valid in the inspected workflow code; rendered/browser verification is still required for runtime link behaviour.

### Next UI/link verification pass

Verify the actual rendered application, not source alone:
- every visible navigation link
- every contextual back link
- every forward workflow link
- active navigation state
- mobile navigation
- light/dark theme
- primary/secondary/ghost action hierarchy
- keyboard focus visibility
- button/link contrast

**Rule:** do not fix individual symptoms before checking the shared component/layout/token that controls them.


NaN## Execution fix pass — 2026-09-26

IMPLEMENTED in Zazu EMP:
- shared dark-theme primary button contrast fix
- semantic colour-token layer for action/link/navigation/focus meaning
- stronger actionable "Start work" link treatment
- semantic `aria-current="page"` on active desktop/mobile primary navigation
- invalid customer-list avatar `Str` reference corrected
- customer + primary-contact creation made transactional
- inactive capability selection rejected at requirement write time
- committed PHPUnit result cache removed and added to `.gitignore`
- duplicate unused application layout removed

UNVERIFIED:
- actual browser/runtime link traversal
- mobile visual inspection
- light/dark rendered inspection
- local PHPUnit execution after the latest changes
- local build after the latest changes


## Ownership correction and Work recovery - 2026-09-26

Implemented:
- Current project ownership record corrected to Isaac Junior Lehlogonolo Maluleka as the present solo developer/owner.
- Rosscore Labs is recorded as a planned future business identity only; it is not currently treated as an incorporated owner.
- Proprietary LICENSE and IP ownership documentation aligned with that reality.
- Added a commercial/legal protection register covering copyright provenance, future company transfer, trade marks, third-party material, POPIA/PAIA/ECTA/CPA considerations and production release gates.

Work recovery findings:
- Existing repository migration `2026_09_26_000008_add_event_night_contact_and_soft_deletes_to_events_table.php` defines `events.deleted_at`, but an existing development database can still be out of sync with repository migration state.
- Added a resilient repair migration `2026_09_26_000011_repair_event_workflow_schema.php` so missing Work columns are restored without requiring a destructive schema replacement.
- Fixed a Work creation defect where `$event` was referenced before the record was created.
- Fixed Work Edit so the night-contact selector actually exists and is populated.
- Photography and camera hire are now explicitly part of the supported capability direction.

**Current incident:** owner-reported UI failure due to `events.deleted_at` missing from the actual database. Source is fixed, but the owner's local database must still have the repair migration applied before runtime recovery is complete.

## Foundation sweep completed - 2026-09-26

Execution batch expanded the existing Zazu foundation across relationship, Work, navigation, accessibility/privacy and file-profile foundations.

Implemented:
- Customer edit/update surface and visible lifecycle controls.
- Customer creation now accepts optional profile photo plus optional Day and Night contacts.
- Customer contact create/edit/remove surface; primary contact removal is protected.
- Work edit/update/remove controls exposed from active Work surfaces.
- Work creation and editing support optional Day and Night contacts, with server-side customer ownership validation.
- Event and contact soft deletes preserve historical references.
- Quote -> Event relationship resolves soft-removed Work records.
- Dashboard and Calendar are live data-backed surfaces rather than static skeletons.
- Shared profile avatar component renders initials or stored profile photos.
- Customer and User models have profile photo paths; staff photo editing remains behind the future authenticated profile/access layer.
- Dead Laravel welcome view removed because it referenced unregistered login/register routes.
- Route integrity tests cover expected registered routes and scan Blade literal route calls.
- Customer/contact and Work lifecycle tests added/updated.
- POPIA/OWASP/WCAG engineering baseline documented in docs/ZAZU_PRIVACY_BASELINE.md.

Verification boundary:
- GitHub main branch was re-inspected after the sweep.
- Route references were statically reviewed and a repository test was added for automated verification.
- PHPUnit, fresh migrations and browser/light-dark/responsive runtime traversal have not been executed in this environment.
- Zazu still lacks authentication, active business context, server-side business isolation, roles/permissions and private/authenticated media delivery. These remain production security/privacy gates, not cosmetic follow-ups.

Legal/security design:
- Personal information minimisation, purpose limitation, security safeguards and retention/deletion principles are reflected in the feature design.
- Soft delete is used for operational history preservation but is not treated as a permanent retention policy.
- Profile-photo uploads use an allowlist and size limit, while production should serve identifiable images through an authenticated/authorised boundary.


## Regression prevention — PHP write integrity — 2026-09-26

Incident:
- A repository write to `app/Http/Controllers/DashboardController.php` produced malformed PHP namespace/import declarations: `AppHttpControllers` and `AppModelsBusiness` instead of namespaced forms.
- The controller file physically existed, but Laravel could not resolve the class, making the dashboard/UI appear unavailable.
- Root cause was a write-integrity failure in the automation path, not a missing Laravel controller scaffold.

Permanent rules:
- Treat PHP namespace/import backslashes as critical syntax, never cosmetic text.
- After every automated PHP file write, re-fetch the exact file from the repository and inspect its namespace, imports and class declaration before considering the batch complete.
- Never report a PHP implementation as verified from the write operation alone.
- Controller-backed routes must have a loadable controller class. The route-integrity test now checks that controller route targets resolve to existing classes.
- A page is not considered executed merely because its Blade file exists or its route name is registered. The full chain is: route -> controller/class -> data/model -> view -> rendered response.
- Regression prevention outranks feature velocity. If a write corrupts a foundational file, repair it first, add a guard, record the failure mode, then resume the planned execution cycle.

## Execution cycle — 2026-09-26: guided interaction + commercial hardening

Implemented:
- Local-only system typography end-to-end; removed the remaining external Bunny font declaration from Vite.
- Guided currency/category/unit controls and business-default currency.
- Explicit visual distinction between section headers, column headers, records, metadata and row actions.
- Workload quick views for all work, today, next 7 days, in progress, drafts and overdue preparation.
- Central active business resolver and model-level direct-record business stamping.
- Business scoping across the touched Work, Customer, Capability, Requirement, Contact, Quote, Cost, Travel, Preparation, Calendar and Dashboard paths.
- Closed Work is treated as immutable for downstream operational/commercial changes.
- Work status transitions are centrally represented by Event domain rules.
- Quote totals use integer-cent calculations with quantity hundredths.
- Added cross-business and workload regression coverage.

Verification boundary:
- Repository source was re-inspected after changes.
- Automated PHP source write integrity was rechecked on critical files.
- Local runtime execution remains unverified because this environment cannot reach GitHub, so PHPUnit, migrations, Blade compilation and browser traversal were not run here.
- Full authentication, explicit business selection, roles/permissions and private media authorization remain production gates.


## Commercial UI hardening cycle — 2026-09-26

Implemented:
- icon-only light/dark theme control
- compact signed-in account menu with sign-out
- removal of implementation/source language from user-facing recently added surfaces
- removal of placeholder authentication artwork copy
- constrained image-input and business-identity control sizing
- service description remains operator-editable after guided service selection
- UI accessibility regression assertions updated

Architecture boundary:
- Existing business-scope guards and CurrentBusiness logic were preserved.
- No working workflow routes or business-domain behaviour were deliberately rewritten in this UI hardening cycle.

Verification:
- GitHub source re-inspected after each change.
- Static checks confirmed changed files contain the intended new controls and no read-only/system-filled service description UI.
- Local runtime execution remains required for PHPUnit, migrations, Blade rendering and browser responsive/light-dark verification.


## Full foundation integration sweep — 2026-09-26

Implemented:
- Connected Suppliers, Inventory and Assets foundation pages to live Work requirements and the reusable capability catalogue.
- Connected Reports to current-business operational, quote and cost records without mixing currencies or businesses.
- Replaced static foundation routes with controller-backed, business-scoped views.
- Added explicit session-backed active business context with membership-bound workspace switching.
- Centralized owner-role evaluation.
- Removed owner-only Settings/catalogue actions from staff-visible navigation and dashboard surfaces.
- Added live Owner Administration overview.
- Hardened quote revision authorization ordering to avoid cross-business lifecycle disclosure.
- Hardened branding/catalogue/customer image write cleanup against orphaned uploads.
- Corrected EventCost category validation to use the cost-category vocabulary.
- Aligned the declared PHP runtime to the PHP 8.4 CI/locked dependency baseline.
- Added integration and role/context regression coverage.

Important verification incidents caught and resolved:
- Foundation integration test data was initially not isolated with RefreshDatabase, contaminating later tests. Fixed.
- Resource eager-load callback initially used an Eloquent Builder type against a BelongsTo relation. Fixed.
- Controller route integrity initially did not recognise invokable controller actions. Fixed.
- Multi-workspace owner access initially depended on whichever workspace happened to be active. Fixed.
- Quote revision lifecycle checking initially occurred before business authorization. Fixed.

Verification:
- GitHub Actions run 309 passed 50 tests and 263 assertions after the major foundation corrections.
- Subsequent runs are validating the final owner/security refinements.
- Frontend dependency installation and build have passed in the CI workflow on the verified runs.
- Local Windows browser traversal, rendered responsive/light-dark inspection and direct local runtime checks still require the owner's checkout.


## Authentication recovery + usability hardening — 2026-09-26

Implemented:
- Username is a first-class user identity and login identifier.
- Login accepts username or email, with case-normalized lookup and Laravel's native Auth::attempt path.
- Dedicated owner login route remains protected by server-side owner membership checks.
- Registration requires a unique username and prevents username/email cross-field identifier collisions.
- Existing users receive deterministic backfilled usernames through migration 000018.
- Password recovery accepts username or email and sends the reset link to the account email.
- Password reset uses Laravel's password broker, consumes reset tokens, updates the password, signs the user in and restores an owned active workspace context.
- Password recovery requests and reset attempts are rate-limited.
- Generic recovery messaging avoids revealing whether an identifier matched an account.
- Login exposes an obvious Forgot password path.
- Settings shortcut is available beside the light/dark control for owners and is not shown to staff.
- Registration and reset forms include password visibility controls and browser-friendly autocomplete hints.
- Account identity now uses @username in the signed-in account control.

Verification:
- Laravel CI run 370 on commit 5231cf passed the full suite with 61 tests and 310 assertions after the rendered-URL test corrections.
- Password recovery, username login, owner login, logout, token reuse and identifier-collision tests passed in that verified run.
- CodeQL actions analysis on the preceding auth context completed successfully; the current post-hardening CodeQL run is still in progress.
- Current Laravel CI run for commit 04bd790 is still in progress after the cross-field identifier hardening.
- Browser traversal, real email delivery and the owner's local Windows database remain runtime-only verification boundaries.
- The example environment uses the log mailer, so local reset messages are logged rather than delivered externally unless deployment configuration supplies a real mail transport.
