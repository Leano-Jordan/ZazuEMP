# Zazu EMP — Commercial Valuation & South African Market Assessment

**Assessment date:** 2026-11-11  
**Repository:** `Leano-Jordan/ZazuEMP`  
**Branch:** `main`  
**Research basis:** repository review + public competitor/pricing/review sources listed below  
**Status:** Strategic estimate, not an independent professional valuation, audited forecast, legal opinion or market-size certification.

## 1. Currency policy — mandatory

**Primary currency for Zazu EMP and Rosscore Labs financial work: South African rand (ZAR / R).**

All budgets, prices, valuations, revenue models, cost models, commercial comparisons, quotations and management reports must be expressed primarily in ZAR. Do not present foreign-currency figures alone. If a foreign price is relevant, show the original currency only as a secondary reference and provide its ZAR equivalent beside it.

Use a dated, cited exchange rate for any conversion. State that the conversion is indicative and may differ from the amount charged by a bank, card provider or supplier. Do not silently reuse stale rates. The user has explicitly said that rand is the currency they understand; rand-first presentation is a permanent communication requirement.

Reference rates used for this assessment (11 November 2026):
- USD 1 = approximately R16.5010.
- EUR 1 = approximately R18.4307.

Sources: https://centreforenergy.org/exchange-rates/usd-to-zar (USD); https://www.ecb.europa.eu/stats/policy_and_exchange_rates/euro_reference_exchange_rates/html/eurofxref-graph-zar.pl.html (EUR).

## 2. Executive conclusion

Zazu is a substantial, differentiated event-business operations product in advanced development, but it is not yet a commercially validated business. The opportunity is credible; customer demand, willingness to pay, retention and acquisition cost have not yet been demonstrated.

The strongest positioning is:

**One operational workspace for small event-service businesses that quote, prepare, deliver and reconcile work across multiple services** — for example catering, equipment hire, sound/DJ, decor, baking, photography and combinations.

Do not position Zazu as merely another event calendar, ticketing system, generic CRM or accounting package. Its product story should centre on the event/job as the operational record linking customer, requirements, quote, preparation, purchasing, resources, costs, documents, payments and completion.

### Director recommendation
Proceed with a tightly scoped V1 commercial pilot after release gates are closed. Do not expand feature scope to chase competitors. Validate the target segment and pricing with real operators before making public revenue or market-share claims.

## 3. Product and release evidence boundary

Repository records reviewed:
- `README.md`
- `memory.md`
- `.ai/engineering/00_DIRECTOR_ENGINE.md`
- `.ai/engineering/STATE.md`
- `.ai/engineering/READINESS_REGISTER.md`
- `docs/ZAZU_V1_SCORECARD_2026-11-04.md`
- `docs/V1_RELEASE_CHECKLIST.md`

The latest release ledger records the 69/100 evidence-maturity score and a NO-GO release position. The 11 November record reports automated Laravel, offline browser, browser smoke, quality, security, static analysis and populated upgrade/rollback checks as passing on the recorded exact source head. This is meaningful engineering evidence, not production certification.

Outstanding operational/commercial gates include:
- successful real registration and workspace creation on the deployed preview;
- replacement of disposable preview persistence with a supported persistent database and durable private-media storage;
- real backup/restore and deployment rollback exercises against the selected target;
- physical phone/tablet offline acceptance on a stable secure origin;
- trusted-device/lost-device operational acceptance;
- production licence issuer/key custody and application-wide entitlement enforcement;
- privacy, retention, incident response and legal/brand clearance.

The Render preview has been documented as disposable SQLite under `/tmp`; it must not be used for customer data. No customer launch or paid pilot should rely on it.

## 4. Competitor landscape

### Direct and adjacent competitors

