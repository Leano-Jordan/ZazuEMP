# ZAZU EMP — FAILURE CASE REGISTRY

This is the persistent failure-history register for the Failure Case / Loop-Breaking Engine.

## Registry rules

- Stable case IDs are never recycled.
- Active cases remain visible until CLOSED or BLOCKED.
- Rejected hypotheses and failed approaches are retained.
- A repeated fingerprint must reuse the existing case unless materially new evidence establishes a distinct failure.
- Do not record speculation as confirmed root cause.
- Keep sensitive secrets, credentials and personal data out of this registry.

## Case index

| Case ID | Target | Fingerprint | Status | Current hypothesis | Next layer |
|---|---|---|---|---|---|
| CASE-ZAZU-0001 | Dashboard account access | GET /dashboard queries missing business_user.primary_niche | CLOSED | Local SQLite schema was behind repository migrations | Monitor deploy/runtime migration evidence |
| CASE-ZAZU-0002 | Offline and backup/restore regressions | Offline feature tests fail on model defaults/fixtures; Windows ZIP restore rejects generated paths | CLOSED | Eloquent defaults and ZIP entry separators diverged from their contracts | Full-suite regression |
| CASE-ZAZU-0003 | Shared browser UI initialization | JavaScript calls undefined setupZazuBusinessSwitcher | CLOSED | Stale initializer remained after the feature was removed | Browser regression |
| CASE-ZAZU-0004 | Backup restore command discovery | PHP cannot parse ZazuRestoreCommand::handle() | CLOSED | Work-directory expression was missing a closing parenthesis | Full backup/restore suite |
| CASE-ZAZU-0005 | Onboarding focus selection | Playwright exact label does not match radio card accessible name | CLOSED | Card description is part of the wrapped radio label | Onboarding E2E |
| CASE-ZAZU-0006 | Demo payment replay | Seeded idempotency values violate UUID validation | CLOSED | Fixture did not satisfy the controller's UUID contract | Seeder, replay and restore tests |
| CASE-ZAZU-0007 | Mobile Work inspector | Closed inspector overlays the Work page and captures taps | CLOSED | Closed state relied on transform without a hidden/interactivity guard | Desktop/mobile/tablet Work E2E |
| CASE-ZAZU-0008 | Theme/navigation browser gate | UI-theme tests were skipped without external storage state and contained stale viewport assertions | VERIFIED | Test setup/locators did not follow the current seeded-login and responsive-navigation contract | Full Playwright suite |
| CASE-ZAZU-0009 | Local browser-suite timeouts | Two long journeys timed out in a four-worker run but passed serially | CLOSED | Local browser/server load under parallel execution | Serial full-suite regression |

### CASE-ZAZU-0001

**Status:** CLOSED

**Engineering diagnostic code:** ENG-SCHEMA-001

**First observed HEAD:** `9e6ff7cccf1c341b99bd7be767f714154d1ece81`

**Current HEAD:** `9e6ff7cccf1c341b99bd7be767f714154d1ece81`

**Target/workflow:** Authenticated workspace dashboard, `GET /dashboard`

**Verification layer:** Local runtime / database schema

**Expected:** The authenticated owner can load the active workspace dashboard.

**Actual:** The branded error surface returned `DB-001`.

**Failure fingerprint:** Dashboard → `CurrentBusiness::resolve()` → membership relation selected `business_user.primary_niche` → SQLite reported `no such column`.

**Runtime/data state:** Local `database/database.sqlite` had five repository migrations pending, including the migration adding `business_user.primary_niche`. Existing business data was retained.

**F1–F8 classification:** F4 — local runtime schema drift; required migrations had not been applied.

#### Hypotheses

| ID | Hypothesis | Evidence for | Evidence against | Result | Status |
|---|---|---|---|---|---|
| H1 | Dashboard code requested a membership column absent from the local schema | Incident log identifies `business_user.primary_niche`; migration status showed it pending | None | Confirmed | CONFIRMED |

#### Experiments

| Attempt | Hypothesis | Action | Evidence/result | Decision |
|---|---|---|---|---|
| 1 | H1 | Inspected the incident log, active database migration status and migration definitions | Exact missing column and pending additive migration confirmed | Applied the pending migrations only |
| 2 | H1 | Ran `php artisan migrate --force --no-interaction` and rechecked dashboard | All five migrations marked applied; browser rendered authenticated Dashboard | CLOSED |

**Confirmed root cause:** The local SQLite database had not been migrated to the current repository schema.

**Correction:** Applied the pending additive migrations. No database reset or data replacement was performed.

**Regression family:** Authenticated dashboard and workspace context.

**Runtime/adversarial evidence:** `business_user.primary_niche` is present in the live local schema; the authenticated browser rendered `Dashboard · Zazu Demo Catering`.

