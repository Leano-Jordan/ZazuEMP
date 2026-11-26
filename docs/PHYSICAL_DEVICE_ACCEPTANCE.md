# Zazu EMP — Physical Phone / Tablet Acceptance

**Status:** OPEN — HUMAN DEVICE TEST REQUIRED  
**Date:** 2026-10-04  
**Release owner:** Isaac Junior Lehlogonolo Maluleka  
**Rule:** browser emulation does not equal physical-device acceptance.

## Founder-reported physical use — 2026-11-22

The Founder confirms Zazu EMP has been physically used on the PC and linked mobile phone, both operating against the same workspace. This is recorded as **OWNER-VERIFIED PASS for normal connected operation and shared-workspace use**. The Founder also confirms the existing fictional workspace is populated across customers/clients, quotes, invoices, suppliers, purchase orders and inventory, and that these modules work as intended.

This report does **not** certify the disconnected cold-launch/relaunch/reconnect flow, attachment round-trip, or a physical tablet test. Device model, OS/browser versions, deployed origin and exact release commit were not supplied with the screenshots. Keep those specific checks open; do not ask the Founder to repeat basic connected PC/mobile setup.

## Phase 1 — connected physical operation

**Status: OWNER-VERIFIED PASS — completed physically by the Founder multiple times; reaffirmed 2026-11-24.**

The Founder has repeatedly exercised the actual Windows PC installation and linked Android phone while connected, confirmed both operate against the same Zazu EMP workspace, and confirmed the populated fictional business modules work in their respective areas. This is accepted evidence for connected PC/mobile operation and shared-workspace use. Do not request that this phase be repeated.

## Phase 2 — offline physical acceptance

**Status: PARTIAL PASS — owner-observed phone offline page rendering; remaining acceptance OPEN.** On 2026-11-26 the Founder confirmed that the PC host was OFF and the Android phone opened offline workspace pages with locally available business data rendered. The offline-state banner appeared at the top and stated that the Zazu server was unavailable, locally saved records remained on the device, and only supported offline screens could be used. The previous frozen-last-online-dashboard / collapsed-all-pages symptom is therefore no longer the current reported behaviour.

The screenshots showed a mobile launchpad header composition defect. Source inspection confirmed that `/settings` is intentionally outside the offline route allowlist because the current settings, pairing and owner controls are server-dependent. Keep that destination hidden while disconnected; do not reintroduce a dead link. A specific setting may be exposed only after its local behaviour is implemented and tested. The header defect has since been corrected and automated-browser-verified; preserve the existing offline UI and continue targeted reliability work without replacing the architecture.

**Evidence boundary:** this is owner-observed proof of phone-side route/page rendering with local data while the PC host is off. It does not yet prove all offline routes, Settings functionality, local writes, airplane-mode cold launch, force-close/restart persistence, attachment round-trip, or exactly-once reconnect. Keep those distinct checks open. Do not ask the Founder to repeat the already-confirmed PC-off/local-data/page-rendering test or connected PC/mobile setup. Request a new physical check only after the relevant engineering work is complete and only for a specific remaining acceptance criterion.
 
**Follow-up screenshot evidence — 2026-11-26, 09:48–09:49:** the Android offline workspace visibly reports 13 saved records, 0 waiting to sync and 0 conflicts needing attention. The screenshots exposed a layout defect: the workspace title/description were squeezed beside the module links. Source diagnosis and the scoped layout correction are recorded in `.ai/engineering/STATE.md` and CASE-ZAZU-0050. The correction is automated-browser-verified on exact checkpoint `4a5bedfc`: 75 Chromium offline/browser journeys passed including the 390×844 geometry assertion; 11 mobile offline Work/visual checks passed; mobile host-outage and phone-local attachment checks passed. The `4a5bedf` test-only instrumentation refinement passed its exact-head rerun. A post-fix physical visual confirmation remains pending. The screenshots do not prove a new local mutation, restart, attachment or reconnect flow. Do not repeat prior PC-off/page/data checks; request only the corrected header visual check.


## Purpose

Provide the controlled acceptance test for a real phone and real tablet before V1 release.

## Secure-origin preconditions

