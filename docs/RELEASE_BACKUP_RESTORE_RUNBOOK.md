# Zazu EMP — Backup / Restore Runbook

**Status:** OPERATIONAL DRAFT  
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
