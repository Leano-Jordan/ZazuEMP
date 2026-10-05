# Zazu EMP — V1 Deployment Runbook

**Status:** OPERATIONAL DRAFT  
**Operational owner:** Isaac Junior Lehlogonolo Maluleka  
****Operating address:** [Operating address — see private records]
**Scope:** clean installation and controlled deployment

## Preconditions

- supported PHP/runtime installed;
- required PHP extensions present;
- supported database configured;
- application environment configured;
- storage directories writable;
- application key/configuration available;
- release artifact identified by commit/tag;
- deployment backup taken when upgrading an existing installation.

## Clean installation

1. Obtain the release artifact from the approved source.
2. Install PHP dependencies from the locked Composer set.
3. Install/build frontend dependencies from the locked npm set where required.
4. Configure environment values.
5. Run database migrations through the normal Laravel migration path.
6. Create required storage links/directories according to the installation model.
7. Run application health/smoke checks.
8. Create the first owner/business workspace through the supported onboarding flow.
9. Verify private media access and business isolation.
10. Record the installed release commit/version.

## Existing installation

Before mutation:

- confirm the installation is healthy enough to back up;
- create a release backup;
- verify backup artifact exists;
- record database driver and application version;
- identify any pending migration;
- stop or quiesce risky concurrent activity where the deployment environment requires it.

Then apply the release using the approved upgrade procedure.

## Post-deploy smoke

Verify:

- sign-in;
- active business context;
- customer creation/view;
- work/event creation/view;
- quote/invoice workflow;
- payment recording;
- private attachment download;
- reports;
- settings access by role;
- backup command;
- audit/security logging.

## Rollback

Do not reverse application files and assume that the database is automatically reversible.

Use `RELEASE_UPGRADE_ROLLBACK_RUNBOOK.md` and the backup/restore runbook.

## Evidence

Record:

- release commit;
- deployment date/time;
- migration result;
- smoke-test result;
- backup identifier;
- operator;
- exceptions.
