# Zazu EMP — Current Engineering State

**Last reconciled:** 2026-11-26
**Repository:** Leano-Jordan/ZazuEMP
**Canonical branch / write target:** `main` only
**Previous application acceptance checkpoint:** `a3ec1523ccdf2f1b6ff92f4e1fcf62ba1914877d` (earlier Laravel, coverage and PostgreSQL evidence; it is not the current UI candidate).  
**Current main candidate:** `66f7392aeb7f187c2b6bd3acb45b96dc6a56cce9` (offline workspace responsive-header correction; current-head CI reconciliation is recorded below).
**Release verdict:** NOT READY
**Formal evidence-maturity score:** 71/100 (baseline retained; no uplift without a formal domain-by-domain reassessment)

This is the concise live state register, not a session diary. Detailed incident evidence belongs in FAILURE_CASES.md, durable decisions in DECISION_LOG.md, recurring regressions in REGRESSION_LEDGER.md, and historical implementation evidence in dated audit/release records.

## Product identity and scope

Zazu is an **event management platform**, not catering software. The event is the centre of the operating model. Business capabilities describe services and resources an operator brings to an event; catering, equipment hire, sound/AV, décor, rentals, baking and photography are examples, not product boundaries. The strategic direction is to help plan, coordinate and carry out different event formats. V1 claims must remain limited to workflows actually implemented and accepted.

The current Laravel modular monolith, event/job-centred records, business isolation, PWA/service-worker shell, IndexedDB workspace, durable mutation queue, phone-local attachments, sync, Windows packaging and recovery architecture are to be preserved. No rewrite or speculative module expansion.

## Non-negotiable operating constraints

- main is the only write target. Never create branches or mutate other refs.
- Zazu does not use Render or AMPPS; do not reintroduce their deployment paths.
- Never reset, clear, replace or modify business data or verified backup files during engineering.
- Never perform an irreversible restore without explicit authority.
- Keep Dashboard free of phone pairing UI. Owner-controlled pairing remains in Settings → Phone sync.
- Automated browser/offline viewport checks do not count as physical Android acceptance.
- Tests are evidence, not a progress metric. Never weaken tests to obtain a pass.
- DB-001 is closed as an historical, unreproduced test-time anomaly. Do not reopen it without recurrence on current main or materially new evidence.

## Latest verified automated evidence

- Previous exact-head source/test baseline: `a3ec1523ccdf2f1b6ff92f4e1fcf62ba1914877d`.
- Current candidate `66f7392aeb7f187c2b6bd3acb45b96dc6a56cce9` (2026-11-26): Laravel workflow `45779103238` — **765 passed, 1 skipped, 5,464 assertions**; coverage report PASS; PostgreSQL migration/recovery smoke PASS. Exact-head offline browser acceptance subsequently completed PASS: 74 Chromium offline journeys passed; 11 mobile-viewport checks passed; the separate mobile stopped-host check passed; and the mobile phone-local attachment round-trip passed. The new 390×844 offline-header geometry assertion ran inside the 74-test offline suite. This is browser evidence, not a physical Android visual sign-off.
- Current Zazu Quality workflow `45779103388`: application checks PASS (npm dependency audit, docs validation, production frontend build, compressed asset budgets and service-worker authentication-cache tests), formatting PASS and secret scan PASS. The separate Composer security-audit job FAILED on five Symfony advisories; see the open security item below. SonarCloud remains skipped, not passed.
- Current CodeQL push workflow `45779103097`: Actions, JavaScript/TypeScript and Python analysis jobs PASS.
- Laravel workflow `45700142579`: application tests **761 passed, 1 skipped, 5,445 assertions**; coverage report and PostgreSQL migration/recovery smoke passed. The offline browser job `139507818283` is still in progress, so the complete Laravel workflow is not yet a pass.
- Latest completed offline browser baseline remains run `45651931394` on `f6a9b3c`: **93 journeys passed**, with no failed or skipped journeys. This previously closed the automated internal-navigation defect for offline Customers/Jobs/Quotes. Neither browser run certifies physical Android use. The latest exact-head result is recorded above.
- Windows V1 package `45700142740` on current head: candidate build, encrypted and legacy business-archive checks, and Windows PowerShell fresh-install/launcher verification all passed. The automated Windows installation check is not the intended-PC owner recovery drill.
- Current-head Zazu Quality `45700142698`, PHPMD `45700142645`, Psalm security scan `45700142619`, populated upgrade/rollback `45700142684`, Director Health `45700142739`, and Push-on-main `45700142312` passed. SonarCloud `45700142666` was skipped, not passed.
- Business archive engineering now streams attachment payloads from local storage into ZIP entries and streams restored payloads through private 0600 temporary files before storage writes. Restore rollback registers destinations before streaming; cleanup continues after a delete failure while preserving the original restore exception. Failure logs now include safe runtime/upload/temp-storage diagnostics without passphrases, archive contents, paths or exception messages.
- Automated encrypted/legacy archive and rollback tests pass on the current head, but synthetic fixtures do not reproduce the Founder's original archive or establish its first failing stage.