**Remaining uncertainty:** Deployment environments still require their normal migration step before serving the updated application.

**Closure evidence:** Migration status confirmed all pending migrations as applied; dashboard loaded successfully.

**Rejected approaches retained for loop prevention:** No application query workaround or migration-history rewrite; neither addresses the schema drift safely.

**Next action:** Ensure deployments run repository migrations before serving new code.

### CASE-ZAZU-0002

**Status:** CLOSED

**Engineering diagnostic code:** ENG-FIXTURE-001

**First observed HEAD:** `9e6ff7cccf1c341b99bd7be767f714154d1ece81`

**Current HEAD:** `9e6ff7cccf1c341b99bd7be767f714154d1ece81`

**Target/workflow:** Offline local entitlement, sync mutation recording/acknowledgement and backup/restore tests.

**Verification layer:** PHPUnit feature/integration.

**Expected:** Offline domain defaults, correctly-scoped test fixtures, ordered acknowledgements and a portable restore round trip.

**Actual:** The preceding full suite had 3 failures and 6 errors across offline license, offline sync and backup/restore.

**Failure fingerprint:** Test bootstrap supplies database defaults that are absent from newly created Eloquent instances; license tests pass the owner `User` instead of the required `Business`; sync test expected the wrong retained sequence; generated ZIP entries contain Windows backslashes rejected by restore path validation.

**Runtime/data state:** Tests use SQLite in-memory and isolated file-backed backup fixtures. Production restore path validation correctly rejects ambiguous separators.

**F1–F8 classification:** Mixed F1 application/default and archive serialization defects; F2 invalid test fixtures/assertions.

#### Hypotheses

| ID | Hypothesis | Evidence for | Evidence against | Result | Status |
|---|---|---|---|---|---|
| H1 | Sync model runtime attributes do not reflect database defaults | Newly created `SyncDevice` was considered inactive; directly created `SyncMutation` had null status | Schema defaults existed | Confirmed | CONFIRMED |
| H2 | Offline-license tests use the wrong service input type | Helper returns `User`; service signature requires `Business` | None | Confirmed | CONFIRMED |
| H3 | Acknowledgement logic corrupts the next sequence | Test expected sequence 1 for a row created at sequence 2 | Protocol filters acknowledgement through the requested sequence | Rejected; assertion was wrong | REJECTED |
| H4 | Backup and restore disagree on ZIP entry path format | Restore rejected an archive entry as an ambiguous path on Windows | Restore’s rejection is intentional for untrusted input | Confirmed; backup serialization was platform-specific | CONFIRMED |

#### Experiments

| Attempt | Hypothesis | Action | Evidence/result | Decision |
|---|---|---|---|---|
| 1 | H1 | Compared model instances with migration defaults and recorder guards | Eloquent-created models did not hydrate omitted DB defaults | Added matching model defaults |
| 2 | H2/H3 | Traced test helper return type, service signature and sequence assertions | Tests used a User as Business and expected 1 rather than 2 | Corrected fixtures and assertion |
| 3 | H4 | Exposed restore response and command output, inspected backup entry naming | Windows-generated backslashes triggered the intended restore rejection | Normalize archive entry names to `/` and assert on all entries |

**Confirmed root cause:** Eloquent runtime defaults and test contracts did not match the schema/domain; backup ZIP names were platform-dependent.

**Correction:** Added `SyncDevice` and `SyncMutation` model defaults, corrected offline license test inputs and sync expectation, normalized ZIP entry names, and added archive path regression coverage.

**Regression family:** Offline license/sync and SQLite backup/restore.

**Runtime/adversarial evidence:** Focused incident/regression suite passed 33 tests / 190 assertions; full suite passed 231 tests / 1,318 assertions.

**Remaining uncertainty:** No separate production restore environment was exercised.

**Closure evidence:** Both backup round-trip feature tests passed on Windows; full PHPUnit suite passed.

**Rejected approaches retained for loop prevention:** Do not weaken restore path validation to accept backslashes; normalize trusted paths when creating archives.

**Next action:** None for this case.

### CASE-ZAZU-0003

**Status:** CLOSED

**Engineering diagnostic code:** ENG-JS-001

**First observed HEAD:** `9e6ff7cccf1c341b99bd7be767f714154d1ece81`

**Current HEAD:** `9e6ff7cccf1c341b99bd7be767f714154d1ece81`

**Target/workflow:** Shared JavaScript UI initialization.

**Verification layer:** Browser runtime.

**Expected:** Application UI initializers run without uncaught exceptions.

**Actual:** Browser reported `ReferenceError: setupZazuBusinessSwitcher is not defined`.

**Failure fingerprint:** Shared `initializeZazuUi()` called an undefined initializer; no implementation or corresponding markup was present.

**Runtime/data state:** Local production-built Vite assets.

**F1–F8 classification:** F1 — stale application initializer.

