# ZAZU EMP — FAILURE CASE REGISTRY

This is the persistent failure-history register for the Failure Case / Loop-Breaking Engine.

## Registry rules

- Stable case IDs are never recycled.
- Active cases remain visible until CLOSED or BLOCKED.
- Rejected hypotheses and failed approaches are retained.
- A repeated fingerprint must reuse the existing case unless materially new evidence establishes a distinct failure.
- Do not record speculation as confirmed root cause.
- Keep sensitive secrets, credentials and personal data out of this registry.

## Case index

| Case ID | Target | Fingerprint | Status | Current hypothesis | Next layer |
|---|---|---|---|---|---|
| — | — | — | No V2 cases registered yet | — | — |

## Case record template

### CASE-ZAZU-XXXX

**Status:** OBSERVED / INVESTIGATING / FORENSICS / CORRECTED / VERIFIED / BLOCKED / CLOSED

**First observed HEAD:**

**Current HEAD:**

**Target/workflow:**

**Verification layer:**

**Expected:**

**Actual:**

**Failure fingerprint:**

**Runtime/data state:**

**F1–F8 classification:**

#### Hypotheses

| ID | Hypothesis | Evidence for | Evidence against | Result | Status |
|---|---|---|---|---|---|
| H1 | | | | | |

#### Experiments

| Attempt | Hypothesis | Action | Evidence/result | Decision |
|---|---|---|---|---|
| 1 | | | | |

**Confirmed root cause:**

**Correction:**

**Regression family:**

**Runtime/adversarial evidence:**

**Remaining uncertainty:**

**Closure evidence:**

**Rejected approaches retained for loop prevention:**

**Next action:**
