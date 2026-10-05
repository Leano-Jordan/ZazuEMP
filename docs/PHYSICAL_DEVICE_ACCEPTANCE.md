# Zazu EMP — Physical Phone / Tablet Acceptance

**Status:** OPEN — HUMAN DEVICE TEST REQUIRED  
**Date:** 2026-10-04  
**Release owner:** Isaac Junior Lehlogonolo Maluleka  
**Rule:** browser emulation does not equal physical-device acceptance.

## Purpose

Provide the controlled acceptance test for a real phone and real tablet before V1 release.

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
