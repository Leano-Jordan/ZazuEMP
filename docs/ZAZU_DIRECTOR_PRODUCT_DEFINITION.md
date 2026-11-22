# Zazu Director — Product Definition & Direction Log

> Living product-direction record for the Zazu EMP Director. This document records user-approved product decisions and principles so other Director sessions can continue from the same direction.
>
> **Current baseline checked:** `02299044aec9d80d158640d0f0775791febae659` — 2026-10-03.
>
> **Operating rule:** This is direction-setting, not a rewrite mandate. Protect working functionality. Make evidence-based nudges only. Before proposing a change, inspect the current implementation and compare it with this direction.

## Current product-direction reconciliation — 2026-10-03

The historical WhatsApp discussion is requirements evidence, not current-state authority. Current repository evidence wins where the two differ.

### Business focus
Zazu remains one shared operational product for event-service businesses. Primary niche is a presentation lens, not a separate product or permission boundary.

Current choices:
- Chairs & tents
- Catering & baking
- Sound & DJ
- Mixed event services

The third niche, Sound & DJ, is the concrete first niche-focused reference. Other supported capabilities remain available through the common workspace.

### Progressive disclosure
Primary niche and Basic / Intermediate / Advanced are complementary presentation controls:
- primary niche decides which work should be emphasised;
- experience level decides how much detail and guidance is surfaced;
- neither changes roles, permissions or sources of truth.

Prefer one disclosure pattern per user intent. Native disclosure is appropriate for secondary settings/detail; do not stack competing collapse mechanisms for the same task.

### Connectivity
Offline and online are two operating conditions of the same Zazu product:
- offline users must retain a good core operating experience;
- online users should receive the richer connected experience;
- loss of connectivity must not break normal local work;
- cloud synchronization is additional capability, not the foundation of local operation.

Current repository evidence supports PWA/static-asset caching and an offline licensing foundation. A full local data/write/sync engine is not yet implemented.

## Core product definition

Zazu should provide the capability needed to run a business while presenting it simply enough for a non-technical South African business owner with basic English to learn and use comfortably.

**Core philosophy:** powerful underneath, simple on top.

Zazu should adapt to the business rather than forcing the business to adapt to Zazu.

## Approved product decisions

1. **Whole-business promise:** Zazu should handle the broad set of business needs in the simplest practical way, replacing fragmented paperwork, spreadsheets and scattered work processes without overwhelming the user.
2. **Audience:** owner and staff should both be able to use Zazu simply. It must work for a one-person caterer/sound-hire operator, a 10–50 person business, and more established/tender-based businesses.
3. **Business-defined system:** the user defines what their business does and what problems they have. Zazu recommends relevant capabilities instead of forcing a fixed product shape.
4. **Starting setup:** Zazu provides a sensible ready-made setup based on the business, then lets the owner switch capabilities on/off and customise them.
5. **Flexible categories:** a business category is a starting point, never a cage. A caterer can also do sound hire, rentals, decor, etc. for different jobs.
6. **Adaptive recommendations:** Zazu quietly recommends small/obvious improvements and asks the owner about decisions that materially affect the business.
7. **Calm home experience:** the home should feel like a calm business command centre, not a crowded data dashboard. It should give the owner a sense of control, reassurance that important things are not being forgotten, and clarity about what to do next.
8. **Zazu Helper:** the Helper, onboarding guide and assistance layer are one coherent product experience. The Helper uses one shared character framework with a selectable mascot skin; current candidates are Gecko, Ant and Chameleon. The selected skin can rest when nothing needs attention and move toward relevant areas when help or attention is needed. It must not become constant visual noise.
9. **South African context:** language, examples and terminology should feel naturally South African while remaining professional. Avoid forced stereotypes/slang. Relevant context can include rands, VAT, tenders, WhatsApp-heavy workflows and local business practices where appropriate.
10. **Guided learning:** give a short introduction, then teach users naturally while they work. Basic/Intermediate/Advanced changes wording, explanations and guidance depth, not the underlying product capability.
11. **Experience vs authority:** Basic/Intermediate/Advanced is an experience level, NOT a permission level. Business hierarchy and permissions remain separate. A highly capable employee does not gain authority simply because they understand Zazu well; the owner can promote/change their actual role.
12. **Native automation:** automation is built inside Zazu. No external AI dependency is required for this product direction.
13. **Automation boundary:** Zazu should notice needs, explain them and suggest an action. Important business-changing actions should normally require user approval.
14. **Attention levels:** small suggestions should be subtle; useful recommendations noticeable; important issues clearly highlighted; urgent issues prominent. Do not make every notification feel urgent.
15. **Conversational setup:** onboarding should feel like a short guided conversation while collecting required information behind the scenes, not a large technical form.
16. **Business description:** offer common business types for speed but always allow the owner to describe their business in their own words.
17. **Recommended capabilities:** Zazu prepares a recommended setup and lets the owner switch capabilities on/off. The owner remains in control.
18. **Post-setup:** after setup, give a very short confirmation, then take the owner straight into the business.
19. **Search as universal navigation/help:** search should understand user intent, not only database records. A search such as “I want to go back to Basic” should surface the relevant setting/action. This should apply to any setting or action the user may be looking for.
20. **No dead-end search:** when search cannot find something, offer useful recovery paths such as Help, Settings, or telling Zazu what the user is trying to do.
21. **Work-first entry:** users should be able to start with what they are trying to accomplish rather than needing to understand Zazu's internal data structure first. Zazu can internally connect customer, request, job/event, quote, money, stock, documents and other records as work develops.
22. **Adaptive questions:** Zazu should ask only the information needed for the situation. Sometimes one question is best; sometimes 2–3 related questions belong together.
23. **Business authority:** “owner” means the business owner/authorised business user operating the Zazu account, not the customer's owner. The business controls its workflow and permissions.
24. **Industry wording:** terminology must be researched by industry before being selected. Zazu should use the natural term for the user's business rather than forcing one universal label such as “Job” or “Event.”
25. **Offline accessibility:** important daily business work should remain usable without internet. Internet-dependent actions can wait until connectivity returns. Do not promise full offline parity until the current architecture has been audited.

