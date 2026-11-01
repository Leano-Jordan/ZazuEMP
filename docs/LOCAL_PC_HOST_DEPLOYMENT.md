# Zazu EMP — Local PC Host Deployment Plan

**Status:** IMPLEMENTATION PLAN — PC/runtime verification pending  
**Target mode:** Local-network Zazu (Mode B) with device-local continuity (Mode C)  
**Repository policy:** `main` only; no feature branches  
**Scope:** Turn the owner's Windows PC into a stable, always-available local Zazu host without making VS Code part of runtime operation.

## 1. Outcome

A customer installs and runs Zazu from a stable HTTPS address on their own local network. The PC hosts Laravel, the authoritative database and private files. Phones and tablets connect over the same Wi-Fi/router. Internet access is not required for local-network operation.

If the PC is unavailable, a phone can still use only the workflows and records already supported by its local IndexedDB workspace. Shared database access and synchronization resume when the host returns. This is not a claim that every Zazu screen is already a complete offline replacement.

## 2. Current blocker confirmed in source

The previously used origin, `http://192.168.137.237:8000`, is a development address served by Laravel's built-in server. It is plain HTTP on a LAN IP, so it is not a secure browser context for service-worker registration. The current client already displays a diagnostic notice for this case.

Do not solve this by merely changing the IP or leaving VS Code open. Do not use `php artisan serve` as the permanent customer-facing server.

## 3. Target topology

```text
                 Local Wi-Fi router
           DHCP reservation + local DNS
                         |
             https://zazu.home.arpa
                         |
             Windows PC — Zazu host
        +--------------------------------+
        | Apache HTTPS virtual host      |
        | Laravel application            |
        | Local database (PC only)       |
        | Private business file storage  |
        | Scheduled local backups        |
        +--------------------------------+
                 |              |
             Phone/PWA      Tablet/browser
          IndexedDB queue    Browser client
                 \              /
                  Local network only
```

The router is the network naming authority. Prefer a DHCP reservation for the PC and a local DNS record such as `zazu.home.arpa` (the reserved home-network namespace). Do not assume that `.local` resolves consistently on every Android/router combination.

If the router cannot provide local DNS, use the reserved LAN IP as the host name and issue a certificate containing that exact IP address as a subject alternative name. Do not rely on a manually typed, changing DHCP address.

## 4. Server runtime

Select a persistent Windows host stack only after comparing the actual installed runtimes, HTTPS support and service-recovery capabilities. AMPPS is installed on the owner's PC but was not used to run Zazu; it is an unselected candidate, not the assumed or preferred host. The observed Zazu development runtime is standalone PHP 8.4 running `php artisan serve`. Any selected host must provide PHP 8.4+, the required extensions, a supported auto-start/recovery mechanism, HTTPS, durable database storage and private-file storage. AMPPS's bundled PHP runtime must not be treated as compatible merely because its Apache executable is present.

Requirements:

- Apache listens on HTTPS port 443 for the LAN host name.
- Apache serves only Laravel's `public/` directory; never expose the repository root.
- PHP 8.4+ and required extensions match the locked Composer/runtime requirements.
- Frontend assets are built before deployment with `npm run build`; customer runtime does not need Node/Vite or VS Code.
- Database listens on loopback only; LAN clients never connect directly to MySQL/SQLite.
- Laravel sessions, cache, queue and private files use durable local storage appropriate to the selected database driver.
- Apache and the database start automatically with Windows and recover after restart.
- Runtime logs rotate and have a documented location.
- No development server, debug mode or demo credentials are used for customer runtime.

Do not configure a production database until the existing customer data, current database driver, selected host's database layout and service lifecycle have been inspected. Do not replace or recreate an existing database as part of host setup.

## 5. Stable HTTPS on a private LAN

Use a private local certificate authority (for example, mkcert) to issue a certificate for the router-provided host name. Install the CA trust certificate on each device that will install/use the PWA.

Acceptance requirements:

- The PC serves the exact same HTTPS origin after reboot.
- The phone resolves the local host name while connected to the LAN.
- The browser reports a secure context and no certificate warning.
- Service-worker registration succeeds and controls the installed app.
- The certificate remains valid for the configured name/IP and has a renewal/replacement procedure.
- The private CA signing key stays on the PC and is backed up securely; never put it in Git.

A self-signed leaf certificate with a browser warning is not an acceptable workaround. A trusted certificate must be installed on each client device. Public ACME certificates are not the default for a private-only hostname.


## 5A. Origin migration is a data migration

