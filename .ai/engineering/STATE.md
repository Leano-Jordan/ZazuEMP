# ZAZU EMP — ENGINEERING CONTROL STATE

## Identity
- Product: Zazu – Event Management Platform
- Repository: Leano-Jordan/ZazuEMP
- Canonical branch: main
- Master control: Morpheus / Director
- Owner-facing nickname: Jarvis

## Authority
1. Explicit owner instruction
2. Current repository state
3. Current living documentation / ledgers
4. Verified runtime / CI evidence
5. Verified external research
6. Historical records / chat memory

## Canonical control contract
`.ai/engineering/00_DIRECTOR_ENGINE.md` is the single canonical Director contract.
`.ai/engineering/00_ENGINE_ROUTER.md` is a compatibility pointer only.
Entry files must point to the canonical contract and must not define a competing read order.

## Control loop
IDENTITY → BASELINE → TARGET → ROUTE → INSPECT → CHANGE → VERIFY → BREAK → RECONCILE → ACCEPT/REPAIR → RECORD → NEXT

## Historical local-PC hosting investigation — superseded 2026-11-03

The 2026-11-01 local Windows-host proposal was exploratory and is no longer the active deployment target. The owner has clarified that this PC is a development workstation; future production hosting will be a paid server when funding and release readiness permit. The 2026-11-03 host diagnostic is recorded below. Do not configure this PC as production infrastructure.

## Active target
**Owner-directed V1 convergence:** repair source defects, keep exact-main CI green, complete the remaining offline/device and commercial evidence, and remove every operational task that can be safely verified or automated from code. The owner-accepted dashboard remains closed. All implementation stays on canonical `main`.

## Current verified source — 2026-11-08

- Verified application/test source: `d44ec33a57a4958380f2cfe4624f040859cbcf7a` on canonical `main`; CI workflow correction commit: `7946a87e78cf4dd2305d12fb20d3f77ed5653c87` (workflow-only; no application source changes).
- Exact-head workflow evidence on that workflow commit: Laravel run [42481998972](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/42481998972) PASS; Quality + secret scan [42481999066](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/42481999066) PASS; browser smoke [42481998954](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/42481998954) PASS; populated upgrade/rollback [42481998959](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/42481998959) PASS; Director Contract Lint [42481998956](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/42481998956) PASS; Psalm [42481998947](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/42481998947) PASS; PHPMD [42481998949](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/42481998949) PASS. SonarCloud [42481998971](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/42481998971) SKIPPED, not a pass.
- The quality workflow now uses the same complete Laravel bootstrap as the dedicated Laravel workflow. The corrected run reports 407 passing tests / 2,758 assertions; browser smoke reports 65 passed / 22 skipped / 0 failed.
- Laravel: **407 tests / 2,758 assertions — PASS**.
- Zazu quality + secret scan: PASS.
- Psalm security scan: PASS.
- PHPMD: PASS.
- Populated upgrade/rollback: PASS.
- Browser smoke: **65 passed / 22 skipped / 0 failed — PASS**.
- Director health report: PASS.
- SonarCloud: skipped, not a pass.
- Business export: 23 owner-only, business-scoped CSV datasets; streaming, audit logged and formula-injection protected.
- Global document search: filename/description/MIME type and linked job metadata search; business-scoped, permission-aware, no private storage path disclosure.
- Offline Work attachments remain integrated into active Work; phone-local persistence/reconnect logic is automated-tested, but the installed-device disconnect/restart/reconnect sequence remains physically unverified.
- The current application/test source has a fully green automated verification batch. The release remains NO-GO pending installed-device offline acceptance, representative operator backup/restore and deployment rollback drills, operational privacy/legal closure and official Zazu brand clearance.
- Commercial follow-on gaps are explicitly tiered: product/service and supplier spreadsheet import for the R499 bridge; event/work staff assignment and scheduled reminders for the R799 team tier.
- Canonical release ledger: `docs/V1_RELEASE_CHECKLIST.md`. Never repeat a CI status from an older SHA as current evidence.

## Index-only dashboard refinement — 2026-11-06

Owner clarified that the authenticated index/dashboard must be refined alone until visual approval; no rollout to other application pages is authorized.

- Reworked the dashboard welcome hierarchy to match Designer (2): greeting, owner name, prominent lead line and secondary supporting line; removed the extra uppercase kicker.
- Separated quick actions from the enclosing panel so they render as individual dashboard actions rather than a boxed toolbar.
- Increased KPI label/supporting-text readability while retaining the four live, permission-aware business metrics.
- Inset the business-capability callout within the welcome banner and preserved the optional business-owned artwork hook.
- Kept the two-column run-sheet / quote-pipeline row and priority / operational-readiness row.
- Dashboard-only selectors remain prefixed `.zazu-dash-*`; shared shell, navigation, global tokens and other page views were not intentionally changed.
- Changed files: `resources/views/dashboard.blade.php`, `resources/css/zazu-final-visual-sweep.css`.
- Latest source commit at record time: `58da15307477eb49a8adb2655585a1102735acda` (main).
- Verification boundary: source re-read; current-head browser smoke and Laravel/quality workflows were still running at record time. Earlier quality runs exposed stale assertions for superseded shell palette/structure and a pre-existing PHPMD complexity threshold; those are not visual acceptance evidence.
- Rendered desktop/mobile visual acceptance remains **UNVERIFIED**. Owner review is the design acceptance gate. Do not propagate styling to other pages before explicit approval.


## Dashboard quick-action refinement — 2026-11-06

- Compared the current dashboard source with the supplied Designer (1) and Designer (2) references.
- Finding: quick actions were plain text links with trailing symbols; the references use compact icon-led actions with a clear title and one-line task description.
- Updated the four existing permission-gated actions (Create event, Create quote, Add customer, Open calendar) with inline SVG icons and concise supporting copy. Existing destinations and permission checks are unchanged.
- Updated the existing dashboard quick-action CSS owner in place: 40px icon tiles, consistent two-line copy, stronger primary action contrast, and 34px icon tiles at mobile widths. No new global tokens, shared-shell rules, routes, or page styles were added.
- Added a focused source regression test for action labels, supporting copy, icon treatment and mobile sizing.
- Changed files: `resources/views/dashboard.blade.php`, `resources/css/zazu-final-visual-sweep.css`, `tests/Feature/UiProportionFoundationTest.php`.
- Commits: `107377e` (view), `845bb39` (CSS), `7563404` (test).
- Re-read all three changed files from current `main`; source integrity checks passed. Automated test execution and rendered desktop/mobile acceptance remain **UNVERIFIED**. No styling rollout to other pages is authorized.


## Dashboard priority and workspace health panels — 2026-11-06

- Added the reference-style icon-led Priority Now header and a concise attention-focused subtitle.
- Reframed the lower-right panel as Workspace Health with an icon-led header and four permission-gated resource cards for open purchase orders, low stock, available assets and unpaid invoices.
- Kept each card tied to its real dashboard metric and existing destination. Status dots and supporting copy are derived from the actual metric values; no unverified claims about connectivity or system uptime were introduced.
- Added responsive card styling and regression coverage for truthful zero/non-zero states, metric bindings and route preservation.
- Changed files: `resources/views/dashboard.blade.php`, `resources/css/zazu-final-visual-sweep.css`, `tests/Feature/UiProportionFoundationTest.php`.
- Commits: `1a2bcbc` (view), `bdf077d` (CSS), `0c58b35` (test), `de600fb` (test literal correction).
- Re-read current-main source; markup, metrics, routes, responsive CSS and regression-test presence checks pass. Automated test execution and rendered desktop/mobile acceptance remain **UNVERIFIED**. Dashboard-only scope remains in force.



## Dashboard events and quote panels — 2026-11-06

- Reworked the Upcoming Events and Quote Pipeline panel headers to match the supplied reference hierarchy: compact icon tile, clear heading, supporting sentence and contextual action link.
- Replaced the event timeline's narrow date/time column and connector line with a dedicated day / month / year date tile, event identity block, status and trailing navigation cue. Existing event links and status values remain intact.
- Refined quote stage cards with clearer status labels, count emphasis and status-colour markers. Kept the actual persisted workflow semantics: draft, sent, accepted (presented as Approved), declined and expired (combined for the closed bucket). No unsupported "under review" state or count was fabricated.
- Added responsive panel-header, event-card and two-column quote-stage treatments down to narrow mobile widths.
- Added focused source regression coverage for the new panel hierarchy, date tile, status mapping, route preservation and responsive rules.
- Changed files: `resources/views/dashboard.blade.php`, `resources/css/zazu-final-visual-sweep.css`, `tests/Feature/UiProportionFoundationTest.php`.
- Commits: `86be043` (view), `de9f824` (CSS), `c7eb8fe` (test), `3dc8d11` (test literal correction).
- Re-read current-main source and checked required markup, status-count expressions, routes, responsive rules and regression-test presence. Source checks pass. Automated test execution and rendered desktop/mobile acceptance remain **UNVERIFIED**. Dashboard-only scope remains in force; no styling rollout to other pages.



## Shared shell topbar correction — 2026-11-06

- Source audit found a later `.zazu-topbar` rule overriding the earlier near-white workspace surface with 88% sidebar colour. This was a direct conflict with the intended light workspace / blue sidebar composition.
- Corrected the effective topbar rule to a near-white surface/page blend, with a restrained turquoise-tinted lower border. Sidebar colour remains owned by the sidebar only.
- Added a regression test against the effective final topbar rule to prevent sidebar colour bleed.
- Commit: 54391382052ac28e0da5fae834cf881c75a12b70.
- Source verification and automated test execution: pending; rendered review remains with owner.

## Coastal dashboard visual treatment — 2026-11-06

- Refined the existing dashboard hero into a visible sky-blue → sea-mist/turquoise → warm-sunlight wash using shared theme tokens.
- Gave the existing capability side panel its own subtle blue/turquoise surface, preserving the current content and responsive stacking.
- Dashboard operational cards now use a consistent coastal-tinted border and cleaner radius; section headers receive a soft blue-to-white wash.
- KPI tiles use more visible coastal semantic tints; the former green KPI tint now uses the existing turquoise accent family.
- Event hover uses a restrained blue wash. No markup, data, routes, permission checks or information architecture changed.
- Dark theme continues to derive these treatments from its own dark token values.
- Regression assertions added for the hero, capability panel and KPI surface treatment.
- Commit: 4751d58b89bbb1fc612afe94f6dabd3fc815140b.
- PHPUnit and rendered desktop/mobile visual acceptance remain UNVERIFIED.

## Bright Coastal Blue palette implementation — 2026-11-06

- Owner reaffirmed the sunny beach-day Coastal Blue direction; references remain inspiration, not literal templates.
- Replaced the muted blue-slate light palette at the canonical `:root` token source with a visibly brighter sky/ocean/turquoise system.
- Light canvas: cloud white `#F5FBFE`; sea mist `#DDF4FC`; sidebar sky blue `#8ED8F8`; ocean primary `#159DD2`; vivid ocean accent `#35B9E8`; turquoise `#49C7B8`.
- Shifted supporting text, borders, hover/active navigation and surface tokens to harmonise with the brighter palette. Existing dark theme token block was left untouched.
- All shared components inherit the new palette through canonical tokens; no new CSS override layer or structural changes.
- Updated canonical palette regression assertions.
- Commit: 500f1ba9d843044a78737c72be677b08bacc661c.
- Automated tests and rendered desktop/mobile visual acceptance remain UNVERIFIED.

## KPI scan-order refinement — 2026-11-06

- Ordered the four dashboard KPIs as Active Events → Draft Quotes → Customers → Next 14 Days, matching the primary reference's business-first scan path while retaining an honest scheduled-work metric instead of inventing revenue.
- Added a source regression assertion for the order.
- Runtime and rendered visual acceptance remain UNVERIFIED.

## Quote pipeline reference refinement — 2026-11-06

- Replaced the single draft-quote count presentation with a four-stage dashboard breakdown matching the supplied reference's compact pipeline treatment.
- Counts are queried from business-scoped Quote records grouped by their actual lifecycle status.
- Displayed stages: Draft, Sent, Accepted, and Closed (Declined + Expired). The lifecycle vocabulary comes from Quote::STATUS_TRANSITIONS; no unsupported "Under review" state was invented.
- Retained the draft queue warning/clear state and existing Quotes/Finance actions.
- Added responsive four-column desktop and two-column mobile styling plus source regression coverage.
- Changed files: app/Http/Controllers/DashboardController.php, resources/views/dashboard.blade.php, resources/css/zazu-final-visual-sweep.css, tests/Feature/UiProportionFoundationTest.php.
- Runtime query execution and rendered acceptance remain UNVERIFIED.

## Workspace health panel refinement — 2026-11-06

- Reconciled the lower-right panel against Designer 2: renamed Resource Readiness to Workspace Health with Operational Readiness as its heading.
- Kept the panel honest: its tiles remain business-scoped live counts (open purchase orders, low stock, available assets, unpaid invoices), not fabricated network, finance-connection or platform-health statuses.
- Changed the desktop panel to four compact tiles and mobile to two columns, collapsing to one column on very narrow screens.
- Preserved the existing permission-gated links and experience-level visibility.
- Added regression coverage for the reference-aligned heading, live-count framing and responsive tile layout.
- Changed files: resources/views/dashboard.blade.php, resources/css/zazu-final-visual-sweep.css, tests/Feature/UiProportionFoundationTest.php.
- Runtime and rendered acceptance remain UNVERIFIED; source assertions alone do not certify the visual result.

## Dashboard reference alignment — 2026-11-06