26. **Situation-aware work intake:** Zazu should recognize what an incoming interaction represents based on the situation and available information. It should not force every business or every customer interaction into one universal label such as Enquiry, Request or Booking. Zazu may begin with the appropriate lightweight state and guide the user as the interaction becomes clearer (for example, enquiry → request → booking/confirmed work). The underlying records should remain connected rather than requiring the owner to understand the data model.

27. **Next-useful-question behaviour:** after recognizing the situation, Zazu should ask the next useful question needed to move the work forward instead of presenting a large form. Questions should be adaptive and based on what is already known; do not ask for information that is unnecessary at that stage.

28. **Proactive next-step preparation:** when Zazu has enough information to identify an obvious useful next step, it should prepare that step and ask the owner for approval before carrying it out. Example: once a catering booking has enough information, Zazu can prepare a quote rather than making the owner navigate to a separate quote workflow.

29. **Review-before-approval:** anything Zazu prepares for a business-changing action (such as a quote, shopping list, invoice or reminder) must be reviewable and editable by the owner/authorised user before approval. Preparation is not execution; the user remains in control of the final content and action.

30. **Owner preferences from review:** when an owner repeatedly changes Zazu's prepared suggestions in a meaningful, repeatable way, Zazu may retain that as a business preference to improve future suggestions. Preferences must never silently change or execute business actions, and they must remain reviewable/changeable by the authorised business user.

31. **Deferred suggestions:** when the owner says “Not now” to a non-urgent Zazu suggestion, Zazu should offer the owner control over when to be reminded. The owner can choose a suitable reminder time rather than Zazu repeatedly resurfacing the suggestion on its own schedule.

32. **WhatsApp conversation capture with privacy boundaries:** Zazu should treat relevant WhatsApp Business conversations as part of the customer's business record when the owner intentionally imports/captures them, but it must not silently scrape, monitor, or copy a person's WhatsApp conversations. The first implementation should favor explicit owner-initiated capture/import and selective attachment of relevant messages/media to the appropriate customer/work record, rather than assuming Zazu may ingest an entire chat. Zazu must clearly identify what is being imported, why it is being stored, and where it will be attached, with the owner able to review before saving. Zazu must not turn imported conversation data into unrelated marketing or other secondary uses. Any future direct WhatsApp integration must be designed against Meta's current WhatsApp Business policies/technical terms and applicable South African privacy law, including POPIA, with consent/other lawful basis, purpose limitation, data minimisation, access controls, retention/deletion, security, and data-subject rights considered. This is a product requirement, not a claim of legal compliance; implementation should receive a dedicated privacy/legal review before release.

33. **Multi-channel communication record:** Zazu should not design around WhatsApp alone. A small business may receive customer work through WhatsApp, email, phone calls, SMS, Facebook/Messenger, Instagram/social messages, marketplace messages, website/contact forms, and in-person conversations. Zazu should provide one business communication/history layer that can connect relevant customer interactions to the right customer and work record regardless of channel. This does **not** mean Zazu should automatically access every channel. Explicit capture/import should be the default where a channel does not provide an approved integration. For imported conversations, support both selective capture and deliberate whole-conversation import, with review before saving. Calls should be recordable as a communication entry (for example, date, contact, outcome, notes and follow-up) rather than assuming Zazu can record call audio. Channel-specific integrations must respect the provider's current rules and applicable privacy/data-protection requirements. The product goal is **one business history, not one messaging app**.

