# Zazu EMP — Upgrade / Rollback Runbook

**Status:** OPERATIONAL DRAFT  
**Operational owner:** Isaac Junior Lehlogonolo Maluleka  
**Operating address:** 1068 Block JK, Soshanguve, Pretoria, South Africa  
**Scope:** representative existing installations

## Before upgrade

- identify exact installed version/commit;
- take and verify a pre-upgrade backup;
- record database driver and deployment topology;
- verify the application starts normally;
- record migration status;
- identify sensitive/private media that must survive the upgrade.

## Upgrade

1. Install the new release artifact.
2. Apply database migrations through the supported migration path.
3. Run smoke checks.
4. Verify representative populated records.
5. Verify invoices/payments and audit history.
6. Verify private media.
7. Verify business isolation and role restrictions.
8. Record the migration outcome.

## Rollback trigger

Rollback is justified where the new release causes a release-critical defect involving:

- data integrity;
- authorization/isolation;
- critical workflow failure;
- migration failure;
- private media loss;
- unrecoverable deployment failure.

## Rollback

1. Stop further mutation of the affected installation where practical.
2. Preserve logs and incident evidence.
3. Use the verified pre-upgrade backup.
4. Follow the supported restore runbook.
5. Verify the restored database and private storage.
6. Reapply only the migrations explicitly required by the recovery plan.
7. Run representative workflow/security smoke checks.
8. Record the final state and release decision.

## Upgrade/rollback evidence standard

The release record should include:

- pre-upgrade backup identifier;
- source release;
- target release;
- migration output/result;
- representative populated records checked;
- private-media checks;
- rollback result if exercised;
- final migration state.

Never claim an upgrade is safe merely because a clean installation succeeds.