- Inspected both supplied dashboard references and current main source before changing the UI.
- Current Dashboard already had the KPI strip → quick actions → paired operational queues → paired lower panels grid foundation. The missing reference alignment was chiefly the welcome composition and KPI/action ordering, not a reason to rewrite the whole dashboard.
- Replaced the oversized generic operational hero with a user greeting and a business-capabilities callout. The optional business-owned dashboard image remains supported as restrained decorative artwork.
- Prioritised Draft Quotes in the KPI strip when the Quotes workspace is available; unpaid invoices remain the fallback when Quotes is unavailable and Finance is permitted.
- Reordered quick actions to Create Event → Create Quote → Add Customer → Open Calendar, each retaining existing permission checks and routes.
- Renamed the operational panels to Upcoming Events and Quote Pipeline. Existing Priority Now and Resource Readiness panels remain data-backed; no fabricated Workspace Health or revenue values were introduced.
- Preserved the existing two-column desktop composition and single-column responsive collapse.
- Changed files: resources/views/dashboard.blade.php, resources/css/zazu-final-visual-sweep.css, tests/Feature/UiProportionFoundationTest.php.
- Commits: 4ca48de (dashboard source), 42799ea (dashboard styling), 6c3a4aa and dbdf432 (regression coverage).
- Source re-read after writes; branch inventory confirms main only.
- PHPUnit, Vite build and browser-rendered desktop/tablet/mobile acceptance were not run in this environment. Rendered visual acceptance remains **UNVERIFIED**.


Current deployment boundary:
- The Windows PC is development-only. No permanent local-PC host, AMPPS production setup, Windows service, router/DNS or private-CA work is in scope.
- A paid production server is a future owner-approved decision. No provider, monthly spend, production DB engine or instance size is selected.
- The current production-readiness plan is `docs/LOCAL_PC_HOST_DEPLOYMENT.md` (renamed content; historical filename retained).

Current state:
- Canonical repository/ref: `Leano-Jordan/ZazuEMP` / `main` only. No branches are to be created or used.
- Broad green CI evidence recorded for `7940033b6fd4f691692eec5e9d7f6ad587486f61` (2026-10-15) is historical and does not certify later commits.
- Service-worker identity-state writes and logout cleanup are now serialized through a shared promise queue.
- Three Node behavioral regressions exercise the actual `public/sw.js`: delayed persisted identity read, delayed authenticated HTML response, and identity-cache write already in flight during logout.
- `npm run test:service-worker` is wired into `.github/workflows/quality.yml`. Repository files were re-read after writes; current-main runtime/CI result remains **UNVERIFIED** until the exact workflow run is observed.
- Stale health report snapshots from an October 12 Dependabot branch were archived and misleading root-level `docs/health/latest.*` files removed. The current health workflow is artifact/summary-only with `contents: read`; use exact Actions run evidence, not a persistent latest file.

Next hard gate: **obtain exact-main Laravel, quality, browser, Psalm/PHPMD and populated upgrade/rollback evidence for the current application head; then run the operator backup/restore drill against representative database and private media. Keep installed-device offline acceptance open until the secure-origin disconnect/restart/reconnect sequence passes.**

Physical device acceptance is now owner-verified: Zazu installed on PC and mobile; screen rotation and presentation reported in order.

## Open / blocked cases

## Evidence rules
- IMPLEMENTED = repository change exists.
- TESTED = relevant automated check actually ran and passed.
- VERIFIED = intended behaviour has sufficient evidence.
- PROVEN = repeated realistic/production/recovery evidence exists.
- UNVERIFIED = required evidence unavailable.
- BLOCKED = safe progress materially prevented.
- A green narrow check never closes a broader behaviour gate.
- No case is CLOSED without its required evidence.

## Current CI boundary
The earlier browser run for `0a10d05` is not evidence for this final control-plane head. Current-head CI must be observed after this cycle. Record the exact run URL and SHA before closing any pending case.

## Application gates after browser convergence

1. Complete the populated commercial workflow traversal.
2. Execute the real backup/restore drill, including representative private media.
3. Execute the populated upgrade/rollback drill.
4. Complete physical phone/tablet acceptance.
5. Complete legal/privacy operational controls.
6. Complete dependency/licence notice closure.
7. Complete Zazu brand/trade-mark clearance.
8. Final Director re-audit against the selected release head.

**Current execution priority:** reconcile the 2026-11-01 offline-continuity source/test batch on exact `main`; then establish the independent HTTPS install origin and run installed-device disconnected/reconnect drills.

## Director cycle update — 2026-10-07

Offline operational depth expanded in current `main` through:
- inventory item + inventory movement handlers with business scoping, negative-stock protection and mutation idempotency;
- operational event-cost create/update handling with financial-state validation;
- asset create/update and allocation/return handling with explicit state-transition protection;
- direct and paired phone bootstrap coverage for inventory, assets and costs;
- phone-local UI actions for inventory, assets and costs;
- focused regression coverage for the new offline boundaries.

Current evidence status: **IMPLEMENTED; runtime TESTED/VERIFIED pending execution of the new regression set.** No release score uplift is claimed from source changes alone.

Last updated: 2026-10-07 — offline operational depth and parity hardened; current release gates remain current-head verification, recovery, rollback, legal/privacy and brand closure.

## PHPUnit / offline parity defect cycle — 2026-10-07

- Root cause of PHPUnit exit 255: malformed `catch (\\RuntimeException ...)` syntax in `tests/Feature/OfflineSyncFoundationTest.php`.
- Adjacent malformed test marker introduced during test-contract editing was also removed before validation.
- Offline local reconciliation was hardened for payments and purchase receipts so local invoice/payment and inventory state reflects accepted offline actions immediately.
- Targeted PWA regression assertions now cover those reconciliation boundaries.
- Current head CI is queued; runtime closure remains pending fresh Laravel/PHPMD/browser results.


## Director health-report hardening — 2026-10-08 (historical; superseded)

- A prior implementation experimented with persistent `docs/health/latest.json` and `latest.md` files alongside downloadable artifacts.
- That persistence is not present in the current `.github/workflows/roscore-director-health.yml`, which has repository contents permission set to read-only and publishes the per-run artifact and Actions summary.
- The October 12 committed `latest.*` snapshots described a Dependabot branch with queued CI, not current `main`. They have now been retained under `docs/health/archive/` and the root-level misleading pointers removed.
- Do not restore a privileged persistent publisher without a separate least-privilege, main-only design and verified security controls. The workflow must never check out untrusted refs or create working branches.

## Current-head CI reconciliation automation — 2026-10-08

- Director health reporting now observes the actual Laravel and Zazu browser smoke workflow results for the target commit.
- The health workflow runs on completion of those required workflows as well as on source pushes/manual dispatch.
- Reports distinguish PASS, FAIL and PENDING current-head CI state and retain the workflow run URLs.
- Workflow-run-triggered reports serialize per target SHA to prevent competing report commits.
- Implementation commit: `af0f4158160488873bbaf072de5db75ccdc10192`.


## Director deep software audit — 2026-10-08

Jarvis completed a deep repository audit against docs/product-specification.md and the V1 capability map.

Confirmed defects corrected in this cycle:
- authenticated PWA page-cache lifecycle race;
- Director health report non-fast-forward publication race;
- concurrent offline pairing-code consumption race;
- concurrent offline mutation replay race;
- rejected offline mutation permanently blocking later sync pulls.

Regression coverage was added for authenticated offline navigation, pairing/conflict behavior and rejected-mutation recovery. A newly added pairing regression initially failed CI because of a test namespace typo; this was corrected immediately in de2413d.

Current audited application HEAD: de2413d78f1efbb8a511fadd78375f1ceba025ce.

Conservative assessment:
- V1 implementation maturity: approximately 87%.
- Commercial software readiness: approximately 72%.
- Current release is not certified.

Release-critical gates remaining:
1. current-head CI/browser/security convergence;
2. real offline installed-device proof and restart/reconciliation;
3. populated commercial workflow certification;
4. real backup/restore including representative private media;
5. populated upgrade/rollback drill;
6. final authorization/security challenge;
7. production hardening and operational runbooks;
8. offline licensing activation/signing/enforcement where required for the commercial model;
9. final Director release audit.

Explicit exclusion from these scores: logo, artwork, photography, branding, trademark and other human-controlled creative/legal tasks.

Audit record: docs/DIRECTOR_DEEP_AUDIT_2026-10-08.md.


## Pre-launch landing page — 2026-10-12

- Added a standalone static marketing site under `prelaunch/`, separate from the Laravel application and PWA runtime.
- Added the GitHub Actions workflow `.github/workflows/deploy-prelaunch.yml` to publish only the `prelaunch/` directory to GitHub Pages from `main`.
- Page includes product positioning, conceptual (clearly labelled) workspace illustration, responsive feature sections, FAQ, early-access/demo enquiry form and a privacy notice.
- Form submission is configured through FormSubmit to the Rosscore Labs enquiry mailbox. Recipient activation and real delivery are **UNVERIFIED**; GitHub Pages does not store submissions.
- Static source integrity checks passed: expected files present, local asset references consistent, form consent/redirect/privacy links present, CSS/JS brace counts balanced, no remote image/font dependencies.
- GitHub Pages deployment, live browser rendering, mobile browser acceptance and end-to-end form delivery remain **UNVERIFIED** pending the Actions run, Pages settings and recipient activation.
- Expected project URL: `https://leano-jordan.github.io/ZazuEMP/`; confirm from the deployment output before public promotion.


## Pre-launch conversion copy and South African image refinement — 2026-10-13

- Revised public-facing early-access copy to lead with reduced chasing and a clearer view of the work behind each job, with a low-pressure choice between early access, a product demonstration, or both.
- Broadened copy to independent operators and growing teams across multiple South African event-related business types; avoided scarcity claims, invented social proof, and founder-support framing.
- Replaced six catering/venue-led photo references with responsive Wikimedia Commons image variants; added srcset/sizes, a public author/licence page, and privacy disclosure for the Wikimedia image host.
- Changed the pre-launch policy intentionally: image requests are remote but restricted by CSP and the deployment audit to HTTPS upload.wikimedia.org/wikipedia/commons/thumb/. This is not a self-hosted/offline image set.
- Verification remains pending: the changed deployment workflow must run on the new main HEAD, the deployed page must be checked in desktop and mobile browsers, and image loading/credits must be verified from the published artifact. FormSubmit delivery remains separately unverified.


## Landing-page reliability and first-impression refinement — 2026-10-13

- The SVG illustration substitution was unauthorised and has been fully removed from the landing page, media credits, privacy notice, source notes, and deployment workflow.
- Restored the six existing Wikimedia Commons photographic image references and responsive srcset variants, along with the required CSP host permission and original image-credit/privacy documentation.
- Removed all six newly added illustration assets from the repository. No AI-generated artwork is approved for this page.
- Kept the clearer hero headline and early-access/product-demo calls to action.
- Remaining acceptance: diagnose why the original photos did not display on the live GitHub Pages deployment, verify the current Actions deployment, then inspect image loading on desktop and mobile. Restoring source references does not prove live image delivery.

## Director cycle — 2026-10-15 current-main reconciliation

- Canonical branch: `main`; current assessed HEAD before this state-record update: `7940033b6fd4f691692eec5e9d7f6ad587486f61` (offline reliability/browser regression hardening).
- Current-main push workflows at that SHA: Laravel PASS (run 38789104896), Zazu quality PASS (38789104893), PHPMD PASS (38789105223), Psalm Security Scan PASS (38789105085), Zazu browser smoke PASS (38789104865), populated upgrade/rollback PASS (38789104911). SonarCloud was SKIPPED by workflow condition (38789105277); do not describe it as a pass.
- The Director health workflow completed successfully for the target SHA (run 38789753467). The committed `docs/health/latest.md` is stale and still describes an unrelated older branch/commit; generated workflow evidence and committed health report are not reconciled. Treat this as a control-plane reporting defect, not an application test failure.
- The backup/restore workflow provides CI evidence for a populated upgrade/rollback round trip, including representative business, event, invoice/payment, private-media and migration-state checks. It does **not** satisfy the release runbook's installed-customer-environment recovery drill.
- Remaining next hard gate: run the documented controlled Windows installation backup/restore drill and capture environment, archive ID, representative record/media/security checks. This requires access to the installed environment; do not mark PROVEN from CI alone.
- Branch policy remains main-only. GitHub currently exposes the stale `director/fix-offline-job-browser-selector` ref in addition to `main`; the connected GitHub action set exposes no branch-delete operation. Do not create another branch or misreport cleanup as complete.

Evidence: https://github.com/Leano-Jordan/ZazuEMP/actions/runs/38789104896 ; https://github.com/Leano-Jordan/ZazuEMP/actions/runs/38789104893 ; https://github.com/Leano-Jordan/ZazuEMP/actions/runs/38789105223 ; https://github.com/Leano-Jordan/ZazuEMP/actions/runs/38789105085 ; https://github.com/Leano-Jordan/ZazuEMP/actions/runs/38789104865 ; https://github.com/Leano-Jordan/ZazuEMP/actions/runs/38789104911 ; https://github.com/Leano-Jordan/ZazuEMP/actions/runs/38789753467

Last updated: 2026-10-15 — reconciled current-main CI and recovery proof boundary.

## Director cycle — 2026-10-15 MySQL restore rollback hardening

- Canonical branch: `main`; implementation/test head: `fb0278949e8971d32a65d1d36e074b0561159cab`.
- `app/Console/Commands/ZazuRestoreCommand.php` now captures a pre-restore MySQL dump before private-storage mutation, uses a shared MySQL import path, attempts database rollback after a failed candidate import, and preserves the rollback snapshot with explicit operator warning if automatic rollback also fails.
- `tests/Feature/ZazuBackupRestoreTest.php` now exercises a simulated partial MySQL import failure and asserts the pre-restore SQL is imported and original private storage is restored. Test passed in Laravel workflow run 38793762832.
- At the time of this record, the following checks passed for this head: Laravel 38793762832, Zazu quality 38793762950, PHPMD 38793762939, Psalm Security Scan 38793762878, populated upgrade/rollback 38793762891, Push on main 38793762946, and Director health 38793901676. SonarCloud 38793762938 was skipped. Browser smoke 38793762835 was still running and is not marked passed here.
- Release limitation unchanged: no actual installed Windows/customer-environment backup/restore drill has been recorded. CI is not a substitute.
- Branch policy unchanged: no new branch or PR was created. The stale feature ref remains visible alongside `main`; the connected GitHub actions still expose no branch-delete operation.

Evidence: https://github.com/Leano-Jordan/ZazuEMP/actions/runs/38793762832 ; https://github.com/Leano-Jordan/ZazuEMP/actions/runs/38793762950 ; https://github.com/Leano-Jordan/ZazuEMP/actions/runs/38793762939 ; https://github.com/Leano-Jordan/ZazuEMP/actions/runs/38793762878 ; https://github.com/Leano-Jordan/ZazuEMP/actions/runs/38793762891 ; https://github.com/Leano-Jordan/ZazuEMP/actions/runs/38793762946 ; https://github.com/Leano-Jordan/ZazuEMP/actions/runs/38793762835

