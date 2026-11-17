# Claude handover — Zazu EMP V1
**Date:** 2026-11-17  
**Canonical repository:** `Leano-Jordan/ZazuEMP`  
**Engineering ref:** `main`  
**Current source head at handover:** `c941e90ff019be39961559889bc5874d8605d203`

## Purpose and engagement mode

Claude is being added as an independent development auditor and technical adviser to accelerate V1 closure.

**First pass is read-only:** inspect current source and tests, identify concrete defects or high-value corrections, and return prioritized advice. Do not commit, push, create branches, alter databases/customer data, or change project/deployment configuration unless the owner explicitly assigns that implementation work.

The owner remains the product owner and final acceptance authority. Director/Jarvis remains the sequencing and evidence-reconciliation point. Claude's audit must complement current repository evidence, not create a second backlog or scorecard.

## Required reading

Read these before giving technical advice:

1. `memory.md` — opening Owner V1 shipping mandate.
2. `.ai/REPOSITORY_IDENTITY_LOCK.md`.
3. `.ai/engineering/00_DIRECTOR_ENGINE.md`.
4. `.ai/engineering/STATE.md`.
5. `.ai/engineering/V1_SCOPE_FREEZE.md`.
6. `docs/V1_RELEASE_CHECKLIST.md` and `.ai/engineering/READINESS_REGISTER.md`.
7. The active implementation contracts below, plus failure records where relevant.

Use current `main` as source of truth. Some dated historical notes in large living documents refer to older heads; never transfer old CI results to the current head.

## Non-negotiable operating boundaries

- Zazu EMP only: `Leano-Jordan/ZazuEMP`.
- All engineering writes, when separately authorized, go directly to `main`. Do not create or update development/feature branches. Treat any existing non-main ref as read-only unless the owner says otherwise.
- Do not introduce another hosting/deployment path or use unrelated project assumptions.
- V1 scope is frozen. Work only on reproducible defects in agreed workflows, demonstrated security/privacy/data-integrity failures, or explicit owner-approved requirements.
- No speculative features, broad rewrites, architecture experiments, or generic best-practice checklists.
- Never weaken tests to make CI green. Do not repeat tests that have no new evidence or changed input.
- Separate confirmed defects from risks, hypotheses, accepted limitations, and missing physical evidence.
- Automated Chromium/mobile viewport success is not proof of physical Android cold-launch, force-close/reopen, host-loss, or reconnect behaviour.

## Latest engineering progress

Recent commits on `main`:

| Commit | Change |
|---|---|
| `1042738` | Corrected the offline quote inspection test to open the dashboard, which owns the device-local workspace. |
| `33fdb33` | Corrected a Playwright exact-text assertion: quantity, price and line total are intentionally combined in one metadata element. |
| `273cdfd` | Fixed invoice bootstrap normalization so nested payments are preserved. |
| `c941e90` | Reworked quote inspection acceptance to exercise the real sync-bootstrap envelope rather than manually injecting already-normalized client state. |

### Confirmed defect corrected

`app/Support/Offline/SyncBootstrapPayloadBuilder.php::bootstrapInvoices()` returns each invoice as an envelope with invoice fields under `record`; payment entries are nested at `record.payments` and each payment is itself an identity envelope.

Previously, `public/zazu-offline.js::applyBootstrap()` normalized `item.payments` only. On this real payload shape that property is absent, so the normalizer replaced the payments already present in the nested invoice record with an empty array. Result: a paired device could receive invoice details but lose the payment history in its local copy, affecting the new offline quote/invoice/payment inspection.

Commit `273cdfd` now normalizes outer payments when supplied, otherwise normalizes the nested `record.payments` collection. Commit `c941e90` changes the E2E fixture to call `refreshBootstrap()` against a server-shaped wrapped payload and assert that the EFT payment survives normalization before going offline.

This is a demonstrated source-contract defect and a targeted correction. Do not reopen it without new evidence.

### Verification snapshot

Latest exact-head run for `c941e90`:

- [Laravel workflow 44035583278](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44035583278): Laravel test job **PASS**; PostgreSQL migration/recovery job **PASS**; offline-browser job was still running at the time this handover was written.
- [Zazu quality 44035583283](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44035583283): check current run status before quoting it.
- [Push on main 44035583570](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44035583570): check current run status before quoting it.
- SonarCloud was skipped and is not evidence.

Previous run `44031616765` passed Laravel and PostgreSQL but its offline-browser job failed first because the test opened `/quotes` instead of the dashboard, then at an overly strict exact-text locator. Those test-contract issues were corrected in the commits above. Do not classify them as application runtime failures.

## Highest-value audit targets for Claude

Return only findings that survive checking the current implementation and relevant tests.