The current client stores business workspaces in origin-scoped browser storage. The active workspace database is named `zazu-client-business-<business-id>`; the offline attachment queue is stored separately in `zazu-phone-attachments`. IndexedDB, Cache Storage, service-worker registrations and browser cookies are isolated by origin (scheme + host + port). Moving from `http://192.168.137.237:8000` to `https://zazu.home.arpa` therefore creates a **new, empty browser storage partition**. The old phone workspace, pending mutations, cached pages, attachment blobs and pairing session do not automatically move with it.

Treat the cutover as a controlled data migration, not a URL change:

1. Keep the old origin available while preparing the cutover. On each paired device, open Zazu on the old origin and confirm it has synchronized; inspect pending mutations, unresolved conflicts and queued attachments.
2. Do not proceed with an old-origin device that has unsynchronized work or unresolved conflicts. Resolve/retry it while the old host is still available. If the old origin cannot be reached, preserve that browser profile and its storage; do not clear site data, uninstall the PWA or assume a fresh pairing recovers its local queue.
3. Deploy the same business database and private files to the new host without recreating them. Back up first and verify the restored business identity and records.
4. Open the new HTTPS origin, install/update the PWA, sign in and pair the device again. The new origin must bootstrap the expected business workspace from the authoritative server.
5. Verify customer/job/quote/preparation counts and relationships, then create a harmless test mutation and confirm it syncs exactly once. Confirm attachments are present before retiring the old install.
6. Retain the old origin/profile until all devices pass reconciliation. Only then retire the old endpoint. Never instruct users to clear browser storage as a migration step.

**Release gate:** add a tested export/import or explicit migration mechanism before promising preservation of unsynchronized local-only work across an origin change. Until then, cutover is permitted only after old-origin queues, conflicts and attachment uploads are drained and verified. Server-backed records can be bootstrapped again; unsynchronized device-only data cannot be assumed recoverable from the new origin.

## 6. Network and security boundaries

- Reserve the PC address in the router.
- Permit inbound HTTPS (TCP 443) only from the trusted private LAN/subnet.
- Do not expose Apache, database ports, remote administration or SMB shares to the public internet.
- Keep the database bound to loopback.
- Keep Laravel authorization/business isolation authoritative on the server.
- Keep private attachments outside the public web root and preserve existing access controls.
- Use unique owner credentials; remove demo users and demo seed data from customer installations.
- Set production environment values: `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://zazu.home.arpa`, secure session cookies, and a generated unique `APP_KEY`.
- Because Apache terminates TLS directly, do not add broad trusted-proxy rules unless a real reverse proxy is introduced and explicitly configured.

## 7. Windows startup and recovery

The host must not depend on an interactive VS Code terminal or a user remembering to run Artisan.

- Configure Apache and the database as Windows services or supported auto-start services in the installed stack.
- Set service recovery to restart on failure.
- Confirm services start after a full Windows reboot and before a user signs in, if the installed stack supports it.
- Add a local health check for the HTTPS application and database.
- Document a safe shutdown/restart procedure.
- Verify that closing VS Code, closing the browser and signing out of the Windows desktop do not stop the host services.

Do not add a custom background PowerShell service wrapper until the selected web-server binary, service registration, startup behavior and recovery mechanism are inspected. Avoid running two competing Apache/PHP servers on the same ports.

## 8. Backup and storage

The PC becomes the local authority, so it also becomes a single point of failure.

- Keep database and private-file backups on a separate physical drive or other independent destination.
- Schedule backups and retain multiple restore points.
- Include database, private attachments, environment/configuration recovery material and certificate renewal instructions.
- Protect secrets and CA private keys; never commit them.
- Run a restore drill to a separate test location before the first customer uses the host.
- A second drive in the same PC is not an off-site backup; an additional copy should be stored separately.

## 9. Implementation sequence

### Phase 0 — Inspect the actual PC (no changes)
Record Windows version, AMPPS version, PHP CLI and Apache PHP versions, enabled extensions, Apache service name/status, database service/driver, current Zazu path, current database path, storage path, router capabilities and current LAN subnet. Confirm available disk space and backup destination.

### Phase 1 — Prepare the app for a stable origin
Audit hard-coded origins, absolute URLs, cookie settings, service-worker scope/cache behavior, CORS, CSRF, OAuth/callback assumptions and any origin-bound pairing state. Make the app use the configured HTTPS host consistently. Add automated checks for secure-context/service-worker registration and same-origin behaviour.

### Phase 2 — Configure local network identity and HTTPS
Reserve the PC's LAN address, configure local DNS, issue the private certificate, install CA trust on target devices, and validate HTTPS from PC and phone.

### Phase 3 — Install persistent host services
Configure the existing Apache/PHP and database services to start automatically. Deploy Laravel from a release artifact, point Apache at `public/`, build assets, set production environment and verify logs/health.