Last updated: 2026-10-15 — MySQL rollback behavior and regression evidence recorded.

## Director offline-launch defect cycle — 2026-10-16

- Root cause found in `resources/js/app.js`: service-worker registration sent `prime-pages` twice—once from the registration and again from `navigator.serviceWorker.ready`. The service worker aborts an active priming run when another `prime-pages` message arrives, so the duplicate call could cancel page caching during initial setup.
- Changed registration to await `navigator.serviceWorker.ready` and prime the installed pages once.
- Added regression assertions in `tests/Feature/PwaOfflineFoundationTest.php` to prevent the duplicate priming path returning.
- Status: source change committed to `main`; current-head CI and real installed-device offline relaunch are **PENDING**. This does not yet prove the user's offline launch issue is resolved.

## Director offline asset-cache hardening — 2026-10-16

- Further inspection found the service worker's static-asset handler started cache writes without awaiting them, then returned the network response. Browsers can suspend the worker before that write completes, leaving required JS/CSS unavailable offline.
- Reworked static-asset handling to return a cache hit immediately; on a cache miss, fetch and await the cache write before completing the event. Cache-storage failures do not break an otherwise successful online response.
- Bumped the static cache from v14 to v15 so installed clients activate a fresh cache namespace for the changed worker behavior.
- Added regression assertions for cache-first handling, awaited writes and the new cache version.
- Status: source and regression changes committed to `main`; current-head CI and actual installed-device offline launch remain **PENDING/UNVERIFIED**. The cache priming and asset-cache defects are evidence-backed contributors, not proof they explain every part of the reported failure.

## Director offline-origin correction — 2026-10-16

- Confirmed current source on canonical `main` at baseline `10f432bdd6a4d501bbd949c7973283e3ef8f7b66`.
- Current-head Laravel and Zazu quality workflows both failed because `tests/Feature/PwaOfflineFoundationTest.php` still asserted the removed `cache.put(request, copy)` implementation after the service-worker handler had been changed to await `cache.put(request, response.clone())`. This is test-contract drift (F2/F7), not evidence that the current asset-cache implementation failed.
- Corrected the stale assertion and added a regression contract for a user-facing warning when the app is opened in a non-secure context. The notice explains that a plain-HTTP LAN address depends on a reachable PC/server and cannot register the service worker.
- Updated the offline architecture contract to record the exact LAN-origin failure and the full disconnected-device acceptance gate.
- Verification on source/test commit `3f5d1f1877b281dc84a4bbce4bcabecc9fd12952`: Laravel passed ([run 38891784115](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/38891784115)); Zazu quality passed ([run 38891784112](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/38891784112)); Director Contract Lint, PHPMD, Psalm and populated upgrade/rollback also passed on that SHA.
- Browser smoke [run 38891784057](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/38891784057) remains **in progress** and is not represented as passed. Actual installed-device offline relaunch remains **UNVERIFIED**.
- The product-wide offline parity gap remains open; no full-offline claim is made from this correction.



## Director audit — 2026-10-19

- Current control files now mark current-main CI as unverified rather than reusing green results from October 15 or the stale October 12 health artifact.
- Added three direct behavioral tests for service-worker identity/cache races and integrated them into the Quality workflow.
- Serialized `setUser` identity-state mutations with `clearActiveUser` cleanup in `public/sw.js` to prevent an in-flight identity write from surviving logout cleanup.
- Corrected V1 checklist/scorecard evidence language, archived and removed the misleading stale health pointers, and documented the current artifact-only health-report contract.
- Verification disposition: IMPLEMENTED at source; **latest-main execution and browser regression remain UNVERIFIED**. No numerical readiness increase is claimed.
- Next: reconcile the exact current-main Actions run, then perform installed-customer backup/restore including private media and an actual rollback exercise. Legal/privacy operations and trademark clearance remain external release gates.


## Dead/stale source and dependency cleanup — 2026-10-19

- A bounded declaration scan across `resources/css/app.css`, `zazu-responsive-theme.css`, `zazu-final-visual-sweep.css`, and `zazu-mobile-refinement.css` found exactly two duplicate `font-variant-numeric: tabular-nums` declarations; both were removed. Re-scan found no remaining repeated declarations in those four stylesheets.
- Removed unreferenced `@fontsource/anton`; package/lock are reconciled and the current npm licence inventory now records 153 package entries with declared metadata. Current-main `npm ci` is the required validation.
- Corrected the duplicate `REG-017` identifier to `REG-022`; the regression-ledger heading scan reports no duplicate REG IDs.
- Search returned no TODO/FIXME/HACK/DEPRECATED markers in the indexed results, but this is not proof that no dead code exists. Comprehensive PHP/Blade/JS reachability analysis remains open.
- Current-main install/build, service-worker behavioral tests, Laravel, browser, security and upgrade/rollback results remain UNVERIFIED until exact run conclusions are observed.


## Focused P0/P1 workflow audit — 2026-10-20

- P0 defect found: invoice creation displayed accepted quote, independent job, direct lines and tax treatment as concurrently editable despite quote invoices deriving job/lines/currency/tax from the accepted quote version.
- Source correction committed: selecting a quote binds the linked job, disables job/tax overrides, hides/disables direct invoice lines and explains the source. Clearing the quote restores direct mode and its prior manual job selection.
- Added a focused feature-test assertion for quote-bound invoice form hooks and event metadata.
- Existing server guards remain in place for quote/job mismatch, accepted latest version, duplicate invoice conversion and historical quote snapshotting.
- Existing populated reconciliation scenario covers R11,500 invoice, R3,450 deposit, R8,050 final payment and paid status. Asset overlap checks exist in online and offline paths.
- Current-head tests, CI and rendered browser behaviour are **UNVERIFIED** in this session. No release score/readiness uplift.
- Next: run targeted commercial and asset/offline tests, observe exact current-main CI, then browser-traverse quote select/switch/clear and one populated job end-to-end.

Last updated: 2026-10-20

## Director offline continuity cycle — 2026-10-21

- Re-read the canonical Director contract, current engineering state, offline architecture contract, service worker, local IndexedDB client, mobile bootstrap controller and PWA regression tests before making changes.
- Confirmed the existing offline-first implementation is established work, not a new proposal. It includes a user-scoped service-worker page cache, IndexedDB local workspace and mutation queue, pairing/sync endpoints, and explicit offline domain handlers. Full disconnected parity is still explicitly unproven.
- Confirmed the immediate origin blocker: `http://192.168.137.237:8000` is a PC-hosted plain-HTTP LAN origin. When its server process stops, server-dependent requests fail; because it is not a secure browser context, service-worker registration is unavailable there. The source already contains a user-facing diagnostic for this case.
- Added a direct behavioral regression in `tests/js/service-worker-auth-cache.test.js`: prime the authenticated dashboard, make the simulated host unavailable, and verify the service worker serves the saved dashboard from the user-scoped cache.
- Recorded the exact hosting and installed-device acceptance gate in `docs/ZAZU_OFFLINE_FIRST_ARCHITECTURE.md`.
- Changes were committed directly to canonical `main`; no branch was created.
- New test status: **IMPLEMENTED, execution pending**. A source write and a passing static assertion are not runtime proof.
- Deployment reconnaissance: hosting configuration was not part of this repository-only review. Production provider, URL, database and durable storage status must be established from Zazu-specific deployment evidence; do not infer them from another product.

### Active target

**P0 — finish VS Code-independent offline continuity:** observe current-main CI for the new offline-navigation regression; establish a durable HTTPS production origin separate from the VS Code server; install/update from that origin; then perform physical-device acceptance with the PC and internet unavailable, followed by reconnect/idempotency/conflict and logout/privacy checks.

**Acceptance rule:** no claim of "solid offline" until current-head CI and the realistic installed-device gate both pass. The host-independent launch gate and full offline-domain parity remain **OPEN**.

## Director offline continuity and V1 workflow hardening — 2026-11-01

**Source baseline for this cycle:** `main` at `ddf2d72bf79365c94f05a1a69784d6a404116cb7`; later same-day commits remain governed by the live `main` ref. All implementation and documentation writes target `main`; no working branch was created.

### Implemented in source

- Offline form submission probes the Zazu origin with a bounded same-origin request. It no longer assumes `navigator.onLine` means the Laravel host is reachable; supported forms can enter the local queue while Wi-Fi/network-interface status remains online.
- The offline mutation, the `local_dirty` marker and the staged optimistic workspace copy are committed in one IndexedDB transaction. The client adopts the new in-memory state only after transaction completion.
- The service-worker cache generation is `zazu-static-v25`, invalidating the older precached offline client after the persistence changes.
- Offline customer creation carries the primary contact, legal name, registration/tax/VAT details, billing address, notes and day/night contacts. Offline profile-photo upload is explicitly declined while disconnected; metadata is not presented as proof that a file was saved.
- Owner pairing with an empty selection now bootstraps a bounded recent working set: up to 25 customers, 25 jobs, 25 quotes and 25 preparation items, alongside the existing supporting catalogue, suppliers, purchasing, inventory, assets, costs, invoices and expenses. The settings screen explains the data copied to the paired device.
- Selected customer/job/quote/preparation records carry stable local identities and relationship references into the phone's IndexedDB store.
- Offline Work creation carries the selected customer's local identity, job type, date, address and services. The server resolves the customer identity within the same business, creates service requirements, and rejects a customer identity belonging to another business.
- The sync dependency sorter is covered with a reversed customer/job batch so the customer mutation must apply before its dependent Work record.

### Regression coverage added

- Browser coverage for an online browser with an unreachable Zazu origin, full offline customer details, queue persistence after reload, and a phone-local customer selected in offline Work creation.
- Laravel coverage for customer profile/contact persistence, bounded pairing bootstrap and parent identities, offline Work/customer linking, service requirement creation, reversed dependency ordering and cross-business customer rejection.
- Existing service-worker behavioral assertions now follow cache generation v25.

### Evidence disposition

- Source and tests: **IMPLEMENTED**.
- Exact-main Laravel, Quality, browser, PHPMD, Psalm and populated upgrade/rollback: **PENDING / UNVERIFIED** until the current `main` Actions runs finish and their exact SHA is recorded. Earlier successful runs do not certify these later changes.
- Real installed-device operation with the PC and internet unavailable: **UNVERIFIED**.
- Stable HTTPS Zazu origin with durable database/private-file storage and recovery: **NOT ESTABLISHED**.
- Full offline parity across every V1 screen, complete bidirectional reconciliation, attachment/file parity and real-world conflict recovery: **OPEN**.
- No readiness-score uplift is awarded for source/test additions alone.

### Next acceptance gate

1. Reconcile current-main Laravel, Quality, browser, PHPMD, Psalm and populated upgrade/rollback runs.
2. Resolve every confirmed regression on `main`; queued jobs are not passes.
3. Establish an independent HTTPS deployment target with durable persistence and tested backup/restore.
4. Install from that origin and complete disconnected/reconnect/logout/multi-user device acceptance.
5. Re-audit complete workflow parity and recovery before V1 release acceptance.


## Director current-main CI reconciliation — 2026-11-01

**Canonical target:** `Leano-Jordan/ZazuEMP` / `main`  
**Exact application SHA:** `6b16b74a7b773ac2797bf7135f85d38b8af0de8d`

### Observed current-head Actions evidence

The following workflows completed successfully against the exact SHA above:

- [Zazu quality](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/41290751642)
- [Laravel](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/41290751650)
- [Psalm Security Scan](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/41290751603)
- [PHPMD](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/41290751665)
- [Zazu populated upgrade and rollback](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/41290751645)

The quality workflow includes frontend build, the Node behavioral service-worker authentication-cache race suite, and the Laravel application suite. The populated upgrade/rollback workflow is a CI result, not a substitute for a customer-environment drill.

**Browser smoke:** [run 41290751609](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/41290751609) was still `in_progress` when reconciled. It is not counted as PASS.

### Acceptance disposition

- Current-main source/automated quality: **TESTED — PASS** for the workflows listed above.
- Browser smoke: **PENDING**.
- Offline launch with PC and internet unavailable on an installed customer device: **UNVERIFIED**.
- Independent HTTPS hosting with durable database and private-file storage: **NOT ESTABLISHED**.
- Real customer-environment backup/restore and rollback: **OPEN**.
- Full offline parity and multi-device conflict/recovery: **OPEN**.

No V1 readiness uplift is claimed from these CI passes alone. Continue the P0 offline-continuity gate, then execute the populated customer workflow and real recovery drills. All work remains on `main`; do not create branches.


## Director browser-regression repair cycle — 2026-11-01

**Target:** `main` only.  
**Failure baseline:** application SHA `6b16b74a7b773ac2797bf7135f85d38b8af0de8d`.  
**Failure evidence:** [Browser smoke run 41290751609](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/41290751609), [job logs](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/41290751609/job/124801314146).

### Diagnosis

The browser run had 46 passing tests and 6 failures (14 skipped). The failures were concentrated in offline form coverage:

- Job-create offline test selected a server-rendered customer that was not guaranteed to exist in the device-local workspace. The offline handler correctly rejected the unresolved relationship, but its early-return path could leave the generic form submit lock engaged.
- The customer-photo offline rejection path also exposed a submit-lock recovery gap: any unsuccessful offline submit must restore the button so the user can correct the form and retry.
- The customer-contact test attempted to fill Night contact inputs inside a collapsed `details` disclosure.
- The local-customer-to-Work test attempted to click a native radio obscured by its visible choice-card label.

### Changes committed directly to main

- `resources/js/app.js`: unsaved offline form attempts now release the generic submit lock, restore the original button label, and clear `aria-busy` in the finalizer. A rejected local mutation no longer strands the form disabled.
- `e2e/offline-attachments.spec.js`: seed a customer into IndexedDB/local mutation queue before creating a disconnected job; exercise a real local customer relationship and service selection.
- `e2e/pwa-offline.spec.js`: expand the Night contact disclosure and interact with the visible event-type choice card.

### Verification status

