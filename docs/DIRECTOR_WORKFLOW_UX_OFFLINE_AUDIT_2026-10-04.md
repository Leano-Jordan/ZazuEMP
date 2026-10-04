# Zazu EMP — Director Workflow / UX / Offline Audit — 2026-10-04

**Repository:** Leano-Jordan/ZazuEMP  
**Branch:** main  
**Audit head before this record:** 74101cf49dafad5a77cfc9772106985be1998fd3  
**Director focus:** non-technical usability, workflow coherence, typography, spacing, visual balance, mobile access, and honest offline capability.

## 1. Design principles applied

The audit uses DesignerUp / Elizabeth Alli principles as practical heuristics, not as a requirement to copy another product:

- One primary type family with hierarchy created through weight, size, spacing and colour.
- Body text must remain comfortable at small sizes; line-height should support reading rather than compressing it.
- Space should communicate relationships; not every grouping needs another border/card.
- Alignment and grid rhythm should be consistent.
- Controls need clear hierarchy and finger-friendly hit areas.
- Colour should be intentional and harmonised rather than a collection of unrelated vivid accents.
- UI states and "what happens if..." conditions must be considered, including empty, error, progress, success and unavailable states.
- Real device and real data review matters; responsive emulation is evidence, not physical acceptance.

Sources reviewed:
- DesignerUp typography guidance
- DesignerUp colour guidance
- DesignerUp spacing / UI pro tips
- DesignerUp "What happens if..." layout/state method
- DesignerUp buttons/selection-control guidance
- DesignerUp real design-system/pattern-learning guidance

## 2. Findings

### UX-01 — Quote workflow copy contradicted implemented capability
**Severity:** P2  
**Fix effort:** Low  
**Release impact:** Medium

The quote editor previously described Travel & costing as "Later foundation" and Customer acceptance as "Later workflow". Both concepts already exist in the surrounding workflow: travel/cost records are implemented and quote status can be moved through customer-decision states.

**Action:** corrected the labels to describe the actual current workflow.

**Commit:** c43a99c127545423addaf3f74f5898c38a1acb20

### UX-02 — Job workflow must be judged by task completion, not page availability
**Severity:** P1  
**Fix effort:** Medium  
**Release impact:** High

The job workspace has a strong operational chain, but the acceptance standard should be: a non-technical owner can understand what to do next without knowing Zazu's internal module names.

Critical path to test:
1. enquiry/customer
2. job
3. services
4. quote
5. customer decision
6. invoice
7. payment
8. preparation
9. purchasing/costs
10. completion

The job workspace already has a "Next action" pattern. Director should expand verification around state transitions and empty states rather than adding more navigation.

### UX-03 — Contextual creation should be the default
**Severity:** P1  
**Fix effort:** Low/medium  
**Release impact:** High

The customer-from-job gap has been corrected. The same audit principle now applies to other dependencies: when a user is completing a task and discovers a missing prerequisite, Zazu should provide a nearby creation path or a clearly explained hand-off without losing the current task.

Priority targets:
- missing capability/service while adding a job requirement
- missing supplier while creating purchasing
- missing asset/inventory item where an operational record expects one
- missing tax/business setup when creating commercial documents

Do not blindly add modals everywhere. Use contextual creation where it preserves task context and reduces backtracking.

### UI-01 — Typography has multiple competing type-system intentions
**Severity:** P2  
**Fix effort:** Medium  
**Release impact:** Medium

The code currently references Inter / Plus Jakarta Sans / Segoe UI Variable Text / Segoe UI / system fallbacks, while some headings reference Cabinet Grotesk and numeric/reference content uses JetBrains Mono. Cabinet Grotesk is not bundled as an application font in the repository.

The important issue is not the number of fallbacks; it is that the product should have one deliberate UI type family and a small, predictable hierarchy. Headings should not depend on an unbundled decorative font accidentally resolving on one machine and not another.

**Target:** consolidate the application UI around the locally reliable primary family, with deliberate numeric/reference treatment only where it improves scanning.

### UI-02 — Small text is doing too much work
**Severity:** P2  
**Fix effort:** Medium  
**Release impact:** Medium

There are many 9–12px labels, metadata lines and uppercase eyebrow treatments. These are useful as secondary hierarchy, but a non-technical business owner should not need to read a wall of tiny metadata to understand a task.

**Target:** protect 13–16px as the normal operational reading range, reserving smaller sizes for genuinely secondary metadata.

### UI-03 — Card/border density needs restraint
**Severity:** P2  
**Fix effort:** Medium  
**Release impact:** Medium

The current design system has many panels, cards, list borders and shadows. DesignerUp's container guidance reinforces that borders should contain and organize, not imprison content.

**Target:** use surface, spacing and alignment as primary grouping tools; reserve stronger borders/shadows for interactive or elevated surfaces.

### UI-04 — Action hierarchy must remain decisive
**Severity:** P1  
**Fix effort:** Medium  
**Release impact:** High

