# DB-001 incident capture (Windows)

Use this when Zazu shows a DB-001 reference. It extracts the matching structured incident record and prints an allowlist of diagnostic fields only. It does not print exception messages, SQL, bindings, credentials or unrelated log entries.

From the Zazu project folder, run PowerShell:

```powershell
.\\ops\\windows\\Get-ZazuIncident.ps1 -Reference '895e2c23-bc6b-4919-a891-1e8e5c1e27e4'
```

Send the resulting JSON to the maintainer. It identifies the exception class, route/action, source file/line and (when supplied by the database) driver, SQLSTATE and numeric driver code.

If no matching structured record is found, the running installation may not yet contain the diagnostic update, the incident may predate that update, or the log may have rotated/been stored elsewhere. Restart the existing Zazu installation using its normal launcher, reproduce once, and run the command again. Do not copy the full Laravel log or share the environment file.

The request reference alone is a correlation ID, not the database cause. Do not reset, reinstall, or alter the database to investigate this incident.
