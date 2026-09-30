# Zazu EMP — External Capability & Dependency Register

**Status:** ACTIVE RESEARCH / GOVERNANCE REGISTER  
**Director update:** 2026-10-01

## Integration principle

Zazu is local-first.

External services and open-source libraries should **enhance** the product rather than become hidden dependencies for core business operations.

Every adopted dependency must be checked for:
- current license;
- maintenance/activity;
- bundle/runtime impact;
- security implications;
- privacy/data egress;
- offline behaviour;
- mobile/browser compatibility;
- removal/replacement path.

## Candidate capability map

| Capability | Candidate | Where it could live | Why |
|---|---|---|---|
| OCR | Tesseract.js | Receipt/expense capture, imports | Turn photos/scans into structured data |
| Local search | MiniSearch / FlexSearch | Global search | Fast client-side search without an online API |
| PDF viewing | PDF.js | Documents | View PDFs inside Zazu |
| QR/barcode scanning | html5-qrcode or equivalent | Inventory/assets | Faster stock and asset identification |
| QR generation | QR library | Assets/jobs | Label equipment/resources |
| Signature capture | Signature Pad | Deliveries/job completion | Customer acknowledgement |
| Charts | Chart.js | Reporting/dashboard | Visual business information |
| Date utilities | date-fns | Scheduling/calendar | Reliable date handling |
| Maps | MapLibre GL JS / Leaflet | Delivery/location | Mapping without making Google Maps mandatory |
| Offline browser storage | IndexedDB / Dexie | PWA/offline enhancements | Local client-side state/cache |
| Voice | Vosk or browser speech capability | Job intake/helper | Capture simple spoken instructions |
| Icons | Tabler Icons / Lucide | Whole UI | Consistent iconography |
| Self-hosted fonts | Fontsource | Whole UI | Local/offline typography without CDN dependency |

## High-value candidates

### OCR — HIGH FUTURE VALUE
Potential flow:
Photo/scan → OCR → field extraction → validation → user approval → business record.

First targets:
- receipts;
- supplier documents;
- customer imports;
- job paperwork.

OCR output must never silently create commercial records. Human confirmation remains the approval boundary.

### Local search — HIGH VALUE
Search should remain useful offline and should not require an AI service.

Use structured indexes for:
- customers;
- jobs/events;
- services;
- suppliers;
- documents;
- purchasing;
- finance.

### PDF.js — MEDIUM/HIGH VALUE
Good fit for Zazu's document-heavy workflows and local-first approach.

### QR/barcode — MEDIUM/HIGH VALUE
Strong fit for physical inventory/assets, especially hire businesses.

### Signatures — MEDIUM VALUE
Useful for delivery, collection and job acknowledgement workflows.

### Voice — EXPERIMENTAL
Potentially useful for:
"Add 50 chairs to Saturday's wedding."

Must remain an enhancement, not a core dependency.

### Maps — OPTIONAL
Useful for delivery/travel context. Do not make basic job creation or job viewing dependent on a map provider.

## API policy

An external API is allowed when it provides meaningful enrichment, but the application must define:

**online available → enhanced experience**

**online unavailable → safe fallback**

Core operations must not fail merely because an optional service is unavailable.

Examples:
- weather unavailable → show no forecast, not a broken job;
- map unavailable → show saved address;
- translation unavailable → retain original text;
- external messaging unavailable → preserve message content and status;
- AI unavailable → normal workflow remains usable.

## License policy

"Open source" is not sufficient evidence for adoption.

Before adding a dependency, record:
- exact package/repository;
- version;
- exact license;
- attribution obligations;
- redistribution requirements;
- known restrictions;
- date checked.

## Data-egress policy

Before using an external API, explicitly answer:

1. What Zazu data leaves the device/server?
2. Where does it go?
3. Is personal/customer/business information included?
4. Is it necessary?
5. Can the feature work locally?
6. Can the user disable it?
7. What happens if the service is unavailable?

## Current status

These are candidates, not approved dependencies.

No candidate in this register becomes a release dependency without a separate Director adoption decision and license/privacy verification.