34. **Confident record matching:** when Zazu captures or receives a customer interaction, it may automatically connect it to an existing customer and/or work record when the match is sufficiently confident. If identity, customer, or work context is ambiguous, Zazu must ask the owner rather than guessing. The owner must be able to correct the match. Matching should use relevant business context and minimize unnecessary personal-data processing; uncertainty should favor clarification over silent association.

35. **Excel replacement principle:** Zazu should not merely import Excel and reproduce spreadsheets; it should absorb the everyday business work owners currently use Excel for and make the result simpler. This includes familiar lists/tables, calculations, totals, percentages, pricing, markups/margins, budgets, comparisons, filtering, sorting, summaries, recurring calculations, reports and other common business spreadsheet tasks. Where Zazu understands the business context, the owner should get the result through a simple action or choice instead of constructing formulas, ranges, lookups or complex spreadsheet logic. Excel-like familiarity should remain available where it helps the transition, but the long-term experience should be **“click and get the business result,”** not “learn another spreadsheet.” Zazu should automate the underlying calculation and keep the result explainable/reviewable. Advanced spreadsheet functionality that genuinely requires specialist analysis should not be promised as a blanket Excel replacement; the product goal is to replace the repetitive business work that ordinary owners use Excel for.

35. **Spreadsheet familiarity without spreadsheet dependence:** Zazu should feel immediately understandable to people coming from Excel and similar spreadsheets, while presenting a modern 2026 SaaS experience rather than copying Excel's interface. Familiar concepts such as tables, rows, columns, sorting, filtering, editing, totals and import/export should remain available where useful, but Zazu should make relationships, status, workflow and next actions visually clearer and easier to understand.
36. **Zazu replaces spreadsheet work, not spreadsheet familiarity:** Zazu should cover the common calculations, transformations, summaries and repetitive tasks that small businesses commonly perform in Excel, but turn them into simple business actions wherever possible. The owner should not need to build formulas for routine work; Zazu should calculate, validate, connect and present the result automatically, while still allowing transparent detail when the owner wants to understand or verify it.
37. **Excel interoperability and physical records:** Excel import and export are first-class capabilities. Import should help map, clean, validate and review spreadsheet data before bringing it into Zazu; export should provide useful business records in a practical spreadsheet format. Zazu should also respect businesses that retain printed or physical documents as proof, backup or compliance records. Digital Zazu records should therefore be exportable/printable in clear, usable forms rather than assuming every business will abandon paper immediately.
38. **The gradual Excel exit:** Zazu should not tell owners they must abandon Excel or paper on day one. It should make the everyday business workflow progressively easier inside Zazu until the owner naturally stops reaching for Excel for work Zazu can handle. The product success signal is not “Excel is blocked”; it is “the owner no longer needs Excel for routine business work because Zazu does it more simply.”

39. **Visual-first calculation explanations:** Zazu should automate common spreadsheet calculations and business analysis, but never present important results as unexplained numbers. When a calculation matters, show a clear visual explanation in business language: what was calculated, which inputs were used, the result, and where useful the simple underlying calculation. Users should be able to drill down without being forced to understand spreadsheet formulas or cell references. The interface should teach through visual hierarchy, labels, examples, summaries, charts and progressive detail rather than dense grids or formula syntax. This is a core Zazu usability principle: **if Excel makes the user decode the screen, Zazu should explain the screen.**


## Products, suppliers, purchasing, inventory and assets — product language and workflow

These are first-class operational capabilities in Zazu, not secondary setup screens or late-stage extras. A job may depend on what the business sells, what it already owns, what it has in stock, and what it must obtain from suppliers. The Job workflow must make those choices visible while scope and delivery are being planned, not introduce purchasing only after the quote is finished.

Use these terms consistently in navigation, headings, forms, empty states, summaries and helper guidance:

- **Products and services (catalogue):** what the business offers customers or uses as reusable quoted line definitions. A catalogue entry describes an offering, its type, pricing basis and unit. It does not, by itself, prove that physical stock exists or that the business owns an asset.
- **Inventory (stock):** consumable or quantity-managed items tracked by units and stock movements. Examples: food ingredients, beverages, disposable plates, packaging and cleaning supplies. Show on-hand quantity, unit, receipts, issues, returns, adjustments and reorder level. Inventory is counted in quantities; it is not an individually identified piece of equipment.
- **Assets (owned equipment):** durable, reusable physical things tracked individually because identity, condition, location, availability or return matters. Examples: a specific oven, chafing dish, tent, table, speaker or vehicle. Track asset tag, condition, status, location and job allocation/return. Assets are not consumable stock, even when several identical units exist.
- **Suppliers:** the businesses or people the company buys from. Supplier records are operational relationships used to source products, stock, equipment and job-specific services. Supplier discovery, maintenance and selection must be easy from purchasing workflows.
- **Purchase orders (POs):** formal records of what the business intends to buy from a selected supplier, in what quantity, at what price and by when. POs can be linked to a job or remain general business purchases. A PO is a supplier commitment, not an inventory movement, an asset registration or proof of receipt.
- **Receiving:** what actually arrived, in what quantity and condition. Receiving must preserve the PO line and supplier trail, then route the received item according to its classification: stock receipt into inventory; individually tracked durable equipment into the asset register; or a job-specific/non-stock purchase into its cost/expense trail. Do not silently treat every PO line as stock.