These changes are committed on `main` through application commit `df52f7c0a5b0d7c9a21a50db479cae6ee446246e`. Fresh Quality, Laravel, Psalm, PHPMD, populated upgrade/rollback and browser smoke workflows have been triggered for this SHA. Their results are pending; no repair is marked verified until those exact runs complete.

**Do not certify offline continuity from this repair alone.** Installed-device disconnected launch, data persistence after process restart, real sync/reconciliation and production recovery drills remain release gates.


## Director software audit and repair cycle — 2026-11-01

**Canonical target:** `Leano-Jordan/ZazuEMP` / `main`  
**Application/test candidate:** `c3370a317b016be8355df799f1eed37bc55c4488`  
**Working tree discipline:** main only; no branches; no database mutations.

### Verified source repairs

1. **Default phone working-set freshness**
   - Empty-selection pairing no longer freezes the first 25 customer/job/quote/preparation IDs at pairing time.
   - Bootstrap now recalculates the recent working set on refresh.
   - Explicit selections remain pinned; legacy devices with non-empty selections preserve their prior semantics.
   - Regression coverage: post-pairing records appear on refresh; explicit/legacy selections remain bounded and pinned.

2. **Offline asset update validation**
   - Replaced invalid Eloquent `whereKey('!=', $asset->id)` usage with `whereKeyNot($asset->id)`.
   - Duplicate asset tags now produce the intended validation response instead of falling through to the database unique constraint.
   - Regression test verifies both original asset tags remain unchanged.

3. **Sync conflict recovery**
   - Added an actionable Settings panel for per-device sync exceptions, with retry and discard actions.
   - Retry restores the failed IndexedDB mutation to the pending queue; discard removes it locally.
   - Server resolution is transactional and locks both conflict and mutation; repeat/concurrent requests return the authoritative mutation status.
   - Client now follows the server's final status instead of assuming its requested action won.
   - Playwright coverage exercises retry and discard; pending browser run below.

4. **Cross-business offline storage isolation**
   - Replaced the origin-wide IndexedDB workspace with a business-scoped database name.
   - Added the active business ID to the app shell.
   - Legacy storage is migrated only when the stored business and paired-device business IDs both match the active business.
   - A migration tombstone survives local logout, preventing already-cleared legacy queue data from being imported again.
   - Added browser tests for matching legacy migration, queue preservation, logout tombstone behaviour and foreign-business rejection.
   - Service-worker static cache generation advanced to `zazu-static-v28` so installed clients do not continue using the prior offline JS.

### Exact-main CI evidence

Against application candidate `c3370a317b016be8355df799f1eed37bc55c4488`:

- **PASS** — [Zazu quality](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/41299518886)
- **PASS** — [Laravel](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/41299518930)
- **PASS** — [Psalm Security Scan](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/41299518944)
- **PASS** — [PHPMD](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/41299518954)
- **PASS** — [Populated upgrade and rollback](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/41299518856)
- **PASS** — [Push on main](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/41299519274)
- **PENDING at reconciliation** — [Zazu browser smoke](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/41299519019)
- **SKIPPED** — SonarCloud analysis; not counted as a pass.
- Director health-report workflows succeeded; they are status/reporting workflows, not application-test substitutes.

### Release disposition

Source changes and automated server/security/quality checks are green. Browser smoke is still pending and must be reconciled before the new Playwright cases are accepted.

Still **OPEN / NOT CERTIFIED**:
- Installed-device launch after force-close with both PC/server and internet unavailable.
- Complete offline parity across every V1 workflow and reliable cross-device reconciliation beyond the bounded local working set.
- Independent stable HTTPS hosting with durable database and private-file storage.
- Real customer-environment backup/restore and deployment rollback drills.
- Final privacy/legal and Zazu brand clearance.

No release-readiness score uplift is claimed from these source fixes or CI passes alone.


## Director stable-origin migration audit — 2026-11-01

- Re-fetched current `main` source for the PWA client, service worker, session config, environment example, deployment plan, feature tests and Playwright offline tests.
- Confirmed service-worker registration uses root-relative `/sw.js`; service-worker request/cache logic and app logout checks consistently compare against the active origin. No hard-coded development LAN IP was found in the inspected runtime paths.
- Confirmed `config/session.php` defaults secure cookies to true in production when `SESSION_SECURE_COOKIE` is unset; `.env.example` now correctly leaves that variable unset.
- **Critical migration finding:** browser storage is origin-scoped. The current business workspace uses IndexedDB database `zazu-client-business-<business-id>`; legacy migration reads `zazu-client`; offline attachment blobs use a separate `zazu-phone-attachments` database. A scheme/host/port change creates a separate storage partition. Local queues, conflicts, attachment blobs, service-worker caches and cookies do not automatically transfer from the old HTTP IP origin to the new HTTPS hostname.
- Updated `docs/LOCAL_PC_HOST_DEPLOYMENT.md` with a controlled origin-cutover procedure: drain and verify old-origin mutations/conflicts/attachments; preserve old browser storage; deploy/restore the same authoritative business data; re-install and re-pair on HTTPS; verify record relationships and a one-time test sync; retire old origin only after all devices pass.
- Explicitly recorded that unsynchronized device-only data needs a tested export/import or migration mechanism; do not clear site data or uninstall the old PWA as part of cutover.
- Source changes in this cycle: documentation only. No runtime code, database schema or customer data changed. No tests were run in this remote GitHub-only cycle.
- **Next P0:** inspect the exact origin migration and attachment retry behaviour in the client, add automated guardrails for migration readiness where feasible, then reconcile the latest browser Actions run. Physical LAN HTTPS and disconnected-device tests remain pending until the PC is available.
- All work committed directly to canonical `main`; no branch created.


## Director offline attachment integration audit — 2026-11-01

- Inspected the current Work attachment partial/controller, authenticated app layout, service worker, standalone `public/offline-attachments.js`, sync attachment API and existing attachment feature tests.
- Found that the server-side upload/list/download endpoints and the online Work attachment panel exist, but the standalone phone transfer script is not imported by any active view. Its expected globals (`data`, `syncFetch`, `render`, `#content`, `.hero-actions`) are not provided by the current authenticated layout. The script is only referenced by service-worker caching rules, tests and docs.
- Corrected `docs/ZAZU_OFFLINE_FIRST_ARCHITECTURE.md` and `docs/ZAZU_V1_STATUS.md`: online job attachments are implemented; offline attachment queue/upload/download is **NOT IMPLEMENTED in the active app**. Do not include the orphaned script in the app layout without adapting its contracts and adding business-scope, logout, origin-migration and retry tests.
- This correction prevents the project from counting a precached but unreachable script as a shipped offline capability. No runtime app changes were made for this discovery.
- Separately tightened sync attachment idempotency: an existing key is now checked against the requested business-owned job, original file name, size, MIME type, description and SHA-256 content before returning duplicate success. A mismatched key reuse returns HTTP 409; inability to verify the existing stored file returns HTTP 503 and leaves the client upload queued.
- Added regression coverage for key reuse across a different job and for changed file contents. Exact-main execution is pending on the latest commit.
- No database schema, database data or branch changes. All commits remain direct to `main`.


## Director transaction integrity and attachment storage hardening — 2026-11-01

**Canonical target:** main only. No branch was created. No database schema or customer data was changed.

### Verified source corrections committed

- Attachment deletion now commits the business-scoped metadata deletion before removing the private file. A rolled-back DB transaction can no longer leave a live attachment row pointing at a file already deleted from storage.
- Added focused attachment tests for both rollback retention and successful row/file removal.
- Both normal Work uploads and device-sync attachment uploads now reject a failed private store() operation with HTTP 503 before attempting to create attachment metadata.
- Sync attachment retries validate the same business-owned job, file name, byte size, MIME type, description and SHA-256 file content. Reusing a key for a different payload returns 409; inability to verify the stored file returns 503. Concurrent same-key insert races are checked against the same payload before duplicate success.
- Finance payment retries now compare invoice, event, currency, type, amount, method, reference, paid date and notes. A changed payload using the same key returns 409.
- Finance expense retries compare business-bound event/purchase-order/supplier links, description, amount, currency, expense date, status, reference and notes. A changed payload returns 409.
- Inventory movement retries compare item, type, quantity, unit cost, date, event, reference and notes. A changed payload returns 409.
- Purchase-order creation retries compare supplier, event, currency, expected date, notes and all line details. A changed supplier commitment returns 409.
- Invoice creation retries compare event/quote identity, currency, totals, tax snapshot, submitted dates/notes and invoice lines. A changed invoice returns 409.
- Removed an early quote-version “already invoiced” guard that previously ran before invoice idempotency lookup. The transactional guard remains after same-key replay handling, allowing a legitimate retry of the accepted quote conversion.
- Purchase receipt replay now checks whether the key was already committed before rejecting a fully received or subsequently closed order/job. Regression coverage verifies replay after the order reaches received.

### Test coverage added or expanded

- Offline conflict fixture now reacquires the live workspace object after queue writes replace it.
- Offline logout tests capture the authenticated business DB name before redirecting to the public page; IndexedDB blocked conditions fail explicitly instead of hanging.
- Attachment idempotency rejects the same key when used for a different job or different file bytes.
- Attachment deletion rollback and success paths are covered.
- Payment, expense, inventory movement, purchase order and invoice tests now verify that changed payloads with the same key return HTTP 409 without duplicate posting.
- Commercial reconciliation now retries a quote-derived invoice after initial conversion and verifies the invoice count remains one.
- Purchase receipt test replays the final receipt key after the order becomes fully received.

### Known remaining integrity limitation

The purchase_order_receipts table stores only the business, purchase order and idempotency key. It does not persist the line quantities/payload applied by a receipt. Current code safely suppresses duplicate stock posting and permits retries after completion, but cannot prove that a same-key replay carries the same quantities. Full payload-mismatch rejection requires an additive persisted receipt-line fingerprint or receipt-line table. This is deliberately left open pending approval for a database migration; no schema change is made here.

### Current verification disposition

- Source changes are committed, but automated workflows must be reconciled against the exact current main SHA before any new tests are claimed as passed.
- Browser smoke on earlier candidates remained in progress; the latest run must complete before the offline fixture corrections are certified.
- Local Windows PC host installation, stable HTTPS, installed-device disconnected launch, and physical reconnect/restore drills remain unverified.
- Offline phone attachment upload/download is still NOT IMPLEMENTED in the active app. The standalone legacy script remains dormant and is not loaded into the authenticated layout.
- No release-readiness score uplift is claimed from source changes alone.


## Director local PC host inspection — 2026-11-01 22:24 +02:00

- Owner supplied read-only output from `ops/windows/Inspect-ZazuHost.ps1` for the actual Zazu PC.
- Windows 10 Pro build 19045; ESU/security-update enrollment and UBR remain unknown. Standard Windows 10 support ended 2025-10-14; do not certify this machine as a long-term host until its supported update state or OS migration is confirmed.
- PHP CLI 8.4.26 satisfies `^8.4`; common required extensions including PDO MySQL and OpenSSL are present. Apache's PHP runtime still needs independent verification.
- AMPPS exists under `C:\Program Files\Ampps`, but the inventory found no Apache/MySQL/MariaDB Windows services.
- Only detected listener: PHP on `0.0.0.0:8000` (PID 10268). It is consistent with a development server; full executable/command line was not captured. Do not use it as permanent customer endpoint.
- Current Wi-Fi address `192.168.137.237`, gateway/DNS `192.168.137.1`; require router DHCP reservation before stable hostname/certificate setup.
- Project dependencies/assets are present; C: free space approximately 127.8 GB.
- Deployment plan updated with these findings and the next read-only inspection checklist. No local services, firewall, router, DNS, certificates, database or files were changed.
- Latest observed main SHA at review: `2c984a71724fe8aebaf57cbb777fa029ca45ec12`. Exact-head workflows: Laravel PASS, Zazu quality PASS, PHPMD PASS, populated upgrade/rollback PASS, Psalm security PASS, browser smoke PASS, push checks PASS; SonarCloud SKIPPED.
- Remaining work: inspect exact PHP server process and AMPPS Apache/database layout, determine active DB driver/data paths and backup target, verify Windows security-update/ESU state, then design a safe deployment plan. No host configuration until these are known.


## Director attachment retrieval defect — 2026-11-01

- Expanded the audit beyond the offline host work into the active Work attachment workflow.
- **Defect found:** the Work attachment panel displayed uploaded filenames and a Remove action, but omitted the already-implemented authorized download route. Users could store files but had no download control in the normal job screen.
- Fixed `resources/views/work/attachments.blade.php` to expose a Download action using the existing `work.attachments.download` route. No public file URL was introduced; the route retains the `work.view` permission and controller business-boundary checks.
- Added a positive feature regression in `tests/Feature/BusinessIsolationTest.php` verifying an authorized user can download their own private job attachment. Existing foreign-business download rejection remains intact.
- Commits: `7a4ea84f214f3766bcecf4ea3a7f76a2a8f0c51f` (UI) and `f626522b5043549617e6a4689f695afb79c4f53d` (test), both directly on `main`.
- Verification status: source and test committed; PHPUnit/CI execution on this exact head is pending. Do not mark the defect runtime-verified until the Laravel workflow passes.
- No database/schema changes, no branch, and no attachment storage permissions were broadened.

- Follow-up refinement: added a rendered Work-screen assertion to the positive download regression, so it checks both that the download link is present and that the private file endpoint returns a download response. Tidied the Blade action markup. Follow-up commits: `56cf37bdf4f49c25f6cd06507af8a06b61a2626d` and `2ff49d6b0853f08cc9f5a539f49fe3583e660136`, direct to `main`.

- Reconciled `docs/ZAZU_V1_STATUS.md` on 2026-11-01: corrected stale assessment date/head, clarified that the old application candidate is historical, corrected the conflict-control status, and recorded the online attachment-download fix separately from still-unimplemented offline attachment transfer. The page now explicitly leaves exact-head CI pending and does not reuse historical green runs.


## Director deep host-forensics preparation — 2026-11-01

