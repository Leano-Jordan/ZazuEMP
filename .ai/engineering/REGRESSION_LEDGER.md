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
