# Changelog

All notable Zazu EMP project changes will be recorded here.

## 2026-09-26

### Fixed / Updated

- Added a conditional Work event-schema repair migration for existing databases missing `events.deleted_at` and/or the Work night-contact column.
- Fixed Work creation code that referenced `$event` before the event record existed.
- Restored the Work Edit night-contact selector so the matching JavaScript and submitted field are present together.
- Added photography/Photographer and camera-hire capability direction for event and wedding workflows.
- Corrected project ownership records so Isaac Junior Lehlogonolo Maluleka is the current solo developer and owner.
- Recorded Rosscore Labs as a future business identity only, pending registration and any later written IP transfer.
- Added the commercial/legal protection register and event-media privacy controls.
- Populated the third-party licence register with top-level dependency information and a release-level transitive-audit gate.

## 2026-09-23

### Updated

- Established `memory.md` as the living Zazu EMP project context and bootstrap document for future project sessions.
- Recorded Rosscore Labs Pty Ltd as product owner and Isaac Junior Lehlogonolo Maluleka as founder, company owner, creator and lead developer.
- Recorded the multi-business product direction across catering and event-related services.
- Recorded real operator discovery findings from Sindi Sithole.
- Recorded desktop/laptop and mobile field-use requirements.
- Recorded workspace-first UX and reduced-primary-navigation direction.
- Recorded the current R0 budget constraint and free/open-source-first infrastructure evaluation.
- Recorded role-based access and business-data isolation as foundational requirements.
- Recorded the current Laravel/Livewire/MySQL technical direction as a proposal subject to owner approval.
- Updated README, IP ownership and licence notices to match current project reality.

## 2026-09-22

### Added

- Repository ownership and IP-control documentation.
- Proprietary project licence.
- Security policy.
- Contribution controls.
- Third-party licence register.
- Project memory.
- Project Genesis framework.
- Human-First Discovery framework.
- RossCore Engineering Command Engine framework.

## 2026-09-26

### UX / Architecture hardening cycle

- Reworked operational list hierarchy so page titles, section labels, column headers, records and row actions are visually distinct.
- Replaced avoidable typed currency/category/unit fields with guided choices and centralized configuration.
- Added a business default currency setting and reused it in quote, cost and travel workflows.
- Added Work quick-view workload navigation and overdue preparation visibility.
- Centralized active business resolution and added model-level business ownership protection.
- Scoped affected operational queries and writes to the active business context.
- Added lifecycle rules preventing invalid Work status movement and mutation of closed Work records.
- Prevented downstream requirements, quotes, costs, travel and preparation changes after Work closes.
- Hardened quote monetary arithmetic around integer cents and historical snapshots.
- Moved quote construction and revision logic into a transaction-aware QuoteService so Work requirements, quote versions and historical snapshots stay aligned.
- Added stale-quote detection and an explicit draft-revision review/edit workflow when Work services change.
- Attached currency to reusable catalogue prices and prevented catalogue defaults from silently crossing quote currencies.
- Added exact two-decimal validation for requirement quantities and commercial prices.
- Fixed catalogue service selection so choosing a saved service does not clear its capability binding.
- Kept customer profile-photo replacements on private local storage, with cleanup of legacy public paths.
- Added regression coverage for quote revision integrity, currency-aware pricing, exact Money arithmetic, private media replacement and route integrity.
- Added BusinessIsolationTest coverage for cross-business access, workload isolation and lifecycle controls.