## Confirmed open findings and release gates

### 1. Physical Android offline acceptance — OPEN

Automated offline navigation is now passing. The owner must still accept disconnected cold launch, route isolation, local writes, force-close/restart, reopening phone-local attachments and reconnect reconciliation on the actual Android device. Connected PC/phone operation previously passed; do not repeat that setup.

### 2. Encrypted business archive restore — owner test failed; root stage UNKNOWN

On 2026-11-23 the Founder verified an installation backup and a business archive. The encrypted business archive was attempted in a disposable empty workspace and failed with “archive failed to load”; a later SYS-002/429 was rate limiting, not the proven cause. The first failing stage remains unknown. Preserve the original archive, passphrase and disposable target. Never retry against the live business or weaken the three-attempt throttle. The endpoint is capped at 100 MB upload and the expanded archive at 250 MB; the user interface states both limits. No evidence currently proves either limit caused the failure. Investigate with the original incident-stage logs or safe reproducible fixtures.

The encrypted path uses Sodium secretstream XChaCha20-Poly1305 with Argon2id, private temporary staging, ZIP/manifest allowlisting, business ownership validation and transactional restore. Automated encrypted/legacy archive tests and Windows archive checks pass; these do not reproduce the owner's real archive.

### 3. Installation-wide recovery — OPEN

Automated backup/restore, rollback, PostgreSQL recovery smoke, Windows packaging and fresh-install checks pass. A controlled full installation recovery drill on the intended owner PC remains mandatory and unaccepted.

### 4. Security, commercial and legal gates

- DEC-019 remains a documented **HIGH** trusted-device shared-profile risk: anyone with access to an owner-approved shared browser profile can inherit its approved offline workspace/mutation capability. Do not claim per-staff offline identity on that trust model.
- Populated quote-to-payment-to-actual-cost/profit reconciliation remains open.
- POPIA operational controls and ZAZU name clearance remain open.
- **New dependency-security finding — OPEN:** Composer audit on candidate `66f7392` reports five Symfony advisories. Locked packages: `symfony/http-kernel v7.4.23`, `symfony/routing v7.4.23`, and `symfony/mailer v8.0.15`. Reported CVEs: CVE-2026-133743; CVE-2026-133746; CVE-2026-133751; CVE-2026-133754; CVE-2026-133755. The Zazu Quality Composer audit therefore remains FAILED even though its other listed checks passed. Resolve through a supported Composer dependency update and full regression; do not suppress advisories or count the quality workflow as green.
- PHPMD/Psalm SARIF publication still fails; analysis steps ran, but do not claim published results. SonarCloud is skipped.
- No independent penetration test has been completed; automated scanners and source review are not a penetration test.

## Cleanup completed on main

- Removed the retired Render/Docker deployment stack and its Docker-only Hadolint workflow.
- Removed an unused legacy offline-attachments script, two generated placeholder tests, a stale Claude handover file and an auto-downloading/self-committing vendor media workflow.
- Reduced STATE.md from approximately 331 KB / 2,500 lines to this concise evidence-led register. Historical audit/decision/failure records remain preserved and are explicitly identified as historical.
- Reconciled Zazu product identity across README, formal product specification, Composer metadata, manifest, app footer, Director mandate and chat bootstrap.
- Removed contradictory DB-001 active-blocker guidance and stale Morpheus role hierarchy from operating bootstraps.
- The existing deploy/render-staging ref is preserved as read-only historical state; no non-main ref was modified and no branch was created.

## Execution order

1. Forensically isolate the real encrypted business-archive restore failure without touching the live business or original verified backup.
2. Continue targeted adversarial tenant/session/device/file and business-rule tests; resolve SARIF publishing configuration so completed analysis reaches GitHub code scanning.
3. Complete installation recovery key-custody review and owner-PC drill when the Founder can accept it.
4. Reconcile the populated commercial workflow.
5. Prepare POPIA and ZAZU name-clearance decisions for Founder acceptance.

