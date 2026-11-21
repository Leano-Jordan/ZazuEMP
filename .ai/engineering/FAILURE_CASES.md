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

| CASE-ZAZU-0033 | Offline sync existing-record updates | A queued update based on an old device snapshot is applied to the latest server row without a revision precondition | CLOSED — exact-head CI PASS | Revision preconditions, causal chains and conflict preservation verified across paired devices | Broader offline workflow matrix tracked separately |
| CASE-ZAZU-0034 | Receiver bootstrap completeness | A receiver acknowledges a delivery batch after storing a bounded bootstrap that omitted the affected aggregate | CLOSED — exact-head CI PASS | Snapshot collection limit/display ordering can exclude a delivered or recently revised aggregate | Targeted bootstrap inclusion, recency, device isolation and post-ack reconciliation tests |
| CASE-ZAZU-0035 | Bootstrap stale-device authorization | Bootstrap returns a business snapshot using a device model authenticated before revocation or credential replacement | CLOSED — exact-head CI PASS | Bootstrap did not re-read the active device/credential under lock before generating the snapshot | Revocation and replaced-token stale-snapshot regressions, business-first lock order |
| CASE-ZAZU-0036 | Sync stale-device authorization | Conflict listing/resolution or attachment operations proceed from a middleware snapshot after revocation; a stale token may revoke a re-paired device | CLOSED — exact-head Laravel/PHPMD/Psalm PASS | Several endpoints did not share durable business/device lock and token-hash revalidation | Stale-snapshot regression tests for conflict listing/resolution, attachment upload/list and replacement-token revocation |
| CASE-ZAZU-0037 | LAN host outage leaves phone on stale page | Zazu host stops while Wi-Fi remains connected; browser receives no offline event and visible server-rendered page remains stale | CLOSED — exact-head desktop/mobile browser PASS; physical Android drill remains a release gate | Origin availability was only rechecked during UI render/events; concurrent probes could race network transitions | Exact-head 503→200 health transition tests on desktop and Pixel 7 viewport; physical Android cold-launch/stop-host/restart remains separate |
| CASE-ZAZU-0038 | Backup archive exceeds restore acceptance limits | Backup accepts up to 50,000 private files plus metadata/database, but restore previously allowed only 50,000 ZIP entries; backup also lacked the restore 2 GiB total-size ceiling | CLOSED — exact-head Laravel/PHPMD/Quality PASS; owner restore drill remains open | Backup and restore maintained separate, drifting archive limits | Shared entry/size policy, boundary tests and complete-ZIP verification before publishing |
| CASE-ZAZU-0039 | Empty private-storage restore retains stale target files | Backup manifest declares private_storage but ZIP contains no file entries; restore skips the private directory swap and leaves files from the pre-restore installation | CLOSED — exact-head Laravel/PHPMD/Quality PASS; owner restore drill remains open | Restore inferred private-storage presence only from file entries; ZIP omits empty directories | Restore uses declared contents manifest and swaps in an empty private directory; integration test proves stale file removal |
| CASE-ZAZU-0040 | Stale device snapshot writes sync state after credential replacement | Mutation apply or conflict recording can proceed after a device is re-paired if the later transaction checks only active status | CLOSED — exact-head Laravel/PHPMD/Psalm/browser/quality PASS on 04db872 | Mutation recorder checked token hash, but separate apply/conflict writes did not consistently revalidate the authenticated snapshot | Apply and conflict writes now use the same business-first/device-row credential guard; regressions prove stale snapshots return 401 without apply/delivery/conflict |
| CASE-ZAZU-0041 | Unsupported offline mutation operation | Customer, job and service handlers could interpret an unsupported operation as create/update rather than rejecting it | CLOSED — exact-head Laravel + PHPMD + PostgreSQL PASS | Three handlers lacked the explicit operation allowlists already used by other entity handlers | Regression sends delete operations for all three entities and proves rejection before domain writes |

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
| CASE-ZAZU-0010 | Calendar navigation active-route ownership drift | /calendar route owned by Work but active-group contract was stale | CORRECTED | Source correction exists; browser/runtime evidence pending | Current-head browser verification |
| CASE-ZAZU-0011 | Calendar runtime lockout/reachability | Calendar remains owner-reported inaccessible despite source safeguards | BLOCKED | Runtime causality remains unproven; owner-authenticated evidence required | Owner runtime forensics |
| CASE-ZAZU-0012 | PHPUnit 12-failure stale/brittle contract batch | Historical PHPUnit batch exposed stale/brittle source and selector contracts | CORRECTED | Repository corrections exist; synchronized verification pending | Current-head CI/PHPUnit evidence |
| CASE-ZAZU-0013 | PHPUnit 269-pass/7-fail stale-checkout batch | Historical failures traced to stale checkout against current main | CORRECTED | Repository corrections exist; synchronized verification pending | Current-head CI/PHPUnit evidence |
| CASE-ZAZU-0014 | Dark-theme browser token assertions | E2E had stale palette literals and sampled active navigation before its theme transition settled | VERIFIED | Exact-head browser smoke passed on `153c4d01bef4b5c3bd01a1db770902b0c7649bb0` | Closed |
| CASE-ZAZU-0032 | Dashboard shared theme / dark-mode token mapping | Dashboard canvas alias differs from shared page token because a higher-specificity dark selector wins | CLOSED | High-specificity legacy dark rule overrides lower-specificity token alias | Exact-head light/dark Playwright and CI |
| CASE-ZAZU-0015 | Dark form-control theme timing | Browser test observed a transient light background immediately after switching theme; retry passed | CORRECTED | Test waits for dark theme and settled control backgrounds | Exact-head browser CI |

### CASE-ZAZU-0014

**Status:** VERIFIED — exact-head browser smoke passed.

**Target/workflow:** Dark theme token regression in `e2e/ui-theme.spec.ts`.

**Classification:** F2 — stale test/contract expectations plus a timing-sensitive browser assertion; no application palette defect was established.

**Failure fingerprint:** The browser test authenticated and read the live computed dark theme. After surface and primary expectations were corrected, the active-navigation assertion still sampled during the CSS theme transition, producing intermediate or previous-theme colours.

**Observed evidence:**
- At `80bf602b4fdb2531997aed86e9f8278958307ebf`, the stale surface expectation `#243d52` failed; browser output established the rendered canonical surface as `#122235`.
- After correcting surface, the same test exposed a second stale primary expectation: expected `#6fa4ff`, received `#78a9e8`.
- The canonical dark token in `resources/css/zazu-product-authority.css` is `--zazu-primary: #78A9E8`.
- On the exact-head run `42692427303`, the active-background assertion expected `rgb(72, 97, 116)` but observed `rgb(180, 200, 215)` and, on a retry, `rgb(98, 121, 139)`. These are inconsistent with the settled dark value and indicate the test read during the CSS transition.
- The canonical settled dark active-navigation declaration is `#486174` (rgb(72, 97, 116)) in `resources/css/zazu-product-authority.css`.

