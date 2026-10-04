# Director — Zazu EMP Full UI/UX + Commercial Audit
Date: 2026-10-04
Branch: main

## Executive disposition
**Current state: strong pre-release product candidate, not release-certified.**

Zazu has moved beyond an MVP-shaped CRUD demo. The repository contains a broad event/catering operations system with authentication, onboarding, customers, work/jobs, requirements, preparation, quotes, finance, purchasing, inventory, assets, calendar, reporting, settings/compliance, auditability, backup/restore and offline-first server-side foundations.

The biggest remaining risks are **proof and product completeness at the physical-device/disconnected-phone boundary**, not absence of a basic application.

## UI/UX sweep executed

### Dark mode
- Base form CSS no longer hard-codes white form surfaces.
- Added a base theme contract so form/background colours are theme tokens rather than dependent on a later visual override.
- Existing dark form selectors remain explicitly dark and readable.
- Dark input, select, textarea, file-input, placeholder, label and option states were rechecked.
- Added an automated Playwright regression test that fails if primary dark-mode form controls render white/light.

### CSS authority / maintainability
The previous final visual stylesheet had become a second styling system layered over the base system:
- ~257 KB
- ~1,561 CSS blocks
- 74 !important declarations
- substantial repeated selectors/rules.

The stylesheet was deduplicated by retaining the latest rule for repeated selectors. Result:
- ~213 KB
- ~1,263 blocks
- same 74 explicit !important declarations, now concentrated in deliberate theme/interaction rules rather than multiplied copies.

This is a reduction, not a claim that the visual CSS is perfect. The next architectural cleanup should eventually move toward one canonical component/theme layer rather than continuing to append sweep files.

### Shared page visual
- Shared command band now uses the configured business dashboard artwork.
- Calendar hero receives the same visual treatment.
- Dark navigation rail and section tabs were strengthened.
- Secondary text was darkened in light mode.
- Dark-mode text/controls were strengthened globally.

### Calendar
Holiday data now propagates into 3-month and year calendar views. Holiday category dots remain visible without requiring the user to focus/open a month.

## Build/release finding
The repository generates Vite assets during CI/build rather than storing every generated asset in Git. The workflow already runs npm install, Vite build, manifest verification, Laravel tests and configuration validation. The source CSS is therefore the authority; stale generated browser assets must be refreshed after this cycle.

## Repository audit
Current repository inventory:
- 155 Blade views
- 35 controllers
- 24 services
- 67 migrations
- 48 feature-test files
- 6 unit-test files
- 3 E2E suites
- 17 JavaScript files
- 14 CSS files.

Static hygiene checks found:
- no dump()
- no var_dump()
- no console.log()
- no browser alert()
- no NotImplemented markers
- TODO/FIXME references are confined to release-roadmap documentation.

## Current release evidence
Current engineering state records:
- 242 Laravel tests / 1,381 assertions passed.
- 25 Playwright tests passed, with 2 intentional registration-flow skips.
- Desktop, Pixel 7 and tablet emulation exercised.
- Populated authorization/isolation challenge passed.
- Backup/restore and populated upgrade/rollback evidence passed.
- CodeQL, Quality, PHPMD, Psalm Security Scan and browser smoke passed on the recorded current release head.

This is strong evidence, but physical-device acceptance is still outstanding.

## Scorecard
| Domain | Score | Position |
|---|---:|---|
| Correctness | 84/100 | Strong; populated commercial traversal still needs final repeatable proof |
| Architecture | 87/100 | Coherent modular monolith; offline phone-local architecture is not finished |
| Data integrity | 82/100 | Strong transactions/idempotency/isolation foundation; continued reconciliation drills useful |
| Security | 88/100 | Populated isolation challenge passed; not a formal certification |
| Workflow integrity | 78/100 | Major chain exists; final real-user commercial traversal remains |
| UX/accessibility | 78/100 | Major source-level refinement done; physical device acceptance remains |
| Reliability/recovery | 80/100 | Backup/restore and upgrade/rollback evidence now strong |
| Operability | 72/100 | Error/audit/runbook foundation good; operational ownership still needs real-world exercise |
| Deployment/upgrade safety | 82/100 | Current populated upgrade/rollback evidence passed |
| Documentation/compliance | 72/100 | Release documentation exists; legal/licence/brand operational closure remains |
| Release evidence | 82/100 | Current evidence is substantially stronger than the earlier 67/100 snapshot |
| **Overall commercial readiness** | **80/100** | **Strong candidate; not certified** |