#### Hypotheses

| ID | Hypothesis | Evidence for | Evidence against | Result | Status |
|---|---|---|---|---|---|
| H1 | The feature exists elsewhere but the script omits an import | Only the invocation exists in repository resources; no switcher markup was found | None | Rejected | REJECTED |

#### Experiments

| Attempt | Hypothesis | Action | Evidence/result | Decision |
|---|---|---|---|---|
| 1 | H1 | Searched shared JS, views and tests for a switcher implementation | No implementation or markup exists | Removed the stale invocation |
| 2 | H1 | Rebuilt assets and reloaded the authenticated dashboard | Dashboard rendered with the new hashed script and no current page error | CLOSED |

**Confirmed root cause:** A stale initializer invocation stopped the remainder of the shared UI initialization.

**Correction:** Removed the unsupported invocation and rebuilt Vite assets.

**Regression family:** Shared dashboard and landing page JavaScript boot.

**Runtime/adversarial evidence:** Browser loaded `app-DjedU74j.js`; dashboard title and authenticated workspace content rendered.

**Remaining uncertainty:** No separate device/browser matrix was run.

**Closure evidence:** Updated local build manifest and successful dashboard browser load.

**Rejected approaches retained for loop prevention:** No no-op stub or broad catch; both would hide a nonexistent feature or mask later initializer failures.

**Next action:** None for this case.

### CASE-ZAZU-0004

**Status:** CLOSED

**Engineering diagnostic code:** ENG-SYNTAX-001

**First observed HEAD:** `28a80784995ec03565aae5d66358cac209508455`

**Current HEAD:** `4c467c6b7a6ca0f6022284a0dd8912102ed8d0a1` (correction is committed)

**Target/workflow:** Artisan restore command discovery and populated backup restore.

**Verification layer:** PHP source integrity, Artisan command discovery and PHPUnit backup/restore integration.

**Expected:** Laravel parses and registers `zazu:restore`, allowing the restore workflow to run.

**Actual:** PHPUnit reported an unclosed parenthesis in `ZazuRestoreCommand.php`; Laravel omitted the unparsable command, breaking restore.

**Failure fingerprint:** The work-directory `storage_path(...)` expression in `ZazuRestoreCommand::handle()` lacked its closing parenthesis.

**Runtime/data state:** No production data was changed. The local database was not reset or replaced.

**F1–F8 classification:** F1 — application syntax defect.

#### Hypotheses

| ID | Hypothesis | Evidence for | Evidence against | Result | Status |
|---|---|---|---|---|---|
| H1 | PHP parse failure prevents Artisan from discovering the restore command | Parser identified the malformed expression; after correction PHP lint and `php artisan help zazu:restore` succeeded | None | Confirmed | CONFIRMED |

#### Experiments

| Attempt | Hypothesis | Action | Evidence/result | Decision |
|---|---|---|---|---|
| 1 | H1 | Inspected the failing expression and corrected its closing parenthesis | PHP lint passed and `zazu:restore` appeared in Artisan command discovery | Retain the syntax correction |
| 2 | H1 | Ran backup/restore integration tests and the complete PHPUnit suite | Restore tests passed; current full suite passed 236 tests / 1,331 assertions | CLOSED |

**Confirmed root cause:** The restore command file did not parse, so framework command discovery omitted it.

**Correction:** Closed the `storage_path(...)` expression in the restore command.

**Regression family:** Backup/restore command discovery and populated restore.

**Runtime/adversarial evidence:** Command registration and backup/restore tests passed; no browser E2E or production restore environment was exercised in this cycle.

**Remaining uncertainty:** PHP still emits a startup warning because the configured `pdo_firebird` extension DLL is absent. It did not block the current suite.

**Closure evidence:** PHP lint passed; `php artisan help zazu:restore` resolves successfully; `composer test` passed 236 tests / 1,331 assertions.

**Rejected approaches retained for loop prevention:** Do not change restore data handling or weaken archive validation; the failure was a source syntax defect.

**Next action:** None for this case.

### CASE-ZAZU-0005

**Status:** CLOSED

**Engineering diagnostic code:** ENG-CONTRACT-001

**First observed HEAD:** `a43e7ac827b2c88e3f167b1c7fd29299035f6933`

**Current HEAD:** `4b1fbb8c6139215d5338b442b73a943d820948d9`

**Target/workflow:** Workspace-focus onboarding, primary-niche radio selection.

**Verification layer:** Playwright browser test.

**Expected:** The owner selects the Catering & baking radio and continues onboarding.

**Actual:** `getByLabel('Catering & baking', { exact: true })` could not match the radio because its accessible name includes the descriptive copy inside the wrapping label.

**Failure fingerprint:** Registration → workspace focus → exact accessible-name lookup for a labelled radio card.

