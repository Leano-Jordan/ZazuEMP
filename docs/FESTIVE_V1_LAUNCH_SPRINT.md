# Zazu EMP — Festive V1 Launch Sprint

**Date:** 2026-10-05  
**Repository:** Leano-Jordan/ZazuEMP  
**Branch:** main  
**Objective:** get Zazu into a commercially usable V1 for the 2026 festive event season without widening scope.

## Launch posture

The priority has changed from broad engineering completion to **commercial launchability**.

Zazu does not need every possible feature before the festive season. It needs to reliably handle the money-making event cycle:

Customer → Event → Quote → Acceptance → Invoice → Deposit → Planning/Work → Purchasing → Receiving → Costs → Final Payment → Completion.

Anything outside that path is secondary unless it blocks reliability, security, recovery or actual use.

## Sprint order

### S1 — Commercial proof
- [x] Populated quote → acceptance → invoice → deposit → final payment proof harness.
- [x] Purchase order → receiving → inventory movement → event-cost proof harness.
- [x] Receipt inventory movement now records an attributable audit entry.
- [ ] Confirm current green result on the release candidate.
- [ ] Promote commercial reconciliation to proven release evidence.

### S2 — Full populated operational chain
- [ ] Customer → event → requirements/work.
- [ ] Work → resources/purchasing.
- [ ] Purchasing → receiving → inventory/costs.
- [ ] Quote/commercial state → finance.
- [ ] Completion guards against unresolved operational/financial state.
- [ ] One populated end-to-end proof test covering the connected chain.

### S3 — Field usability
- [ ] Desktop critical workflow pass.
- [ ] Phone critical workflow pass.
- [ ] Tablet critical workflow pass.
- [ ] Calendar/event planning pass.
- [ ] Quote/customer-facing presentation pass.
- [ ] Finance/payment pass.
- [ ] Purchasing/receiving pass.
- [ ] Documents/media pass.
- [ ] Fix only verified usability defects; no visual redesign.

### S4 — Release safety
- [ ] Final authorization/business-isolation challenge.
- [ ] Backup and restore drill.
- [ ] Private-media recovery verification.
- [ ] Populated database upgrade.
- [ ] Rollback exercise.
- [ ] Production configuration/security review.
- [ ] Legal/privacy/operator release closure.
- [ ] Brand clearance disposition.

### S5 — Release candidate
- [ ] Current-head Laravel/quality/static evidence green.
- [ ] Current-head browser evidence green.
- [ ] Populated workflow evidence green.
- [ ] Recovery/upgrade/rollback evidence green.
- [ ] Physical-device acceptance recorded.
- [ ] Final Director certification.

## Scope lock

Do **not** spend the festive sprint on:
- autonomous AI;
- predictive analytics;
- enterprise integrations;
- enterprise SSO;
- warehouse-scale inventory;
- broad collaboration features;
- cosmetic redesigns;
- speculative abstractions.

## Operating rule

Every new defect is classified immediately:

1. **Revenue blocker** — fix now.
2. **Workflow blocker** — fix now.
3. **Security/data-loss risk** — fix now.
4. **Field usability blocker** — fix now.
5. **Polish** — batch after the critical path is stable.
6. **Nice-to-have** — defer.

**Goal:** make Zazu dependable enough that a real event business can use it for the festive rush, then harden from real usage rather than building a larger theoretical product.
