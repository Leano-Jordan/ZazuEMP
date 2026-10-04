# ZAZU EMP — REGRESSION LEDGER

Known failure patterns and permanent controls.

## REG-001 — Automated PHP write corruption
Automated writing previously corrupted PHP namespace/import backslashes.

**Control:** re-fetch changed PHP files immediately and inspect namespace, imports, declarations and route/controller compatibility.

**Status:** CONTROL ACTIVE

**2026-10-01 recurrence:** a newly added UI regression test briefly received flattened model namespace references during automated repository writing. Director re-read the file, detected the corruption before closure and restored valid imports/usages. This remains a write-integrity control issue, not an application design issue.

## REG-002 — Repository migration vs existing database drift
A repository migration can be correct while an existing local database remains behind.

**Control:** populated-database upgrade verification is a release gate. Never assume source migrations equal local schema state.

**Status:** CONTROL ACTIVE

## REG-003 — Symptom patching
Repeated local fixes can leave the shared root cause intact.

**Control:** two failed corrections on one root cause → FORENSICS.

**Status:** CONTROL ACTIVE

## REG-004 — Historical documentation masquerading as current state
Dated records can contain old heads, statuses and sequences.

**Control:** STATE.md is current control state; historical records stay dated and are not treated as current instructions.

**Status:** CONTROL ACTIVE

## REG-005 — Cross-project context contamination
Generic terms can cause an agent to import concepts from an unrelated project.

**Control:** repository identity lock + context firewall + current-state source hierarchy.

**Status:** CONTROL ACTIVE

## REG-006 — Shared UI collateral damage
Shared tokens/layout/components can affect many screens at once.

**Control:** shared UI changes require blast-radius review, theme/responsive review and Guardian regression checks.

**Status:** CONTROL ACTIVE


## REG-007 — Verification-layer conflation
A failing test, static scanner, CI workflow, fixture, environment or application defect can present as the same red execution result.

**Control:** classify every failure F1–F8 before changing application code using 06_VERIFICATION_AND_QUALITY_ENGINE.md.

**Status:** CONTROL ACTIVE

## REG-008 — Append-only visual token drift
Zazu accumulated repeated visual sweep/root-token layers. Later declarations silently overrode earlier visual decisions, producing inconsistent colour relationships and theme behaviour.

**Control:** one shared light root + one shared dark root for design tokens; visual changes modify the shared authority rather than appending another sweep. Rendered desktop/mobile theme verification remains required.

**Status:** CONTROL ACTIVE

## REG-009 — Temporary branch residue
Ordinary Director execution created multiple non-main branches around isolated work, increasing repository/sync clutter and fragmenting the working state.

**Control:** main-only Director execution unless the owner explicitly authorizes another ref. Do not create temporary branches for routine verification.

**Status:** CONTROL ACTIVE

## REG-010 — Role-to-permission drift
A business role can be seeded or assigned in data without a corresponding permission definition, causing silent authorization denial across the application.

Observed 2026-10-01: the populated demo creates role manager, while config/zazu.php has no manager permission map. PermissionService therefore returns an empty permission set for that membership.

Control: every non-owner role referenced by seeders, membership fixtures or production-facing role administration must have an explicit permission matrix and adversarial route coverage. Missing role mapping is a release evidence blocker, not an implicit deny-by-default success.

Status: CONTROL ACTIVE

## REG-011 — AI failure-loop recurrence

A verification failure can recur across cycles while the Director repeatedly reinterprets it as a new task. This causes symptom patching, contaminated experiments and wasted verification cycles.

**Control:** persistent failure case ID + fingerprint + hypothesis ledger + 2-attempt hypothesis budget + 3-cycle case budget + mandatory escalation. Rejected approaches remain recorded.

**Status:** CONTROL ACTIVE

## REG-012 — Offline cache privacy/staleness leakage

A service worker that caches all same-origin images can accidentally retain authenticated/private media and stale business presentation assets on the device.

**Control:** service-worker caching is restricted to public static asset paths under /build/ and /images/. Old Zazu static cache generations are deleted during activation. Private /media paths are never cacheable by this policy.

**Status:** CONTROL ACTIVE

## REG-013 — MySQL restore partial-failure exposure

A restore can partially modify a MySQL database because the restore operation is not equivalent to an atomic SQLite file replacement.