### Job planning must expose the fulfilment choice

When a user adds or reviews a job requirement, Zazu should help them identify how it will be fulfilled:

1. Use existing inventory/stock.
2. Allocate an owned asset/equipment item.
3. Purchase from a supplier (create or link a PO).
4. Hire/obtain externally where appropriate.
5. Deliver as a service or labour requirement.

The user should see demand, available quantity/assets, shortages, supplier/PO status and expected arrival in the job context. Purchasing and resource planning should be visible alongside quote, preparation and delivery planning; they must not be hidden behind a generic “later” step. A PO or stock receipt should update the correct operational record and job readiness without duplicating costs or counts.

### Language acceptance rules

- Never use **asset** as a synonym for inventory, stock, product or generic resource.
- Never use **inventory** when the intended meaning is the asset register.
- Use **stock/on hand** for quantity-controlled inventory; use **asset/equipment** for individually tracked durable property.
- Use **catalogue product/offering** when referring to what the business sells or quotes, and **inventory item/stock item** when referring to quantities physically held.
- If a catalogue offering is linked to an inventory item or asset capability, explain that relationship explicitly; the link does not make the records interchangeable.
- Avoid generic labels such as “resources” where the user needs to choose between stock, owned equipment, supplier purchase or external hire.

This direction strengthens the existing V1 modules and their connections. It does not authorize a warehouse-management suite, a full procurement platform or enterprise asset management.

## Product guardrails

- Do **not** rewrite Zazu just to satisfy this definition.
- Do **not** add complexity merely because a feature exists.
- Do **not** force every business into the same workflow.
- Do **not** make Advanced mean more authority.
- Do **not** make the helper behave like an intrusive chatbot.
- Do **not** make automation silently alter important business records.
- Do **not** make internet access a needless requirement for everyday work.
- For every future product recommendation, research successful SaaS/corporate patterns and the relevant industry wording first, then compare against Zazu's current implementation.

## Director workflow for this definition

For future product-direction decisions:

**Research → inspect current Zazu implementation → propose small evidence-based direction → get owner decision → consolidate here → only then plan implementation.**

This log is a product-direction source of truth, not permission to execute a rewrite.


## Approved role authority baseline — 2026-10-01

The Zazu authority model is explicitly separate from experience level:
- **Owner:** full business authority, including owner-only administration and financial mutations.
- **Manager:** day-to-day operational authority across customers, work, quotes, purchasing, inventory, assets, suppliers and operational reports; finance is view-only; work deletion and owner administration remain restricted.
- **Staff:** operational execution according to the existing staff permission matrix; finance remains view-only and owner-only administration remains restricted.

Basic / Intermediate / Advanced remains an experience-level setting, never a permission level.

## Zazu Helper character-system foundation — 2026-10-03

Zazu Helper is now defined as one character framework with selectable mascot skins. The animal is the appearance; the Helper remains the same character, behaviour model and interaction system. Initial skin candidates are Gecko, Ant and Chameleon; visual design remains intentionally deferred to the owner/design phase.

Basic / Intermediate / Advanced must not select different mascots. Experience level controls presentation and assistance depth. The selected Helper skin works across all experience levels.

The Helper foundation must be lightweight: 2D/vector-oriented assets, reusable state-machine animation, semantic actions, responsive workspace anchors and no dependency on 3D/game-engine production. Movement is event-driven rather than continuous decoration. The Helper may travel to relevant workspace points, explain, notice, work, warn, synchronise or rest, while remaining quiet when nothing requires attention.

The application should address the Helper through semantic intent such as notice, approach, point, explain, think, work, warn, sync and return rather than skin-specific animation commands. A responsive semantic-target layer must resolve workspace destinations so traversal works across desktop, tablet and mobile layouts.

This is a product/architecture foundation, not a visual design commitment. The final appearance, proportions, colours, expressions and physical movement language remain a later design deliverable. See docs/ZAZU_HELPER_CHARACTER_SYSTEM.md for the authoritative foundation.