**Root cause:** UI token evolution was not reflected in hard-coded browser regression expectations. The test also read computed navigation styles immediately after the theme toggle, before the CSS transition had settled.

**Corrections on `main`:**
- `80bf602b4fdb2531997aed86e9f8278958307ebf` — dark surface assertion aligned to `#122235`.
- `c675eaf61166ced4aaee3f6ce6e6b59c71a53553` — dark primary assertion aligned to `#78a9e8`.
- `153c4d01bef4b5c3bd01a1db770902b0c7649bb0` — waits for the document's dark-theme state and settled active-navigation background before sampling computed styles.

**Rejected approach:** Do not change the product palette to satisfy stale test literals. The rendered application and canonical stylesheet are the authority; tests must follow them.

**Closure evidence:** Browser smoke workflow `42694636938` passed on exact `main` SHA `153c4d01bef4b5c3bd01a1db770902b0c7649bb0`. The workflow completed successfully, including the browser regression suite and diagnostic upload. Dark-theme token and responsive navigation assertions are verified at this SHA.


### CASE-ZAZU-0015

**Status:** CLOSED — exact-head browser, Laravel, PostgreSQL and quality checks passed.

**Target/workflow:** Dark-mode primary form-control regression in `e2e/ui-theme.spec.ts`.

**Observed evidence:** Browser run `42697001542` completed successfully with 64 passed, 22 skipped and 1 flaky. The first attempt observed a light control background immediately after the theme toggle; the retry passed.

**Correction:** Commit `6814d2a849f969e410dfb3537bd21d4f61d2bebb` waits for the dark theme and polls visible form-control backgrounds until the transition settles. Assertion messages now identify the control tag and type.

**Closure evidence required:** Exact-head browser smoke must complete with zero flaky tests. Do not weaken the dark-mode assertion or change production colours to mask a timing issue.


## Active case detail

Cases CASE-ZAZU-0010 through CASE-ZAZU-0013 remain active until their stated verification boundary is satisfied.

Archive pointer: closed historical cases CASE-ZAZU-0001 through CASE-ZAZU-0009 are archived in `.ai/archive/failure-cases-2026-10.md`.

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

**Status:** CORRECTED — verification pending

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

**Status:** CORRECTED — verification pending

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


### CASE-ZAZU-0014

**Status:** CORRECTED — verification pending

**Engineering diagnostic code:** ENG-BROWSER-001

**First observed date:** 2026-10-06

**Target/workflow:** Onboarding Playwright browser regression on the current main checkout.

**Expected:** The registration journey completes into /dashboard with the authenticated session intact, and closing the authentication modal restores focus to the correct visible control on desktop and responsive navigation surfaces.

**Actual:** The initial local headed run using 3 workers reported two failures: desktop registration redirected to /?auth=login after onboarding catalogue continuation; mobile auth-modal closure reported no data-auth-modal-open focus target. A subsequent serial run on the same workflow passed desktop registration and all public/login surfaces, leaving only the mobile focus assertion failure.

**Failure fingerprint:** onboarding.spec.js + parallel local workers → registration completes /setup/catalogue then loses authenticated state before /setup/experience; mobile auth modal closes while its opener is hidden by the mobile navigation drawer, so the application correctly falls back to the visible navigation toggle.

**F1–F8 classification:** F1/F2 — application interaction state / verification-contract mismatch. The desktop registration cause remains a hypothesis until a serial run confirms it.

#### Hypotheses

| ID | Hypothesis | Evidence for | Evidence against | Result | Status |
|---|---|---|---|---|---|
| H1 | Mobile focus assertion encoded the hidden modal opener rather than the visible recovery control | Mobile drawer is closed when the modal opens; close() intentionally falls back to the navigation toggle | None | Confirmed | CONFIRMED |
| H2 | Parallel local workers caused session instability against the shared local SQLite/database session environment | CI already forces one worker; historical CASE-ZAZU-0009 records local browser instability under parallel load; the same registration journey passes with one worker | None in the serial rerun | Confirmed as the cause of the observed parallel-run desktop failure | CONFIRMED |
| H3 | Catalogue/onboarding request raised a server-side exception or throttle | Registration reaches /setup/catalogue successfully | Serial run reaches /dashboard; supplied 21:02 Laravel entry is an older landing-view decor error and does not match the onboarding failure | Rejected for this failure | REJECTED |

#### Corrections

1. Mobile auth E2E now records whether the responsive navigation toggle was visible before opening the modal and expects focus on that toggle after Escape; desktop retains the login-trigger focus contract.
2. Populated runtime dashboard assertion was aligned from stale Command Centre to the current Dashboard heading.
3. CI browser execution remains serial (--workers=1); the required serial experiment now passes desktop registration, confirming the parallel-run desktop failure is not a reproducible application-session defect.

**New evidence:** The full 39-test serial suite reproduced the mobile focus failure on Chromium and Chromium-tablet. The failure now occurs after the application fallback focus path, so the application focus restoration is being hardened with a post-close animation-frame focus restore. The same run also exposed two separate stale browser contracts: the populated quote traversal expected an Open child link although the current quote row is itself the navigable anchor; and the Calendar navigation test selected the first matching Work group instead of the Work group that owns Calendar, then assumed mobile auto-open behavior that the current responsive navigation does not guarantee.

**Remaining uncertainty:** Fresh serial execution after these bounded corrections is required. No auth/session regression is currently evidenced.

**Required closure evidence:** Run npm run test:e2e -- --workers=1 from a checkout synchronized to the latest main. Close this case only after current-head browser evidence is green or remaining failures have their own fingerprinted cases.

**Rejected approaches retained for loop prevention:** Do not weaken or delete the focus assertion. Do not force desktop focus behavior onto hidden mobile controls. Do not change authentication/session code until serial execution or server logs establish an application defect.


### CASE-ZAZU-0015

**Status:** CORRECTED — verification pending

**Engineering diagnostic code:** ENG-BROWSER-002

**First observed date:** 2026-10-06

**Target/workflow:** Populated quote → customer-facing quote browser traversal.

**Expected:** The seeded quote row opens its quote detail route.

**Actual:** The browser test waited for a child link named Open inside the quote row and timed out. Current resources/views/quotes/index.blade.php renders the quote row itself as the navigable anchor.

**Classification:** F2/F7 — stale browser selector contract.

**Correction:** The E2E now clicks the navigable quote row itself. No application selector was added solely to satisfy the stale test.

**Verification:** Pending current-head browser run.

