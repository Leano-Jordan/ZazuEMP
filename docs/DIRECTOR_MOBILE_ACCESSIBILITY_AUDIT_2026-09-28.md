# Director Mobile + Desktop Accessibility & UX Audit — 2026-09-28

## Iteration

Controlled refinement of the shared application shell after the previous mobile repair. The desktop visual benchmark, business logic, routes, permissions and operational workflows were protected.

## Findings carried into this cycle

| Finding | Severity | Fix effort | Release impact |
|---|---|---:|---|
| Mobile navigation previously had duplicate toggle handling | Critical | Low | Functional mobile defect |
| Mobile navigation previously exposed only the current section | High | Low | Navigation dead ends |
| Mobile header/section navigation consumed excessive vertical space | High | Low | Poor content visibility |
| Mobile drawer backdrop could remain keyboard-focusable while visually hidden | High | Low | Accessibility defect |
| Mobile drawer had no keyboard focus trap | High | Low | Accessibility defect |
| Header action slots relied on implicit inline flow | Medium | Low | Inconsistent desktop/mobile header behaviour |
| Accessibility test expected a removed “Open settings” control | Medium | Low | Stale failing test |
| Legacy mobile-nav rules remained in shared responsive CSS | Medium | Low | CSS ownership/consolidation problem |
| Duplicate route selectors remained in the final visual layer | Low | Low | CSS maintenance error |

## Refinement executed

The desktop sidebar remains the source of truth for mobile navigation. On phone widths it becomes a compact off-canvas drawer controlled by a hamburger/close button.

The shared shell now also:

- groups page-level header actions explicitly;
- keeps mobile header controls at a consistent 40px target;
- hides contextual section tabs on mobile to preserve vertical space;
- uses safe-area-aware top and bottom spacing;
- makes the mobile backdrop visibility-hidden when closed;
- traps Tab/Shift+Tab inside the open drawer;
- returns focus to the hamburger control after drawer dismissal;
- uses inert on the closed mobile drawer;
- updates browser theme chrome to the current visual palette;
- removes obsolete mobile navigation CSS from the responsive architecture file;
- removes duplicate route selectors from the final visual layer;
- aligns accessibility tests with the actual current header.

## Verification

Static verification after execution confirms:

- JavaScript parses successfully;
- all three relevant CSS files have balanced braces;
- no legacy data-mobile-nav markup remains;
- no legacy mobile-nav controller remains in JavaScript;
- the responsive architecture file no longer owns obsolete mobile navigation selectors;
- mobile drawer focus, Escape, inert and hidden-backdrop safeguards are present;
- the accessibility test covers the current mobile drawer contract;
- the desktop visual files remain unchanged except for duplicate-selector cleanup;
- the new header-action wrapper has an explicit responsive layout owner.

## Remaining proof

GitHub Actions did not report a workflow run for the final commit. Therefore rendered phone/tablet/desktop browser QA remains the final evidence gate.

**Release posture:** the shell architecture is substantially cleaner and more consistent across desktop and mobile, but rendered-device verification is still required before marking the UI accessibility gate complete.