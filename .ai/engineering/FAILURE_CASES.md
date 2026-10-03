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

### CASE-ZAZU-0001

**Status:** CLOSED

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
