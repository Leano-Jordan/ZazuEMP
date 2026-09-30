# Zazu Director — Product Definition & Direction Log

> Living product-direction record for the Zazu EMP Director. This document records user-approved product decisions and principles so other Director sessions can continue from the same direction.
>
> **Current baseline checked:** `2cbb0869ec77bdcc1457cb6fce7c2c3a333720d7` — 2026-09-30.
>
> **Operating rule:** This is direction-setting, not a rewrite mandate. Protect working functionality. Make evidence-based nudges only. Before proposing a change, inspect the current implementation and compare it with this direction.

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
8. **Zazu Helper:** the bird mascot, helper and onboarding guide are one coherent product experience. The bird can rest when nothing needs attention and move/fly toward relevant areas when help or attention is needed. It must not become constant visual noise.
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