### 1. Offline bootstrap and local commercial record integrity
Inspect:
- `app/Support/Offline/SyncBootstrapPayloadBuilder.php`
- `public/zazu-offline.js` — especially `applyBootstrap()`, `refreshBootstrap()`, queue reconciliation and record identity normalization
- `resources/js/app.js` — device-local quote/invoice/payment and Work inspection
- `app/Http/Controllers/SyncController.php`
- `tests/Feature/OfflineBootstrapDepthTest.php`
- `e2e/pwa-offline.spec.js`

Look for real envelope-shape mismatches between server payloads and client normalizers, lost nested records, incorrect local/server identity joins, tenant leakage, stale bootstrap overwriting queued local work, and missing assertions around the actual server contract. Pay particular attention to nested purchase-order items, invoice payments, inventory movements, asset allocations, and selected quote/Work relationships. Several collections already have explicit nested handling; do not assume they are broken—prove it.

### 2. Offline session and trusted-device transition
Inspect:
- `app/Http/Middleware/AuthenticateSyncDevice.php`
- `app/Http/Controllers/OfflineWorkspaceController.php`
- `app/Http/Controllers/SyncController.php`
- `app/Support/Offline/SyncMutationRecorder.php`
- `app/Support/Offline/SyncMutationApplier.php`
- `e2e/offline-session-transition.spec.js`
- `tests/Feature/SyncDeviceManagementTest.php`

Look for revoked/expired device handling, stale in-flight responses, business switching, local queue preservation, replay/idempotency and whether a revoked device can still apply a queued financial mutation. Existing lock/recheck controls and owner-authorized registration are present; do not duplicate existing checks as new findings.

### 3. Phone-local attachments
Inspect:
- `public/offline-attachments.js`
- `app/Http/Controllers/EventAttachmentController.php`
- `app/Models/EventAttachment.php`
- `e2e/offline-attachment-sync.spec.js`
- `tests/Feature/SyncAttachmentIdempotencyTest.php`
- `tests/Feature/SyncAttachmentListingTest.php`
- `tests/Feature/WorkAttachmentDownloadTest.php`

Focus on durable file bytes across reload/restart, Work/business ownership, idempotent reconnect, upload failure recovery, and whether UI status reflects actual local persistence and server acknowledgement. Do not claim physical force-close persistence from browser automation alone.

### 4. Recovery and data portability
Read `docs/RELEASE_BACKUP_RESTORE_RUNBOOK.md`, then inspect the business archive export/import and installation backup/restore implementation plus:
- `tests/Feature/ZazuBackupRestoreTest.php`
- `tests/Feature/PopulatedBackupRestoreTest.php`
- PostgreSQL recovery CI

Distinguish tenant-scoped business archive restore from installation-wide operator recovery. Automated round-trip tests do not close the actual installed-target recovery drill.

### 5. Financial workflow integrity
Inspect the complete event → quote version → acceptance → invoice → deposit/payment → event cost → profit path. Check integer-cent money handling, idempotency, business scoping, and that offline display does not imply a pending local mutation has been server-accepted. Seeded automated reconciliation is not the same as reconciling a real populated business.

### 6. Existing UI quality
Only report a concrete regression in the current Zazu visual system. The dashboard has shared light/dark theme tokens and a layered visual composition; do not recommend replacing it with generic cards or plain HTML. PC and physical-phone rendered acceptance remain separate from automated checks.

## Release gates still requiring owner/operator evidence

The release is **NOT READY** until the existing V1 gates are evidenced:

1. Physical Android installed-app acceptance: PC host unavailable, full disconnection, cold launch, force-close/reopen, local customer/Work and attachment persistence, then reconnect and exactly-once reconciliation. Follow `docs/ZAZU_ANDROID_OFFLINE_INSTALL_ACCEPTANCE.md`.
2. Actual installed-target backup/restore drill, including private media, using the controlled procedure in `docs/RELEASE_BACKUP_RESTORE_RUNBOOK.md`.
3. Live populated-business quote, payment, cost and profit reconciliation. Do not substitute seeded fixtures.
4. POPIA operational/privacy controls and sign-off.
5. Actual owner-PC/browser rendered acceptance where still outstanding.

The Windows fresh-install package and its CI first-install smoke have already passed their defined candidate criteria. That does not close the other operational gates. The formal evidence maturity recorded in the living register is 73/100; do not change it without new qualifying evidence.

## Required Claude audit output

Keep the report decision-ready and short:

- **Finding / severity:** blocker, major, minor, or no issue.
- **Evidence:** exact file and line/function plus the observed code/test behaviour.
- **Customer impact:** the workflow or data affected.
- **Confidence:** confirmed, likely, or unverified.
- **Recommended action:** smallest safe correction and the regression that would prove it.
- **V1 decision:** must fix now, accepted limitation, or defer.

Rank no more than the five highest-value findings. Include positive confirmations only when they close a meaningful uncertainty. End with the single best next engineering target. Do not create an alternative scorecard or a speculative backlog.
