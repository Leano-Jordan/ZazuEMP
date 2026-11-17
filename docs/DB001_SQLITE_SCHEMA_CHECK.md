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