**Control:** before a MySQL restore, stage a pre-restore dump. On failure after database replacement has begun, attempt restoration from that pre-restore dump before reporting the failure. Never activate private-storage replacement before the rollback snapshot exists.

**Status:** CONTROL ACTIVE

## REG-014 — E2E auth-state and responsive-navigation coverage gaps

Browser suites can silently skip authenticated UI coverage when they depend on an externally generated storage-state file; desktop-only locators can also miss controls collapsed into mobile navigation.

**Control:** Authenticate UI E2E tests through the seeded demo account by default, assert current semantic buttons, and explicitly open collapsed navigation before locating mobile actions. Keep only documented device-specific skips.

**Status:** CONTROL ACTIVE


## REG-015 — Resource register right-edge collapse / single-item layout regression

Asset and inventory registers previously regressed into a flex row where useful record content became squeezed while the action cluster consumed disproportionate right-edge space. A single record could therefore look like a large empty container with controls visually detached.

**Control:** asset/inventory rows use an explicit three-region desktop grid (record / state / actions), bounded action forms, left-aligned action groups, and deterministic 1100px/760px/520px/400px breakpoints. Do not restore generic right-justified flex treatment for these registers.

**Status:** CONTROL ACTIVE

## REG-016 — Foreground/surface theme contrast regression

Legacy landing and shared-banner declarations mixed dark-shell foreground tokens with light landing surfaces and dark page text with dark/image-backed surfaces.

**Control:** meaningful text uses an explicit foreground family matched to its surface. Image-backed banners are intentionally dark surfaces with light foregrounds. Light public landing surfaces use dark blue foregrounds. Contrast review covers default, hover, focus and state surfaces.

**Status:** CONTROL ACTIVE

## REG-017 — Workspace-state visual language drift

Page command banners previously used a separate pale-card treatment while the dashboard workspace-state area carried the stronger image-led operational language.

**Control:** shared .zazu-command-band is the page-banner authority and inherits the dashboard image variable, dark readability overlay, explicit light foregrounds and workspace-style state meta panel. Page-specific selectors must not replace this with unrelated light/dark text combinations.

**Status:** CONTROL ACTIVE

## REG-018 — Business logo presentation drift

Business logos are identity-critical. Treating the logo as a small decorative square or allowing arbitrary image sizing makes customer branding look unfinished and can create distorted or cropped marks.

**Control:** shared brand area reserves a 40px identity slot, keeps 72px shell height, uses object-fit: contain, centered positioning and a controlled shell surface. Preserve the supplied logo without distortion/cropping.

**Status:** CONTROL ACTIVE

## REG-019 — Landing navigation/composition geometry drift

Public navigation links, CTAs and later bento items can drift vertically or leave unused horizontal space when individual elements use unrelated sizing or implicit auto-placement.

**Control:** landing navigation uses a common 40px control rhythm; Plan and Delivery explicitly occupy the final 50/50 desktop row; responsive rules reset explicit placement below 920px. Do not reintroduce uneven CTA heights or unbounded final-row auto-placement.

**Status:** CONTROL ACTIVE

## REG-017 — Director visual recovery invariants

The 2026-10-05 UI recovery exposed a cluster of regressions caused by later CSS/JS overrides defeating earlier visual decisions.

**Control:** treat these as protected shared contracts:
- `.zazu-command-band` remains an intentionally dark, image-led page-introduction surface with explicit light foregrounds. Late generic light-surface harmonization must never target it.
- The light application palette remains blue-slate and must not drift back to white/near-white as the default shell direction.
- The canonical card/panel primitive owns the restrained 10px/110% glass treatment; later visual sweeps must not remove or duplicate that authority.
- Global desktop search is bounded utility chrome and must not expand the application shell. Mobile search stays a full-width utility row.
- The Zazu Helper remains the established mascot size. It yields specifically to Work and Sales navigation popovers by moving to the right edge of the active popover; generic collision engines must not be reintroduced.
- Customer quick-create must expose a permissioned path to the full customer editor after creation.
- Customer forms present identity and primary contact information before business, billing and tax details.
- Native customer dialogs must have an explicit viewport-centering contract.
- Any broad visual change touching these contracts requires source re-read, regression review and rendered desktop/mobile/light/dark acceptance before closure.

**Status:** CONTROL ACTIVE