| Competitor | South African relevance | Published pricing / ZAR equivalent | Competitive strength | Zazu response |
|---|---|---:|---|---|
| Rentflo | Direct local rental-management competitor; testimonials include South African event-hire operators | Quote-based; no public fixed price found | Local rental workflows, real-time availability, bookings, webstore, repairs, scanning and ZAR reporting | Do not claim to beat it at pure rental depth. Win with cross-service event/job operations and simpler connected commercial workflows. |
| EventSync | Direct local venue/catering/event operations competitor | Starter US$59 ≈ R974/mo; Professional US$139 ≈ R2,294/mo; Business US$279 ≈ R4,604/mo, before VAT | Events, customers, seating, catalogue/packages, quoting, invoicing, stock, POS, finance, staff and venue operations | Strong competitor. Zazu must prove its multi-service SME workflow is easier and more appropriate for operators who are not venue groups. |
| Rentman | International event/media production and equipment competitor, used by South African businesses | Platform €39 ≈ R719/mo; add-ons include Inventory €14 ≈ R258, €19 ≈ R350; Crew €14–€24 ≈ R258–R442 per Power User/month | Mature equipment, crew, project, quoting, scheduling, barcode/QR and operational tooling | Do not compete feature-for-feature in production logistics. Emphasise lower operational complexity and the broader event-service lifecycle. |
| Booqable | International rental software alternative | Public listings show from US$27 ≈ R446/mo; confirm current vendor plan before purchase comparison | Rental inventory, online bookings and payments | Adjacent competitor, not a full Zazu equivalent. |
| Xero / Sage / OxyAccounting | Accounting substitutes and price anchors, not direct event-operation competitors | Xero regular plans shown at R450, R795 and R1,095/mo; OxyAccounting advertises R149/mo | Accounting, invoicing, purchasing, inventory and tax/bookkeeping workflows | Do not sell Zazu as accounting replacement. Integrations/exports and event-level operational profitability matter. |

Foreign-price conversions use the reference rates in Section 1. Rounded figures are indicative, not transaction quotes. Pricing pages may change.

### Competitor evidence and customer feedback

**Rentflo:** Its official site describes real-time inventory, instant bookings, repairs/location tracking, product scanning, webstore and ZAR reporting. It publishes customer testimonials from Scuttle and Signature Event Hire; one testimonial describes order management and stock tracking in one place and onboarding within ten days. These are vendor-published testimonials, not independently audited outcomes.  
Source: https://rentflo.co.za/

**EventSync:** Its public pricing page lists the three plans above. Its catering page specifically markets reusable packages/per-head pricing, enquiry-to-quote workflow, stock/load-out checklists and invoicing/payment links. It also says its POS can continue through a dead connection. This means Zazu must not claim offline operation as a unique differentiator without a precise side-by-side capability test.  
Sources: https://eventsync.co.za/pricing and https://eventsync.co.za/solutions/catering

**Rentman:** Its official pricing is modular and per Power User for specialist products. Current public pricing includes a €39/month platform and separate Inventory, Crew and Organize products. Review aggregators show a strong 4.6/5 rating across roughly 240 reviews. Positive reviews mention equipment tracking, conflict warnings, QR scanning and time saved; reported negatives include learning curve, mobile-app limitations for some workflows and fit/pricing concerns for smaller or unusual use cases. Reviews are self-selected and should be treated as directional, not representative market research.  
Sources: https://rentman.io/pricing ; https://www.capterra.com/p/144616/Rentman/reviews/ ; https://www.softwareadvice.com/hr/rentman-profile/reviews/

**Local price anchors:** South African accounting products span very low entry points (for example OxyAccounting at R149/month) through Xero at R450–R1,095/month on regular published pricing. These products are not direct substitutes for Zazu, but they show that SMEs can compare subscriptions against inexpensive accounting tools. Zazu must justify its price through operational value, not feature count.  
Sources: https://oxyaccounting.co.za/ ; https://www.xero.com/za/pricing-plans/

### What the competitor research actually says

1. Rental inventory and booking are already served by capable local software. Rentflo is a serious direct competitor.
2. EventSync is a particularly close competitor for catering and venue workflows, including package quoting, stock and offline POS.
3. Rentman has strong proof of product-market fit in event production, but its modular/per-user model and complexity may not suit every small multi-service operator.
4. Accounting software is cheap relative to a specialised operations platform. Zazu should not compete on bookkeeping alone.
5. The opportunity is not “no competition”. It is serving the overlap between small event-service businesses whose work crosses catering, hire, equipment, preparation, suppliers and event-level profitability.