### Release blockers still open
**Critical**
1. Final populated commercial workflow traversal: customer → job → quote → acceptance → invoice → payment → completion.
2. Physical phone/tablet acceptance.
3. Fully disconnected phone-local operation and device pairing/bootstrap are not yet implemented/proven.

**High**
4. Legal/privacy operational completion.
5. Dependency/transitive licence notice closure.
6. Zazu brand/trademark clearance.
7. Final UI regression pass on the actual physical phone after the latest CSS/build changes.

## Competitive position
The market confirms that Zazu is solving a real category rather than inventing a fake problem.

EventSync in South Africa explicitly targets caterers/mobile businesses with catalogue, quoting, invoicing, inventory, checklists and finance, and advertises $139/month for its entry venue-management plan. It also has dedicated offline companion apps for point-of-sale and door operations. This is a serious local competitor and proves the category has commercial demand.

Curate targets event businesses with proposals, recipes, production, purchasing, reports, payments and integrations; its published checkout page lists $150/month Startup, $250/month Established and $400/month Unlimited, while its current site has moved to conversation-led pricing.

Better Cater is listed at $83/month Plus and $125/month Premium, covering events, menus, proposals, invoices, production documents and reporting.

The implication is important:

**Zazu does not win because it has more modules. The incumbents already have depth.**

Zazu's potentially interesting wedge is:
- South African small-business focus;
- lower price point;
- simple owner-first operation;
- local-first/offline priority;
- event/job-centric workflow;
- eventual phone-local operation rather than merely responsive web UI;
- business branding and practical operational records.

That wedge is still a **hypothesis until users repeatedly choose it**.

## Pricing and valuation view
### Plausible customer price
A target of roughly **R300–R400/month** is commercially plausible for the South African SME market. Local products such as SizaBill advertise R249 and R449/month tiers, while AdminOS advertises R349/month entry pricing.

Zazu should not try to price like a US enterprise catering platform before proving its value.

### Current product/code replacement value
Published South African 2026 development ranges put multi-module business applications broadly around **R250k–R750k**, with more substantial systems reaching R1m+ depending on depth, security and integrations.

Given Zazu's actual repository scope and engineering controls, a reasonable replacement-cost discussion is approximately:

**R450k–R900k**

That is **not the same thing as what someone would currently pay to acquire Zazu**.

### Realistic current acquisition value
Because Zazu currently lacks proven recurring revenue, customer retention, production usage history and a completed physical/disconnected phone product, a rational buyer would discount heavily for execution and market risk.

My current estimate:

**R150k–R400k as an early software/product asset.**

Potentially higher for a strategic buyer who specifically wants the event/catering vertical and can exploit the existing codebase.

I would not market it today as a R1m+ company. The code may have substantial replacement cost; the business does not yet have the evidence to command that valuation.

### What changes the valuation
At R350/month:
- 50 paying users = R17,500 MRR / R210,000 ARR.
- 100 paying users = R35,000 MRR / R420,000 ARR.
- 250 paying users = R87,500 MRR / R1.05m ARR.

Small private SaaS transactions commonly use much lower multiples than headline public SaaS multiples. A rough 1–3x revenue orientation is more realistic for a tiny, founder-dependent SaaS than applying a public-company multiple.

For example, 100 retained users at R350/month could support roughly **R420k–R1.26m** of revenue-based enterprise value depending on growth, churn, margins, founder dependence and buyer interest. That is a future scenario, not today's valuation.

## Director verdict
**Zazu is worth continuing.**

The product has crossed the point where abandoning it because there are too many screens would be wasteful. The technical foundation is substantial.

But the next money-making milestone is **not another 30 features**.

It is:

**make the current product bulletproof on the phone → put it in front of real operators → get repeated usage → fix what repeatedly hurts → charge.**

The strongest strategic uncertainty is no longer whether this software can be built. It is:

**will a South African event/catering operator choose Zazu often enough to pay for it?**

That question will move the valuation more than another 50,000 lines of code.