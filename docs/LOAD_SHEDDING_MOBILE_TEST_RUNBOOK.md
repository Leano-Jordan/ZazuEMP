# Zazu EMP — Field Testing During Load Shedding

## Purpose

Zazu is currently a Laravel application. It is **not yet a full offline-first application**. During load shedding, the safest current test setup is to keep the development server running on the laptop and connect the phone directly to that laptop over a local network.

This works without GitHub and without internet access.

## Recommended setup: phone hotspot + laptop

1. Keep the Zazu laptop on battery power.
2. Turn on the phone's mobile hotspot.
3. Connect the laptop to the phone hotspot.
4. In the Zazu project on the laptop, build the frontend assets once: npm run build
5. Start Laravel so it listens beyond localhost: php artisan serve --host=0.0.0.0 --port=8000
6. Find the laptop's hotspot/LAN IPv4 address with Windows ipconfig.
7. On the phone, open: http://LAPTOP-IP:8000
8. Sign in and use Zazu from the phone browser.

The phone does **not** need GitHub. The laptop serves the application locally.

## If Windows Firewall blocks the phone

Allow PHP/Laravel on the **Private network** when Windows asks. Do not expose the development server to a public network.

## What this currently proves

- Real Zazu pages on a phone.
- Mobile navigation and responsive UI.
- Forms and server-side workflows.
- Real local database behaviour.
- Behaviour while the internet/GitHub is unavailable.

## What it does not prove

- Full offline operation with the laptop powered off.
- Offline writes that synchronise later.
- Production hosting or production TLS.

Those require a separate offline-first architecture and are **not** being faked by the current PWA asset cache.

## Current offline/PWA boundary

Zazu now has an installable web-app manifest and a conservative service worker that caches static assets only. It deliberately does **not** cache authenticated HTML or business data. This reduces the risk of exposing stale/private workspace data while still allowing previously loaded assets to remain available when the network disappears.

Full offline business operation remains a later architecture decision. The immediate goal is reliable **local-network mobile testing** during load shedding.
