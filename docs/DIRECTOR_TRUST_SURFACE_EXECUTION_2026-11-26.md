# Director Execution Record — Trust Surface and Product Credibility

**Date:** 2026-11-26  
**Authority:** Founder-directed execution; Founder retains final authority  
**Repository:** `Leano-Jordan/ZazuEMP`  
**Write target:** `main` only  
**Status:** ACCEPTED DIRECTION / IMPLEMENTATION IN PROGRESS  
**Release posture:** Private, non-commercial pilot only when explicitly authorised; no advertising, charging, public beta or public pricing.

## Objective

Improve the credibility Zazu presents to a prospective pilot operator without destabilising the application or implying release readiness. The engineering V1 score and the public-credibility score are separate measures.

**Founder-provided credibility baseline:** 22/40 (55%), judgement-based:
- Product depth 4/5
- Engineering/security discipline 4/5
- Visual consistency 3/5
- Positioning/focus 3/5
- South African relevance 3/5
- Accessibility baseline 3/5
- Public trust surface 1/5
- Outside proof 1/5

Retain the existing formal evidence-maturity score of 71/100 separately. Do not combine, average or silently replace either score. Reassess only with dated evidence.

## Accepted work order

1. **Public trust surface:** accurate Privacy, Terms, Contact, About, Security and What's New destinations. Status/availability information is deferred until real monitoring exists.
2. **Demonstration proof:** use a real, source-derived Zazu workflow capture/recording for enquiry/customer → event/job → quote → acceptance/deposit/payment → actual cost → profit. Never fabricate customer records, outcomes, testimonials or screenshots.
3. **Private pilot evidence:** one named pilot participant only after explicit Founder authorisation and privacy/data-handling readiness. Synthetic data remains the default until controls are confirmed. No commercial commitment or payment request.
4. **Visual polish:** a bounded CSS-level refinement batch after critical release gates; no broad redesign and no interruption of active security/recovery work.

## Positioning rules

- Zazu remains an **event management platform**. Event/job-centred operations are the product category; catering, equipment hire and other services are configurable business capabilities, not the product boundary.
- "From enquiry to profit" is accepted as a concise workflow/value-story direction, not evidence that every stage is fully accepted in production. Demonstrate only verified workflow steps.
- Preserve the current V1 mandate's working promise, **"Run every job. Control every cost. Keep working."**, until the Founder explicitly changes the canonical product mandate. Use the enquiry-to-profit journey as supporting explanation, not as permission to reclassify Zazu as a catering product.
- South African relevance may be explained through verified ZAR/VAT handling, local operating context and tested offline capabilities. Do not imply native WhatsApp integration unless implemented and verified.
- Do not lead with module count. Do not claim competitor superiority without evidence.
- No public pricing, ads, charging, scarcity, customer logos, testimonials, service-level promises or unsupported availability claims.

## Legal, identity and publication constraints

The authoritative Founder record is `FOUNDER_LEGAL_BRAND_DECISIONS_2026-11-26.md`. It overrides older commercial drafts and chat summaries.

- The Founder personally owns current Zazu IP and is the current individual/solo operator.
- Rosscore Labs is a future identity, not yet registered and not the current contracting provider or IP owner.
- ZAZU is not approved as the final product name and has unresolved high-risk name-clearance evidence. Do not claim clearance, registration or safe use.
- No production hosting, external processor, analytics, payment, messaging, monitoring or support provider is currently provisioned/approved.
- The current Outlook address is provisional human contact, not a dedicated Zazu privacy mailbox or configured application support integration. Verify monitoring before public use.
- Information Officer registration is not complete. Never claim registration.
- Privacy notice and terms remain pre-launch drafts. Do not publish them as effective legal notices or contracts until placeholders, actual deployment/provider details, request channels and owner/legal review are resolved.
- No uptime/status page or SLA claim without real monitoring and an approved operational target.
- Do not expose personal residential/contact details in public repository content.

## Implementation and acceptance rules

- First inspect existing route/view/test architecture and shared design tokens. Reuse the existing Laravel application and visual system; do not introduce a second site, framework or public CMS.
- Add public GET-only routes with no authentication requirement, no customer data access and no new external integrations.
- Use a shared accessible public-page layout and consistent navigation/footer links. Preserve existing authenticated landing behaviour.
- Security page must be a factual summary derived from current `SECURITY.md` and verified controls. It must distinguish implemented controls from limitations and reporting procedure; no certification or "secure" guarantee.
- What's New entries must be dated and traceable to actual commits/releases. Do not label unreleased work as shipped.
- About/Contact must accurately state current ownership/operator identity and provisional contact status; do not represent Rosscore Labs as incorporated.
- Privacy/Terms routes must remain non-effective review copies until publication gates close. No fake effective date.
- Add route integrity, unauthenticated access, content accuracy/no-placeholder, link integrity and accessibility regression coverage. Verify rendered desktop/mobile pages where browser tooling is available; otherwise record visual verification as UNVERIFIED.
- Do not weaken tests or claim a CI pass from a commit with no reported checks.

## Deferred / prohibited in this workstream

- No broad app redesign, wallpaper/CSS sweep or token replacement before critical release gates.
- No pricing/tier matrix publication while entitlement conflicts remain unresolved.
- No public status page until monitoring is deployed and tested.
- No use of synthetic demo imagery labelled as real customer/event proof.
- No real pilot personal information until the privacy, Information Officer and operator/deployment boundaries are addressed.
- No branch creation or writes outside `main`.

## Current engineering dependency

The adversarial authentication audit remains OPEN. Latest source commit at this record is `bf8dc41aa7ad4837a3c19d54bbd9d5158e4c53c7` (retains the auth-context BroadcastChannel listener across back-forward-cache restores). The latest commit reports no combined status checks through the connected GitHub status endpoint; do not infer a pass. Continue the active security investigation and the remaining V1 gates in `.ai/engineering/STATE.md`.

## Next execution slice

Implement the smallest safe trust-surface foundation in the existing Laravel public surface, starting with factual About, Security and What's New content plus a real Contact route. Keep Privacy and Terms as clearly labelled review copies until publication gates are met. Update this record and `.ai/engineering/STATE.md` with each verified implementation/acceptance change.