- Reconfirmed canonical repository `Leano-Jordan/ZazuEMP` and only visible branch `main`.
- Current observed main before this inspection-script commit: `a60d403c6305444f2f4a67ea72a398555a7261df` (V1 status reconciliation).
- Added `ops/windows/Inspect-ZazuHostDeep.ps1`, a read-only follow-up to the first host inventory.
- It captures the previously missing PHP listener executable/command line and parent PID; Windows build/UBR and recent hotfix indicators; AMPPS executable versions; PHP CLI and AMPPS PHP configuration/extensions; relevant Apache directives; non-secret Laravel DB connection/database/path metadata; SQLite file presence/size when applicable; database datadir config references; private-storage footprint; and disk space.
- It deliberately does not read database credentials, start/stop services, edit files, connect to the database, change firewall/router/certificate settings or migrate data.
- Script was committed directly to `main` as `f71fb703cf0565f8839bd7a197b18e9ce56d1f3e` and re-fetched from GitHub for content verification.
- Runtime result: **PENDING owner execution**. The remote GitHub session cannot inspect the local Windows process table, AMPPS installation, actual `.env`, database files or ESU state.
- Next gate: owner runs the deep inspection script from `C:\Projects\ZazuEMP` and returns its output. Use that evidence to choose the existing PHP/Apache/database runtime; do not configure services or certificates before the process and data paths are known.

- Branch-policy reconciliation during this cycle: GitHub branch search exposed `main` plus existing Dependabot refs `dependabot/composer/laravel/framework-13.39.0` and `dependabot/composer/phpunit/phpunit-13.4.5`. No branch was created or used by this Director cycle. Treat both Dependabot refs as existing policy drift; do not work on or delete them without owner authorization.


## Director takeover cycle — 2026-11-01 — current-main CI and host-plan correction

- Canonical target: `Leano-Jordan/ZazuEMP`, `main`; assessed source head: `8700fa6e1afdbbfd7a2fd75c1f07b26cdc438467`.
- Corrected the PHPMD failure observed on the preceding main head. The attachment download positive/negative tests were moved from `BusinessIsolationTest` into the focused `WorkAttachmentDownloadTest`; the former now has nine public test methods. This is test organisation only; no application behavior or database schema changed.
- Commits: `dcd03aba29c6c9356f4ae961059acb3da1639149` (new focused test class), `5fc6098328495cc585b997b5eec3cce0cd16c625` (isolation test refactor), and `8700fa6e1afdbbfd7a2fd75c1f07b26cdc438467` (deployment-plan correction).
- Corrected `docs/LOCAL_PC_HOST_DEPLOYMENT.md`: AMPPS is installed but was not used to run Zazu and is not the selected/default host. Current observed Zazu development runtime is standalone PHP 8.4 via `php artisan serve`. Host selection must be based on PHP 8.4 compatibility, HTTPS, persistent service recovery, database and private storage—not on AMPPS being present.
- Re-read the changed test files and deployment document from GitHub after writes.
- Exact-head Actions evidence for `8700fa6`: Laravel PASS (41355442767); Zazu quality PASS (41355442798); PHPMD PASS (41355442910); Psalm Security Scan PASS (41355442824); populated upgrade/rollback PASS (41355442795); Director Contract Lint PASS (41355442755); Push on main PASS (41355442522); Director Health Report PASS (41355483317). SonarCloud was SKIPPED (41355442974). Zazu browser smoke (41355442725) was still running at the time of this record and is not claimed as passed.
- Source-level offline audit: service-worker registration is deliberately skipped on insecure origins; the previous `http://192.168.137.237:8000` LAN origin cannot register the worker. The runtime uses same-origin/root-relative service-worker paths and production-secure session-cookie defaults. The offline client persists a device-local workspace and mutation queue, but the complete web application is not mirrored locally. Offline attachment transfer remains NOT IMPLEMENTED in the active app; the standalone script is not loaded by the authenticated layout. Origin changes create a separate browser-storage partition; queues and local-only data do not automatically migrate.
- No local PC services, Apache/PHP configuration, database, firewall, router, DNS, certificates or browser storage were changed. No feature branches were created or used.
- Next target: select and prove a persistent HTTPS host from the actual Windows/PHP/database/security evidence, then run the secure-origin installation and disconnected-device/reconnect acceptance. Do not request more owner terminal work until repository evidence is exhausted and the exact missing physical fact is identified.


## Director correction — development workstation vs future paid production server — 2026-11-03

- Owner clarified that the Windows PC is a development machine, not Zazu's permanent production host. Future production hosting will be a paid server when funding and release readiness allow.
- The previous local-PC production-host target is superseded. Do not select/configure AMPPS, Apache, Windows services, router DNS, LAN certificates or the owner's PC as a customer host. Do not recommend purchasing a cloud server merely to remove the VS Code terminal dependency.
- Owner supplied `Inspect-ZazuHostDeep.ps1` output. Observed development runtime: PHP 8.4.26 at `C:\\PHP84\\php.exe`; PHP built-in server on `0.0.0.0:8000` (PID 9664); active DB is SQLite at `database/database.sqlite`; private storage has 8 files / approximately 0.98 MB. No Windows web/database services were detected. AMPPS PHP is 7.4.33 and AMPPS was not hosting Zazu. Windows 10 Pro 22H2 build 19045, UBR 2251; listed hotfixes do not prove ESU enrollment.
- Replaced `docs/LOCAL_PC_HOST_DEPLOYMENT.md` with a production-readiness plan that explicitly separates temporary local development from a future paid server. It no longer treats the owner's PC as production infrastructure or assumes a provider/database.
- Future production baseline: HTTPS, PHP 8.4+, public-web-root isolation, persistent private files, tested database engine, queue/scheduler supervision where needed, off-server backup/restore, monitoring and safe release/rollback. Provider, server size, OS and database choice remain unselected until tested and owner-approved.
- Offline product capability remains independent of hosting choice. A cloud server does not provide offline operation; only declared device-local workflows can work during WAN loss. Offline attachment transfer and realistic reconnect/recovery proof remain open.
- Commit: `f638437097b94f5a87f37179efe492719dcf7ba6` on `main`. Deployment document was re-fetched and its updated content SHA verified.
- No local PC settings/services, database, firewall, router, certificates, browser storage or customer data were changed. No branch was created or used.
- Active next target: repository-only production portability audit. Remove verified environment-specific assumptions and establish repeatable release/deployment requirements; defer infrastructure purchase and owner-PC hosting changes.


## Local development runtime decoupling — 2026-11-03

- Added `Start-Zazu-Dev.cmd` and `Stop-Zazu-Dev.cmd` wrappers with `ops/windows/Start-ZazuDev.ps1`.
- Purpose: let the owner start/stop the temporary Laravel development server without leaving VS Code open. This is explicitly **not** a production host or Windows service.
- Launcher validates PHP 8.4+, project dependencies and `.env`; defaults to loopback; refuses to take over an occupied port; logs to timestamped files under `storage/logs`; and stops only a process chain it can identify as this Zazu project.
- A non-loopback bind can be supplied for trusted-LAN testing, but plain HTTP on a LAN IP is not a secure origin and cannot prove phone service-worker/PWA offline operation.
- Static source re-read completed. Owner-PC execution, process survival after VS Code closure and stop behaviour are **NOT YET VERIFIED**. No claim of runtime success.
- Commits: `412b979` launcher; `8323d97` start wrapper; `b28445f` stop wrapper; `dc11eb9` process verification; `bc1ea51` bind-address validation; `f821ea6` local test runbook; `4d7e4eb` deployment-readiness status.
- The future production target remains a paid server selected later; no local-PC hosting configuration or infrastructure purchase is authorized or planned in this cycle.


## Offline architecture alignment and evidence hygiene — 2026-11-03

- Updated `docs/ZAZU_OFFLINE_FIRST_ARCHITECTURE.md` to match the owner's clarified deployment direction: paid persistent production server in future; Windows PC development-only; device-local IndexedDB continuity during internet/server outages; server remains shared business-data authority when reachable.
- Explicitly ruled out an implied multi-device LAN sync mode while the cloud server is unreachable. That would require a separate approved architecture.
- Corrected a misleading browser-test filename: `e2e/offline-attachments.spec.js` contained only an offline job-creation test and did not test attachment transfer. Renamed it to `e2e/offline-work-creation.spec.js`; test content unchanged. Offline attachment integration remains NOT IMPLEMENTED in the active app.
- Current main after this batch: `a0637f15efbf05330c6d561bf4cfbcf1431bbdbe`. Exact-head CI has been triggered and is pending reconciliation.


## Director repository security audit — 2026-11-03

- Completed a main-branch source review of authentication/session handling, tenant boundaries, permissions, sync pairing/token handling, mutation idempotency, attachment/media storage, PWA cache/offline persistence, spreadsheet parsing, tests, workflows and current security documentation.
- Hardened CSV/XLSX parser resource boundaries, corrected the customer import controller's workbook response-shape mismatch, and applied request-ID/security-header middleware to the API group.
- Added regression coverage for parser budgets, customer import preview and API response headers. These are **implemented, not yet CI-verified** until the new exact-main SHA completes its test workflows.
- Recorded the complete findings and evidence limits in `docs/ZAZU_SECURITY_AUDIT_2026-11-03.md`.
- Security-audit correction: business-scoped IndexedDB and device tokens are intentional under DEC-019 owner-approved trusted-device pairing. The owner selects and discloses the bounded data copied to the paired device; normal staff web permissions are not a separate record-level security boundary inside that trusted offline device. Remaining controls are device inventory/revocation, physical device lock and a lost/reassigned-device response. Do not redesign as user-scoped storage without an owner-approved architecture change.
- GitHub reports `main` protected with no required status-check contexts. Owner must configure required checks; no setting was changed in this audit.
- No live Zazu black-box test was performed in this repository-only review because no Zazu runtime URL and test credentials were supplied. No security-alert clean bill is claimed.


## Director cycle — 2026-11-04 — installation recovery scope

A source audit found that business-owner settings exposed installation-wide backup and restore commands. The commands operate on the full database and private storage, not one tenant. This was a cross-business disclosure/destructive-restore boundary defect.

Corrective source changes on main:
- Removed business-owner backup/restore routes and controller actions.
- Removed misleading tenant-scoped backup/restore controls from settings.
- Added an explicit installation-operator / tenant self-service limitation notice.
- Updated regression tests and the recovery runbook.

Evidence: exact-head CI pending for this commit. Existing CLI command tests do not prove a real recovery drill. Business-scoped export/restore remains unimplemented and must not be claimed.

Next priority: finish V1 recovery design with explicit tenant-vs-installation scope, then verify remaining current-head CI and continue populated commercial workflow and offline device acceptance. Keep all work in this repository and on main.


## Director V1 scorecard — 2026-11-04

The V1 specification scorecard is recorded in `docs/ZAZU_V1_SCORECARD_2026-11-04.md`. Current unweighted evidence-maturity average: **3.0/5 (60/100) across 13 domains**. This is not a feature-completion percentage.

Assessment source head: `d1e2a4397b064d3fbfe7fc2cf1dc59236e00922f`; CI for that exact head was queued when assessed. Do not promote the score or readiness until the latest-head checks finish. Commercial release remains NO-GO.


## Exact source CI — 2026-11-04

For application SHA `d1e2a4397b064d3fbfe7fc2cf1dc59236e00922f`, Laravel, quality/secret scan, Psalm, PHPMD, populated upgrade/rollback and push-on-main passed. Browser smoke remains in progress; SonarCloud was skipped. Documentation-only commits followed; no application source changed after the assessed source SHA. Release certification remains blocked by browser acceptance and the operational/legal/brand gates.


## Director offline transition and attachment lifecycle hardening — 2026-11-04

- Preserved DEC-019's owner-approved business-trusted device architecture. Do not bind the offline business workspace to the current staff user or clear it on a same-business account transition without an approved architecture change; the paired device is explicitly trusted for the bounded business data selected by the owner.
- Added business-scoped local attachment storage using `zazu-phone-attachments-business-<business-id>`; new and downloaded records carry the business ID.
- Logout assessment now includes pending local attachments. The logout confirmation warns about pending attachment files, and local session cleanup clears the active business's attachment database.
- Cleanup fails closed if attachment inspection or clearing fails. The old unscoped `zazu-phone-attachments` database is deliberately not migrated/deleted because its records lack reliable business ownership metadata.
- Added PWA regression assertions for the attachment database boundary and logout cleanup.
- Source/test commits: `92bb9c0`, `8a14965`, `ba48760`, `cdea317`. Source/test head: `cdea317643dd5e226d685f84c32f51d875defc80`.
- CodeQL run `41882179271` completed successfully. This is not a substitute for Laravel, browser, or runtime acceptance; those checks were not present on the exact source SHA at the time of review.
- **Not complete:** account/session transition acceptance (logout, expiry, same-business account change, business switch, interrupted cleanup, late responses and revocation) and active Work-screen offline attachment integration. The legacy attachment client remains dormant.
- Evidence: `docs/ZAZU_OFFLINE_TRANSITION_AND_ATTACHMENT_HARDENING_2026-11-04.md`.


## Owner-directed active focus — 2026-11-05

The owner has explicitly directed the current execution focus to **UI/UX improvement**. This is the active session priority and supersedes the older offline-continuity sequencing until the owner redirects scope. Offline acceptance gates remain open; they are not being silently closed or reclassified.

Current UI/UX mission: continue the property-level consolidation of sidebar, brand and hierarchical navigation rules in `resources/css/app.css`, preserving the separate responsive/mobile ownership and all navigation interaction states. Keep the Designer 1 and Designer 2 references as design direction, not copy targets.

Latest source change: commit `29a38f55d3d3ff5979cf844ddbd624a9ea279515` consolidated the brand lockup/logo/fallback mark base styling and hierarchical navigation trigger/flyout base geometry into their canonical declarations. Source re-fetch passed. Rendered visual acceptance and Vite/runtime verification remain **UNVERIFIED**.


## Dashboard hero theme correction — 2026-11-05

- Active UI/UX focus continues.
- Corrected the final dashboard hero gradient so it uses semantic theme surface tokens instead of a fixed white background.
- Added a source regression assertion in `tests/Feature/UiProportionFoundationTest.php`.
- Commit evidence and source verification recorded in `docs/DIRECTOR_CSS_AUTHORITY_AUDIT_2026-10-04.md`.
- Browser-rendered acceptance and PHPUnit execution remain open; no visual/runtime pass is claimed.

## Dashboard hierarchy and visual proportion pass — 2026-11-05

