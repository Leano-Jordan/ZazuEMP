# Zazu EMP — Backup / Restore Runbook

**Status:** OPERATIONAL DRAFT — INSTALLATION-LEVEL OPERATOR PROCEDURE  
**Operational owner:** Isaac Junior Lehlogonolo Maluleka  
****Operating address:** [Operating address — see private records]
**Commands:** `php artisan zazu:backup` / `php artisan zazu:restore`

## Scope and access boundary

The current `zazu:backup` and `zazu:restore` commands operate on the **entire installation database and private storage**. They are not business-scoped. They must only be run by the installation operator through a controlled command-line session. They must never be exposed as ordinary business-owner settings or self-service routes. A restore replaces data for every business in that installation.

Business-level export/restore is a separate product capability and is not implemented by these commands.

## Backup

1. Confirm the target installation and record that the operation affects every business in that installation.
2. Ensure no known corruption is present.
3. Execute the supported Zazu backup command.
4. Confirm the archive was created successfully.
5. Preserve the backup in the approved storage location.
6. Record date, installation/release version, database driver and backup identifier.

Backups may contain database records and private storage. Treat backup artifacts as sensitive.

## Restore preflight

1. Identify the intended installation-wide backup artifact and confirm explicit operator authority.
2. Verify it came from an approved source.
3. Confirm database driver compatibility.
4. Confirm the archive is not unexpectedly large or malformed.
5. Verify database integrity before activation.
6. Confirm the destination and private-storage expectations.
7. Preserve/record rollback evidence before mutation.

## Restore

Use the supported Zazu restore command from a restricted operator session during a maintenance window. Do not expose this operation through a business-owner account. Confirm that all businesses in the installation are expected to be rolled back to the archive state. Do not manually replace the database or private storage unless performing a controlled engineering recovery under the documented process.

The current restore implementation stages and validates the restore input, protects private-storage replacement, and retains rollback handling for supported drivers.

## Post-restore verification

Verify:

- application boots;
- migration state is valid;
- representative customer/work/invoice/payment data is present;
- private media can be downloaded only by authorised users;
- foreign-business records remain inaccessible;
- audit/security logging still works;
- current migrations can be applied if the recovery drill requires it.

## Recovery failure

If restore fails:

- do not repeatedly mutate the live installation;
- preserve the failure evidence;
- use the command's safe rollback path;
- restore the pre-operation state;
- classify the incident and determine whether the backup itself is suspect.

## Release evidence

A customer-facing release claim requires recorded evidence of the actual recovery drill, including the input backup, environment, result and post-restore checks.

## Controlled Windows recovery drill (release evidence)

Use this procedure on the installed Zazu environment. It is the release-gate drill; automated tests alone do not close this gate.

### 1. Create the backup

From the Zazu project folder:

```powershell
php artisan zazu:backup --output=storage/app/zazu-release-backups
```

Confirm the command prints **Backup created:** and that exactly one new `zazu-backup-*.zip` appears in `storage/app/zazu-release-backups`.

### 2. Preserve the evidence

Record:
- Zazu version/commit being tested
- Windows version
- PHP version (`php -v`)
- database driver
- backup filename and backup ID
- date/time
- backup file size

Do not edit the backup archive.

### 3. Verify the recovery target

Use a controlled copy/test installation or a disposable test database where available. Do not overwrite a live customer installation merely to prove the gate.

### 4. Restore

Run:

```powershell
php artisan zazu:restore "PATH\TO\zazu-backup-<id>.zip" --force
```

Record the command result and any error output.

### 5. Post-restore acceptance

Verify all of these in the restored installation:

- [ ] application boots
- [ ] login works
- [ ] representative customer exists
- [ ] representative event exists
- [ ] representative work/planning data exists
- [ ] representative invoice/payment data exists
- [ ] restored private media opens for an authorised user
- [ ] unauthorised/foreign-business access remains blocked
- [ ] audit/security logging still works
- [ ] migrations report the expected state

### 6. Gate result

**PASS** only when the archive was created, restored successfully, and the representative data/media/security checks above pass in the real installed environment.

A CI round-trip is supporting evidence, not a substitute for this release-environment drill.


## Multi-business safety restriction — 2026-11-04

The business settings UI no longer exposes the installation-wide backup or restore commands. The previous controls implied that an owner was exporting/restoring only their business, while the commands snapshot or replace the entire installation database and private storage. This was an unsafe scope mismatch. The CLI commands remain available to the installation operator; business-level self-service export/restore remains a V1 gap until a tenant-scoped format and safe import strategy are implemented and tested.


## PostgreSQL / Render recovery notes — 2026-11-12

The CLI backup and restore commands support **SQLite, MySQL and PostgreSQL**. The PostgreSQL path uses the PostgreSQL connection resolved by Laravel, including a configured `DB_URL` (for example, a managed Render PostgreSQL URL with an `sslmode` query parameter). The Render Docker runtime must contain `pg_dump` and `psql`; the current repository Dockerfile installs `postgresql-client`.

The archive contains the installation database snapshot, private storage payload and a SHA-256 checksum manifest (format version 2). Restore validates the archive and checksums before installation mutation. PostgreSQL restore runs with stop-on-error and a single SQL transaction; it also captures a pre-restore database dump so a failed import can attempt to restore the pre-operation database. Private storage has a separate staged rollback path.

### PostgreSQL operator commands

Run from the deployed application container or a controlled operator shell with the same production environment and database connectivity:

```sh
php artisan zazu:backup --output=storage/app/zazu-release-backups
php artisan zazu:restore "storage/app/zazu-release-backups/zazu-backup-<id>.zip" --force
```

Replace the archive path with the exact artifact created by the backup command. Restore is installation-wide and destructive to all businesses in that installation. Use a maintenance window and an isolated recovery target for the release drill; do not use `--force` on a live customer installation as a test.

### PostgreSQL-specific preflight and acceptance

- Confirm `DB_CONNECTION=pgsql` and that Laravel resolves the intended database from `DB_URL` or the individual `DB_*` settings. Never print or paste database credentials into logs or evidence.
- Confirm the runtime has `pg_dump` and `psql` available and can reach the database with the configured TLS mode.
- Ensure the operator has sufficient PostgreSQL privileges to dump, drop/replace and recreate the objects contained in the backup. A managed database role may not have superuser privileges; test with the actual role on an isolated target.
- Save the backup archive outside the private-storage tree. Verify its size and preserve its SHA-256 hash and manifest metadata.
- Copy the completed archive to an independent off-host location. A backup stored only on the same Render persistent disk is not disaster recovery.
- Restore to an isolated PostgreSQL target using a matching database driver and compatible application version. Confirm migration state, representative business records, private uploads, authorization boundaries and audit logging.
- Record the exact source commit, target database service, archive identifier/hash, operator, timestamps, command outcomes and verification results. Redact credentials and personal data.

**Verification boundary:** the 2026-11-12 GitHub Actions run passed a real PostgreSQL 16 database and private-file round-trip. This is automated CI evidence only. It does not verify the live Render role's privileges, actual `DB_URL` secret, persistent disk, off-host retention, production-sized data, or restore/rollback behaviour on the selected hosting target. Those remain mandatory operator release gates.
