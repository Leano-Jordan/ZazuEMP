# Changelog

## Unreleased — 2026-11-17 (Rental allocation correction candidate)
- Reproduced the non-overlapping asset booking rejection (HTTP 422) at c7be735; added online and offline regression coverage.
- Enabled multiple non-overlapping date allocations for serialized assets, added allocation end-date entry, per-allocation return handling and consistent asset status while other bookings remain active.
- Kept physical return operator-confirmed; no automatic release on event completion or date passage.
- Exact-head CI for this correction is pending; no readiness score uplift.

## Unreleased — 2026-11-17 (Director audit reconciliation)
- Reconciled Director state, readiness register and V1 release checklist to live main HEAD ac795437 and its exact-head CI evidence.
- Archived prior long-form state, readiness register and memory snapshots without discarding history.
- Recorded Claude's eight findings in the existing readiness register; no duplicate backlog or scorecard.
- Confirmed root ROSCORE_PROJECT_MANIFEST.md as canonical; docs/ROSCOR_PROJECT_MANIFEST.md is now a legacy pointer.
