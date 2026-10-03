# Zazu Helper — Lightweight Animation Technology Research

**Status:** R0 architecture decision / research
**Date:** 2026-10-03

## 1. Decision first

Zazu Helper V1 must require **R0 to operate and ship**.

Therefore the core implementation will not depend on a paid animation editor, paid runtime, animation-hosting subscription or commercial character platform.

**V1 baseline: SVG artwork + CSS + Web Animations API (WAAPI).**

This uses browser capabilities already aligned with Zazu's web application and avoids adding an animation dependency to the current package. The current Zazu package has no dedicated animation library.

Rive remains technically interesting as a future renderer option, but it is not a V1 dependency or requirement.

## 2. Zazu requirements

2D character artwork; reusable semantic states; state-machine behaviour; responsive movement; interactive transitions; reduced motion; mobile browsers; offline/static asset delivery; multiple skins; lightweight production; Laravel/PWA integration; no game-engine dependency; no paid production dependency.

## 3. SVG + CSS + Web Animations API

This is the recommended V1 route.

SVG provides a natural container for compact 2D character artwork with independently transformable groups. CSS can handle simple presentation states, while WAAPI lets JavaScript control timing, playback, cancellation and transitions.

MDN describes WAAPI as a browser animation API that combines timing and animation models and supports programmatic playback control. MDN also recommends pause/disable mechanisms and reduced-motion handling for animated interfaces. citeturn586528search0turn586528search4

### Strengths
- No paid service or subscription is required.
- No third-party animation runtime is required.
- Assets can be stored and delivered locally with the Zazu application, which fits offline operation.
- The renderer can remain small and replaceable.
- JavaScript can coordinate movement with the Helper state machine.
- SVG groups can support reusable anatomy and skin-specific expression.

### Weaknesses
- Zazu must build the small amount of rig/state tooling it actually needs.
- Complex character deformation or elaborate authoring workflows would require more custom work.
- The visual authoring experience is less specialised than a dedicated character-animation editor.

### Best use
Zazu's V1 Helper and first production behaviours.

## 4. Anime.js as an optional R0 library

Anime.js is MIT-licensed and supports CSS properties, SVG, DOM attributes and JavaScript objects. citeturn586528search9turn586528search2

However, it is not necessary for the first Helper implementation.

**Decision:** do not add Anime.js merely because it is free. Add a library only when a demonstrated animation requirement cannot be kept simpler with native CSS/WAAPI.

## 5. Motion One

Motion One has an MIT license, but the original Motion One repository is archived and read-only. citeturn586528search1turn586528search10

**Decision:** not a new dependency for Zazu V1.

## 6. Rive

Rive's official runtimes are open source and MIT-licensed, including the web/WASM runtime. citeturn403848search0turn403848search5

The important distinction is the **creation/shipping workflow**. Rive's current pricing page says its free tier is for creating and learning, while the Cadet plan is currently listed at $9 per seat per month to ship work and provides .riv export. citeturn403848search6

That means the Rive runtime itself being open source does not make the full Rive production workflow R0 for Zazu.

### Decision
Do not make Rive a V1 requirement.

Keep the renderer boundary flexible enough that Rive could be evaluated later if the business eventually chooses to pay for a specialist animation workflow.

## 7. Lottie / dotLottie

Lottie-style assets can be useful for predetermined animation sequences and supporting micro-interactions.

For Zazu's central Helper, however, the problem is not simply playback of a finished sequence. The Helper needs semantic state, target-aware movement, interruption, approvals, offline state and multiple skins.

**Decision:** not the core Helper engine for V1.

Third-party animation assets must also be separately reviewed for commercial licensing before use.

## 8. Canvas and game-engine approaches

Canvas-heavy character rendering and game engines are unnecessary for the current product.

MDN notes that larger or more numerous animations can increase processing cost and recommends cutting non-essential animation, particularly on lower-powered or mobile devices. citeturn586528search14

**Decision:** reject as the V1 Helper foundation.

## 9. Accessibility

Whatever renderer is used, semantic meaning must survive reduced or disabled motion.

Required presentation levels:

**Full motion** — normal movement and expressions.

**Reduced motion** — shorter/smaller movement and simpler transitions.

**Minimal/static** — no meaningful character movement required; the UI communicates the semantic state.

The Helper must not use animation as the only carrier of critical information. citeturn586528search0turn586528search14

## 10. R0 technology stack

### Required
- Original owner-supplied Zazu artwork.
- SVG.
- CSS.
- Browser Web Animations API.
- Existing Laravel/Vite application.

### Optional only when justified by a proven need
- A free/open-source animation helper such as Anime.js.

### Not required
- Rive subscription.
- LottieFiles subscription.
- animation hosting.
- game engine.
- paid character marketplace.

## 11. Architecture rule

The Helper architecture remains renderer-neutral:

**Zazu Helper Engine → semantic state → semantic target → skin adapter → renderer**

Application behaviour must never be rewritten around a renderer-specific API.

## 12. Ownership rule

Prefer original Zazu artwork supplied by the owner.

Do not build the identity of Zazu around third-party mascot artwork or marketplace animation assets.

Every external asset used in production must have a recorded license suitable for the intended commercial product use.

## 13. Minimal technology proof

Before adding any external animation dependency, demonstrate these states using the native R0 baseline:

IDLE, NOTICE, APPROACH, POINT, THINK, WORKING, SUCCESS, WARNING, OFFLINE and RETURN.

The same semantic commands must work against more than one skin.

## 14. Final R0 position

**Use native SVG + CSS + WAAPI first. Pay nothing for the Helper animation stack.**

Add a library only when the implementation can show a concrete technical requirement that the native baseline cannot meet cleanly.

This keeps the character system lightweight, offline-friendly, maintainable and under Zazu's control.