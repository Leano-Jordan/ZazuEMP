# Zazu EMP 2027 Hardening & Commercial Readiness Scorecard

## Purpose

This is the working gate for focused development. It deliberately separates:

- IMPLEMENTED = code exists
- TESTED = automated test exists
- VERIFIED = test/runtime evidence has actually run
- PROVEN = repeated production or realistic drill evidence exists
- UNVERIFIED = not enough evidence yet

### Evidence maturity

0. Not started
1. Designed
2. Implemented
3. Automated
4. Verified in CI/runtime
5. Proven through production evidence or a realistic recovery/scale drill

A high code score without runtime evidence does not count as production readiness.

## Current first-sweep status

| Area | Level | Gate to advance |
|---|---:|---|
| Browser/E2E core | 3 | All critical journeys pass in CI |
| Current-HEAD CI | 2 | Current main commit has green workflow evidence |
| Concurrency | 2 | Parallel-operation tests for critical mutations |
| Parent/child invariants | 3 | Full cross-business and mismatch matrix |
| Financial reconciliation | 3 | End-to-end ledger/reconciliation suite |
| Backup/restore | 3 | Real install restore drill with media/data verification |
| Production observability | 2 | Metrics, alerts, retention and incident runbook |
| SaaS entitlements | 1 | Policy + model + enforcement + downgrade/expiry tests |
| Operational documentation | 2 | Deploy/rollback/backup/incident/migration runbooks |
| Large-data performance | 1 | Seeded scale test with agreed thresholds |
| Production-proven reliability | 0 | Soak/real-world evidence |
| Release readiness | 1 | All release gates green |
| SaaS billing | 1 | Gateway + subscription lifecycle + reconciliation |
| Disaster recovery | 1 | Measured RPO/RTO and successful restore drills |
| Browser regression coverage | 2 | Critical workflow matrix across desktop/mobile |
| Zazu Helper | 3 | Browser-verified default/on/off persistence |

## Browser/E2E gate

Critical journeys:
- registration
- catalogue setup
- business setup
- dashboard landing
- customer creation
- job creation/edit/view
- requirements
- quote creation/revision/status
- purchasing
- receiving/inventory
- invoice creation
- payments
- expenses
- settings
- business switching
- logout/session expiry

Required evidence:
- Chromium CI pass
- desktop viewport
- mobile viewport
- validation/error states
- permission-denied states
- stale/session-expiry states
- no console-breaking JS errors on critical paths

## CI evidence gate

- [ ] Laravel tests run on current main
- [ ] Browser suite runs on current main
- [ ] Frontend build passes
- [ ] Fresh database migration passes
- [ ] No failed or missing required checks
- [ ] Failed run artifacts are retained
- [ ] CI uses pinned/reproducible dependencies where practical

Current evidence note: the repository connector currently reports no workflow runs/status checks for HEAD 80586f5. CI is therefore not declared green.

## Concurrency gate

Critical operations:
- duplicate invoice submission
- duplicate payment submission
- duplicate expense submission
- duplicate purchase-order submission
- duplicate inventory movement
- purchase-order receiving
- quote status transitions
- quote revision updates
- settings saves
- business switching during mutation

Required:
- parallel request test
- unique database guard
- row/business locking where needed
- retry-safe outcome
- no double posting
- no impossible intermediate state

## Parent/child invariant gate

Every relationship must obey business ownership and lifecycle rules.

Examples:
- Event.customer must belong to Event.business
- Event contacts must belong to the same customer/business
- Quote.event must belong to current business
- Quote version must belong to its quote
- Quote version lines must match current requirements when required
- Requirement.capability must belong to the event business
- Purchase order supplier/capabilities must belong to the PO business
- Purchase order lines must belong to their PO
- Inventory movement item/event/PO references must remain business-compatible
- Invoice quote/event must agree when both are supplied
- Payment invoice must belong to payment business
- Finance expense supplier/event must belong to the same business

## Financial reconciliation gate

Reconcile these paths:
- quote totals
- invoice subtotal/tax/total
- invoice payments
- invoice balance
- payment status
- finance expense totals
- event cost projected vs actual
- purchase-order line totals vs order total
- stock receipt value vs purchase costs where applicable
- currency consistency
- decimal precision and rounding

Required evidence:
- normal path
- duplicate request
- partial payment
- full payment
- rejected overpayment
- cancellation/void paths where supported
- concurrent update path

## Backup/restore gate

- [ ] SQLite backup tested
- [ ] MySQL backup tested
- [ ] Private media restored
- [ ] Representative customer restored
- [ ] Representative job restored
- [ ] Representative quote restored
- [ ] Invoice/payment restored
- [ ] Inventory restored
- [ ] Archive safety validation tested
- [ ] Restore drill measured
- [ ] Backup kept outside source data tree
- [ ] Off-site copy strategy documented
- [ ] Backup retention documented

## Production observability gate

- [ ] Request IDs
- [ ] Structured application errors
- [ ] Health endpoint
- [ ] Application logs
- [ ] Queue/failure visibility
- [ ] Database failure visibility
- [ ] Slow request/query visibility
- [ ] Backup failure alerts
- [ ] Disk/storage alerts
- [ ] Error-rate alerts
- [ ] Incident response runbook
- [ ] Sensitive data excluded from logs

## SaaS entitlement gate

Define before implementation:
- account state
- plan
- feature entitlement
- usage limits
- grace periods
- trial state
- payment failure state
- suspension state
- cancellation/downgrade state
- owner/admin authority
- audit trail

Entitlement checks must be server-side and must never depend on a UI-only flag.

## Operational documentation gate

Required runbooks:
- fresh install
- deploy
- rollback
- migration
- backup
- restore
- incident response
- account lockout/session issue
- queue failure
- storage failure
- database failure
- release checklist
- security response

## Large-data performance gate

Test realistic data volumes:
- 1,000 customers
- 10,000 customers
- 50,000+ transactional records
- large event requirement sets
- large invoice/payment histories
- large inventory movement histories

Measure:
- p50
- p95
- p99
- query count
- memory
- pagination response
- dashboard response
- export/report response

Agree thresholds before calling scale readiness proven.

## Production reliability gate

Do not call Zazu production-proven from code inspection alone.

Evidence should include:
- monitored production period
- incident history
- failed request rate
- recovery evidence
- rollback evidence
- backup success history
- restore drill history
- upgrade/migration evidence

## Release gate

A release is ready only when:
- automated tests are green
- browser critical paths are green
- database migrations pass from clean and representative existing states
- backup/restore drill passes
- security boundaries pass
- critical concurrency tests pass
- observability is active
- rollback is documented
- known exceptions are explicitly recorded

## Zazu Helper acceptance gate

Required behavior:
- enabled by default
- non-blocking
- contextual to current workflow
- dismissible
- can be turned off
- can be turned back on
- persists the user's preference
- remembers that a route was already introduced
- keyboard accessible
- mobile friendly
- reduced-motion friendly
- never contains fake functionality or invented routes
