# Claude handover — Zazu EMP V1

**Updated:** 2026-11-17 · **Repository:** `Leano-Jordan/ZazuEMP` · **Canonical ref:** `main`

## Role and boundary
Claude is an independent read-only auditor/adviser for V1. The owner remains final authority; Director/Jarvis owns sequencing and acceptance. Do not edit code/docs, commit, push, create branches, alter data or change configuration unless the owner explicitly authorises implementation. Do not create a second backlog or scorecard.

## Current baseline
- Verify live `main` HEAD before every audit; this handover is a pointer, not a substitute for current repository evidence.
- At update time, HEAD was `0e5d4e0cc92f90a98d8930df3e1ecdcdab4e33cf`.
- Recent work exercised offline invoice-payment bootstrap through the real device-pairing path, including nested payment preservation.
- Exact-head CI at `0e5d4e0`: Laravel **PASS** ([44036479173](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44036479173)); Zazu Quality **PASS** ([44036479156](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44036479156)); Push on main **PASS** ([44036478578](https://github.com/Leano-Jordan/ZazuEMP/actions/runs/44036478578)); SonarCloud **SKIPPED**.
- These workflow conclusions do not prove physical Android acceptance or live-business reconciliation.

## V1 boundaries
- Frozen scope: fix reproducible defects in agreed workflows and demonstrated security, privacy or data-integrity failures. No speculative features, broad rewrites or generic best-practice lists.
- Never weaken tests to get green. Do not repeat checks without new evidence.
- Automated mobile viewport tests are not proof of physical Android installed-app cold launch, host-loss, force-close/reopen or reconnect behaviour.
- Keep confirmed defects separate from hypotheses, accepted limitations and owner-dependent evidence.

## Audit focus — choose one target at a time
1. **Offline commercial records:** real server bootstrap envelopes, nested payments/items, identity joins, queue reconciliation and business isolation. Recent payment normalization work is already covered; do not reopen without new evidence.
2. **Phone-local attachments:** durable bytes, business/Work ownership, retry/idempotency and truthful sync status.
3. **Offline session/device transition:** revoked or expired devices, business switching, stale responses and queued financial mutations.
4. **Recovery:** business archive portability versus installation-wide backup/restore, including private media.
5. **Commercial integrity:** quote → acceptance → invoice → payment → costs → profit, using integer-cent money and real business scoping.
6. **Rendered UX:** report only concrete regressions in the existing Zazu visual system; distinguish source inspection from rendered proof.

Read only the relevant source/tests for the chosen target. Useful paths are discoverable from the current implementation and tests; do not preload every listed file.

## Owner/operator release evidence still outstanding
- Physical Android installed-app cold launch, host disconnection, force-close/reopen, local persistence and reconnect.
- Controlled recovery drill on the intended installation, including private media.
- Populated-business quote/payment/cost/profit reconciliation.
- POPIA operational/privacy sign-off.
- Owner-PC/browser rendered acceptance where still outstanding.

The current release checklist and readiness register govern these gates. Do not restate or duplicate them in a new tracker. Do not inflate the recorded 73/100 evidence-maturity score without qualifying evidence.

## Required audit response
Return at most **three** ranked findings, each with severity, exact evidence (file/function/line or reproducible test), customer impact, confidence, smallest recommended correction and V1 disposition (**fix now / accepted limitation / defer**). If no finding is confirmed, say so. End with one best next target. No alternative scorecard, speculative backlog or long report.
