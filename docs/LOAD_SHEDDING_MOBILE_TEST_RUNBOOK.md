# Zazu EMP — Local-Network Mobile Test Runbook

**Purpose:** Temporary development testing from a phone while the laptop hosts Laravel. This is not the customer PC-host deployment procedure; use [Local PC Host Deployment](LOCAL_PC_HOST_DEPLOYMENT.md) for that.

## Temporary development setup

1. Keep the laptop powered and connected to the phone hotspot or trusted private Wi-Fi.
2. Build frontend assets once: `npm run build`.
3. Start Laravel for development only: `php artisan serve --host=0.0.0.0 --port=8000`.
4. Find the laptop's current LAN IPv4 address with `ipconfig`.
5. On the phone, open `http://LAPTOP-IP:8000`.

The phone does not need GitHub. The laptop serves the application locally.

**Security boundary:** this plain-HTTP Artisan server is temporary development tooling. Use only on a trusted private network. Do not expose it to a public network or use it as the permanent customer server. A changing IP and non-secure HTTP origin are known limitations: service-worker registration requires a secure context, so this address is not the target install origin.

## What this test proves

- Phone can reach the Laravel application over the local network.
- Responsive pages, forms and server-backed workflows work on the phone.
- Local network access can continue when the internet/GitHub is unavailable, provided the laptop and hotspot/router remain running.

## What this test does not prove

- A stable hostname or customer-grade HTTPS.
- Server operation after VS Code is closed or Windows restarts.
- Phone operation while the laptop is powered off.
- Full offline parity across every Zazu business workflow.
- Durable production backups or successful customer recovery.

## Current offline/PWA implementation boundary

Zazu has progressed beyond a static-asset-only service worker. Current source includes a user-scoped authenticated page cache, a phone-local IndexedDB workspace and durable mutation queue, owner-approved pairing/sync foundations, and domain-specific offline mutation handlers. This provides meaningful device-local continuity for supported workflows.

It is still not a complete disconnected replacement for every web-app screen and workflow. Offline reads, writes, attachments, conflict handling, restart persistence and reconnect reconciliation must be judged against their specific automated and physical-device acceptance tests. Do not infer complete parity from the presence of the PWA or service worker.

## Customer deployment

The customer target is a stable local HTTPS hostname served by a persistent Windows web/database runtime, independent of VS Code and the Artisan development server. Follow the deployment plan's phased inspection and acceptance gates. Do not install certificates, change firewall rules, alter services or migrate customer data until the actual PC/network configuration has been inspected and backed up.