## 5. Target customer and market entry

### Recommended initial ideal customer profile

Start with owner-operated and small-team businesses in Gauteng that:
- handle recurring events/jobs rather than one-off personal event planning;
- offer at least two service categories, or combine catering with equipment hire;
- prepare quotes and invoices repeatedly;
- manage stock, hired assets, suppliers or job-specific purchases;
- currently coordinate work through spreadsheets, WhatsApp, paper records or disconnected apps;
- experience avoidable errors in availability, preparation, costs, deposits or payment follow-up.

Priority prospect segments:
1. Catering + equipment/chair/tent hire combinations.
2. Event hire companies with delivery, collection and damage/return accountability.
3. DJ/sound/lighting operators who also hire equipment or coordinate crews.
4. Decor, baking and photography businesses that bundle multiple services.
5. Funeral service operators only as a later vertical validation exercise, not the initial message.

### Market sizing discipline

No sufficiently reliable public count of South African small multi-service event operators was established in this research. Broad third-party event-management software market forecasts use definitions too wide to serve as Zazu's addressable market. Do not quote a top-down TAM figure as if it represents reachable Zazu customers.

Build a bottom-up market list:
- identify real businesses in Pretoria, Johannesburg and nearby Gauteng corridors;
- classify by service mix, team size, visible booking activity and operational complexity;
- interview operators and qualify software pain;
- record current tool, booking volume, quote volume, stock complexity, decision-maker and acceptable monthly price.

The first meaningful market metric is not a national market-size estimate. It is the number of reachable, qualified operators and the percentage willing to pay after a working demonstration.

## 6. Recommended launch pricing — hypothesis to validate

All prices below are proposed ZAR monthly prices, not current Zazu prices or proven willingness to pay. They should be tested with prospects before being committed to the public pricing page.

| Plan | Proposed price | Intended customer | Commercial boundary |
|---|---:|---|---|
| Starter | R499/month | Sole operator or very small team | Core customer/job/quote workflow and a modest user allowance |
| Team | R899/month | Active multi-service operator | More users, operational coordination and connected purchasing/resources |
| Business | R1,499/month | Established operator with staff and higher workflow complexity | Higher user/storage allowances, stronger reporting and priority support |

Additional commercial hypotheses:
- Optional onboarding/data setup: R1,500–R3,500 once-off, only where real migration/training work is required and clearly scoped.
- Annual payment: test a 10% discount only after monthly retention is understood.
- Early adopter offer: first 10–20 qualified pilot businesses may receive a fixed founding price in exchange for structured feedback and permission to use a testimonial only if they independently agree.
- Use a 14-day guided trial or a scheduled demonstration. Avoid a free-forever tier that creates support obligations without revenue.
- Do not charge separately for the core offline capability. Offline continuity is part of the product promise, within its explicitly supported workflows.
- Keep all plan boundaries tied to implemented and verified features. Do not advertise functionality that is only planned.

### Pricing judgement

The proposed R499–R1,499 range positions Zazu below EventSync's published converted tiers while remaining above commodity accounting tools at the entry point. This is a deliberate test position, not proof that Zazu is worth more or better. Rentflo's pricing is quote-based, so a true price comparison requires a sales quote.

Do not underprice support. If onboarding and ongoing support consume substantial founder time, a R499 account can become unprofitable quickly. Track support minutes per account, infrastructure cost per account and gross margin from the first pilot.

## 7. Three-year illustrative revenue model

This is a planning scenario, not a forecast. It assumes a gradual customer ramp during each year, no churn, no failed payments, and the average monthly revenue per account (ARPA) shown. It excludes VAT, refunds, payment fees, hosting, support labour, sales costs, tax and founder salary.