**Runtime/data state:** Fresh isolated SQLite database; desktop Chromium.

**F1–F8 classification:** F2 — stale test locator; the rendered label and radio association were correct.

#### Hypotheses

| ID | Hypothesis | Evidence for | Evidence against | Result | Status |
|---|---|---|---|---|---|
| H1 | The label is not associated with the radio | Exact label lookup failed | The radio is nested inside its label; role/name lookup succeeds | Rejected | REJECTED |
| H2 | Exact accessible-name matching omits the wrapped description | Component markup wraps input and descriptive text in one label | None | Confirmed | CONFIRMED |

#### Experiments

| Attempt | Hypothesis | Action | Evidence/result | Decision |
|---|---|---|---|---|
| 1 | H2 | Replaced the exact label locator with a radio role locator matching the label prefix | Registration/onboarding passed on desktop in the targeted and full suite | Retain semantic radio locator |

**Confirmed root cause:** The assertion expected a shorter accessible name than the label actually provides.

**Correction:** Locate the radio by role with `/Catering & baking/`; application markup was unchanged.

**Regression family:** Onboarding registration, workspace focus and final dashboard.

**Runtime/adversarial evidence:** The focused desktop journey and complete multi-project suite passed. Mobile/tablet registration are intentionally skipped; responsive public entry tests ran separately.

**Remaining uncertainty:** No physical-device registration run was performed.

**Closure evidence:** Full browser suite passed with only the two documented mobile/tablet registration skips.

**Rejected approaches retained for loop prevention:** Do not remove the descriptive text from the accessible name or alter correct component markup to satisfy an exact locator.

**Next action:** None for this case.

### CASE-ZAZU-0006

**Status:** CLOSED

**Engineering diagnostic code:** ENG-FIXTURE-002

**First observed HEAD:** `a43e7ac827b2c88e3f167b1c7fd29299035f6933`

**Current HEAD:** `4b1fbb8c6139215d5338b442b73a943d820948d9`

**Target/workflow:** Seeded Finance payment replay and populated backup restore.

**Verification layer:** Playwright, PHPUnit seeder and backup/restore integration.

**Expected:** The seeded payment can be replayed idempotently using the same UUID key, and a new overpayment uses a distinct valid UUID.

**Actual:** Demo seeder values such as `demo-payment-balance-001` were rejected by the controller's existing `uuid` validation, redirecting the request instead of exercising idempotency.

**Failure fingerprint:** POST `/finance/payments` with a seeded non-UUID idempotency key fails validation before replay lookup.

**Runtime/data state:** Fresh isolated seeded SQLite database; existing validation contract requires UUIDs.

**F1–F8 classification:** F3 — fixture/data defect.

#### Hypotheses

| ID | Hypothesis | Evidence for | Evidence against | Result | Status |
|---|---|---|---|---|---|
| H1 | Controller validation is too strict for payment replay | Seeded key fails request validation | Controller intentionally accepts UUIDs and generates UUIDs when omitted | Rejected | REJECTED |
| H2 | Demo fixture violates the current idempotency-key contract | Seeder uses descriptive non-UUID keys; validation requires UUID | None | Confirmed | CONFIRMED |

#### Experiments

| Attempt | Hypothesis | Action | Evidence/result | Decision |
|---|---|---|---|---|
| 1 | H2 | Replaced seeded deposit/balance keys with stable UUIDs and aligned replay, overpayment and restore assertions | Seeder/restore test family passed; replay passed on desktop/mobile/tablet | Retain UUID fixture contract |

**Confirmed root cause:** The demo fixture violated the controller's UUID contract.

**Correction:** Seeder now uses stable UUID keys; the replay reuses the balance UUID and the overpayment uses a new UUID. Validation was not weakened.

**Regression family:** Demo seeder idempotency, financial replay/overpayment and populated backup restore.

**Runtime/adversarial evidence:** Focused PHP tests passed (5 tests / 75 assertions); complete Laravel suite passed (241 / 1,377); browser replay passed on desktop, phone and tablet emulation.

**Remaining uncertainty:** No production payment gateway or external payment side effect was involved.

**Closure evidence:** Full PHP and Playwright suites passed.

**Rejected approaches retained for loop prevention:** Do not weaken UUID validation or re-use the deposit idempotency key for a distinct overpayment.

**Next action:** None for this case.

### CASE-ZAZU-0007

**Status:** CLOSED

**Engineering diagnostic code:** ENG-UI-001

**First observed HEAD:** `a43e7ac827b2c88e3f167b1c7fd29299035f6933`

**Current HEAD:** `4b1fbb8c6139215d5338b442b73a943d820948d9`

**Target/workflow:** Work inspector closed/open state on phone and tablet layouts.

**Verification layer:** Browser runtime and responsive visual inspection.

**Expected:** A closed inspector is absent from the visual and pointer-interaction layers; opening it displays the selected job details.

