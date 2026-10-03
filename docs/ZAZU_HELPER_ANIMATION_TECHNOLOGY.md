# Zazu Helper — Lightweight Animation Technology Research

**Status:** Research / recommendation
**Date:** 2026-10-03

## Zazu requirements
2D character artwork; reusable semantic states; state-machine behaviour; responsive movement; interactive transitions; reduced motion; mobile browsers; offline/static asset delivery; multiple skins; lightweight production; Laravel/PWA integration; no game-engine dependency.

## SVG + CSS + Web Animations API
This is the lightest architectural baseline. Modern browsers support SVG animation and the Web Animations API. MDN documents SVG animation as widely available and recommends reducing unnecessary animation and supporting prefers-reduced-motion.

Strengths: no animation platform dependency, strong application control, compact assets, offline-friendly delivery and easy alignment with the semantic Helper architecture.

Weaknesses: the character rig/state-machine tooling must be built or managed by us; complex character animation becomes more labour-intensive.

Best use: lightweight prototype or maximum-ownership implementation.

## Rive
Rive is strongly aligned with Zazu because its workflow is built around interactive graphics and state-machine-style animation. Its current pricing page lists JavaScript/web runtime support and production export capabilities. The current production shipping plans are paid.

Strengths: state-machine model, reusable animation states, interactive characters, separation between artwork and interaction logic and web integration.

Weaknesses: vendor/toolchain dependency, production-plan cost, and the need to manage runtime/assets alongside Zazu.

Best use: polished production Helper where animation quality and maintainability justify the dependency.

## Lottie / dotLottie
Lottie is strong for portable animation assets and broad web use. LottieFiles currently provides optimized JSON and dotLottie formats and web distribution options.

Strengths: established ecosystem, compact animation formats, good for predetermined animation sequences and micro-interactions.

Weaknesses: the Helper is more than a collection of predetermined sequences; rich state-machine behaviour needs orchestration; commercial use and third-party asset licensing must be checked carefully.

Best use: supporting animations and micro-interactions rather than the central Helper engine.

## Canvas/game-style rendering
Not recommended for the current product. It adds unnecessary complexity and risks turning a business UI into a mini-game system.

## Accessibility
Whatever technology is selected, animation must honour reduced-motion preferences. The Helper needs Full motion, Reduced motion and Minimal/static presentation levels. Semantic state remains the same across all three.

## Current recommendation
Do not lock the product architecture to a renderer yet.

Preferred production candidate: Rive.
Lightweight fallback/prototype: SVG + CSS/Web Animations API.
Supporting-animation option: Lottie/dotLottie.
Rejected for current scope: game-engine/canvas-heavy character system.

Rive currently fits the architecture because Zazu already defines semantic states, semantic actions, skin adapters, responsive targets and reduced-motion requirements. However, Rive-specific APIs must remain underneath the semantic contract.

## Ownership and licensing rule
Prefer original Zazu artwork and retain ownership of the character design and source assets. Avoid building the core mascot from third-party marketplace artwork. Record the exact license of every external asset and re-check technology licensing before commercial release.

## Renderer boundary
The intended architecture remains:
Zazu Helper Engine → semantic state → semantic target → skin adapter → animation renderer.

The renderer must be replaceable so Zazu is not architecturally locked to Rive or Lottie.

## Minimal proof before production
Demonstrate IDLE, NOTICE, APPROACH, POINT, THINK, WORKING, SUCCESS, WARNING, OFFLINE and RETURN through the same semantic commands across more than one skin.

## Decision boundary
This is a research recommendation, not a permanent technology lock.