- Continued the owner-directed UI/UX reference pass on the dashboard, using Designer 2 as a design direction rather than copying its content or visuals.
- Moved dashboard KPIs ahead of the work queues and separated create shortcuts into a dedicated Quick actions strip. Upcoming work and quote action now share the second row; Priority Now and Resource Readiness share the lower row.
- Refined final dashboard proportions: widened the hero's contextual side panel, compacted card headers and KPI scale, normalized dashboard surface radii, tightened event/priority spacing, and laid out resource links in two desktop columns with a single-column phone fallback.
- Consolidated the latest dashboard composition into one final CSS authority block instead of stacking another competing set of overrides.
- Added source regression coverage for hierarchy, quick-action placement, card proportions and responsive resource layout.
- Commits: 9fd69b9, 0046af8, dbb6a3a, 41bd22d, 03364ca, 99b25b4, 469c41e, a743263. Latest source/test head: a743263ef89ee0e664e6ef826d5327a53896fb1c.
- Re-fetched CSS and test files from main; verified expected layout declarations and balanced CSS braces. Only main was visible.
- Not verified: PHPUnit execution, Vite build, browser-rendered desktop/tablet/mobile acceptance, and light/dark rendered comparison. GitHub combined status was empty and the workflow-run wrapper returned no runs; neither is evidence of a pass.
- Next UI/UX source objective remains the documented property-level consolidation of sidebar, brand and hierarchical navigation rules in resources/css/app.css, preserving responsive/mobile ownership and all navigation state dependencies.

## Sidebar and hierarchical navigation cascade consolidation — 2026-11-05

- Continued the owner-directed UI/UX work in `resources/css/app.css`, consolidating repeated sidebar/navigation base rules, active/hover states, priority treatment, sidebar workspace identity, footer card/title/copy, and hierarchical desktop flyout geometry.
- Consolidated base selectors to one canonical rule: `.zazu-nav-stack`, `.zazu-nav-group`, `.zazu-nav-label`, `.zazu-nav-link`, `.zazu-nav-icon`, `.zazu-sidebar-workspace` and its label/name/meta/dot, `.zazu-nav-link:hover`, `.zazu-nav-link.active` and marker/icon states, `.zazu-nav-priority`, and sidebar footer card/title/copy.
- Consolidated the desktop hierarchical-navigation styles into a readable canonical block. Preserved the distinct open-state descendant and direct-child selectors, active-area styling, hover/focus access, light-theme active override, flyout active styling, 850px inline expansion and reduced-motion rules.
- Kept `resources/css/zazu-responsive-theme.css` and `resources/css/zazu-mobile-refinement.css` unchanged; their scrolling, off-canvas sidebar, mobile tap target and safe-area ownership remain intact.
- Added regression checks in `tests/Feature/UiProportionFoundationTest.php` for canonical rule counts and preservation of navigation states, theme override, responsive ownership and reduced motion. The chevron has a legitimate reduced-motion override and is validated separately rather than incorrectly treated as a single-rule selector.
- Commits: `faa5bd9`, `e2a4139`, `8649a6e`, `bea06b9`, `a8975f9`, `6c37f97`, `9461414`, `9bce61e`, `435454a`, `0a83eef`, `94479ce`. Current source/test verification to be reconciled at the latest commit below.
- Source re-fetch checks confirm the 25 canonical base/state selectors each have one definition (chevron intentionally excluded because its reduced-motion override is separate), CSS braces are balanced, and no literal newline escape remains. Only `main` was visible.
- Not verified: PHPUnit execution, Vite build, PHP/CSS toolchain lint, browser-rendered desktop/tablet/mobile acceptance, or light/dark rendered comparison. Empty status/workflow wrapper responses are not treated as passes.
- Next UI/UX step: review the remaining brand lockup, logo/fallback mark and topbar identity rules against the canonical shell, then arrange a rendered acceptance pass before broad cleanup of unrelated app.css history.

## Brand lockup and topbar cascade review — 2026-11-05

- Reviewed brand identity and topbar-related rules after sidebar/navigation consolidation.
- Brand link, logo image, lockup strong/span colour rules each have one canonical base declaration. The brand fallback mark intentionally shares geometry with the uploaded logo and has a separate appearance rule; these are complementary roles, not duplicate declarations.
- Added regression coverage for the unique brand link/logo/lockup rules and retained assertions for the shared logo/fallback geometry.
- Topbar, page title, eyebrow, workspace status and theme-toggle styles have multiple later declarations tied to sequential theme, wallpaper, mobile and shell treatments. They were deliberately not flattened: source inspection shows these are context-specific cascade overrides, not safe duplicate base rules. A follow-up should map their media/theme ownership before any migration.
- No additional brand or topbar CSS was changed in this review. Responsive/mobile stylesheets remain unchanged.
- Test update: `b06cc02e72fe7712671ab3058f1422d87907bd1d`.
- Source verification: brand base selector counts and the final navigation/sidebar state checks are recorded in `tests/Feature/UiProportionFoundationTest.php`. Runtime and rendered acceptance remain pending.
- Next objective: map the topbar/page-title/theme-toggle cascade by theme and viewport into explicit ownership, then consolidate only where computed property precedence is unchanged. Browser light/dark and mobile acceptance remains a release gate.

## Topbar, page title and theme-toggle cascade consolidation — 2026-11-05

- Confirmed the actual shared layout asset order: `app.css` → `zazu-responsive-theme.css` → `zazu-final-visual-sweep.css` → `zazu-mobile-refinement.css`. The header markup is in `resources/views/components/app-layout.blade.php`.
- Consolidated the desktop base rules in `app.css`: `.zazu-topbar`, `.zazu-topbar-inner`, `.zazu-page-title`, `.zazu-eyebrow`, and `.zazu-theme-toggle` now each have one canonical top-level definition.
- The topbar retains its theme-token sidebar surface, blur, sticky desktop positioning and current shadow. Its user-wallpaper translucency remains a higher-specificity state rule.
- The topbar inner container now has one desktop sizing rule. The <=1023px side padding and <=767px legacy layout declarations remain in their media scopes; the responsive layer retains the >=821px search/tab/action constraints.
- The page title keeps its existing effective desktop size/weight/line-height/letter-spacing and sidebar-aware foreground. The mobile layer still owns the 18px mobile title and 17px <=520px title.
- Moved shared `.zazu-eyebrow` typography out of `zazu-final-visual-sweep.css` and into the canonical `app.css` rule. The extension stylesheet no longer declares or groups the shared eyebrow selector. The effective 12px/700/1.35/.055em/non-uppercase typography and token-aware label colour are preserved; specific command-banner eyebrow contrast remains separate.
- Consolidated the theme toggle's effective 36px desktop geometry, token-aware surface/border/foreground, radius, focus and transition base. Hover/active/focus/reduced-motion states remain distinct. The mobile layer continues to size the toggle at 40px.
- Source property comparison against the pre-pass `b550514` tree found no effective declaration changes for topbar, page title or eyebrow. The topbar inner padding shorthand resolves to the same 11px vertical / 24px horizontal dimensions. The theme-toggle border shorthand carries the same token colour as the former border plus border-color cascade.
- Kept `zazu-responsive-theme.css` and `zazu-mobile-refinement.css` unchanged. Added `test_topbar_title_and_theme_toggle_have_one_canonical_base_with_theme_and_viewport_overrides` to `tests/Feature/UiProportionFoundationTest.php`.
- Commits: `dc42c84` (canonical desktop header bases), `7a40909` (shared eyebrow ownership), `cafea19` (regression coverage). Latest source/test head to be verified below.
- Not verified: PHPUnit execution, Vite build, browser-rendered desktop/tablet/mobile acceptance, light/dark rendered comparison, or physical touch/keyboard acceptance. Source checks do not replace those runtime gates.
- Next objective: obtain rendered browser acceptance of the dashboard/shared shell at desktop, tablet and mobile widths in both themes. Do not continue broad CSS consolidation until those visual results are reviewed.


## Dashboard density and duplicate-priority cleanup — 2026-11-06

- Removed two repeated entries from the Priority Now panel: workspace setup (already presented in the dedicated setup banner) and the next scheduled event (already listed in Upcoming Events).
- Priority Now now stays focused on distinct actions: quote review, open purchasing, low stock, unpaid invoices, and the setup banner remains separately visible to owners.
- Removed the upcoming-event dependency from the empty-priority condition so a scheduled event alone no longer suppresses the truthful "No immediate priority flagged" state.
- Added a source regression test to prevent setup and upcoming-event duplication inside the priority panel.
- Scope deliberately limited to dashboard content density; no unrelated shell, palette, route, permission, or data changes.
- Source re-fetch and structural checks required after commit. PHPUnit, Vite, and rendered desktop/tablet/mobile plus light/dark acceptance remain unverified until executed in an available runtime/browser.


## Dashboard run-sheet density refinement — 2026-11-06

- Limited the dashboard run-sheet to the five nearest upcoming, non-completed events; the existing All work link remains the route to the complete work list.
- Reduced dashboard event-row minimum height from 76px to 64px and tightened vertical padding. Existing mobile status placement and wrapping rules remain unchanged.
- Added regression coverage for the five-record limit, compact row sizing and continued access to the full work list.
- Source-level verification only; PHPUnit, build and rendered viewport/theme acceptance remain outstanding.


## Dashboard navigation duplication removal — 2026-11-06

- Removed the extra "Workspace surfaces" navigation strip beneath Quick actions. It repeated destinations already available through the primary navigation and the dashboard's own focused actions, adding a second navigation layer not present in the supplied reference.
- Removed its now-unused component CSS selectors and removed only the commandbar selector from shared grouped rules, preserving unrelated selectors and declarations.
- Added regression coverage confirming the duplicate strip and its styling stay absent while Quick actions remain.
- Dashboard layout is shorter and more focused; primary navigation remains the full route-discovery surface.
- Source re-fetch and selector/structure checks required after commit. PHPUnit/build/browser visual acceptance remains outstanding.


## Dashboard copy density pass — 2026-11-06

- Removed secondary explanatory sentences from the Quick actions, Upcoming events, Quote pipeline, Priority now and Workspace health headers. The section labels and titles already communicate their purpose.
- Removed repeated helper captions from the four Workspace health count tiles; each tile keeps its clear metric label and live count.
- Removed the dashboard-only finance disclaimer from Quote pipeline; finance navigation remains available and the finance workspace remains the source of financial detail.
- Kept action-specific priority explanations and the honest operational-readiness heading. No data, permissions, routes or calculations changed.
- Added regression coverage for the compact copy and retained section identity.
- Source re-fetch and structure checks required after commit; runtime and rendered acceptance remain outstanding.


## Dashboard KPI density refinement — 2026-11-06

- Removed four secondary KPI captions that repeated the meaning of each metric label or added no decision-making context.
- Kept all permission-gated KPI cards, live values, labels, routes and calculations unchanged.
- Removed the now-unused KPI-caption CSS rule.
- Added regression coverage for the four retained KPI labels and the absence of redundant captions/styling.
- Source-level change only; runtime, PHPUnit, Vite and rendered viewport/theme acceptance remain unverified.


## Theme default correction — 2026-11-06

- Root cause: both the layout's pre-paint bootstrap and the shared JavaScript theme initializer selected dark mode from the operating-system preference whenever no explicit Zazu theme had been saved. This made the dashboard open in dark mode on devices configured for dark appearance, despite the light-theme dashboard references being the active design target.
- Correction: default to the reference-aligned light theme when no explicit Zazu preference exists. A saved `light` or `dark` preference still takes precedence, and the manual theme toggle continues saving the user's selection.
- Kept the dark palette and all explicit dark-theme component rules intact; this changes only the no-preference fallback.
- Added a source regression test covering both initialization paths and persistence of an explicit toggle choice.
- Source verification required after commit. PHPUnit, Vite and rendered browser checks remain unverified in this environment.


## Light shell colour refinement — 2026-11-06

- Adapted the existing light-theme shell to the supplied visual direction rather than copying either reference literally.
- Shifted the light sidebar from a very pale cyan surface to a clearer mid-blue surface with coordinated navy-blue foreground, muted labels, hover and active tokens. Existing navigation, flyouts, footer and workspace panels inherit these shared tokens.
- Separated the desktop topbar from the sidebar palette: it now uses the existing near-white page surface and normal page foreground, matching the references' lighter header/content relationship.
- Updated page-title/eyebrow and theme-toggle colours to use page tokens instead of sidebar tokens. Dark-theme token values and explicit dark-mode overrides remain unchanged.
- Added source regression coverage for the light shell palette and separate topbar surface.
- Source-level only; rendered acceptance, PHPUnit and Vite build remain outstanding.


## Light sidebar navigation state refinement — 2026-11-06

- Tuned navigation hover and active states to stay within the new blue sidebar palette rather than borrowing the general content-surface colours.
- Active navigation now reads as a light selected surface with a restrained blue edge indicator; hover uses a soft blue tint. Navigation icons inherit the sidebar's coordinated muted blue.
- No navigation structure, routes, permissions, interaction logic or dark-theme rules changed.
- Added source regression coverage for the light navigation state declarations.
- Source-level only; rendered visual acceptance and automated runtime checks remain outstanding.


## Dashboard KPI surface treatment — 2026-11-06

- Added four restrained semantic surface tints to dashboard KPI tiles, using existing info, success, primary and warning theme tokens blended with the current card surface.
- Preserved the existing metric content, dimensions, links, hover behaviour and responsive grid. No positional business meaning is assigned to the tints; they are purely visual differentiation.
- Added source regression coverage for all four token-based treatments.
- The treatment uses theme tokens so the existing dark theme remains internally consistent; rendered contrast still requires browser acceptance.


## Shared panel header surface refinement — 2026-11-06

- Source review found shared panel headers remained flat while form-section headers already used the workspace surface token family, creating a small but repeated component-language inconsistency.
- Added a restrained surface-2 to surface gradient to the canonical shared `.zazu-panel-head` rule, retaining its existing dimensions, border, content, layout and responsive behaviour.
- The treatment is token-driven and therefore follows the existing light/deep-water theme definitions; no page-specific overrides or new palette values were introduced.
- Added a focused regression assertion against the effective final panel-header rule.
- Source-level verification only. PHPUnit, Vite build and rendered desktop/mobile review have not been run in this environment.

## Workspace search reference alignment — 2026-11-06