**Actual:** The inspector retained `aria-hidden="true"` but remained visible at `x=0`, full mobile viewport width, with pointer events enabled. Tapping the job inspector opener timed out because the closed panel intercepted it.

**Failure fingerprint:** Mobile `/work` → closed fixed inspector spans viewport → click on row opener is intercepted.

**Runtime/data state:** Fresh isolated SQLite database, Pixel 7 emulation. Computed-style and bounds inspection reproduced the overlay.

**F1–F8 classification:** F1 — application UI state defect.

#### Hypotheses

| ID | Hypothesis | Evidence for | Evidence against | Result | Status |
|---|---|---|---|---|---|
| H1 | The job template lacks inspector data, so JavaScript fails to open it | Failure screenshot showed placeholder content | The panel was closed (`aria-hidden=true`) and the click did not reach the opener; template content was present | Rejected | REJECTED |
| H2 | Closed state lacks a visibility and pointer-interaction guard | Computed style was visible/auto and panel covered the viewport | None | Confirmed | CONFIRMED |

#### Experiments

| Attempt | Hypothesis | Action | Evidence/result | Decision |
|---|---|---|---|---|
| 1 | H2 | Tie `visibility` and `pointer-events` to `aria-hidden`; assert closed/open states in E2E | Work journey passed on desktop, mobile and tablet; reviewed rendered phone screenshot | Retain explicit hidden-state contract |

**Confirmed root cause:** The transform alone did not reliably remove the inspector from mobile hit testing; `aria-hidden` did not affect visual or pointer behavior.

**Correction:** `aria-hidden=true` now hides the panel and disables pointer events; `.is-open` restores visibility/interactivity. Added browser assertions for both states.

**Regression family:** Work inspector open, tabs, finance link, Escape/backdrop closure and responsive layouts.

**Runtime/adversarial evidence:** Populated Work traversal passed on all three Playwright projects. A phone-emulation screenshot showed vertically stacked readable record sections and visible selected job content.

**Remaining uncertainty:** Physical phone/tablet interaction remains a release acceptance item.

**Closure evidence:** Full browser suite passed; screenshot reviewed.

**Rejected approaches retained for loop prevention:** Do not add more click retries/timeouts; they cannot correct a closed overlay intercepting input.

**Next action:** None for this case.

### CASE-ZAZU-0008

**Status:** VERIFIED

**Engineering diagnostic code:** ENG-CONTRACT-002

**First observed HEAD:** `4b1fbb8c6139215d5338b442b73a943d820948d9`

**Current HEAD:** `4b1fbb8c6139215d5338b442b73a943d820948d9` (additional mobile-toggle locator is in the working tree)

**Target/workflow:** UI theme and landing-to-workspace browser verification.

**Verification layer:** Playwright desktop/mobile/tablet.

**Expected:** Theme/nav checks execute in a normal seeded test run and assert current accessible controls at each viewport.

**Actual:** The suite skipped all theme/navigation checks unless `ZAZU_E2E_STORAGE_STATE` was externally supplied. When enabled, assertions used uppercase CSS hex values, stale “Register”/link assumptions, and did not open the mobile menu before locating its actions.

**Failure fingerprint:** Theme tests skipped without external storage state; opt-in run fails on normalized CSS serialization and responsive public-nav semantics.

**Runtime/data state:** Fresh seeded SQLite; no storage-state file required after correction.

**F1–F8 classification:** F2 — verification setup and locator contract drift.

#### Hypotheses

| ID | Hypothesis | Evidence for | Evidence against | Result | Status |
|---|---|---|---|---|---|
| H1 | Theme token values differ from the expected palette | Initial assertion failed | Values matched after case normalization | Rejected | REJECTED |
| H2 | Test setup/locators are stale and the external state gate hides the failures | Current UI uses seeded owner login, buttons, and a collapsed mobile menu | None | Confirmed | CONFIRMED |

#### Experiments

| Attempt | Hypothesis | Action | Evidence/result | Decision |
|---|---|---|---|---|
| 1 | H2 | Make tests log in with seeded demo credentials; normalize CSS hex casing; use current buttons and open mobile nav when present | Theme/nav tests passed across desktop, phone and tablet; full suite passed | Retain self-authenticating tests and responsive locator |

**Confirmed root cause:** The tests depended on optional external authentication state and stale viewport-specific selectors.

**Correction:** Theme tests now authenticate via the seeded demo owner; token comparisons normalize hex case; public actions use current buttons and the mobile menu is opened before locating collapsed controls.

**Regression family:** Theme tokens, public navigation, authentication modal, workspace and Work inspector.

**Runtime/adversarial evidence:** Full Playwright suite passed 25 tests / 27 total with only the two explicitly skipped mobile/tablet registration cases.

