# DB-001 incident capture (Windows)

When Zazu shows a DB-001 reference, first use **Download safe diagnostic report** directly on the error screen. The reference-scoped link works from the same browser/phone and downloads only the matching structured incident fields; no PC-to-phone file transfer or PowerShell is needed. It excludes exception messages, SQL, bindings, credentials and unrelated log entries.

The PowerShell command below remains the fallback for installations that do not yet show the download link.

From the Zazu project folder, run PowerShell:

```powershell
.\\ops\\windows\\Get-ZazuIncident.ps1 -Reference '895e2c23-bc6b-4919-a891-1e8e5c1e27e4'
```

Send the resulting JSON to the maintainer. It identifies the exception class, route/action, source file/line and (when supplied by the database) driver, SQLSTATE and numeric driver code.

If no matching structured record is found, the running installation may not yet contain the diagnostic update, the incident may predate that update, or the log may have rotated/been stored elsewhere. Restart the existing Zazu installation using its normal launcher, reproduce once, and run the command again. Do not copy the full Laravel log or share the environment file.

The request reference alone is a correlation ID, not the database cause. Do not reset, reinstall, or alter the database to investigate this incident.