Physical-device, owner-PC recovery, populated-business and legal/brand acceptance remain separate Founder gates. Continue source, test, documentation and CI work that does not require those actions.

## Founder physical offline observation — 2026-11-26 (new evidence)

The Founder reports that with the Zazu PC host OFF, the Android phone successfully opens offline workspace pages and those pages render locally available business data. This is a material improvement over the previously observed frozen last-online Dashboard / all-sections-on-one-page failure. The offline-state banner is visible and communicates that the server is unavailable, locally saved records remain on the device, and only supported offline screens are available. The Founder reports that the Settings navigation is absent and the rendered UI still needs refinement.

**Evidence classification:** OWNER-OBSERVED PARTIAL PASS for phone-side offline route/page rendering with local data while the PC host is off. This is not a full offline acceptance pass. The PC being off is explicitly recorded; do not ask the Founder to repeat that condition or basic connected PC/mobile setup.

**Remaining engineering work:** inspect and correct supported offline navigation (including the Settings destination where its functions are supported offline), verify route-specific page content rather than merely page shells, and refine visual composition without replacing the existing UI. Preserve the existing offline-state banner and its honest limitations. Continue to verify local persistence/restart, supported local mutations, attachment access and reconnect reconciliation through existing automated evidence and the remaining targeted physical checks.

**Owner interaction rule:** treat this observation as durable acceptance evidence. Do not ask the Founder again whether offline pages load, whether they contain local data, or whether the PC was on. Ask for another physical check only when engineering has reached a new, specific acceptance step that cannot be established through source inspection or automation; state exactly what new evidence is needed and why. Never repeat a previously completed setup or test.

**Gate status:** physical Android offline acceptance remains OPEN pending the remaining distinct checks; the prior all-pages-collapse/frozen-dashboard symptom is no longer the current owner-reported behaviour. UI/navigation refinement is now the active engineering target, not restarting the offline architecture.

## Founder mobile screenshot review and responsive-header regression — 2026-11-26

The Founder supplied two further Android screenshots (09:48 and 09:49) showing the current offline workspace. Visible local-state counters are **13 saved records, 0 waiting to sync and 0 needing attention**. The offline-state banner remains visible. The launchpad heading and description are compressed into a narrow left column while the eight module navigation actions occupy the right side of the same header, creating excessive vertical height and poor readability.

**Confirmed source mechanism:** the offline-navigation correction at `f6a9b3c5` made the permission-filtered module links visible again, but the offline header still inherited the shared flex-row composition. The generic `.zazu-action-group` becomes `width: 100%` at mobile breakpoints while its parent header remains a flex row, allowing the navigation to consume the row and shrink the heading. The earlier browser contract asserted navigation visibility but did not measure mobile heading/navigation geometry.

**Implemented correction on main:**
- `52152f2f6770a10377c59d28cdc376fa8baa76d3` — add an offline-only responsive grid to the existing dashboard authority stylesheet; phone layout stacks introduction, two-column module actions and status, with one-column actions on very narrow screens.
- `20e313ef1ade96071ceee45c9afa26e56bc20e3a` — add offline-only scope classes to the existing Blade shell.
- `66f7392aeb7f187c2b6bd3acb45b96dc6a56cce9` — extend the offline browser contract to assert the heading and navigation align full-width and stack correctly at 390×844.

This is **IMPLEMENTED IN SOURCE and AUTOMATED-BROWSER-VERIFIED** on candidate source/test commit 66f7392 (74 Chromium offline journeys passed, including the 390×844 geometry assertion; separate mobile host-outage and attachment checks passed). No post-fix Android screenshot or physical visual acceptance is claimed.

**Settings navigation boundary:** source inspection confirms the offline route allowlist in `resources/js/app.js` includes only route families with an implemented local view. `/settings` is not mapped, so its unsupported sidebar destination and the otherwise-empty System group are hidden while disconnected. Pairing, owner administration and server-backed business settings remain online-only under the current contract. Do not restore a generic Settings link merely to make it visible; only expose a specific Settings function after its local data/behaviour is implemented and tested. The shell theme toggle remains independently available.

**Owner-test rule:** do not ask the Founder to repeat the already-established offline page/data/host condition. After current-head browser CI passed, the only new physical confirmation to request is the corrected offline launchpad layout at phone width while the server remains unavailable. Persistence, airplane-mode cold launch, restart, attachments and reconnect remain separate open checks.
