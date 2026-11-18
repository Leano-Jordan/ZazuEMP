# Zazu EMP — Windows V1 Candidate Package

**Package status:** V1 installation candidate; not a production/SaaS release certification  
**Target:** a single Windows PC installation using PHP 8.4+, SQLite and the included compiled frontend assets  
**Data location:** this extracted installation folder (`database/` and `storage/`)

## What is included

The package workflow creates a versioned ZIP containing the Laravel application, production Composer dependencies, compiled CSS/JavaScript, Windows startup helpers, licensing notices and this guide. Node.js, npm and Composer are build-time dependencies only; they are not required to run the extracted package.

The package does not contain a customer database, `.env`, application key, private uploads, or demo credentials. The installer creates a fresh local SQLite database and a private `.env` file on first run.

## Host requirements

- Windows 10/11, 64-bit.
- PHP 8.4 or newer, with the extensions listed by `Install-Zazu.ps1`.
- PowerShell 5.1 or newer.
- Enough free disk space for the extracted app, database, private files and local backups.

PHP must be available as `php.exe` on `PATH`, or at `C:\PHP84\php.exe`. The installer does not download PHP, change `PATH`, install services, alter Windows Firewall, or modify router settings.

## Fresh installation

1. Extract the complete ZIP into a permanent folder, for example `C:\ZazuEMP`.
2. Double-click `Install-Zazu.cmd`.
3. Wait for the installer to confirm that migrations completed.
4. Double-click `Start-Zazu-Dev.cmd`.
5. Open `http://127.0.0.1:8000` on the same PC.
6. Register the first business account through Zazu. Do not use development demo credentials.

The fresh-install profile uses a non-`Secure` session cookie because its default address is local HTTP (`127.0.0.1`). Keep that HTTP server on the PC or a trusted private network only. For a TLS-only deployment or HTTPS-only operation, set `SESSION_SECURE_COOKIE=true` in `.env` and use the HTTPS origin; do not expose the HTTP port to the internet.

The installer is safe to re-run for a partially completed fresh installation: an existing `.env`, application key and SQLite database are preserved. It runs pending migrations but does not seed sample customers or overwrite business records.

## Founder Control Centre identity

Before using the founder-only Sentinel Control Centre, set `ZAZU_PLATFORM_ADMIN_EMAIL` in the installation's private `.env` file to the exact email address of the designated founder account, then rebuild the configuration cache if the installation uses one. This is a single identity, not a comma-separated administrator list. The former `ZAZU_PLATFORM_ADMIN_EMAILS` variable is deliberately ignored; a business account's `admin` role does not grant platform access.

## Phone/tablet access

The normal HTTP launcher is loopback-only and is intended for the PC. For a phone/tablet on the same trusted private Wi-Fi network, use the private-LAN HTTPS procedure in `LOCAL_PC_HOST_DEPLOYMENT.md`:

- Install the official Caddy Windows executable separately.
- Run `Start-Zazu-LAN-HTTPS.cmd` on the PC.
- Install only Caddy's public root certificate on the test device after reviewing the device's certificate warning.
- Open the HTTPS URL printed by the launcher.

Do not expose port 8000, enable router port forwarding, use public/guest Wi-Fi, or bypass certificate warnings. HTTP and HTTPS are separate browser-storage origins; reconcile pending local work before moving an existing device to a new origin.

Physical phone/PWA/offline acceptance is not implied by a successful package build or automated browser tests.

## Local Sentinel recovery

If the normal Zazu sign-in or database-backed session cannot open **System → Sentinel Rescue**, but the local Zazu web process is still running:

1. Open PowerShell in the extracted Zazu folder.
2. Run `php artisan zazu:sentinel-recovery-code`.
3. On the same PC, open `http://127.0.0.1:8000/api/sentinel/recovery`.
4. Enter the one-use code shown in PowerShell within five minutes.

This screen is loopback-only; phones and other LAN devices cannot open it. The code is single-use and is not a URL. It runs installation health checks only. It does not expose customer records or replace the founder Control Centre. If PHP/Laravel or the local web process cannot start, use the Windows PowerShell diagnostics documented in `DB001_INCIDENT_CAPTURE.md`.

## Data safety and updates

- Keep the installation folder intact. The SQLite database is in `database/database.sqlite`; private business files are under `storage/app/private`.
- Use Zazu's business-level Export function for a business-scoped archive.
- The `zazu:backup` / `zazu:restore` Artisan commands are installation-wide operator tools. They affect every business in that installation; follow `RELEASE_BACKUP_RESTORE_RUNBOOK.md` and restore to an isolated target during drills.
- Store backup copies outside the PC and installation folder. A backup left only on the same disk is not disaster recovery.
- This candidate ZIP is for a **fresh installation**. It is not an in-place application updater. Do not extract a new candidate over an installation containing business data. A production upgrade must preserve `.env`, the database and private files and follow a verified backup/migration/rollback procedure.

## Operational boundary

This package is a local Windows installation candidate, not a hosted multi-customer SaaS deployment. Hosted deployment requires a separately selected persistent database and private-file store, HTTPS domain, secrets management, monitoring, off-host backups and an operator recovery drill. An installation ZIP and a successful CI build do not certify a hosted customer deployment.

## Evidence required before calling V1 release-ready

The package build must pass on its exact commit. Before calling the product release-ready, the release owner must also complete the intended Windows installation acceptance, physical-device acceptance for any claimed phone/offline workflows, an operator backup/restore drill on an isolated copy of the intended installation, live populated-business quote/payment/cost/profit reconciliation, and POPIA operational/privacy sign-off. A generated ZIP alone does not close these gates.