### CASE-ZAZU-0016

**Status:** CORRECTED — verification pending

**Engineering diagnostic code:** ENG-BROWSER-003

**First observed date:** 2026-10-06

**Target/workflow:** Calendar reachability from the Work navigation group across desktop/mobile/tablet.

**Expected:** Calendar is owned by Work, marks the Work group active on /calendar, and remains reachable when the responsive navigation is opened.

**Actual:** The browser test selected the first matching Work navigation area and, on responsive layouts, assumed the active Work area would already be is-open after opening the outer mobile navigation. Current navigation contains the Calendar link in the Work area and responsive logic does not require that exact interaction sequence.

**Classification:** F2/F7 — brittle responsive selector/interaction contract.

**Correction:** The E2E now selects the Work area that actually contains the Calendar link and explicitly opens its Work trigger when the responsive navigation exposes it.

**Verification:** Pending current-head browser run.


### CASE-ZAZU-0021

**Status:** CORRECTED — verification pending

**Engineering diagnostic code:** ENG-CONTRACT-005

**First observed date:** 2026-10-07

**Target/workflow:** Signed customer quote acceptance PHPUnit coverage.

**Expected:** The acceptance test refreshes the latest quote version model and verifies its accepted status.

**Actual:** The test called fresh() on Quote::latestVersion(), which returns a HasOne relation; Laravel raised BadMethodCallException: HasOne::fresh().

**Failure fingerprint:** PHPUnit → QuoteWorkflowTest signed customer quote acceptance → HasOne::fresh() undefined → 1 failed / 288 passed.

**F1–F8 classification:** F2 — verification/test contract defect.

**Correction:** Changed the assertion to fetch the related latest version with firstOrFail() before calling fresh(). Application behavior was not changed.

**Verification boundary:** Current-head CI must pass before this case is marked VERIFIED.

**Rejected approach:** Do not alter the Quote model relationship merely to accommodate an invalid test call.

**Next action:** Reconcile current-head Laravel and Zazu quality runs; close the case only after both pass.

### CASE-ZAZU-0022

**Status:** VERIFIED — source/test commit 3f5d1f1

**Engineering diagnostic code:** ENG-CONTRACT-006

**First observed:** 2026-10-16, current-head Laravel and Zazu quality workflows.

**Target:** `tests/Feature/PwaOfflineFoundationTest.php` → service-worker static asset cache contract.

**Expected:** The test asserts the current cache-first/awaited-write contract in `public/sw.js`.

**Actual:** One assertion still required the removed `cache.put(request, copy)` expression. The worker now awaits `cache.put(request, response.clone())`; the stale assertion failed the only Laravel feature test at that checkpoint (318 passed, 1 failed) and also failed the Zazu quality test job.

**Classification:** F2/F7 — test contract drift after a source implementation change.

**Correction:** Removed the stale positive assertion and added explicit regression checks for the unsupported-origin notice. No application cache behavior was weakened to satisfy the test.

**Verification:** Laravel and Zazu quality passed on the commit containing the corrected source/test set (`3f5d1f1`). Runs: [Laravel](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/38891784115), [Zazu quality](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/38891784112). The subsequent commit in which this record is reconciled changes control documentation only; the verified source/test blobs are unchanged.

**Loop prevention:** Do not reintroduce `cache.put(request, copy)` or rewrite working service-worker code to satisfy the obsolete string assertion.

### CASE-ZAZU-0023

**Status:** OPEN — offline delivery/installation boundary unresolved

**Engineering diagnostic code:** ENG-RUNTIME-002

**First observed:** 2026-10-16, owner-supplied Chrome screenshot and current-source audit.

**Target:** Installed phone launch with PC/server and internet unavailable.

**Expected:** Zazu launches from its installed app icon and supports the declared core offline UI and local operations without the PC or dev server.

**Actual:** Screenshot shows `http://192.168.137.237:8000` and `ERR_CONNECTION_REFUSED`. The address depends on a reachable PC-hosted server; additionally, plain HTTP on a non-loopback LAN IP is not a secure origin for service-worker registration. The current architecture also explicitly lacks full phone-local parity for every Zazu screen/workflow.

**Classification:** F4/F7 — launch environment and product offline-delivery contract mismatch. The screenshot does not, by itself, prove whether a separately installed HTTPS PWA shell was previously cached.

**Correction in this cycle:** Add an in-app diagnostic notice for non-secure contexts and document supported origin and device acceptance requirements.

**Still required:** Complete and verify the intended independent offline delivery path and phone-local workflow parity. Do not close this case on a source-level test, static cache check, or CI pass. Require a force-closed installed-app relaunch with both internet and the PC/server unavailable, plus offline data persistence and reconnect reconciliation.

**Loop prevention:** Do not repeat IP/port changes or add another cache-only patch as a substitute for a self-contained offline launch path.



### CASE-ZAZU-0024

**Status:** CORRECTED — Laravel regression coverage passed; browser smoke pending.  
**First observed:** 2026-11-01.  
**Target:** Default phone pairing / `SyncBootstrapPayloadBuilder::bootstrapSelectedRecords()`.  
**Actual:** Empty-selection pairing stored a fixed list of recent customer, job, quote and preparation IDs. Later bootstrap refreshes reused that frozen list, so records created after pairing were not introduced into the phone working set.  
**Correction:** Default selection is now recalculated from current business records at bootstrap time. Explicit and legacy non-empty selections remain pinned.  
**Regression coverage:** Records created after pairing appear after refresh; legacy explicit selections remain pinned.  
**Verification:** [Laravel](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/41299518930) and [Zazu quality](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/41299518886) passed on candidate `c3370a3`.  
**Classification:** F4/F7 — offline dataset freshness / stale bootstrap contract.

### CASE-ZAZU-0025

**Status:** CORRECTED — Laravel regression coverage passed.  
**First observed:** 2026-11-01.  
**Target:** `OfflineDomainMutationHandler` asset update duplicate-tag validation.  
**Actual:** The duplicate guard called `whereKey('!=', $asset->id)`, which is not the correct Eloquent key-exclusion API. The guard could fail to exclude the current asset and allow a duplicate to reach the database unique constraint, surfacing as a database exception instead of a clean validation error.  
**Correction:** Use `whereKeyNot($asset->id)`.  
**Regression coverage:** Attempt to reuse another asset's tag; assert a validation rejection and unchanged persisted tags.  
**Verification:** [Laravel](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/41299518930), [Zazu quality](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/41299518886) and [PHPMD](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/41299518954) passed on candidate `c3370a3`.  
**Classification:** F2/F4 — validation contract and predictable error handling.

### CASE-ZAZU-0026