- The supplied dashboard reference uses a prominent, wide workspace search field; the live desktop header constrained it to a maximum of 330px, materially weakening the shared-shell match.
- Expanded the search field to a responsive 420px–760px range on wide desktop screens (1200px+), while retaining the existing compact sizing below that breakpoint and the existing full-width mobile rule.
- Updated the placeholder to name the actual searchable business areas: events, customers, quotes and inventory.
- Added a focused regression assertion covering the wide desktop sizing, mobile rule and search scope copy.
- Source re-read confirms all three changes are present on main. Automated tests, Vite build and rendered desktop/mobile/light/dark acceptance remain UNVERIFIED; the browser-rendered appearance still requires owner review.

## Dashboard KPI icon hierarchy — 2026-11-06

- Matched the supplied dashboard reference's icon-led KPI scan pattern using four lightweight inline SVG icons: active events, draft quotes, customers and the next 14 days.
- Reworked the KPI cards into a two-column icon/content layout on desktop and a narrower icon/content layout on phones. The existing KPI labels, live Blade values, route destinations, permission gates and metric calculations remain unchanged.
- Kept the icon treatment CSS-token-driven so the existing light and dark themes inherit their current semantic colours; no raster/generated assets were added.
- Added a focused regression test for all four icons, desktop/mobile card geometry and continued live metric rendering. The stylesheet is included in the shared app Vite bundle.
- Source re-fetch confirms the markup, CSS and regression assertion are present on main. PHPUnit, Vite build and rendered desktop/tablet/mobile light/dark acceptance remain UNVERIFIED; do not claim visual acceptance until rendered review is completed.

## Dashboard composition correction — 2026-11-06

- Owner rejected the previous refinement as insufficiently close to the references. Re-inspection identified the main structural mismatch: KPI cards and lower operational panels were not consistently arranged in the reference scan order.
- Corrected the existing canonical Director dashboard rule block rather than leaving a second appended override layer.
- Desktop composition now follows: welcome banner → four KPI cards in one row → quick-action strip → Upcoming Events / Quote Pipeline side-by-side → Priority Now / Operational Readiness side-by-side.
- Tablet and mobile breakpoints collapse the grid deliberately; KPI and quick-action cards use two columns on narrower screens and one column on very narrow screens.
- Resource readiness tiles now use a compact two-column layout on desktop, matching the paired lower panel rather than stretching across the full dashboard.
- Scope remains limited to dashboard CSS and its regression test. No shared shell, sidebar, navigation, other page views, data, routes or permission rules changed in this pass.
- Canonical CSS correction: `4f0d2176f34a643ef68b05eae1cb6a2b937761d9`. Regression assertion update: `74165ccce0c4008471989b290af797e76723a417`.
- CI evidence for the latest tested source: dashboard-specific assertions pass; Laravel reports 369 passed and 6 failures in legacy shell/onboarding expectations unrelated to this dashboard layout. PHPMD still flags the existing DashboardController `__invoke()` at 101 lines (threshold 100). Psalm and populated upgrade/rollback checks pass. Browser regression completed with 56 passed and 6 failures: Finance horizontal overflow, offline Work-record checkbox interaction, and browser theme assertions still expecting the superseded palette. No dashboard-specific rendered screenshot/acceptance was produced; rendered visual acceptance remains UNVERIFIED.
- Owner visual approval remains the acceptance gate. Do not propagate dashboard styling to other pages.



## Dashboard adaptive KPI alignment — 2026-11-06

- Corrected the canonical dashboard KPI grid from a fixed four-column layout to auto-fit columns with a 190px minimum card width. Permission-filtered workspaces now use the available row width instead of leaving a blank KPI slot for each hidden metric.
- Reduced KPI grid gap from 12px to 10px to keep the strip proportionate as cards expand.
- Updated the dashboard composition regression assertion to require the adaptive grid declaration.
- No metric data, permissions, routes, dashboard section order, shared shell or non-dashboard page changed.
- Source re-read confirms the canonical CSS rule and matching regression assertion on `main`. PHPUnit, Vite build and rendered desktop/tablet/mobile light/dark acceptance remain unverified. Browser connector was unavailable because no originating browser context was attached.
- Next: review the dashboard's desktop/tablet breakpoints and shortcut-row density; retain the supplied reference scan order and do not propagate styling beyond the dashboard.


## Dashboard desktop/tablet composition refinement — 2026-11-06

- Kept the paired dashboard work panels in a two-column layout through tablet widths; the full single-column collapse now begins at 760px rather than 900px.
- Preserved the 900px transition for the welcome hero and compact two-column KPI/quick-action rows, so the banner and dense controls adapt before the main work panels stack.
- Corrected the quote-pipeline regression assertion to verify the actual four-stage quote grid instead of accidentally asserting the KPI grid rule.
- Added an explicit regression assertion for the 760px dashboard stacking breakpoint.
- No shared shell, non-dashboard page, business logic, routes, permissions or metrics changed.
- Source-level verification only. PHPUnit, Vite build and rendered desktop/tablet/mobile light/dark acceptance remain unverified; browser access is unavailable in this session.

## Dashboard panel density and alignment refinement — 2026-11-06

- Tightened Upcoming Events row height and vertical spacing while retaining the date tile, event identity, reference and status hierarchy.
- Reduced Quote Pipeline stage-card height and internal spacing, and tightened the queue-state strip so the four lifecycle stages remain the main visual focus.
- Reduced Workspace Health tile height and padding to balance the paired lower panels without hiding labels or live values.
- Kept Priority Now content, business data, routes, permissions, responsive breakpoints and shared shell unchanged.
- Added source regression coverage for the compact panel dimensions. Source changes are committed; PHPUnit, Vite and rendered desktop/tablet/mobile light/dark acceptance remain unverified.

## Dashboard panel-header copy correction — 2026-11-06

- Source audit found four explanatory subtitles still present in the dashboard panel headers despite the earlier copy-density objective.
- Removed the leftover subtitles from Upcoming Events, Quote Pipeline, Priority Now and Workspace Health. Section titles, action links, quote-stage descriptions, live records and priority explanations remain.
- Updated the Priority/Workspace Health source assertions and added a regression test for all four concise panel headers.
- Dashboard-only Blade and test changes. No data, routes, permissions, panel structure, shared shell or theme changes.
- Source changes committed; PHPUnit, Vite and rendered browser acceptance remain unverified.

## Dashboard priority empty-state accuracy — 2026-11-06

- Corrected the Priority Now clear-state sentence: its condition checks setup, quotes and resource actions, but does not check the event schedule. The copy no longer claims schedule status is part of that condition.
- Added a regression assertion for the truthful empty-state wording.
- Dashboard-only copy and test change; no priority logic, data, routes or permissions changed.
- Source re-fetch verification required. PHPUnit and rendered acceptance remain unverified.



## Dashboard fallback KPI consistency — 2026-11-07

- Source scan found the invoice KPI fallback (shown when quotes are unavailable) still used the older text-only markup while the other KPI cards use the icon-led reference layout. This produced a different grid structure for users whose workspace exposes finance but not quotes.
- Added the same compact document icon treatment and explicit label span to the invoice fallback. Metric value, supporting copy, destination and permission/tool conditions remain unchanged.
- Added a focused regression assertion that the invoice fallback includes the icon wrapper.
- Dashboard Blade and its existing feature test only; no shared shell, non-dashboard pages, data, routes, metrics or permissions changed.
- Source re-fetch verification completed after the commits. PHPUnit, Vite build and rendered desktop/tablet/mobile light/dark acceptance remain unverified.


## Dashboard mobile event status visibility — 2026-11-07

- Responsive cascade review found an older max-620px rule hiding `.zazu-dash-status`, while the refined event row retained a status-and-arrow trailing group. On phones this removed useful event state from the dashboard list.
- Added a narrow final override to keep the status badge visible at compact size, constrain its width and ellipsize long labels. Desktop and tablet rules are unchanged.
- Added a focused source-level regression test for the mobile status rule.
- Dashboard visual CSS and its existing feature test only; no event data, status logic, routes, permissions, shared shell or non-dashboard pages changed.
- Source re-fetch verification pending. PHPUnit, Vite build and rendered acceptance remain unverified.


## Dashboard mobile event markup alignment — 2026-11-07

- Responsive audit compared the current Blade event row (date, event copy, trailing status/arrow) with an older mobile CSS block that still targeted removed time/connector elements and treated the status as a direct grid child.
- Removed the obsolete mobile layout block. The canonical mobile rules now own the actual three-child event row, with the status kept visible inside its trailing flex group and the existing narrow-screen two-row treatment preserved at 380px.
- Replaced the stale regression assertions with checks against the current Blade structure and the actual status wrapper; added guards against reintroducing the removed selectors and invalid grid placement.
- Dashboard CSS and the existing UI proportion feature test only. No business logic, data, routes, permissions, shared shell or non-dashboard pages changed.
- Source-level verification only. PHPUnit, Vite build and rendered desktop/tablet/mobile light/dark acceptance remain outstanding.


## Dashboard adaptive workspace-health panel — 2026-11-07

- Adaptive configuration audit found the Workspace Health section was gated only by experience level. In a non-basic workspace with purchasing, inventory, assets and finance all disabled, the dashboard could render an empty panel.
- Tightened the Blade visibility condition: Workspace Health now appears only for non-basic workspaces with at least one of those four resource tools enabled. Individual metric cards retain their existing per-tool gates.
- Added a regression test covering all four resource-tool gates and the placement of the section condition.
- Dashboard Blade, its existing UI proportion feature test and this engineering state record only. No metrics, data, permissions, routes, shared shell or other page changed.
- Source-level verification only. PHPUnit, Vite build and rendered desktop/tablet/mobile light/dark acceptance remain outstanding.


### Dashboard reference refinement — sparse workspace layouts

- Refined KPI layout for workspaces exposing only one or two metrics: a single KPI is capped and centered; two KPIs share a centered, bounded pair rather than stretching across the full dashboard.
- Quick Actions now use an adaptive minimum tile width so permission-filtered action sets naturally reflow instead of leaving oversized empty columns.
- Added source-level regression coverage in `UiProportionFoundationTest`.
- Commit sequence: `3173be5` (dashboard CSS), `e8f25b3` (regression test).
- Runtime PHPUnit, Vite build and browser visual review remain unverified in this environment.


## Dashboard visual identity refinement — 2026-11-07

- Refined the dashboard welcome banner with a clearer primary-content/capability split, stronger greeting hierarchy and a softer coastal surface treatment.
- Rebalanced KPI tiles around the icon, label and live value so the metric reads as one deliberate unit; retained sparse-workspace and mobile layout rules.
- Unified dashboard work panels with a restrained blue-tinted border, softer shadow and more compact panel headers.
- Added source regression coverage for the dashboard-only visual hierarchy.
- The sidebar was intentionally left unchanged: it belongs to the shared application shell, and changing it would extend this dashboard-only approval scope to every page. Its deep-navy treatment remains a separate owner-approved shell objective.
- GitHub source changes only. PHPUnit, Vite build and rendered desktop/tablet/mobile light/dark review remain unverified; visual approval is still pending.


## UI acceptance contract reconciliation — 2026-11-07

- Rechecked the current dashboard Blade, canonical app theme tokens and dashboard visual stylesheet on `main` before changing test expectations.
- Updated stale source-level assertions to the implemented interface: coastal light tokens (`#F5FBFE`, `#159DD2`), current sidebar/text colours, current event date labels, approved/declined quote-stage labels, Workspace health naming and View all events action.
- Updated the onboarding dashboard assertion to the current Quick actions / Open finance surface, and updated the Playwright theme test to the current coastal palette while retaining the existing dark-mode expectations.
- No application CSS, Blade, business logic, routes or permissions changed in this correction pass.
- Changes are committed on `main`; source re-fetch verification is being completed. GitHub Actions rerun and runtime PHPUnit/Playwright results are not yet available, so do not treat these expectation updates as a green CI result. Finance overflow and other non-dashboard browser failures remain outside this dashboard-only objective.

## Owner dashboard acceptance and V1 execution handoff — 2026-11-07

- Owner accepted the current dashboard as sufficient for now and directed development to continue against V1 requirements. The dashboard refinement mission is closed by owner acceptance; no further dashboard iteration is active.
- Active development focus: V1 implementation and release-evidence closure.
- Added `tests/Feature/SyncAttachmentIdempotencyTest.php` on `main` (commit `d7debb0d8378633174f74479f26258d469799ed6`, corrected namespace commit `b3f9eb360e6259868993814419c09e939a3d372d`). The tests cover exact-content retry idempotency and reject reuse of the same key for different file bytes with otherwise matching metadata.
- PHP syntax check passed locally under PHP 8.4.24. Laravel execution and exact-head CI remain UNVERIFIED; the available combined-status query returned no status contexts. Do not mark the tests passed until the Laravel/quality workflow reports success for the exact current main SHA.
- Next V1 implementation target: integrate phone-local attachment queue/upload/download into the active Work attachment flow using the current business-scoped `ZazuOffline` contract. The existing `public/offline-attachments.js` is legacy code and must not be loaded unchanged; it depends on globals absent from the active Laravel shell. Preserve private storage, business isolation, retry/idempotency, logout cleanup and device-recovery behavior.

## Attachment idempotency coverage extension — 2026-11-07

- Extended `SyncAttachmentIdempotencyTest` to cover three server contracts: identical retry returns the original attachment; same key with different bytes but matching metadata returns HTTP 409; same key cannot move an attachment to a different job in the same business.
- Latest test commit: `a1d16620996479d461574c5317dfccc82c48d35d` on `main`.
- Re-read current-main test source and ran PHP 8.4.24 syntax lint: PASS. Laravel feature execution remains UNVERIFIED pending exact-head CI evidence.


## Phone-local Work attachment integration — 2026-11-07

