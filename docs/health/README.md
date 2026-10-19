# Director health evidence archive

The files in this directory are retained historical snapshots, not current health reports.

- `2026-10-12-dependabot-stale-report.md` and `.json` describe commit `2d26f4b146d90b704eb4b29e1a75ac48f5e773b6` on the Dependabot branch `dependabot/npm_and_yarn/npm_and_yarn-0ef4b58ac6`.
- That report was generated on 2026-10-12 and recorded Laravel and browser workflows as queued. It is not evidence for current `main`.
- The current workflow `.github/workflows/roscore-director-health.yml` publishes a per-run artifact and Actions summary. It does not update persistent `latest.*` files.
- Current CI state must be read from the exact current-main commit's GitHub Actions runs. A missing or stale report is an evidence gap, never a PASS.

Do not recreate `docs/health/latest.md` or `docs/health/latest.json` unless a separately reviewed, main-only, least-privilege publication design is implemented and verified.