- [ ] Zazu is deployed to the selected persistent hosting environment and has a stable HTTPS origin.
- [ ] The PC, phone and tablet all open the same HTTPS origin.
- [ ] The certificate is valid and trusted by each device; no browser certificate warning is bypassed.
- [ ] The test is not served from the development workstation, a temporary LAN IP, the Laravel port 8000, or a temporary local proxy.
- [ ] Before changing from any earlier origin, pending mutations, conflicts and attachment uploads have been reconciled. Browser storage must not be cleared to force migration.
- [ ] The service worker registers and activates on the release HTTPS origin before disconnected-mode testing.
- [ ] The release commit and deployed origin are recorded before testing.

A device that bypasses a certificate warning or is tested only on a temporary development origin has not passed secure-origin/PWA acceptance.

## Record the actual test environment

| Field | Phone | Tablet |
|---|---|---|
| Manufacturer/model | `[record]` | `[record]` |
| OS/version | `[record]` | `[record]` |
| Browser/version | `[record]` | `[record]` |
| Screen size/resolution | `[record]` | `[record]` |
| Network mode | `[Wi-Fi / hotspot / local server]` | `[Wi-Fi / hotspot / local server]` |
| Zazu release commit | `[record]` | `[record]` |
| Tester/date | `[record]` | `[record]` |

## Acceptance flows

### A. Shell and navigation

- [ ] App loads without horizontal clipping.
- [ ] Header does not overlap navigation.
- [ ] Hamburger/menu controls remain reachable.
- [ ] Zazu Helper does not overlap content or sit detached from its trigger.
- [ ] Popovers/drawers escape their containing surface correctly.
- [ ] Back navigation preserves task context.

### B. Authentication/onboarding

- [ ] Login.
- [ ] Registration.
- [ ] Products/services setup.
- [ ] Business information setup and skip/resume behaviour.
- [ ] Dashboard entry.
- [ ] Validation and Zazu toast feedback.

### C. Core operations

- [ ] Create customer.
- [ ] Create job/event.
- [ ] Add requirements/services.
- [ ] Work/preparation view.
- [ ] Supplier/purchasing flow.
- [ ] Inventory/assets.
- [ ] Quote/invoice.
- [ ] Record payment.
- [ ] View reports.
- [ ] Open/remove/download an attachment where permitted.

### D. Information density and controls

- [ ] Tables remain readable without forcing unnecessary full-screen width.
- [ ] Primary/secondary buttons have clear hierarchy.
- [ ] Buttons do not stretch across a large card without purpose.
- [ ] Select/drop-down controls are sized to their content/context.
- [ ] Form fields have sensible maximum widths.
- [ ] Dense records remain scannable at the actual device width.

### E. Offline/local operating conditions

Test the actual supported deployment mode:

- [ ] Zazu remains usable when external internet is unavailable, to the extent claimed by the release.
- [ ] Local server/hotspot connection behaves predictably.
- [ ] An offline failure does not strand the user behind a generic browser error.
- [ ] Any operation requiring connectivity is clearly communicated.
- [ ] No claim of full disconnected multi-device sync is made unless the release actually implements and proves it.

### F. Media and accessibility

- [ ] Upload a representative image/document.
- [ ] Download/open authorized private media.
- [ ] Verify unauthorized media remains inaccessible.
- [ ] Focus states are visible.
- [ ] Text remains readable at the actual device size.
- [ ] Touch targets are practical with normal finger use.
- [ ] Reduced-motion/system accessibility settings do not break the interface.

## Failure classification

- **P0:** security/privacy/data loss or unusable critical workflow.
- **P1:** major device-specific workflow break or persistent overlap/overflow.
- **P2:** noticeable visual or interaction defect without critical workflow loss.
- **P3:** cosmetic/polish issue.

## Acceptance rule

Physical-device acceptance is **OPEN** until both a real phone and real tablet are tested and the results are recorded against a specific release commit.

Playwright Pixel 7/tablet emulation remains useful evidence, but it cannot be promoted to physical acceptance.

**Additional offline reliability evidence — 2026-11-26:** the server-acknowledgement transaction has been changed so queue removal and local workspace reconciliation commit atomically. Exact checkpoint `4a5bedfc` passed the 75-test desktop offline suite, the mobile checks and the 765-test Laravel suite. This does **not** prove a physical abrupt power loss/restart/reconnect. Browser storage retention is also an open engineering item: the app does not currently request/report persistent origin storage or quota, so do not interpret an IndexedDB write as protection against browser-origin eviction or deliberate user clearing. The storage-retention work is recorded in `docs/ZAZU_V1_OFFLINE_CAPABILITY_CONTRACT.md`.
