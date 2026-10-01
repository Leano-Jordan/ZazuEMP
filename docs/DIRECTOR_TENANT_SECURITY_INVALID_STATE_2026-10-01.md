# Director Tenant Security + Invalid-State Challenge — 2026-10-01

## Objective

Move Zazu verification beyond the populated happy path and deliberately test two failure classes:

1. Tenant isolation: a signed-in user must never read another business's records by manipulating direct record IDs.
2. Invalid state: operational and financial mutations must reject impossible or unsafe state changes without partial database effects.

## Current HEAD baseline

The challenge was prepared from the current main baseline after the populated runtime browser challenge passed.

## Coverage added

Tenant/security coverage in tests/Feature/TenantSecurityInvalidStateTest.php:
- foreign customer direct-ID access
- foreign purchase-order direct-ID access
- foreign invoice direct-ID access
- foreign asset direct-ID access
- foreign work/event direct-ID access

Each expects 404 and confirms the foreign secret is not rendered.

Invalid-state coverage:
- receiving an already-received purchase order
- receiving more than the remaining ordered quantity
- overpaying an invoice after an existing payment

The invalid-state tests also verify relevant database state remains unchanged after rejection.

## Existing coverage acknowledged

The repository already contains overlapping protection tests in:
- tests/Feature/BusinessIsolationTest.php
- tests/Feature/ParentChildIntegrityTest.php
- tests/Feature/FinancePurchasingInventoryTest.php

This challenge is therefore an explicit Director gate and consolidation point, not a claim that these protections were previously absent.

## Execution gate

1. Run the full Laravel test suite in GitHub Actions.
2. If failures occur, classify each as test defect, fixture defect, environment defect, or application defect.
3. Inspect application code before changing it.
4. Fix only verified application defects.
5. Rerun the full suite.
6. Record final result before authorizing the next security/reliability challenge.

## Release significance

Tenant isolation: Critical. Cross-business disclosure is a release blocker.

Invalid state: High. Financial or stock corruption is a release blocker.

Passing these tests demonstrates automated protection against the specific attack/state cases listed above; it does not constitute a complete security audit or penetration test.
