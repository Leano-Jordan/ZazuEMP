# Zazu EMP — Current Engineering State

**Last reconciled:** 2026-11-25
**Repository:** Leano-Jordan/ZazuEMP
**Current main HEAD:** 52f81df91ccf1165c47cc381eb029aa9dafdfdf1
**Last application-source HEAD:** 95975c61215373b5796e1adcdfddd70370fc91b5
**Release verdict:** NOT READY
**Formal evidence-maturity score:** 71/100 (no uplift without new evidence)

This is the concise current-state register, not a session diary. Detailed incident evidence belongs in FAILURE_CASES.md, durable decisions in DECISION_LOG.md, recurring regressions in REGRESSION_LEDGER.md, and historical implementation evidence in dated audit/release records.

## Product identity and scope

Zazu is an event management platform, not catering software. The event is the centre of the operating model. Business capabilities describe the services and resources an operator brings to an event; catering, equipment hire, sound/AV, décor, rentals, baking and photography are use cases, not product boundaries. Strategic direction covers planning and carrying out different event types. V1 claims must remain limited to workflows actually implemented and accepted.

The existing Laravel modular monolith, event/job-centred records, business isolation, PWA/service-worker shell, IndexedDB workspace, durable mutation queue, phone-local attachments, sync, Windows packaging and recovery architecture are to be preserved. No rewrite or speculative module expansion.

## Non-negotiable operating constraints

- main is the only write target. Never create branches.
- Zazu does not use Render or AMPPS; do not reintroduce their deployment paths.
- Never reset, clear, replace or modify business data or verified backup files during engineering.
- Never perform an irreversible restore without explicit authority.
- Keep Dashboard free of phone pairing UI. Owner-controlled pairing remains in Settings → Phone sync.
- Automated browser/mobile viewport checks do not count as physical Android acceptance.
- Tests are evidence, not a progress metric. Never weaken a test to obtain a pass.

## Latest evidence

- Current main is 52f81df; the commit after application work was documentation-only.
- Application-source commit 95975c6: Laravel workflow 45644056566 failed in offline browser acceptance. Laravel feature tests reported 762 passed, 1 skipped, 5,445 assertions; browser journeys failed to find the expected offline Customers navigation link during Work creation and the Quotes navigation link during quote acceptance. These are unresolved acceptance defects, not passes.
- On 95975c6, Zazu Quality (45644056785), PHPMD (45644056528), Hadolint (45644056484), Director Health (45644056491), PostgreSQL migration/recovery smoke and Windows V1 package/fresh-install (45644056512) passed. SonarCloud (45644056460) was skipped, not passed.
- On docs-only main 52f81df, Zazu Quality (45644381300), Hadolint (45644381298) and Push-on-main (45644381187) passed. These do not supersede the failed application-source browser acceptance.
- Connected PC and linked-phone operation (Phase 1) was owner-verified previously. Physical Android Phase 2 remains OPEN: disconnected cold launch, route isolation, local writes, force-close/restart, local attachment reopening and reconnect reconciliation require physical acceptance.

## Confirmed open findings and release gates

### 1. Offline browser navigation — active engineering target

The last application-source browser run could not locate the Customers navigation link in offline Work creation or the Quotes navigation link during offline quote acceptance. Inspect the rendered offline DOM, route/permission filtering and event timing from the failed job logs. Fix the root cause; do not weaken selectors, remove acceptance or substitute a Dashboard-only check. After correction, rerun exact-head Laravel/browser acceptance.

### 2. Encrypted business archive restore — owner test failed

On 2026-11-23 the Founder verified two backups (an installation backup and a business archive). The encrypted business archive was imported into a disposable empty workspace and failed with “archive failed to load”; a later SYS-002/429 was rate limiting, not a proven root cause. The first failing stage remains unknown. Preserve the original archive, passphrase and disposable target. Do not retry on the live business or weaken the three-attempt throttle. Investigate upload/PHP runtime limits, temporary staging, Sodium decryption, ZIP validation and the surfaced error path with reproducible fixtures. See docs/RELEASE_BACKUP_RESTORE_RUNBOOK.md and the restore incident in READINESS_REGISTER.md.

### 3. Installation-wide recovery

Automated archive, rollback, Windows packaging and PostgreSQL smoke evidence exists. A controlled full installation recovery drill on the intended owner PC remains OPEN. Do not treat synthetic CI recovery as owner-PC acceptance.

### 4. Security, commercial and legal gates

- DEC-019 remains a documented HIGH trusted-device shared-profile risk: anyone with access to an owner-approved shared browser profile can inherit its approved offline workspace/mutation capability. Do not claim per-staff offline identity on that trust model.
- Populated quote-to-payment-to-actual-cost/profit reconciliation remains open.
- POPIA operational controls and ZAZU name clearance remain open.
- No independent penetration test has been completed; source review and automated tests are not a penetration test.

## Execution order from current evidence

1. Resolve offline browser navigation failures and obtain exact-head CI evidence.
2. Forensically isolate the failed encrypted business-archive restore without touching the live business or original verified backups.
3. Audit installation-wide recovery/key custody and strengthen automated recovery tests where evidence identifies a gap.
4. Complete targeted adversarial tenant/session/device/file and business-rule tests.
5. Reconcile the populated commercial workflow.
6. Prepare the remaining legal/brand operational decisions for Founder acceptance.

Physical-device, owner-PC recovery and commercial/legal sign-offs remain separate Founder acceptance gates. Continue all source, test, documentation and CI work that can be completed without those physical actions.