The repository has improved intrinsic button sizing, but the remaining audit target is semantic hierarchy: one primary action per task area, secondary actions visually subordinate, and destructive/terminal actions clearly separated.

This matters especially on phone widths where multiple header actions can compete for attention.

## 3. Mobile / phone access finding

The current repository supports responsive phone UI and caches static assets through a service worker.

It does **not** currently implement full phone-local offline business data.

The repository's own offline contract explicitly states:
- local PC/laptop mode is the current practical offline authority;
- local-network phone/tablet access is an architecture target;
- full disconnected phone operation with durable local writes and sync is not implemented.

Therefore the correct claim today is:

**A phone can use Zazu without public internet when it can reach the local Zazu server over the same local network/hotspot. This is local-network operation, not disconnected phone-local offline mode.**

## 4. Physical phone test required

For the current local-server deployment:

1. Start Zazu on the PC using the local server bound to the LAN interface, not only localhost.
2. Identify the PC's LAN/hotspot IPv4 address.
3. Put the phone on the same Wi-Fi/hotspot network.
4. Open Zazu using the PC's LAN address and port.
5. Confirm login and an existing business load.
6. Create a customer.
7. Create a job and add a service.
8. Save the job.
9. Open the job again.
10. Disconnect the PC's external internet while keeping the local network alive.
11. Repeat read/write operations.
12. Confirm the browser never falls back to a public/cloud dependency.
13. If the PC/server itself is unavailable, test whether the phone can still read/write business data. It should currently be recorded as **FAIL / NOT IMPLEMENTED**, not passed.

For a true offline claim, a second test must deliberately disconnect the phone from both the public internet and the local host and prove that the phone still has authoritative business data plus durable queued writes. Current repository evidence says that capability is not implemented.

## 5. Current acceptance position

| Area | Director position |
|---|---|
| Desktop operational workflows | Strong foundation; continue state-transition testing |
| Contextual creation | Improving; customer/job case fixed |
| Typography | Needs consolidation and readability pass |
| Spacing/rhythm | Improved, but still needs cross-screen consistency pass |
| Card/border balance | Needs restraint pass |
| Button hierarchy | Needs cross-screen semantic pass |
| Phone responsive UI | Implemented foundation; physical test still required |
| Phone → local PC without public internet | Architecture supports target, physical proof required |
| Phone-local disconnected offline | NOT IMPLEMENTED |
| Full offline multi-device sync | NOT IMPLEMENTED |

## 6. Director rule going forward

For every critical Zazu workflow, audit the user's intent as:

**What am I trying to accomplish? → What does Zazu need from me? → What happens if something is missing? → What happens after I save? → What should I do next? → Can I recover without losing my place?**

A page being reachable is not sufficient evidence that the workflow is coherent.


## 7. Calendar / local South African context refinement

The calendar now includes a local, code-owned 2026 South African holiday catalogue rather than relying on a remote calendar API. Official South African public holidays are the mandatory baseline. The 2026 catalogue also accounts for the 4 November 2026 local-government-election public holiday declared by the President.

Optional observance categories are available for:
- Muslim
- Hindu
- Christian
- Jewish
- South African/cultural observances

Users can mute optional categories from Workspace Preferences. Official public holidays remain visible.

**Important date-quality rule:** Islamic and other lunar/religious dates can vary by authority, moon sighting and timezone. They are therefore treated as planning observances, not as statutory/public-holiday assertions. The UI should make this distinction clear where necessary.

## 8. Starting-point flexibility

The dashboard now exposes multiple legitimate entry points:
- Start with customer
- Start with job
- Start with quote

The quote-first route creates the required job context and then takes the user directly into quote creation. This preserves the existing domain relationship rather than creating an orphan quote.

This is the beginning of the "start anywhere, connect internally" model recorded in the product definition. The next audit should extend this pattern to missing supplier, capability, inventory and asset dependencies.

## 9. Visual attention-map direction

Director added a semantic monochrome-compatible card accent mechanism. It deliberately avoids turning every card into a different coloured box.

The design rule is:
- neutral surfaces remain dominant;
- one restrained accent identifies the semantic section;
- stronger colour is reserved for meaning, not decoration;
- adjacent sections should be recognisable in seconds through position, title, accent and spacing;
- cards should not become a rainbow.

The next visual pass should apply these semantic tones only to genuinely distinct operational groups and validate them on dashboard, job workspace, finance and calendar.

## 10. Dialog centering

Shared native dialog surfaces were given an explicit viewport-centering rule and bounded mobile height/width. This addresses the observed class of popup drift where a modal can appear visually offset depending on viewport/layout context.

Physical-device confirmation remains required.

## 11. Evidence boundary

The latest main head is e7fd90da7fd40fc7a37c679da346ea0298baf07f.

Current-head GitHub Actions are still queued. Do not classify these changes as CI-green until those runs complete.