**Remaining uncertainty:** CI run status is not available in this environment.

**Closure evidence:** All six theme/navigation project cases ran and passed in the full suite.

**Rejected approaches retained for loop prevention:** Do not leave theme tests skipped when storage state is unavailable; do not copy live-user cookies or rely on desktop-only public-navigation assumptions.

**Next action:** None for this case.

### CASE-ZAZU-0009

**Status:** CLOSED

**Engineering diagnostic code:** ENG-FLAKE-001

**First observed HEAD:** `4b1fbb8c6139215d5338b442b73a943d820948d9`

**Current HEAD:** `4b1fbb8c6139215d5338b442b73a943d820948d9`

**Target/workflow:** Full local Playwright suite at default worker count.

**Verification layer:** Browser test runner / local runtime.

**Expected:** All multi-project browser journeys complete within their configured timeouts.

**Actual:** Two long journeys timed out during a four-worker run; both had passed in focused runs, and both passed again when the full suite ran with one worker. A separate mobile navigation locator failure was corrected under CASE-ZAZU-0008.

**Failure fingerprint:** Heavy browser/application concurrency → onboarding and populated mobile traversal exceed 60s/90s; serial full suite completes.

**Runtime/data state:** Same isolated seeded database and app server; four browser workers versus one.

**F1–F8 classification:** F8 — load-sensitive/non-deterministic verification outcome.

#### Hypotheses

| ID | Hypothesis | Evidence for | Evidence against | Result | Status |
|---|---|---|---|---|---|
| H1 | The journeys contain a deterministic application hang | Both tests timed out once | They pass focused and serial full-suite runs without application changes | Rejected | REJECTED |
| H2 | Parallel browser/server load caused local timeouts | Failures occurred only in 4-worker run; serial run passed in 2.9 minutes | No CI runner comparison yet | Confirmed for this local run | CONFIRMED |

#### Experiments

| Attempt | Hypothesis | Action | Evidence/result | Decision |
|---|---|---|---|---|
| 1 | H2 | Re-ran the complete suite with one worker | 25 passed, 2 intentional skips; no timeout | Record serial local command; CI already uses one worker |

**Confirmed root cause:** Local parallel test load exceeded the execution budget for two long workflows; no application correction was warranted.

**Correction:** No product-code change. Use the one-worker full-suite command for reliable local verification; do not inflate journey timeouts based on this single load-sensitive run.

**Regression family:** Full Playwright suite across desktop, mobile and tablet.

**Runtime/adversarial evidence:** Complete serial suite passed.

**Remaining uncertainty:** CI run status is not available in this environment.

**Closure evidence:** Complete serial suite passed on the isolated seeded runtime.

**Rejected approaches retained for loop prevention:** Do not treat timeouts as an application defect or increase all test timeouts without evidence.

**Next action:** None for this case.

### CASE-ZAZU-0010

**Status:** CORRECTED

**Engineering diagnostic code:** ENG-ROUTE-001

**First observed HEAD:** `94914d1f0fcda07156d129c4c17ce2604f1634e2`

**Current application HEAD:** `7fce57b7daaa25305df16d70f8502c0f8c41174a`

**Target/workflow:** Calendar access through the primary navigation on desktop and mobile.

**Verification layer:** F1 — application UI state defect; browser regression coverage added for the affected workflow.

**Expected:** The Calendar route is owned by the Work navigation group. When the Calendar route is active, Work must be marked active and the mobile drawer must auto-expand Work so Calendar is immediately reachable.

**Actual:** Calendar had been moved from Sales to Work, but the Work group's active-route predicate still matched only dashboard/work/customer routes. The Calendar link itself existed and was permission-gated, but the Calendar route produced no active primary navigation group. On mobile, the existing active-group auto-open logic therefore found no active area and left Calendar collapsed inside Work, creating a navigation dead end.

**Failure fingerprint:** `/calendar` → Work active predicate omits `calendar.*` → no active primary group → mobile auto-open has no target → Calendar remains hidden behind collapsed Work.

**Runtime/data state:** Route, permission map and Calendar controller were present; failure was in navigation state composition, not missing Calendar data or route registration.

**F1–F8 classification:** F1 — application UI state defect.

#### Hypotheses

| ID | Hypothesis | Evidence for | Evidence against | Result | Status |
|---|---|---|---|---|---|
| H1 | Calendar permission or route registration was denying access | Calendar route is permission-protected | Route exists; owners and configured staff/manager roles include `calendar.view`; Calendar controller resolves correctly | Rejected | REJECTED |
| H2 | Moving Calendar under Work left the Work active-route predicate stale | Calendar link is inside Work, but `is-active` omitted `calendar.*`; mobile logic only auto-opens the active group | None | Confirmed | CONFIRMED |

#### Experiments

