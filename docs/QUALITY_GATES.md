# Zazu EMP — Automated Quality Gates

This document records what CI currently measures and what those measurements do—and do not—prove.

## Frontend performance baseline

Measured from the production Vite build on 21 November 2026:

| Asset class | Compressed baseline (gzip) | CI ceiling |
|---|---:|---:|
| JavaScript | 23.6 KiB | 64 KiB |
| CSS | 67.2 KiB | 100 KiB |

The ceilings allow room for deliberate product growth while catching substantial unexplained bundle expansion. CI reports raw and compressed totals and the largest individual assets.

These are transfer-size guardrails. They do not measure browser execution time, interaction latency, memory use, CPU consumption, database query time or performance on a low-powered customer PC or phone.

## Accessibility

Playwright runs axe-core against the public landing page and registration/login dialogs using WCAG 2.1 A/AA rules. Critical and serious findings fail the browser acceptance job.

Current scope is an initial public/authentication baseline. It is not yet a whole-application accessibility certification. Extend coverage to authenticated dashboard, event, quote, invoice, inventory and finance workflows before claiming broad WCAG conformance.

## Documentation integrity

CI checks maintained Markdown documents for broken local links and verifies that core installation, security and backup/recovery documents retain required operational topics. Archived engineering notes are excluded from link enforcement because their references describe historical repository states.

## Database compatibility

See [Database Compatibility Verification](DATABASE_COMPATIBILITY.md) for the supported test matrix and release rules.

## Release integrity

The Windows candidate workflow builds from an exact main commit, performs a clean Windows installation/recovery acceptance run, and publishes only as a manually requested GitHub prerelease after both package and fresh-install jobs pass. A release tag is never overwritten. The release notes explicitly avoid claiming that all physical-device, commercial and legal V1 gates are closed.

## Next performance gate

Before introducing runtime performance thresholds, capture repeatable timings and resource use on the actual supported Windows and Android environments. Use fixed datasets and warm/cold runs; report median and p95, PHP/Laravel request timings, slow SQL, browser interaction timings, CPU and peak memory. Do not turn noisy single-run measurements into release blockers.