### Phase 4 — Install and prove the PWA
Install from the stable HTTPS origin. Confirm service-worker control, cached shell and local workspace. Pair devices through the supported owner flow.

### Phase 5 — Prove resilience
Run the acceptance tests below, including host restart, internet-off/LAN-on, host-off, phone restart and data restore.

## 10. Acceptance gates

| Test | Pass condition |
|---|---|
| Stable origin | Same HTTPS hostname resolves after PC/router reboot |
| Secure context | No certificate warning; `isSecureContext === true` |
| Service worker | Registration and controller persist after install/restart |
| No VS Code | Close VS Code; phone and PC still reach Zazu |
| Windows reboot | Apache and database return without manual terminal commands |
| LAN without internet | Phone reaches host and completes server-backed workflows with WAN disconnected |
| Host unavailable | Installed phone opens and supports only its declared local workflows; pending state is visible |
| Reconnect | Queued work reconciles without loss or duplicate financial/inventory effects |
| Firewall | LAN clients reach HTTPS; public/WAN clients cannot reach the host |
| Restore | Database and private files restore successfully to a separate test location |
| Update | App update and rollback preserve existing business data |

## 11. Explicit limits

- This is a local-network product mode, not remote access from outside the premises.
- When the PC is off, the central database is unavailable. Device-local continuity is limited to implemented and tested offline workflows.
- A PC-hosted system needs reliable power, storage health and independent backups.
- Router DNS and private CA trust must be configured on the customer network/devices.
- Cloud hosting may be offered later as an optional mode; it is not required for this local-host target.

## 13. Read-only host inspection — 2026-11-01

The owner supplied the output of `ops/windows/Inspect-ZazuHost.ps1`. This was a read-only inventory; no PC settings were changed.

Observed:

- Windows 10 Pro, build 19045, 64-bit. The output does not include the UBR/revision or confirm Windows 10 Extended Security Updates enrollment.
- LAN interface: Wi-Fi, current address `192.168.137.237`, gateway/DNS `192.168.137.1`.
- Standalone PHP CLI: `C:\PHP84\php.exe`, version 8.4.26. It satisfies the repository's PHP `^8.4` constraint.
- Required common extensions observed include `openssl`, `pdo_mysql`, `mbstring`, `fileinfo`, `gd`, `intl`, `zip` and `curl`.
- Composer 2.10.3, Node 26.10.0 and npm 12.1.0 are installed.
- AMPPS is present under `C:\Program Files\Ampps`; its Apache, PHP and MySQL directories exist.
- No Apache or MySQL/MariaDB Windows services were detected by the inventory.
- The only detected web/database listener was PHP on `0.0.0.0:8000` (PID 10268), consistent with a development server. Process command line and executable path were not captured, so the exact launcher remains to be confirmed.
- Project files, Composer vendor autoload, frontend manifest and `.env` are present.
- C: has approximately 127.8 GB free.

Interpretation:

1. The host has a compatible PHP CLI, but the Apache-served PHP runtime still needs separate verification. CLI PHP does not prove Apache is using the same version/extensions.
2. AMPPS being installed does not prove Apache or its database are installed as services, currently running, or configured for automatic startup.
3. The current port 8000 listener is not an acceptable permanent host endpoint. Do not expose it as the customer endpoint.
4. The current DHCP address must be reserved before relying on it for local DNS or certificate identity.
5. Windows 10 Pro 22H2 reached end of standard support on 2025-10-14. Confirm the device's current security-update/ESU status or plan migration to a supported OS before treating it as a long-term customer host. See Microsoft's [Windows 10 lifecycle](https://learn.microsoft.com/en-us/lifecycle/products/windows-10-home-and-pro) and [ESU guidance](https://support.microsoft.com/en-us/windows/deployment/updates-lifecycle/windows-10-support-has-ended-on-october-14-2025).

Next read-only inspection when the PC is available:

- Identify PID 10268's full executable path, command line and parent process.
- Evaluate available Windows web-server/PHP candidates, including AMPPS only as an installed-but-unused option; verify PHP 8.4 compatibility and service support before selection.
- Identify the actual database driver, executable, data directory and service/start mechanism used by the current Zazu environment.
- Compare PHP CLI with the PHP module/FastCGI runtime Apache would use.
- Confirm the current database and private attachment paths, backup destination, and Windows ESU/security update state.

No Apache, database, firewall, router, certificate, DNS, or Windows service changes are authorized or implied by this inventory.

## 12. Current execution boundary

Repository source and documentation can be prepared remotely. The actual PC, AMPPS services, router, certificate trust store and physical Android installation cannot be configured or certified from a GitHub-only session. Phase 0 must therefore be performed against the owner's actual Windows PC before any service, firewall, database or certificate changes are made.
