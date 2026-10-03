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
