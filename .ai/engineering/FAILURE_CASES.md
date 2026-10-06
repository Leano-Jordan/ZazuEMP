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

## Archived case detail

Closed historical cases CASE-ZAZU-0001 through CASE-ZAZU-0009 are archived in `.ai/archive/failure-cases-2026-10.md`.

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
