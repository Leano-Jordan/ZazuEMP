# Zazu EMP — 2026-11-14 Engineering Checkpoint

## Verified application source

- Repository: `Leano-Jordan/ZazuEMP`
- Branch: `main`
- Application/test source checkpoint: `3dbaad2d9c4b2a1f1df5d5dd215852661dbcb500`
- Change: preserve rejection of a single backslash in referenced archive file paths after extracting archive file collection and path validation into focused services.
- Structural review from current `main`: `BusinessArchiveService` has 19 methods; `BusinessArchiveFileService` has 10; `BusinessArchiveFileCollector` has 8. The collector's path guard rejects traversal markers and backslashes.

## Exact-source automated evidence

All results below completed successfully against the archive refactor source checkpoint:

- Laravel workflow [43521504183](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43521504183): Laravel tests **495 passed, 1 skipped, 3,475 assertions**; PostgreSQL migration smoke **PASS**; offline-browser acceptance **29 passed**.
- PHPMD [43521504151](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43521504151): **PASS**.
- Psalm security scan [43521504203](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43521504203): **PASS**.
- Quality/security [43521504201](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43521504201): dependency audit, secret scan and application job **PASS**.
- Populated upgrade/rollback [43521504629](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/43521504629): **PASS**.

## Release disposition

**V1 remains NOT READY.** Automated archive and browser acceptance are green, but they do not replace these operational gates:

1. Physical Android secure-origin installation, cold launch, disconnected workflow, force-close/reopen persistence and reconnection.
2. Controlled installed-target business archive export/import recovery drill.
3. Live business-record quote → payment → profit reconciliation.
4. POPIA operational controls, privacy/retention/incident procedures and Zazu name/trade-mark clearance.

No deployment, production database, customer data or non-main branch was changed in this checkpoint. The 29-test browser result is CI evidence, not proof of physical Android offline parity.
