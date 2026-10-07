# Zazu EMP — Backup / Restore Runbook

**Status:** OPERATIONAL DRAFT  
**Operational owner:** Isaac Junior Lehlogonolo Maluleka  
****Operating address:** [Operating address — see private records]
**Commands:** `php artisan zazu:backup` / `php artisan zazu:restore`

## Backup

1. Confirm the target installation and active business.
2. Ensure no known corruption is present.
3. Execute the supported Zazu backup command.
4. Confirm the archive was created successfully.
5. Preserve the backup in the approved storage location.
6. Record date, installation/release version, database driver and backup identifier.

Backups may contain database records and private storage. Treat backup artifacts as sensitive.

## Restore preflight

1. Identify the intended backup artifact.
2. Verify it came from an approved source.
3. Confirm database driver compatibility.
4. Confirm the archive is not unexpectedly large or malformed.
5. Verify database integrity before activation.
6. Confirm the destination and private-storage expectations.
7. Preserve/record rollback evidence before mutation.

## Restore

Use the supported Zazu restore command. Do not manually replace the database or private storage unless performing a controlled engineering recovery under the documented process.

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
