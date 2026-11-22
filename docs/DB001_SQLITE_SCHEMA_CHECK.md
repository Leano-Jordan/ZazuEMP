# Read-only SQLite schema check (Windows)

Use this when a local Windows installation reports DB-001 and the request reference alone does not identify the failing SQL statement.

From the Zazu project folder, run PowerShell:

```powershell
.\ops\windows\Test-ZazuDatabaseSchema.ps1
```

The script:
- runs Laravel's migration-status command;
- reads the SQLite migration ledger;
- checks whether the five event scheduling columns exist;
- checks the historical `business_user.primary_niche` column;
- runs SQLite's read-only `PRAGMA integrity_check`;
- saves a small JSON report to the desktop and opens it in Notepad.

It does not run migrations, alter schema, write business records, reset the database, or restore a backup. It intentionally omits the database file path and all customer data.

Send the complete `Zazu-Database-Schema-Check.json` report. A missing migration ledger entry, missing columns, or failed integrity check points to a different repair path; do not infer that the database is corrupt from a missing migration alone.

This report diagnoses the local installation state. It does not by itself prove which SQL statement caused a historical DB-001.

## Capture the matching incident (read-only)

Run this second command from the same installed project folder, using the reference shown on the DB-001 screen:

```powershell
.\ops\windows\Get-ZazuIncident.ps1 -Reference '<DB-001 reference>'
```

The script saves an allowlisted incident report to the Desktop and opens it in Notepad. It includes safe fields such as exception class, route, source file/line and bounded database diagnostics; it does not export full logs, SQL, bindings, credentials or customer records.

For the latest owner-reported recurrence, the reference is:

`895e2c23-bc6b-4919-a891-1e8e5c1e27e4`

If the script reports that no matching structured record exists, send that no-match report too. Do not copy the full Laravel log, `.env`, database file or customer data. The reference alone does not establish root cause.

**Important:** run both checks against the exact installed Zazu folder that displayed DB-001, not a separate Git checkout or development copy. Both checks are read-only. Do not run migrations, reset, reinstall or manually edit the database during diagnosis.