**Status:** CORRECTED — server tests passed; browser smoke pending.  
**First observed:** 2026-11-01.  
**Target:** Paired-device sync conflict resolution.  
**Actual:** Server retry/discard endpoints existed, but the client exposed no recovery UI and did not restore failed mutations to its pending IndexedDB queue. Concurrent retry/discard requests could also race, while the client assumed the requested action had won.  
**Correction:** Added Settings conflict controls, local queue retry/discard handling, transactional server resolution with row locks, and client reconciliation against authoritative server mutation status.  
**Regression coverage:** Laravel verifies a repeated conflicting resolution cannot overwrite the first result; Playwright verifies retry/discard queue effects.  
**Verification:** [Laravel](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/41299518930) passed; [browser smoke](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/41299519019) pending.  
**Classification:** F4/F7 — recoverability and concurrent state reconciliation.

### CASE-ZAZU-0027

**Status:** IMPLEMENTED — business-scope source guard and PHP contract tests passed; browser migration/isolation tests pending.  
**First observed:** 2026-11-01.  
**Target:** Phone-local IndexedDB workspace and mutation queue.  
**Actual:** Every business using the same browser origin used the same `zazu-client` database. The service-worker cache was user-scoped, but local operational data and queued mutations were not scoped by business, creating a cross-business data exposure path on a shared browser profile.  
**Correction:** Namespace IndexedDB by active business ID. Migrate the legacy database only when both its business ID and paired-device business ID match the active business. Preserve a migration tombstone through local logout. Advance service-worker cache to v28.  
**Regression coverage:** Playwright tests cover successful matching migration with queued work, logout non-reimport and foreign-business refusal.  
**Verification:** [Laravel](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/41299518930) and [Zazu quality](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/41299518886) passed; [browser smoke](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/41299519019) pending.  
**Classification:** F4/F7 — tenant isolation / local data boundary.


### CASE-ZAZU-0028

