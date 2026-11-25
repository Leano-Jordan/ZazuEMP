# Zazu EMP — Current Engineering State

**Last reconciled:** 2026-11-25
**Repository:** Leano-Jordan/ZazuEMP
**Canonical branch / write target:** `main` only
**Latest application-source/test HEAD under exact-head acceptance:** `a3ec1523ccdf2f1b6ff92f4e1fcf62ba1914877d` (core Laravel tests, coverage and PostgreSQL smoke passed; offline browser job is still running)
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

- Exact current source/test head: `a3ec1523ccdf2f1b6ff92f4e1fcf62ba1914877d`.
- Laravel workflow `45700142579`: application tests **761 passed, 1 skipped, 5,445 assertions**; coverage report and PostgreSQL migration/recovery smoke passed. The offline browser job `139507818283` is still in progress, so the complete Laravel workflow is not yet a pass.
- Latest completed offline browser baseline remains run `45651931394` on `f6a9b3c`: **93 journeys passed**, with no failed or skipped journeys. This previously closed the automated internal-navigation defect for offline Customers/Jobs/Quotes. The current-head offline suite must finish before claiming a newer result. Neither run certifies physical Android use.
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