# Zazu EMP — Future Feature: Job Feasibility Meter

**Status:** FUTURE / NOT V1 BLOCKING  
**Added:** 2026-10-06  
**Repository:** `Leano-Jordan/ZazuEMP`  
**Branch:** `main`

## Purpose

Give the business owner a quick visual indication of whether a job/event is financially worth taking or is becoming too weak to justify the work.

The meter should be driven primarily by **expected job profit**, not revenue alone.

## Core concept

A job receives a simple feasibility status based on the current financial picture:

| Status | Meaning |
|---|---|
| 🟢 Strong | Healthy expected profit |
| 🟢 Good | Profitable with comfortable margin |
| 🟡 Thin | Profitable, but margin is getting weak |
| 🟠 Risky | Very low expected profit; cost overruns could erase it |
| 🔴 Loss | Expected costs exceed expected revenue |
| ⚪ Unknown | Not enough reliable cost/pricing data to calculate |

The UI should make the result understandable at a glance without requiring the owner to study a financial report.

## Suggested calculation

At minimum:

**Expected Profit = Expected Revenue − Expected Job Costs**

Expected job costs should eventually account for applicable known costs such as:

- purchased goods/materials;
- inventory consumption;
- labour;
- travel/transport;
- equipment/assets;
- subcontractors;
- other direct job expenses.

A future implementation may also calculate:

**Profit Margin % = Expected Profit ÷ Expected Revenue × 100**

The thresholds should be configurable rather than hard-coded forever.

## User experience

The meter could appear on:

- Job/Event overview;
- Quote review;
- Dashboard job cards;
- Financial/job summary.

Example:

**JOB FEASIBILITY**  
🟢 **STRONG**  
Expected profit: **R8,500**  
Margin: **28%**

If the calculation is incomplete, the system must not pretend the job is profitable. Show:

**⚪ UNKNOWN**  
**Add missing costs to calculate feasibility.**

## Important product rule

The meter is an **advisory decision-support feature**, not an automatic decision-maker.

It should help the owner answer:

> “Is this job worth taking at this price?”

It must never silently change a quote, price, purchase, job status or financial record.

## Future enhancements

- Compare current profit against the business's target margin.
- Show which cost category is hurting feasibility.
- Warn when a quote becomes less profitable after purchasing/receiving.
- Recalculate automatically as job costs change.
- Show projected vs actual profitability after job completion.
- Allow owner-configured minimum acceptable margin.
- Add historical profitability comparison across jobs.

## V1 scope decision

**Do not implement for current V1 release certification.**

Keep this documented as a post-V1 commercial intelligence feature. The existing quote, purchasing, cost and finance foundations should provide the required data when this feature is eventually built.

## Acceptance direction for future implementation

A future implementation is only considered complete when:

1. Revenue and relevant direct costs are correctly sourced from authoritative records.
2. Profit and margin calculations are financially accurate.
3. Missing/incomplete data produces **UNKNOWN**, not a misleading positive result.
4. The meter updates when relevant job financial data changes.
5. The status thresholds are documented and test-covered.
6. The UI works on desktop and mobile.
7. The feature does not mutate commercial or financial records merely by calculating the meter.
