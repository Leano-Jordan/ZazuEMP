# Rosscore Director Health Report

- **Repository:** Leano-Jordan/ZazuEMP
- **Director:** Jarvis
- **Branch:** jarvis/hardening-2026-10-08
- **Commit:** 6d8b2baf64cb16a82910aa175a1ef8b4a3a24b47
- **Commit message:** Fix offline job smoke fixture required date
- **Readiness:** **PARTIAL_DIRECTOR_CONTRACT**
- **Current-head CI:** **PENDING**

## Current-head CI Evidence

- **Laravel**: in_progress — https://github.com/Leano-Jordan/ZazuEMP/actions/runs/37897467947
- **Zazu browser smoke**: in_progress — https://github.com/Leano-Jordan/ZazuEMP/actions/runs/37897467864

## Health Signals

- WARN — manifest
- PASS — agents_contract
- PASS — package_json
- PASS — composer_json
- WARN — requirements
- PASS — github_actions
- PASS — tests_directory
- PASS — readme
- WARN — test_script
- PASS — build_script
- WARN — lint_script

## Risk Flags

- NO_ROSSCORE_MANIFEST
- CURRENT_HEAD_CI_PENDING

## Engineering State

- **Active target:** not recorded
- **Next hard gate:** reconcile current-head regression/CI evidence, then customer-environment backup/restore proof**.

## Changed Files

- e2e/offline-attachments.spec.js