**Status:** VERIFIED — corrected fixture passed in the exact-head Laravel workflow.  
**First observed:** 2026-11-11.  
**Target:** MySQL restore partial-import / rollback-failure integration regression.  
**Failure fingerprint:** The fake `mysql` executable for the second (rollback-failure) phase was written with doubled backslashes before newline markers. PHP therefore emitted literal backslash-plus-n sequences rather than shell line breaks. The expected failure assertion could pass because the fake executable itself was invalid, not because the rollback import ran and failed.
**Classification:** F2 — test/verification fixture defect. Engineering code: `ENG-FIXTURE-003`.  
**Correction:** Generate an executable shell script with actual line breaks, record every invocation, and assert exactly two `mysql` calls plus the deliberate `simulated rollback failure` output. The production restore implementation was not changed.  
**Verification:** Commit [`7544c11`](https://github.com/Leano-Jordan/ZazuEMP/commit/7544c11edbf3bb47094f2eb30b52825665b42f19); exact-head Laravel [run 43070247812](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43070247812) passed **454 tests / 3,298 assertions**.  
**Regression control:** The invocation-count and explicit stderr assertions remain in `tests/Feature/ZazuBackupRecoveryTest.php` so a non-executing fake client cannot satisfy the rollback-failure scenario.


### CASE-ZAZU-0029

**Status:** CLOSED — corrected fixture preconditions verified on exact application/test SHA `6303ff59effe95ad95d5df862ff823b01b49d891` (2026-11-13).  
**Engineering diagnostic code:** ENG-FIXTURE-004.  
**First observed:** 2026-11-13.  
**Target:** Offline primary-device registration recovery and local sync-status browser acceptance.

**Failure fingerprint:** On commit `55f1211a95b481644c957f798dc605fce6fe38de`, the primary-registration retry test expected at least two intercepted `/offline/device/register` requests but observed zero. A second sync-status test could not observe its expected `Retrying sync` row after a simulated HTTP 503. The Laravel suite passed; the offline browser acceptance job failed 2 tests (20 passed).

**Classification:** F2/F3 — browser test setup/fixture ordering. The dashboard boot path can initiate primary-device registration before the test installs its route interceptor; the status fixture also queued work before installing its network routes, allowing the scheduled retry to race setup. This is a test-harness finding, not proof of an application defect.

**Correction:** Commit `8ecd3894f30cf8c8218ce852aebbf3017d0eef4c` defers automatic boot-time sync/registration in both affected tests. The status test now installs all network routes before queuing the mutation and explicitly triggers sync after setup. Changed only `e2e/pwa-offline.spec.js`.

**Verification:** Changed file re-fetched from `main`; required setup markers and revised ordering confirmed. Exact-head workflow results for the correction are not yet available. Do not claim the fixture correction passed or the customer sync issue is resolved until browser acceptance reruns green.

**Loop prevention:** Do not patch production sync code to satisfy these assertions unless a corrected, deterministic browser test or owner installation evidence demonstrates an application defect.

**Exact-head browser-smoke follow-up — 2026-11-13**

- Browser run 43419239273 at 234dc9d1366e5ac85bf756ff440ebabe3f5541ae: 84 passed, 1 failed, 1 flaky, 46 skipped.
- The retry-status assertion ran on /dashboard, while the Customer sync chip is rendered only in the Customer directory. The test had not opened /customers; this is an F2 test-location/precondition defect.
- The cross-business pairing test allowed boot-time primary-device registration to establish a valid local token, then incorrectly expected that pre-existing token to be absent after a foreign pairing was rejected. The existing credential remained unchanged; this is an F2/F3 fixture precondition defect.
- Corrected only e2e/pwa-offline.spec.js: open /customers for the status assertion; defer initial registration and establish a no-token baseline for the cross-business pairing test.
- Production public/zazu-offline.js and resources/js/app.js are unchanged. No customer data, business record, or deployed resource was changed.
- Exact-head full browser run [43426696768](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43426696768): **PASS — 86 passed / 46 skipped / 0 failed / 0 flaky**. Both corrected assertions passed on the first completed run.
- Exact-head Laravel [43426697007](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43426697007): **476 passed / 3,383 assertions / 1 skipped**; dedicated offline browser acceptance **22 passed**.
- No production code change was needed. The retry chip is visible after the test opens its owning Customer directory; cross-business revocation is now tested with automatic primary registration deferred and an explicit no-token baseline.
- **Resolution:** CLOSED. Do not reopen unless a new reproducible application failure is observed.


### CASE-ZAZU-0030

**Status:** CORRECTED — exact-head rerun pending.  
**Engineering diagnostic code:** ENG-FIXTURE-005.  
**First observed:** 2026-11-13.  
**Target:** Offline browser startup-readiness acceptance.

**Failure fingerprint:** In offline-browser-acceptance run 43469787582, the test `local client becomes ready before an unresponsive initial sync` called `window.ZazuOffline.ready` immediately after the dashboard URL assertion. The dashboard route had completed before the deferred offline runtime script exposed `window.ZazuOffline`; the first attempt failed with `Cannot read properties of undefined (reading 'ready')`, then the retry passed. The job reported 21 passed and 1 flaky.

**Classification:** F2/F3/F8 — test readiness precondition / nondeterministic fixture timing. This is not evidence of a production offline-runtime defect: the failing assertion accessed the runtime before the test had awaited its existing readiness helper.

**Correction:** Commit `b1da7595665a4f2d4b7ec8766d58c67440495f73` adds `await waitForOfflineRuntime(page)` after login and before fixture state mutation. The existing helper polls for the runtime API and awaits its ready promise. No production source changed.

**Verification:** Exact-head browser and offline-browser-acceptance reruns are pending. Do not close this case until both finish without retries/flakes.

**Loop prevention:** Dashboard navigation completion is not the offline runtime readiness contract. Any test that accesses `window.ZazuOffline` must await `waitForOfflineRuntime(page)` first.


### CASE-ZAZU-0031

**Status:** CLOSED — exact-head offline browser acceptance and supporting CI passed.  
**Engineering diagnostic code:** ENG-FIXTURE-006.  
**First observed:** 2026-11-14.  
**Target:** Existing-customer offline edit browser acceptance.

**Failure fingerprint:** The customer edit test initially depended on a seeded directory customer. After moving to a test-created customer, the browser could not reliably discover that new server record through the service-worker-cached `/customers` navigation, even though a direct Laravel request returned the record. The fixture therefore failed before reaching its intended offline-edit assertion.

**Classification:** F2/F3 — browser fixture precondition and cached-page test coupling. This was not evidence that the customer update queue or reconnect mutation handler was broken.

**Correction:** The test now creates its own record through the real customer form and asserts the Laravel POST redirect. It obtains the server ID from a direct directory response, refreshes the paired device bootstrap, asserts the local entity has the expected server identity, and opens that record's unique detail URL before disconnecting. The test then verifies the local update queue, cached directory override while offline, queue drain after reconnect and persisted server value. Form connectivity probes use Laravel's lightweight `HEAD /up` endpoint; the two simulated unreachable-origin tests intercept that endpoint. The broader service-worker navigation preflight experiment was rejected and reverted.

**Verification:** Exact main `5a9516f275ebbefc3a7df88da18c57fb331b3113`. [Laravel run 43624809134](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43624809134): 496 passed, 3,510 assertions, 1 skipped; Chromium offline browser 33 passed; mobile-viewport customer/Work 2 passed; mobile-viewport phone-local attachment 1 passed; PostgreSQL migration/backup/restore passed. [Zazu quality run 43624809004](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43624809004): application, Composer security audit and secret scan passed.

**Scope boundary:** This closes the deterministic browser fixture and exact-head automated acceptance. Physical Android force-closed launch, disconnected persistence and reconnect must still be verified on the actual device; no hardware pass is inferred from mobile viewport emulation.

**Loop prevention:** Do not require this offline-edit test to discover its just-created fixture through a cached directory navigation. Use the verified server identity and paired local record. Do not modify global service-worker navigation/cache behaviour for this fixture.


### CASE-ZAZU-0032

**Status:** CLOSED — exact-head light/dark browser acceptance and supporting CI passed.  
**Engineering diagnostic code:** ENG-CSS-004.  
**First observed:** 2026-11-16.  
**Target:** Dashboard shared theme alignment in light and dark modes.

**Failure fingerprint:** After the shared-palette correction, the dashboard's computed `--dash-canvas` remained `#0D1D2E` in dark mode while `--zazu-page` resolved to `#0B1726`. The older `html[data-theme="dark"] body.zazu-dashboard-page` selector had greater specificity than the later `body.zazu-dashboard-page` alias mapping, so the custom dashboard token retained the old isolated palette value.

**Classification:** F1 — application CSS cascade defect. This was not an offline runtime, fixture, database or browser-environment failure.

**Correction:** Rebound the dashboard's full `--dash-*` token aliases inside the high-specificity dark-theme selector. Preserved the dashboard's layered radial/linear hero and business-image overlay while sourcing its colour values from shared theme tokens. Updated the visual foundation test to exercise both theme modes and compare dashboard-computed values with shared tokens.

**Verification:** Source/test commit `8cdadcd1563bd30c88b15153ca91181a2d5abb23`, exact Laravel run [43952349496](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43952349496): Laravel **500 passed / 1 skipped / 3,549 assertions**; desktop browser **37 passed**; mobile viewport **6 passed**; phone attachment **1 passed**; PostgreSQL migration/recovery smoke **PASS**. Manifest-only descendant `dfc01749af5718011332a9e86d303b9622fe7ff4) reverified by [Laravel run 43952861421](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43952861421), all three jobs passed. Zazu Quality, Director Health and Push-on-main also passed.

**Scope boundary:** This proves the automated theme and browser contract. It does not claim rendered acceptance on the owner's actual PC or physical Android device. No readiness score uplift.

**Loop prevention:** When a dashboard token alias is defined in both a generic and theme-qualified selector, assert the computed value under both themes. Review specificity before assuming a later source-order rule wins.



### CASE-ZAZU-0033

**Status:** CLOSED — exact-head Laravel, PostgreSQL, desktop/mobile browser and quality checks passed on source/test head `51a75eb1d06017211bd7808093d1863c930b6d71`.  
**Engineering diagnostic code:** ENG-DATA-003.  
**First observed:** 2026-11-20.  
**Closed:** 2026-11-21.  
**Target:** Existing-record offline updates accepted by the supported server domain handlers and command mutations that advance parent aggregate snapshots.

**Failure fingerprint:** Two paired devices bootstrap the same business record. Device B commits a newer version. Device A later pushes an update based on its older snapshot. Without a revision precondition, A's stale payload can replace B's newer values.

**Verified implementation:**
- Server-owned monotonic aggregate revisions and immutable mutation result revisions.
- Expected-revision checks under business/device/mutation/aggregate lock order; stale updates are rejected before domain mutation.
- Same-device causal successors are checked against the actual result revision of their predecessor.
- Conflict payloads preserve both local and authoritative remote state. Keep-local rebase uses a new immutable mutation ID; use-server restores the remote record.
- Parent snapshot revisions advance for quote acceptance, payment, purchase receipt, inventory movement and asset allocation; nested customer, Work, quote, invoice, purchasing, inventory and asset writes advance their parent aggregates.
- The browser records first-edit revisions and same-device predecessor links, and does not silently discard unresolved local edits.
- The final online writer inventory found a remaining mass-update bypass in primary-contact creation. That path now updates locked contact models individually, and a regression proves the parent Customer revision advances. The other identified bulk operations either update a tracked parent in the same transaction or operate on restore/protocol records outside pre-existing domain identities.

**Closure regression coverage:**
- Two paired devices: device B commits revision 2; device A's revision-1 mutation is rejected with revision-2 remote snapshot and its local payload preserved.
- Same-device two/three-edit causal chains, external write between edits, rejected first mutation followed by rebase and successor.
- New-ID keep-local rebase, immutable original mutation payload, use-server discard and retry idempotency.
- Online customer, Work, supplier, service/capability and asset revision advancement.
- Parent revision propagation for quote acceptance, payment, purchase receipt/inventory, inventory movement and asset allocation.
- Cross-business entity identity rejection and business/device-scoped receiver delivery.
- Primary-contact create/edit paths both preserve exactly one primary contact and advance Customer revision.

**Exact-head evidence — `51a75eb`:**
- Laravel: **634 passed / 1 skipped / 4,399 assertions**.
- PostgreSQL migration/recovery smoke: **PASS**.
- Offline browser: **59 desktop Chromium + 7 mobile-viewport + 1 phone-local Work/attachment/reconnect passed**.
- PHPMD, Zazu Quality, Composer security audit, secret scan, application and analysis checks: **PASS**. SonarCloud: **SKIPPED**, not counted.
- [Exact-head workflow](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44881431689).

**Disposition:** CASE-ZAZU-0033 / ENG-DATA-003 is CLOSED. The full offline sync workflow matrix is a separate OPEN V1 gate; closing this stale-write case does not certify every offline entity/operation or physical-device behavior.

**Commit trail:** revision metadata `009b7c4`; stale enforcement `d5d4098`; client queue/bootstrap `fd73afe`; conflict rebase `0533b60`; parent/nested tracking `1924588`; parent command revisions `08bef14`; push processor extraction `7a57c4c`; complexity corrections `7d07f06`, `3137b80`; later exact-head regressions and contact writer correction through `51a75eb`.


### CASE-ZAZU-0034

**Status:** CLOSED — correction and regression coverage passed on exact source head `559e66b87de8617f967b722b55b0fbfd9b116495`.  
**Engineering diagnostic code:** ENG-DATA-004.  
**First observed:** 2026-11-21.  
**Target:** `SyncProtocol::pull` → `SyncController::bootstrap` → `SyncBootstrapPayloadBuilder` → `public/zazu-offline.js::pull`.

**Failure fingerprint:** A receiver pulls a contiguous sequence of committed deliveries, refreshes from the ordinary bounded business bootstrap, then acknowledges the highest sequence. If the affected aggregate is outside a 500-record collection window or a 25-record selected-record window, the snapshot can omit it and the receiver still advances its cursor. Static name/creation ordering could also keep older records that changed online outside the bounded refresh.

**Classification:** F1/F7 — application data-reconciliation defect and delivery/bootstrap contract gap. The receiver's acknowledgement was stronger than the completeness guarantee of the snapshot it had persisted.

**Correction on main:**
- The client now sends the exact delivery sequences with its bootstrap request.
- The controller validates positive integer values and caps the request at 100 sequence values.
- `SyncBootstrapDeliveryTargetResolver` looks up only deliveries for the same business, destination device and business stream, then resolves primary and related aggregates.
- The builder includes those exact target aggregates beyond the normal bounded window, including command-side related records.
- Delivery-driven customer, Work, quote and preparation entries respect an explicit pinned pairing selection; they do not silently expand a restricted local workspace.
- The normal 500-record collections are selected by latest sync-identity update time (fallback to record `updated_at`) while preserving existing display order.
- The browser persists the snapshot before acknowledging the contiguous prefix. The worker cache advances to `zazu-static-v58` to ship the updated precached client.

**Regression coverage:** recent online supplier revision promotion; a supplier intentionally outside the 500-record window; forced inclusion for its intended receiver; non-recipient device isolation; cross-device quote → acceptance → invoice → payment reconciliation; purchase receipt → purchase-order/inventory reconciliation; and client pull → targeted bootstrap → acknowledge ordering.

**Verification:** Laravel 632 passed / 1 skipped / 4,388 assertions; the 500-record supplier promotion/forced inclusion and non-recipient isolation regressions passed. Desktop Chromium 59 passed (including targeted pull → bootstrap → acknowledgement), mobile viewport 7 passed, and the phone-local Work/attachment journey 1 passed. PostgreSQL recovery, PHPMD, Zazu Quality, Director Health, Push-on-main, secret scan and Composer audit passed. SonarCloud was skipped. This closes the bounded receiver omission defect; the wider sync matrix remains open.

**Loop prevention:** Never acknowledge a delivery batch solely because a generic bootstrap returned 200. The snapshot must demonstrably include all primary and related aggregates changed by the exact receiver delivery prefix. Do not remove collection bounds or broaden a device's data scope as a workaround.

### CASE-ZAZU-0035

**Status:** CLOSED — correction and regression tests passed on exact source head `559e66b87de8617f967b722b55b0fbfd9b116495`.  
**Engineering diagnostic code:** ENG-AUTH-001.  
**First observed:** 2026-11-21.  
**Target:** `SyncController::bootstrap` → `bootstrapPayload` → `SyncBootstrapPayloadBuilder`.

**Failure fingerprint:** Authentication middleware attaches a SyncDevice model to the request. If the device is revoked or its bearer-token hash is replaced after that lookup but before the bootstrap snapshot is generated, the old controller path could still return business data using the stale model. The old last-seen update also occurred before the builder acquired the business lock, unlike the canonical business → device → mutation lock order.

**Classification:** F1/F7 — authorization race and lock-order contract gap.

**Correction on main:**
- Bootstrap now starts a database transaction and acquires the business lock first.
- It re-reads the exact device row under the active scope and row lock.
- It compares the persisted token hash to the middleware-authenticated snapshot; a revoked device or replaced token receives HTTP 401.
- Last-seen activity is updated while the device row is locked.
- The authoritative bootstrap snapshot is built inside that same business/device lock boundary before the transaction releases.

**Regression coverage:** two stale-snapshot cases: revoke the device after authentication lookup, and replace the token hash after authentication lookup. Both must return 401 before a bootstrap payload is returned. Existing pull and acknowledgement revalidation regressions remain unchanged.

**Verification:** The Laravel stale-snapshot tests for revocation and bearer-token replacement passed within 632 passed / 1 skipped / 4,388 assertions. PostgreSQL recovery, desktop/mobile offline-browser acceptance, PHPMD, Zazu Quality, Director Health, Push-on-main, secret scan and Composer audit also passed on the same source head. SonarCloud was skipped.

**Loop prevention:** Bootstrap is an authenticated data-read endpoint and must have the same durable device-state revalidation as pull/acknowledge. Never rely solely on a middleware model snapshot for an operation that returns business-scoped offline data.


### CASE-ZAZU-0036

**Status:** CLOSED — test contract corrected on main; exact-head browser verification pending.  
**First observed:** 2026-11-21.  
**Target:** `e2e/pwa-offline.spec.js` — disconnected quote acceptance regression.

**Failure fingerprint:** The acceptance action queues a local mutation and dispatches `zazu:offline-state-updated`. The offline workspace listener then rebuilds the quote register, replacing the row and its detail panel. The test asserted a transient status node inside the old detail panel after that intentional render, so the locator no longer existed even though the queue operation and state transition had completed.

**Classification:** F2 — test assertion targeted ephemeral DOM rather than the durable post-render workflow contract.

**Correction:** Assert that the rebuilt local quote row shows `accepted`, then verify exactly one pending mutation and inspect the persisted quote, version, linked Work status and `quote_acceptance` queue payload. No application code or product behaviour was changed.

**Verification:** The corrected test and regression family must pass on the exact main commit before this case is fully accepted. The 59 other tests passed in the reported run; that does not substitute for the corrected test's execution.

**Loop prevention:** For offline actions that dispatch a state-updated event, assert durable state after the resulting render. Only assert transient status content when the UI explicitly preserves that node across render.


### CASE-ZAZU-0037

**Status:** CORRECTED — exact-head browser verification pending.  
**Engineering diagnostic code:** ENG-FIXTURE-007.  
**First observed:** 2026-11-21.  
**Target:** `e2e/pwa-offline.spec.js` — offline quote acceptance → invoice creation → reconnect identity reconciliation.

**Failure fingerprint:** The test created a new invoice with a runtime-generated local ID, but the mocked server acknowledgement reused the fixed local ID of a separate invoice loaded by the pairing bootstrap. The reconciler therefore attached the returned server identity to the seeded invoice. The final assertion observed the seeded invoice's absent `quote_local_id`, while the newly created invoice remained unacknowledged.

**Classification:** F2/F3 — browser test identity/fixture defect. The failed field was `invoiceQuoteLocalId` (expected the quote's local ID; actual `undefined`). Quote status/revision, linked Work status/revision, invoice server identity/revision on the incorrectly targeted seeded record, and queue drain all matched their assertions. No production defect was established.

**Correction:** Capture the actual `local_id` from the queued offline invoice mutation, use that same ID in the mocked acknowledgement, and inspect the same created invoice after reconciliation. Keep the pre-existing bootstrap invoice on its own fixed fixture identity. Restore the independent Work test's own invoice identity after isolating the variable scope. No production source changed.

**Verification:** Exact-head source/test commit `36a2f605481e3985b647dda710ead7bef157b70f`: offline browser **60 passed**, mobile viewport **7 passed**, phone-local attachment journey **1 passed**; Laravel **649 passed / 1 skipped / 4,456 assertions**; PostgreSQL migration/registration/backup/restore/private-file recovery **PASS**. Zazu Quality, Composer audit, secret scan, application and Actions/Python/JavaScript analysis passed. Documentation-head runs also passed Zazu Quality, Director Health, Director Contract Lint and Push-on-main. The corrected invoice reconciliation test passed without retry.

**Loop prevention:** A sync identity acknowledgement must reference the local ID carried by the mutation under test. Never reuse a bootstrap fixture's identity for a newly created local record. Keep fixture identities scoped to their individual test.


### CASE-ZAZU-0038

**Status:** CLOSED — exact-head browser verification passed.  
**Engineering diagnostic code:** ENG-CONTRACT-007.  
**First observed:** 2026-11-21.  
**Target:** `e2e/offline-session-transition.spec.js` — expired web session requires sign-in without erasing the trusted offline workspace.

**Failure fingerprint:** After clearing web cookies and navigating to `/dashboard`, the browser test required the URL to leave `/dashboard` within five seconds. On one run the service worker's four-second network fetch timeout used its cached-dashboard fallback, leaving the URL unchanged; the test failed before reaching its existing auth-modal assertion. The same test passed on retry.

**Classification:** F2/F8 — verification contract mismatch with a non-deterministic service-worker navigation fallback. The worker intentionally preserves the cached app shell when the network request times out. The security invariant is that the sign-in gate is visible and the user must authenticate before continuing; the URL alone is not a reliable indicator when the cached route is retained. No application defect was established.

**Correction:** Removed the URL-only redirect assertion. The test still requires the sign-in modal, performs a fresh login, and verifies that the trusted paired workspace and queued local mutation survive.

**Verification:** Exact source/test head `b26df40e150fc3b81425db81fe9cb16040970547`: offline browser **61 desktop Chromium + 7 mobile viewport + 1 phone-local attachment passed with no flaky retries**; Laravel **652 passed / 1 skipped / 4,487 assertions**; PostgreSQL migration/recovery, Zazu Quality and Push-on-main passed.

**Loop prevention:** For service-worker-controlled routes, assert the authentication gate and preserved local state, not a URL transition that can legitimately differ during cached-shell fallback.


### CASE-ZAZU-0036

**Status:** CLOSED — exact-head Laravel, PHPMD and Psalm verification passed on `7d4b097`.

**Target:** Sync conflict listing/resolution, private attachment upload/list/download and `/api/sync/revoke`.

**Failure fingerprint:** Middleware authenticated a device and attached an Eloquent model snapshot. A later revocation or re-pair could change the durable device row before the endpoint completed. Conflict listing/resolution and attachment paths did not consistently re-read the device under the same transaction lock as their work. The revoke endpoint also acted on device ID without comparing the authenticated token hash to the current persisted hash.

**Control implemented:** A shared guard locks the business then device row, rechecks active status and compares the authenticated token hash with the persisted credential. Sensitive operations invoke it inside their transaction. A stale credential receives 401 and cannot revoke a replacement credential.

**Regression coverage added:** revoked-snapshot conflict listing, conflict resolution, attachment upload, attachment listing, and stale-token attempt to revoke a replacement credential.

**Verification boundary:** tests are committed but no exact-head pass is claimed yet. Bootstrap, pull and acknowledgement already had separate stale-snapshot controls and remain intact.


### CASE-ZAZU-0037

**Status:** CLOSED — exact-head desktop/mobile browser acceptance passed. Physical Android operational acceptance remains an independent V1 release gate.

**Target:** Installed PWA while the phone remains connected to Wi-Fi but the Zazu LAN host becomes unavailable.

**Failure fingerprint:** navigator.onLine remains true when the Wi-Fi interface is connected, even if the LAN host has stopped. The application previously rechecked HEAD /up only when selected UI render/event paths ran. A currently visible server-rendered page could therefore remain stale without a browser offline event.

**Control implemented:** The offline directory runtime now checks HEAD /up every 10 seconds and on focus/network/visibility changes. It shows a non-destructive host-outage notice with an explicit link to the cached dashboard workspace and updates local-workspace visibility from actual origin reachability. It does not auto-navigate or discard form input.

**Regression coverage:** Playwright simulates /up returning 503 while navigator.onLine remains true, then restoring 200. The test runs on desktop Chromium and Pixel 7 viewport.

**Closure evidence:** exact-head workflow [44934728827](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44934728827) passed 63 desktop Chromium tests, 7 mobile-viewport Work/customer tests, the Pixel 7 host-outage regression and the phone-local attachment/reconnect journey without failures or flaky retries. Laravel passed 670 / 1 skipped / 4,552 assertions; PostgreSQL migration/recovery smoke passed.

**Remaining release gate:** actual installed Android host stop, force-close/restart, disconnected operation and reconnect must still be observed on the owner's physical phone. Browser viewport evidence does not close that gate.


### CASE-ZAZU-0038

**Status:** CLOSED — exact-head automated verification passed; owner-operated installation recovery remains an independent V1 gate.

**Failure fingerprint:** Backup allowed 50,000 private files plus the database and manifest (50,002 ZIP entries), but restore rejected any archive above 50,000 entries. Backup also did not enforce the restore command's 2 GiB uncompressed-size ceiling. A large backup could therefore be published but rejected by the matching restore command.

**Control implemented:** Backup and restore now use the same shared limits: 50,000 private files, 50,002 total ZIP entries, and 2 GiB total uncompressed content. Backup checks staged payload sizes and reopens the complete ZIP to verify the total including manifest before publishing.

**Regression coverage:** boundary tests cover 50,000/50,001 private files, 50,002/50,003 ZIP entries and exact/over-limit uncompressed sizes.

**Closure evidence:** exact-head run [44937037910](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44937037910) passed Laravel **673 / 1 skipped / 4,575 assertions**, including the boundary tests; PostgreSQL migration/recovery smoke, PHPMD and Zazu Quality passed. The owner-operated off-disk backup and full installation restore drill remains a release gate.


### CASE-ZAZU-0039

**Status:** CLOSED — exact-head SQLite backup/restore integration regression passed; owner-operated installation recovery remains a separate V1 gate.

**Failure fingerprint:** A valid v2 backup always declares database and private storage. ZIP does not store empty directories by default. When the source private directory was empty, the archive had no storage/private entries; restore inferred private-storage presence only from payload entries and therefore left the target installation's old private files untouched.

**Control implemented:** v2 restore now requires the declared contents list to include database and private_storage. The declaration controls the private-storage swap even when no payload files exist. Restore creates an empty staging directory and replaces the old private tree, preserving rollback behaviour.

**Regression coverage:** create a valid backup with empty private storage, add a stale private file after backup, restore, and assert the database returns to its snapshot while the stale file is removed and the private directory is empty.

**Closure evidence:** exact-head run [44937037910](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44937037910) passed Laravel **673 / 1 skipped / 4,575 assertions**, including the empty-private-storage backup/restore integration. PostgreSQL migration/recovery smoke, PHPMD and Zazu Quality passed. Owner-operated full restore and media verification remain mandatory.


### CASE-ZAZU-0040

**Status:** CLOSED — exact-head adversarial Laravel regression, static analysis, security checks and offline browser acceptance passed on 04db872.

**Failure fingerprint:** Sync push recorded the mutation under the middleware-authenticated device credential. The applier then started a separate transaction and checked only that the device ID was still active. Conflict recording also locked the device row without comparing the middleware token hash. If the device was re-paired between these writes, a stale request could apply its mutation or create conflict state against the replacement credential.

**Control implemented:** The push processor calls the explicit authenticated applier, which verifies mutation business/device ownership and uses the shared business-first/device-row credential guard inside the domain-application transaction. Conflict recording, bootstrap, pull and acknowledgement use the same guard. The internal applier path is trusted/internal and is not used by HTTP push.

**Regression coverage:** record with the original credential, replace the stored credential, attempt apply with the stale authenticated snapshot, and assert 401, pending mutation status, zero attempts and no delivery; separately attempt conflict recording with a replaced credential and assert 401 with no conflict row. Existing pull/ack/bootstrap revocation regressions remain green.

**Verification:** exact-head Laravel **675 passed / 1 skipped / 4,581 assertions**, PostgreSQL migration/recovery smoke, offline browser **63 desktop + 7 mobile + 1 host-outage + 1 phone-local attachment/reconnect passed with no flaky retries**, PHPMD, Psalm, Zazu Quality, Director Health, populated upgrade/rollback, Windows V1 package/fresh install and Push-on-main passed on 04db872. SonarCloud skipped. No exploit was demonstrated in a running installation; this was a source-level TOCTOU path closed with adversarial regressions.

### CASE-ZAZU-0041

**Status:** CLOSED — exact-head Laravel, PHPMD and PostgreSQL migration/recovery checks passed on `e95dd5c`.

**Target:** `OfflineDomainMutationHandler::customer`, `event` and `capability`.

**Failure fingerprint:** An authenticated trusted device could submit an unsupported operation such as `delete` for a customer, job or service. These handlers did not enforce the operation allowlists already present in the other entity handlers. Their create/update logic could therefore process the payload instead of rejecting the unsupported command.

**Root cause:** Operation validation was inconsistent across entity handlers. The server trusted the mutation operation contract for three handlers rather than enforcing it at the domain boundary.

**Correction on `main`:**
- Added explicit `create`, `upsert` and `update` allowlists to customer, job and service handlers.
- Unsupported operations now raise a field-level validation error before payload processing or domain writes.
- Expanded `OfflineMutationOperationValidationTest` to submit an unsupported `delete` operation to all 17 registered offline entity handlers. Every mutation remains pending with zero attempts, and all 15 entity-record tables remain empty.

**Verification:** Expanded all-17-handler regression: Laravel `676 passed / 1 skipped / 4,647 assertions`; PHPMD PASS; Zazu Quality PASS; PostgreSQL migration/recovery PASS on [test head 0e619e9](https://github.com/Leano-Jordan/ZazuEMP/commit/0e619e9e3675d8ad67a5a1fe8d1b8daf23b4af79). Browser acceptance **63 desktop + 7 mobile viewport + 1 mobile host-outage + 1 phone-local attachment/reconnect passed** on the runtime-identical implementation head [e95dd5c / workflow 44947482747](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44947482747). The repeat browser job on test-only head `0e619e9` was still running at this update.

**Scope:** This closes the unsupported-operation acceptance defect for these three handlers. It does not implement deletion support; delete remains unsupported. The broader offline sync matrix remains OPEN.
