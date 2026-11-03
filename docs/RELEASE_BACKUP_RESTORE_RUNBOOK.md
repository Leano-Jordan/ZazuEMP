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