| Attempt | Hypothesis | Action | Evidence/result | Decision |
|---|---|---|---|---|
| 1 | H2 | Add `calendar.*` to the existing Work active-route predicate; add feature and browser regression assertions | Source re-read shows Calendar now activates Work; browser test explicitly checks Work active state and mobile expansion behavior | Retain bounded fix; do not duplicate Calendar in another group |

**Confirmed root cause:** Navigation ownership was moved without updating the contextual active-state contract.

**Correction:** Work now treats `calendar.*` as an active route. Calendar remains in the existing Work flyout and contextual section navigation; no duplicate route or second navigation authority was introduced.

**Regression family:** Primary navigation, mobile drawer auto-expansion, Calendar route access, contextual section tabs.

**Runtime/adversarial evidence:** Not executed through the current repository connector. A Playwright regression was added to exercise direct Calendar navigation, active Work state, and mobile drawer expansion.

**Remaining uncertainty:** Current source is corrected, but rendered browser/device acceptance still requires the local runtime/CI execution path.

**Closure evidence:** Application source and regression coverage are committed on `main`; visual/browser closure remains pending execution.

**Rejected approaches retained for loop prevention:** Do not restore Calendar to Sales merely to make the current state appear active. Do not duplicate Calendar in multiple primary groups. Do not add JavaScript exceptions to force-open Work on Calendar; the route ownership predicate is the authoritative fix.


### CASE-ZAZU-0011

**Status:** BLOCKED

**Block reason:** Owner runtime evidence required; correction budget exhausted. Do not patch this case further until authenticated runtime evidence is available.

**Correction-attempt state:** 3 attempts/cycles reached; Director rule is STOP PATCHING → FORENSICS / owner runtime evidence.


**Engineering diagnostic code:** ENG-UI-002

**First observed HEAD:** `6f0774a2fa07136bb242de17e61e5b2bbcf64f90`

**Current application HEAD:** `a4084628416c7c4e7ffa38fb980d9914a8e7031e`

**Target/workflow:** Owner-reported Calendar lockout recurrence after a prior navigation repair.

**Verification layer:** F1/F2 — application route/navigation behavior plus verification-contract coverage.

**Expected:** An authorized active workspace user can always reach Calendar from the primary Work navigation, including mobile/tablet widths, and Calendar must not fail because optional presentation preferences are unavailable.

**Actual:** Owner reports Calendar remains inaccessible after the first active-route correction. Repository inspection shows the previous correction addressed only navigation active-state ownership; it did not prove runtime access, and Calendar also had an optional database-backed holiday-preference dependency.

**Failure fingerprint:** Calendar reported inaccessible despite route registration and configured `calendar.view` permissions.

#### Hypotheses

| ID | Hypothesis | Evidence for | Evidence against | Result | Status |
|---|---|---|---|---|---|
| H1 | Calendar permission map drift | Historical fix `9fc9cae...` previously restored `calendar.view` for staff | Current config contains Calendar permission for staff and manager | Not sufficient to explain current report | OPEN |
| H2 | Navigation ownership / mobile expansion defect | Calendar was moved under Work while the prior Work active predicate omitted `calendar.*`; mobile auto-open relied on an active group | Server-rendered active-state fallback is now added | New runtime evidence required | OPEN |
| H3 | Optional holiday-preference schema dependency can break Calendar on a stale local DB | Calendar previously read a newly added `business_user.calendar_holiday_preferences` field | Current Calendar path now checks column existence and falls back safely | Corrected in source | CORRECTED |
| H4 | Calendar month view referenced an undefined `$dayHolidays` variable | Month template called `$dayHolidays->isNotEmpty()` without assigning it in the day loop | Current month loop now resolves `$dayHolidays` from the holiday map; regression test covers April 2026 holiday rendering | Confirmed source defect; corrected | CORRECTED |

#### Experiments

| Attempt | Hypothesis | Action | Evidence/result | Decision |
|---|---|---|---|---|
| 1 | H2 | Restore `calendar.*` to Work active-state ownership and add browser regression | Source contract corrected; owner still reports failure | Not closed |
| 2 | H2/H3 | Make Work active navigation server-revealing on mobile and make optional holiday preference schema non-blocking | Source now contains both safeguards | Requires runtime verification |
| 3 | H1/H2 | Add explicit staff/manager Calendar permission tests plus direct browser navigation coverage | Future config/navigation drift will fail verification instead of silently removing Calendar | Retain |

**Current root-cause state:** A separate Calendar month-render defect is now confirmed and corrected: the month view referenced an undefined `$dayHolidays` variable. This could produce a render failure after Calendar was successfully reached. The original owner-reported lockout may therefore have been a route/render combination; runtime causality is still not proven.

**Correction in current cycle:** Added server-rendered mobile recovery, aligned mobile breakpoint contracts to 850px, normalized Calendar's optional holiday-preference lookup, corrected the undefined month-view holiday state, removed the conflicting mobile calendar display rule, and expanded regression coverage.