| Year | Paying accounts at year end | Average monthly revenue/account | Approx. average accounts during year | Illustrative subscription revenue for year | Year-end MRR | Year-end ARR run-rate |
|---|---:|---:|---:|---:|---:|---:|
| Year 1 | 20 | R699 | 10 | R83,880 | R13,980 | R167,760 |
| Year 2 | 60 | R749 | 40 | R359,520 | R44,940 | R539,280 |
| Year 3 | 150 | R849 | 105 | R1,069,740 | R127,350 | R1,528,200 |

**Three-year cumulative illustrative subscription revenue: R1,513,140.**

Year-end ARR is the monthly recurring revenue at year-end multiplied by 12; it is not revenue already earned. Actual revenue will depend on acquisition timing, churn, discounting, non-payment and plan mix.

### Year-three sensitivity

| Scenario | Paying accounts | Average monthly revenue/account | Year-end MRR | Year-end ARR |
|---|---:|---:|---:|---:|
| Conservative | 60 | R699 | R41,940 | R503,280 |
| Base | 150 | R799 | R119,850 | R1,438,200 |
| Upside | 300 | R899 | R269,700 | R3,236,400 |

These are scenario boundaries, not probabilities. The 300-account case requires a repeatable acquisition and support model; it should not be treated as a solo-founder default.

## 8. Valuation

### A. Current software/IP asset — as-is

**Indicative negotiation range: R100,000–R250,000.**

This is a cautious strategic estimate for the transferable codebase, original workflows, architecture, documentation and engineering work, assuming ownership and third-party licence obligations are clean. It is not an appraisal, guaranteed sale price, or amount that can be raised as cash today.

Why the range is discounted:
- no verified paying customer base or recurring revenue;
- V1 release remains NO-GO;
- persistent production storage and operational restore/rollback are not yet proven;
- physical offline acceptance and licensing enforcement remain open;
- brand/legal and privacy sign-off remain incomplete;
- the solo-founder knowledge dependency is high.

This is a revision of the earlier R200,000–R500,000 conversational estimate. The lower range better reflects the actual release and commercial evidence now documented.

### B. Value after commercial validation

For a small founder-led SaaS, revenue multiples are not automatic. Buyers examine sustainable profit, retention, growth, customer concentration, founder dependency and risk. Acquire.com's 2026 published guidance describes roughly 1x–3x annual revenue or 3x–5x annual profit as directional ranges for many smaller founder-led SaaS businesses; its reported median confirmed SaaS profit multiple was 3.9x in both 2024 and 2025. These are international marketplace benchmarks, not South African Zazu transaction comparables.

Source: https://blog.acquire.com/saas-valuation-multiples/ and https://blog.acquire.com/acquire-com-biannual-acquisition-multiples-report-jan-2026/

Illustrative ARR-based range using 1x–3x ARR only after revenue is recurring, verified and transferable:

| Paying accounts / ARPA | ARR | Illustrative 1x–3x ARR range |
|---|---:|---:|
| 25 accounts × R799/month | R239,700 | R239,700–R719,100 |
| 50 accounts × R899/month | R539,400 | R539,400–R1,618,200 |
| 150 accounts × R849/month | R1,528,200 | R1,528,200–R4,584,600 |

These ranges are not a current Zazu valuation. A buyer may use profit multiples instead, or discount the business heavily if churn is high, customers are concentrated, the founder is essential to support, or production obligations are not independently operable.

### C. What creates valuation, not just activity

The strongest valuation drivers are:
- 12+ months of verified recurring revenue;
- low and measured monthly customer churn;
- repeatable acquisition with known cost per customer;
- healthy gross margin after hosting, payment fees and support;
- low customer concentration;
- documented onboarding and support procedures;
- reliable backup, restore, deployment and incident response;
- production licensing and clear IP ownership;
- reduced dependence on the founder for daily customer operations.

## 9. Commercial validation plan

Do not start by buying ads. Start by proving that the product solves an expensive recurring problem.

### Phase 1 — 10 discovery interviews
Interview 10 real operators across the first two target segments. Ask them to walk through their last completed job: enquiry, quote, deposit, resource availability, purchases, delivery/preparation, actual costs, final invoice and follow-up. Ask what failed, what they currently use, how often the problem happens and what it costs them. Do not lead them toward a preferred answer.

