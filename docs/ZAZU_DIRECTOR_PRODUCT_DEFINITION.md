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