**Regression family:** Calendar route access, primary navigation ownership, mobile drawer expansion, role permissions, business context, optional schema dependencies.

**Remaining uncertainty:** Exact runtime failure observed by the owner remains unresolved in the repository-only environment.

**Required closure evidence:** Authenticated owner/staff/manager Calendar navigation on current `main`, mobile and desktop; response status captured; no blocked overlay; Calendar content rendered; then full UI regression sweep.

**Rejected approaches retained for loop prevention:** Do not keep appending Calendar links, duplicate the destination into another nav group, or declare success from source inspection alone.

### CASE-ZAZU-0012

**Status:** CLOSED

**Engineering diagnostic code:** ENG-CONTRACT-003

**First observed date:** 2026-10-05

**Target/workflow:** PHPUnit test-contract reconciliation batch.

**Verification layer:** PHPUnit source-contract assertions.

**Expected:** Tests assert stable, current ownership contracts without encoding stale selectors, source locations, viewport-specific markup, cache versions or unrelated response bodies.

**Actual:** A 12-failure PHPUnit batch contained stale source-location/selector assertions plus two real contract gaps. Repository evidence records all twelve findings and their repository-level corrections.

**Failure fingerprint:** PHPUnit → stale/brittle source or selector contract → assertion disagrees with current canonical CSS/navigation/PWA ownership.

**F1–F8 classification:** F2/F7 — verification contract defect / contract drift.

**Evidence-backed findings:** wallpaper ownership; Calendar semantic active state; Asset/Inventory grouped selectors; three-row mobile header; service-worker cache v6; manifest file/route contract; service-worker string assertion; accessibility navigation labels; error-summary ownership; canonical visual proportions; 13.5px form-label readability; authentication submit ownership.

**Confirmed disposition:** All twelve findings were reconciled on repository `main`. No obsolete CSS selector or generic override was resurrected solely to satisfy a test.

**Verification boundary:** Repository source was re-read after correction. Local PHPUnit execution still requires the owner's VS Code runtime; no local green result is claimed from this record.

**Rejected approaches retained for loop prevention:** Do not restore obsolete selectors, broaden assertions to unrelated source ranges, or weaken the current application contract merely to obtain a green assertion.

**Next action:** Rerun the current PHPUnit suite from a checkout synchronized to `main`.

### CASE-ZAZU-0013

**Status:** CLOSED

**Engineering diagnostic code:** ENG-CONTRACT-004

**First observed date:** 2026-10-05

**Target/workflow:** PHPUnit reconciliation batch reported at 269 passed / 7 failed.

**Verification layer:** PHPUnit source-contract assertions.

**Expected:** The current checkout executes the same current contract assertions that are present on repository `main`.

**Actual:** The reported seven failures were traced; repository `main` contains the corresponding stale/brittle contract corrections. The available repository record does not preserve seven independent failure fingerprints, so this case intentionally does not invent them.

**Failure fingerprint:** Local PHPUnit batch → 269 passed / 7 failed → reported assertions correspond to a checkout behind current `main`.

**F1–F8 classification:** F2/F7 — verification contract defect / contract drift.

**Evidence-backed corrections:** Calendar navigation assertion; PWA manifest contract; accessibility/navigation and error-summary ownership; authentication width contract; canonical visual-layer proportion contract.

**Verification boundary:** The repository records the seven-failure result and its traced correction commits, but a synchronized local rerun is still required before claiming the current suite is green.

**Rejected approaches retained for loop prevention:** Do not weaken tests or count the historical seven failures as current failures when the checkout is stale. Synchronize first, then classify any remaining failures by fresh fingerprint.

**Next action:** Synchronize local checkout and run the current suite. If any failure remains, create/reuse its individual fingerprinted case rather than reusing this batch case.

## Case record template

### CASE-ZAZU-XXXX

**Status:** OBSERVED / INVESTIGATING / FORENSICS / CORRECTED / VERIFIED / BLOCKED / CLOSED

**First observed HEAD:**

**Current HEAD:**

**Target/workflow:**

**Verification layer:**

**Expected:**

**Actual:**

**Failure fingerprint:**

**Runtime/data state:**

**F1–F8 classification:**

#### Hypotheses

| ID | Hypothesis | Evidence for | Evidence against | Result | Status |
|---|---|---|---|---|---|
| H1 | | | | | |

#### Experiments

| Attempt | Hypothesis | Action | Evidence/result | Decision |
|---|---|---|---|---|
| 1 | | | | |

**Confirmed root cause:**

**Correction:**

**Regression family:**

**Runtime/adversarial evidence:**

**Remaining uncertainty:**

**Closure evidence:**

**Rejected approaches retained for loop prevention:**

**Next action:**
