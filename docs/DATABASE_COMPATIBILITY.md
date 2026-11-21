# Zazu EMP — Database Compatibility Verification

**Purpose:** record the database engines exercised by automated checks. This is a verification matrix, not a promise that every Laravel-supported engine is a supported Zazu deployment target.

## Current compatibility matrix

| Database | Automated coverage | What the check proves |
|---|---|---|
| SQLite | Full Laravel unit/feature suite; populated baseline-to-current upgrade and recovery rollback; Windows package install and backup/restore | The primary single-PC Windows installation path, including schema creation, application behaviour and recovery |
| PostgreSQL 16 | Full migration set; last-batch rollback and forward reapplication; real registration; backup/restore and private-file recovery smoke | The tested server-database migration and selected critical workflow paths |

## Explicitly not certified

MySQL and MariaDB are not currently declared supported Zazu V1 deployment databases. Laravel having a database driver for an engine does not make that engine a Zazu-supported configuration. Do not advertise compatibility or use it for customer deployments until a dedicated migration, populated-upgrade, rollback, transaction, index/constraint and critical-workflow suite is added.

## Release rule

- Every migration change must continue to pass SQLite application tests and the populated SQLite upgrade/recovery workflow.
- PostgreSQL migration changes must pass PostgreSQL 16 migrate → rollback → migrate verification.
- Changes to database-specific queries, constraints, locking or transactions require equivalent critical-workflow tests on each database claimed as supported.
- A green migration-only run is not evidence that the complete application behaves identically on that database.
- Update this matrix whenever the supported deployment contract or CI coverage changes.

## Related checks

- [Laravel and PostgreSQL CI](../.github/workflows/laravel.yml)
- [Populated upgrade and rollback](../.github/workflows/zazu-populated-upgrade.yml)
- [Windows package and recovery acceptance](../.github/workflows/zazu-windows-package.yml)
- [Windows installation instructions](V1_PACKAGE_INSTALLATION.md)