### Phase 2 — 5 workflow demonstrations
Use each operator's actual workflow (with dummy data) to demonstrate Zazu. Record where they hesitate, where the system does not match their process, and which capability creates the strongest value.

### Phase 3 — 3 paid pilot customers
Seek three businesses willing to pay a founding price after the production and recovery gates are closed. A verbal compliment or free trial signup is not validation. Payment and repeat use are stronger evidence.

### Phase 4 — 90-day retention test
Measure:
- weekly active businesses;
- jobs/events created and completed;
- quotes sent and accepted;
- invoices and payments recorded;
- operational users invited;
- offline queue/reconnect failures;
- support time per account;
- churn, failed payments and reasons for cancellation.

### Go/no-go commercial thresholds
- 3 paid pilot customers before broad promotion.
- At least 2 of 3 using Zazu weekly after 60 days.
- No unresolved critical data-loss or tenant-isolation defect.
- A successful customer-environment restore and physical offline acceptance.
- Support burden low enough to serve the next 10 accounts without custom development for every customer.

## 10. Principal risks and countermeasures

| Risk | Consequence | Countermeasure |
|---|---|---|
| Trying to serve every event vertical immediately | Confusing onboarding and bloated scope | Start with catering + equipment hire combinations |
| Competing on feature count against Rentflo/EventSync/Rentman | Losing to established specialists | Sell a simpler end-to-end workflow for small multi-service operators |
| Underpricing | Support burden exceeds subscription revenue | Measure support time and gross margin from first pilot |
| Claiming full offline parity | Loss of customer trust and data risk | Publish only verified offline workflows and limitations |
| Treating disposable Render preview as production | Data loss | Persistent database/media and recovery drills before customer data |
| Founder-only operations | Buyer discount and service bottleneck | Document onboarding, support, deployment and recovery |
| Weak customer acquisition | Product remains technically impressive but commercially idle | Build a qualified Gauteng prospect list and track paid conversion |
| Unclear ownership/brand/legal position | Reduced transferability and launch risk | Close IP, third-party licence, POPIA, terms and trademark review |

## 11. Director decision

**Recommendation: continue to V1 pilot readiness; do not expand the product scope.**

The next commercial work should be:
1. close deployment persistence and real workspace creation;
2. finish physical offline and recovery acceptance;
3. select one launch segment (recommended: catering + equipment hire combinations);
4. conduct 10 operator interviews and 5 demonstrations;
5. test R499 / R899 / R1,499 pricing;
6. secure 3 paid pilots;
7. measure retention and support economics before increasing acquisition spend.

Zazu's defensible opportunity is the connected event/job operating record for small businesses delivering multiple services. The product should win through operational simplicity and reliable execution, not through claims that it has more features than every competitor.

## 12. Sources

- Zazu repository and current project state: https://github.com/Leano-Jordan/ZazuEMP
- Rentflo official site: https://rentflo.co.za/
- EventSync pricing: https://eventsync.co.za/pricing
- EventSync catering workflow: https://eventsync.co.za/solutions/catering
- Rentman pricing: https://rentman.io/pricing
- Rentman reviews: https://www.capterra.com/p/144616/Rentman/reviews/
- Rentman user review summary: https://www.softwareadvice.com/hr/rentman-profile/reviews/
- Xero South Africa pricing: https://www.xero.com/za/pricing-plans/
- OxyAccounting pricing: https://oxyaccounting.co.za/
- USD/ZAR reference: https://centreforenergy.org/exchange-rates/usd-to-zar
- EUR/ZAR reference: https://www.ecb.europa.eu/stats/policy_and_exchange_rates/euro_reference_exchange_rates/html/eurofxref-graph-zar.pl.html
- Acquire.com SaaS valuation guidance: https://blog.acquire.com/saas-valuation-multiples/
- Acquire.com acquisition multiples report: https://blog.acquire.com/acquire-com-biannual-acquisition-multiples-report-jan-2026/
