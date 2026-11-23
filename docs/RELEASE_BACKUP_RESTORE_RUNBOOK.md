## Secondary-copy presence verification — 2026-11-22

**Implementation:** [c992b98](https://github.com/Leano-Jordan/ZazuEMP/commit/c992b986497ca1c233437212d55b3e93ad29c455).

The scheduled Windows runner now persists the secondary archive's absolute path, SHA-256, byte size and last-write timestamp after the copy passes SHA-256 verification. The Zazu backup-health check requires the recorded destination to remain a regular, non-symlink file and its size/timestamp to match the recorded values. A missing/unavailable destination or changed metadata moves backup protection to **ATTENTION** instead of leaving a stale healthy status.

- Existing status files created by older runner versions do not contain this metadata; they remain **ATTENTION** until the next successful scheduled secondary copy writes it.
- SHA-256 is verified when the runner copies the archive. The dashboard does **not** re-hash a potentially multi-gigabyte archive on every page request; it checks presence and recorded size/timestamp.
- This check does not prove that the destination is on a physically separate disk or that a cloud-sync provider has completed upload. The operator must configure a USB/off-disk or independently stored cloud-synchronised destination and verify that it is actually independent.
- Windows PowerShell 5.1 runner assertions for persisted path/hash/size/timestamp and preservation of prior metadata on failure passed in [workflow 44988789562](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44988789562).
- Exact-head PHP regression `InstallationBackupStatusTest` passed on [Laravel workflow 44989474410](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44989474410): **679 passed / 1 skipped / 4,671 assertions**. PostgreSQL migration and backup/restore recovery smoke also passed.

The owner-operated backup and full restore drill on the intended PC remains mandatory.

---

## Windows scheduled-backup runtime verification — CI PASS — 2026-11-21

The Windows Ops workflow now runs the installer and runner, not only source-text checks. On a Windows PowerShell 5.1 runner it:

- registers a real temporary Task Scheduler task and verifies S4U/limited principal, daily 02:00 trigger, StartWhenAvailable, WakeToRun and IgnoreNew overlap handling;
- executes the backup runner against an isolated fixture installation;
- verifies local and secondary retention both stop at 14 archives and the copied archive matches by SHA-256;
- simulates an unavailable secondary drive and verifies the run is marked failed without erasing the last successful-copy timestamp.

[Workflow 44941196865](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44941196865) passed on [db5f25e](https://github.com/Leano-Jordan/ZazuEMP/commit/db5f25e1f6b54fb28909738508ef935792a419cb).

This remains Windows CI evidence. The actual owner PC must still demonstrate a scheduled run, an independent off-disk copy and a controlled restore of the intended installation with representative private media.

---

## Empty private-storage restore correctness — automated verification PASS — 2026-11-21

**Implementation:** [1906fd4](https://github.com/Leano-Jordan/ZazuEMP/commit/1906fd4da1876df66d745a57be9c3bd1d1714c48).  
**Regression:** [1cb1154](https://github.com/Leano-Jordan/ZazuEMP/commit/1cb1154b4b813dca0cf04fe401dbdf38375a5808).

A backup that declares private storage must restore that snapshot even when the directory contains zero files. ZIP omits empty directories, so restore now reads the v2 contents manifest, creates an empty staging directory and replaces the target private tree. A regression proves stale target files are removed while the database snapshot is restored. Exact-head Laravel/recovery CI passed on [d716d5d / run 44937037910](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44937037910); Windows populated and empty-private-storage restore passed on [run 44937732880](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44937732880). The owner-operated recovery drill remains mandatory.

---

## Backup/restore capacity alignment — automated verification PASS — 2026-11-21

**Implementation:** [84dba32](https://github.com/Leano-Jordan/ZazuEMP/commit/84dba32ae96ececf6c1dd71034d79d0b93070b7e).  
**Shared policy:** [4824469](https://github.com/Leano-Jordan/ZazuEMP/commit/48244694d5419df50ce9cdd3938f65aaf77d02eb).  
**Regression:** [675742e](https://github.com/Leano-Jordan/ZazuEMP/commit/675742eef40a819b6e509ea168303a8d239fd584).

Backup and restore now share one policy: 50,000 private files, 50,002 total ZIP entries (database + manifest + private files), and 2 GiB total uncompressed content. Backup rejects oversized staging payloads and verifies the completed ZIP, including the manifest, against the restore ceiling before publishing. Boundary tests cover the entry and byte limits. Empty private-storage restore now also replaces stale target files, even though ZIP omits the empty directory. Exact-head run [44937037910](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44937037910) passed Laravel **673 / 1 skipped / 4,575 assertions**, PostgreSQL migration/recovery smoke, PHPMD and Zazu Quality. The owner-operated off-disk backup and full restore drill remains mandatory.

---

## Automated business-archive restore safety evidence — 2026-11-21

Exact source/test head `e45cab97cbcdbc3f989bf3843d751bb0f373c305` on `main`; [Laravel/PostgreSQL/browser workflow 44904393629](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44904393629).

The business archive restore path now has exact-head regression coverage for:

- refusing a target that already has trusted-device/offline sync state, before importing records or writing files;
- rolling back imported database rows when a private-file write fails partway through restore;
- deleting a file already written earlier in that same failed restore attempt.

The failure-path test simulates the private storage adapter returning success for the first write and failure for the second, then verifies the first file is deleted and the target workspace remains empty. This is automated transaction/cleanup evidence, not a substitute for the owner-operated recovery drill on the intended PC and private media store.

---

# Zazu EMP — Backup / Restore Runbook

**Status:** OPERATIONAL DRAFT — INSTALLATION-LEVEL OPERATOR PROCEDURE  
**Operational owner:** Isaac Junior Lehlogonolo Maluleka  
****Operating address:** [Operating address — see private records]
**Commands:** `php artisan zazu:backup` / `php artisan zazu:restore`

## Scope and access boundary

The current `zazu:backup` and `zazu:restore` commands operate on the **entire installation database and private storage**. They are not business-scoped. They must only be run by the installation operator through a controlled command-line session. They must never be exposed as ordinary business-owner settings or self-service routes. A restore replaces data for every business in that installation.

Business-level export/restore is implemented separately through the owner-only Export business data screen (`/exports`). It uses a tenant-scoped `.zazu` archive with an explicit record allowlist and referenced business files; it excludes credentials, sessions, device-pairing tokens, licence entitlements and sensitive tax credentials. Restore is restricted to an empty, unpaired workspace and remaps record IDs. This does not replace installation-wide operator recovery.

## Confidentiality and storage protection

**Both archive formats are currently unencrypted ZIP files.** The installation archive contains the entire installation database and private storage; the business archive contains the selected business's allowlisted records and referenced files. Neither is password-protected.

The installation backup's SHA-256 manifest detects payload changes; it does not encrypt the data or authenticate who created the archive. Business-archive validation enforces its structure and allowlists; it does not provide cryptographic confidentiality or creator authentication.

- Treat every archive as sensitive personal and commercial information.
- Keep off-disk copies on an encrypted removable drive or in a properly encrypted, access-controlled storage vault. Full-disk encryption helps protect a powered-off device but does not protect a copied archive once it leaves that device.
- Do not email archives, attach them to support tickets, or place them in public/shared folders.
- POSIX backup/restore staging is now restricted to owner-only directories/files (0700/0600). Temporary backup, restore and rollback artifacts are kept under `storage/app/zazu-backups/`, outside the private-media tree. On Windows, `Install-Zazu.ps1` already restricts `storage/app/zazu-backups` to the installation owner, SYSTEM and local Administrators; verify that ACL remains intact before copying archives.
- Application-level archive encryption remains an open hardening item. Do not describe ZIP checksums or local file permissions as encryption.

The allowlist includes `invoice_number_sequences`, so the per-business invoice counter moves with the invoice records. Existing format-v1 archives created before this dataset was added remain restorable; the importer supplies an empty sequence dataset, and the invoice generator reconciles the next number against invoices already restored. Keep the sequence table in archive coverage tests whenever invoice issuance or archive datasets change.

## Backup

1. Confirm the target installation and record that the operation affects every business in that installation.
2. Ensure no known corruption is present.
3. Execute the supported Zazu backup command.
4. Confirm the archive was created successfully.
5. Preserve the backup in the approved storage location.
6. Record date, installation/release version, database driver and backup identifier.

Backups may contain database records and private storage. Treat backup artifacts as sensitive.

## Routine Windows owner-PC backup

The Windows host launcher maintains a per-installation Task Scheduler task named `ZazuEMP-DailyBackup-<installation>`.

- Runs `php artisan zazu:backup` daily at 02:00 and starts a missed run when Windows becomes available.
- Runs under the Windows account that owns the installation; no password is stored in the task.
- Writes complete, self-verified archives to `storage/app/zazu-backups/daily/`.
- Retains the latest 14 successful archives; older archives in that dedicated daily folder are removed only after a new backup succeeds.
- The host launcher prints the latest archive age and warns when no backup exists or it is more than 26 hours old.

**Off-disk protection remains an operator action:** copy the newest archive to a separate USB drive or independently stored cloud-synchronised folder. The daily local copy is on the same PC/disk and does not protect against disk failure, theft or loss of the machine. Treat the archive as sensitive because it contains every business in the installation.

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


## Business archive import validation — 2026-11-21

Business-level `.zazu` restore treats the uploaded ZIP as a strict container, not a general-purpose archive. Restore accepts only:
- `manifest.json`
- `data.json`
- file payload entries explicitly declared by `manifest.json`

Undeclared payloads and all other ZIP entries are rejected. This check occurs before any target-workspace transaction or file write. Keep the export allowlist and restore contract in sync when adding a supported dataset or file type. This is structural validation, not a cryptographic signature: it does not authenticate the archive's creator or prove that its contents were not intentionally rebuilt.

## Multi-business safety restriction — 2026-11-04

The business settings UI no longer exposes the installation-wide backup or restore commands. The previous controls implied that an owner was exporting/restoring only their business, while the commands snapshot or replace the entire installation database and private storage. This was an unsafe scope mismatch. The CLI commands remain available to the installation operator. Separately, tenant-scoped business archive export/restore is implemented through the owner's Export business data screen (`/exports`): the `.zazu` archive contains the explicit allowlisted linked records and referenced business files, excludes credentials, sessions, device pairing tokens, licence entitlements and the SARS TCS PIN, and restores only into an empty, unpaired workspace with record IDs remapped. Its round-trip and tenant-isolation regression is automated-verified in CI. This business archive is not a substitute for the installation-wide operator recovery drill.


## PostgreSQL recovery notes — 2026-11-12

The CLI backup and restore commands support **SQLite, MySQL and PostgreSQL**. The PostgreSQL path uses the PostgreSQL connection resolved by Laravel, including a configured `DB_URL` with its required TLS parameters. The runtime must contain `pg_dump` and `psql`; the repository runtime image installs `postgresql-client`.

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
- Copy the completed archive to an independent off-host location. A backup stored only on the same host or storage volume is not disaster recovery.
- Restore to an isolated PostgreSQL target using a matching database driver and compatible application version. Confirm migration state, representative business records, private uploads, authorization boundaries and audit logging.
- Record the exact source commit, target database service, archive identifier/hash, operator, timestamps, command outcomes and verification results. Redact credentials and personal data.

**Verification boundary:** the 2026-11-12 GitHub Actions run passed a real PostgreSQL 16 database and private-file round-trip. This is automated CI evidence only. It does not verify the production database role's privileges, actual database connection secrets, persistent storage, off-host retention, production-sized data, or restore/rollback behaviour on the selected hosting target. Those remain mandatory operator release gates.


## Backup creation integrity check

The backup command does not publish the ZIP immediately after writing it. It reopens the temporary archive with ZIP consistency checking, validates the Zazu format and configured database driver, checks exact payload membership against the manifest, and recomputes every payload SHA-256. A failed check prevents publication. Restore independently repeats its own validation before any installation mutation.