- Connected the authenticated Work attachment form to the active `ZazuOffline` client. The legacy `public/offline-attachments.js` remains unused.
- Added business-scoped IndexedDB file queue/read/write, idempotent multipart upload retry through `/api/sync/attachments`, remote attachment listing and private-file download caching. Pending local Blobs are retained until the server returns a verifiable attachment ID.
- Multi-file selection (1–10 files, each up to 20 MB) is validated first and saved in one IndexedDB read/write transaction. A failed batch transaction does not intentionally leave a partial queue.
- Work panel uses the online Laravel form path when the same-origin host is reachable; when unreachable it stores the selected files on the device. Unpaired devices are told to pair before upload can occur.
- Added Work attachment UI state/list, local file open action, online/reconnect refresh, and current-page duplicate avoidance using server attachment IDs.
- Advanced service-worker static cache to `zazu-static-v30`, with matching Laravel source assertion and Node service-worker cache test expectations.
- Added `e2e/offline-attachment-sync.spec.js` for disconnected local file persistence, bearer-authenticated reconnect upload using the original UUID idempotency key, and caching a remote attachment. Expanded `PwaOfflineFoundationTest` source assertions. Existing `SyncAttachmentIdempotencyTest` remains responsible for server duplicate/change/move-key contracts.
- Source write/re-read commits: `789820c`, `36acc45`, `c7cdd3d`, `3cf20a6`, `eedacf8`, `fd695f2`, `bc1de62`, `c94678a`, `6ceeec3`, `fd61e3d`, `65a88cc`, `c580099`, `1b06cce`, `1c05cb5`. Documentation follow-up is recorded in `docs/ZAZU_V1_STATUS.md` and `docs/ZAZU_OFFLINE_FIRST_ARCHITECTURE.md`.
- Current `main` source changes are **IMPLEMENTED**. GitHub combined status returned no status contexts for `bc1de6224971803bdecee18dc8bcbed7781c5287`; the commit-workflow-runs connector returned no PR-triggered runs for `1c05cb5359695e0a5588593c1de0fdf3407b3c2e`. Exact-head Laravel, Vite, Playwright and quality execution therefore remain **UNVERIFIED**. No readiness uplift is claimed.
- Physical installed-device acceptance remains open. The current upload endpoint requires a server-backed event ID; an offline-created job must first receive its server identity. Verify the cross-step flow after current source checks are available.


### Attachment identity reconciliation — 2026-11-07

- Added `reconcileAttachmentEventIds()` to the active sync client. Pending, business-scoped attachment records with `event_local_id` are matched against `state.jobs` and updated with the job's `server_id`/`id` in one IndexedDB transaction before upload.
- This preserves the older phone queue's stable local-parent reference across offline Work creation and later identity assignment. The online API request still carries only the resolved server event ID.
- Added a Playwright regression that seeds the previous queue record shape, assigns the synced job identity, and verifies the exact event ID and idempotency key sent by the client.
- Added source-contract assertions and updated the V1 status/architecture notes. Latest implementation/test commit: `e3e2c1ed1fc8c55f4d74ff0b281ffc044e7cb806`; status-doc follow-up: `c30fd588f8dfa29a9e3a52ab87d2b281968b8dfc`.
- Runtime test execution remains UNVERIFIED. The browser test covers the identity-remapping boundary, not a fully rendered offline-create → reconnect → attach journey.


### Attachment list/download API acceptance coverage — 2026-11-07

- Extended `SyncAttachmentIdempotencyTest` through the paired-device API: upload returns the idempotency key/event identity consumed by the client; list returns the expected attachment identity, original name and size; private file download returns the named file.
- Added foreign-business negative cases for event-filtered listing and private download. The fixture confirms the foreign private file actually exists, so the download denial exercises the business boundary rather than a missing-file response.
- Current implementation/test source is on `main`; latest test commit: `bd47ec672ea0f4b609614ce24c3baa9d1ac4bd0a`. Laravel/CI execution remains UNVERIFIED.


### Trusted-device session transition coverage — 2026-11-07

- Added `e2e/offline-session-transition.spec.js`: a paired business workspace with a pending customer mutation must remain signed in and retain its queue when the `/up` auth-host probe fails; after reachability returns, successful account logout and same-account re-login must preserve the paired device and pending mutation.
- Extended `SyncDeviceManagementTest`: owner revocation must reject the old bearer token at bootstrap, pull, push and attachment-list endpoints. Existing non-owner and foreign-business revocation denial cases remain.
- Exact source/test commits: `614c24131edc3023c05baad5e583614a8f4c7156` (browser test fixture), `4de9e8bf018404fe1b67e16a7d55b0ec09ef7109` (revocation matrix). Documentation updates: `0059793c55b8cd3abe13c6fea6bef636e9bcb9bc`, `252d0a4f79751280ea26113ad506ab5f846c6715`.
- Tests are ADDED, not executed. Physical installed-device acceptance, expired-session, interrupted logout and stale async completion cases remain open. No V1 evidence score uplift.


### Backup archive integrity hardening — 2026-11-07

- New `zazu:backup` archives use manifest format version 2 with SHA-256 checksums for the SQLite/MySQL database payload and every private-storage file.
- `zazu:restore` verifies the checksum map matches the exact payload file set and streams each file through SHA-256 before extraction or any target mutation. Missing/malformed/extra/mismatched checksums fail closed.
- Restore retains compatibility with format-version-1 archives without checksum maps; version 2 requires them.
- `ZazuBackupRestoreTest` asserts database/private-file checksum generation and rejects a tampered private payload while preserving the installed private probe.
- Source/test commits: `2c79de9`, `e49dfc7`, `e684203`, `6d361c8`, `6350760`, `1ac0a7e`, `3138d4f`. Docs: `f0e2410`, `e1be645`.
- Integrity hashes detect corruption/drift; they are not a signed authenticity mechanism. Real operator backup/restore verification remains open. Exact-head Laravel/quality execution not yet observed; no recovery score uplift.


- Backup test follow-up: `ZazuBackupRestoreTest` now also rejects a format-v2 archive missing its required checksum map and restores a valid legacy format-v1 archive without a checksum map. Latest backup test commit: `45f3cfed9bee48153d939932ba4c495c85c0b26b`; assertion formatting follow-up: `89daa318f310b5cf873b50711e31eff73db12b92`. Execution remains pending.


## Navigation contrast and geometry correction — 2026-11-09

- Reworked the shared hierarchical navigation authority in `resources/css/app.css`: explicit sidebar foreground/background pairing for idle, hover, open and active states; 4px control radius; tighter 40px area triggers and 38px destination rows; reduced padding; removed hover translation; retained a visible 2px keyboard-focus indicator.
- Reworked dashboard-specific navigation in `resources/css/zazu-dashboard-authority.css`: fixed dark-sidebar hover to a darker navy surface with white foreground (never white text on pale blue), removed permanent blue borders, used a restrained cyan active marker, compacted flyout geometry and retained keyboard focus.
- Removed two superseded dashboard navigation palette blocks that contradicted the current dark-sidebar palette and caused the stylesheet to carry dead, competing state definitions.
- Added `UiProportionFoundationTest::test_navigation_uses_compact_geometry_and_explicit_first_paint_contrast` to guard the intended contrast, radius, focus state and removal of the obsolete pale-blue/white-text declarations.
- Commits on `main`: `b623d33` (shared navigation), `d404b24` (dashboard navigation and obsolete rule removal), `7379a8b` (regression coverage).
- Source changes are committed and re-fetch verification is pending. Runtime PHPUnit, Vite build and rendered first-load/hover acceptance are not yet verified. No claim of visual sign-off.

## App-wide spacing refinement — 2026-11-09

Owner expanded the visual refinement scope beyond the dashboard: apply the established spacing, surface, border and shape preferences consistently across Zazu EMP, without changing business behaviour or page structure.

- Confirmed the dashboard quote-stage grid had only a 2px top inset below its panel header. Increased it to 12px while retaining the existing horizontal and bottom rhythm.
- Confirmed the upcoming-events list had only a 3px top inset. Increased it to 12px so the first event no longer crowds the header divider.
- Found the shared disclosure/accordion body used zero top padding beneath its divider on desktop and mobile. Increased the top inset to 12px desktop / 10px mobile.
- Shared form sections retain deliberate 20px desktop and 14–16px responsive padding; shared panels retain 18px padding. These were not globally overridden.
- Added source regression coverage for dashboard panel insets and disclosure spacing.
- Commits: `9c21375` (dashboard spacing), `95bda8f` (disclosure spacing), `71987f0` (dashboard regression test), `6e8a06b` (disclosure regression test).
- Scope is spacing-only. Existing status pills/chips remain intentionally rounded semantic labels; navigation and action controls retain the compact non-oval treatment already established.
- Source verification and CI/runtime test execution remain pending; no rendered browser acceptance is claimed.

## Product-wide visual authority — 2026-11-09

- Owner confirmed Zazu EMP must maintain one consistent quality level across all authenticated modules, not just the dashboard.
- Added `resources/css/zazu-product-authority.css` as the final shared visual layer, loaded after dashboard/module styles in the authenticated application shell and registered as a Vite entry.
- Shared authority aligns neutral border contrast in light/dark themes, compact 5/7/8px geometry, card/panel/form surfaces, header insets, form-section spacing, button and field geometry, table wrappers and responsive padding.
- Semantic status chips remain compact labels; module-specific layouts and workflows remain intact.
- Added `UiProportionFoundationTest::test_product_wide_visual_authority_is_loaded_after_module_styles_and_applies_shared_primitives`.
- Commits: `25dfe61` (shared stylesheet), `1c3381b` (authenticated layout load), `32008a6` (Vite entry), `67e8eb4` (regression coverage).
- Source is committed on `main`. PHPUnit, Vite build, full-page rendered comparison across modules/themes and mobile acceptance are not yet executed/verified. No claim of visual sign-off or CI pass.

## Shared work-surface refinement — 2026-11-09

- Continued product-wide visual pass using actual shared selectors found in the application CSS and Blade views.
- Standardized command-band and metric-card radius/border/depth; removed metric-card hover lift.
- Converted shared list rows from individually floating/translating cards to a coherent bordered list surface with consistent row separators and neutral hover state.
- Reduced empty-state vertical padding from 56px to 36px to avoid oversized blank areas across modules.
- Aligned document-table wrappers and table headers/cells with the shared surface and border tokens.
- Unified form-contained native controls and actionbar geometry.
- Added regression assertions for list, metric, document-table and empty-state styling.
- Commit: `3c004fc` (shared surface refinement), `6ef6bd0` (regression coverage).
- Runtime test/build and rendered cross-module light/dark/mobile review remain outstanding.

- Follow-up geometry scan found remaining pill-shaped interactive controls and repeated surfaces in workload filters and dashboard quick actions/KPIs. Their geometry now follows the shared compact 5/7px radius system; semantic status/count tags also use restrained corners. Circular progress markers and decorative dots remain circular by design.
- Regression coverage extended for workload filters and dashboard quick-action geometry.
- Commits: `40168a1` (compact control geometry), `f03a8b9` (regression coverage).


## Cross-module visual defect correction — 2026-11-09

- Owner reiterated that thick blue borders and dull, repetitive card surfaces remain unacceptable across the application.
- Replaced the prior weak colour treatment at the end of the canonical resources/css/zazu-product-authority.css rather than layering another competing stylesheet.
- Added coordinated semantic colour families for blue, green, amber, teal, violet and coral in both light and dark themes.
- KPI tiles now rotate through distinct colour surfaces; quote pipeline stages use separate workflow colours; Finance, Inventory/Purchasing, Suppliers/Assets, Customers/Quotes, Reports and Calendar receive route-specific card surface families and matching section headers.
- Normalised shared card, panel, dashboard hero, shell separator and compact editor header borders to 1px neutral separators. Removed oversized blue rails from shared attention/context surfaces while retaining semantic status fills.
- Kept helper dock at the screen edge, compact and hidden while mobile navigation is open.
- Added/updated UiVisualSystemRegressionTest assertions for route-specific card colour families, light/dark semantic colours and thin-border rules.
- Commits: 3b0b902 (colour authority), 4295ca8 (border normalisation), ee61e4f (module card colours), 048ba78 (regression assertions).
- Source re-read confirms current CSS and test changes are on main. Exact-head CI is still running; no green result is claimed yet.
- Rendered desktop/mobile visual acceptance remains UNVERIFIED because no connected browser session for the local Zazu instance was available during this pass. The owner should see updated CSS after the Vite dev server refreshes/rebuilds the imported stylesheet.
- Continue the UI defect audit across authenticated views; source-level border search identified remaining 2px dashboard shell seams and compact editor header declarations, now overridden by the final shared authority. Preserve functional/semantic borders such as focus rings, error summaries and loading indicators.


## Follow-up UI defect sweep — 2026-11-09

- Re-entered through the Director control plane and UI Recovery skill. Target remains Leano-Jordan/ZazuEMP on main; scope remains visual defects only, with routes, permissions, data and business behaviour protected.
- Confirmed the authenticated shell exposes `data-zazu-route` and the dashboard body class, so route-specific module selectors are grounded in actual markup.
- Corrected the dashboard selector mismatch: the rendered Blade panels use `.zazu-dash-surface-head`, not only `.zazu-dash-panel-head`. Added actual dashboard-specific header, event, priority, resource and quote-stage colour treatments with explicit specificity over older dashboard declarations.
- Found and removed the source-level 2px dashboard sidebar, topbar, hero, hero-side and panel-header borders in `zazu-dashboard-authority.css`; reduced the compact editor header divider from 2px to 1px in `zazu-final-visual-sweep.css`.
- Reduced remaining shared document/quote total separators, calendar holiday accents, inspector notes, offline-origin notice and nav flyout accent to thin separators. Added `.zazu-dash-context-note` to the shared thin-rail rule. Preserved intentional focus/error indicators, spinner rings and circular stage markers.
- Regression suite now has 10 public test methods (PHPMD class-method limit respected). The prior Laravel run had 412 passing tests and one visual assertion failure caused by an incorrect minified CSS expectation; that assertion was corrected. The prior PHPMD failure was only the added 11th public test method; assertions were folded into an existing test. Added checks for the source-level dashboard/editor border rules.
- Source re-read confirms actual dashboard markup selectors, route attribute, colour treatment and border declarations on `main`. Latest CI for current HEAD `c8f20d4` is queued/in progress; no pass is claimed yet.
- Browser connector could not read the user's local Edge session because this chat lacks the originating browser context. GitHub browser-smoke workflow is pending; rendered acceptance remains unverified until that workflow completes or a connected browser session is available.
- Continue the UI recovery pass across page-level form spacing, dense registers, dialogs, responsive overflow and module-specific header hierarchy. Do not infer visual acceptance from source or CSS tests alone.
