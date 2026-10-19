# Zazu EMP — Release Dependency Licence Audit

**Audit date:** 2026-10-19  
**Basis:** current `composer.lock` and `package-lock.json` on `main`  
**Scope:** locked Composer packages and npm packages, including development/build dependencies

## Result

All 153 npm package entries and all 109 Composer lock packages expose declared licence metadata. Counts below are derived from the current lockfiles, not carried forward from the October 7 snapshot.

### Composer runtime

**Packages:** 76

| Licence | Locked packages |
|---|---:|
| MIT | 68 |
| BSD-3-Clause | 5 |
| BSD-3-Clause / GPL-2.0-only / GPL-3.0-only | 2 |
| Apache-2.0 | 1 |

### Composer development

**Packages:** 33

| Licence | Locked packages |
|---|---:|
| MIT | 9 |
| BSD-3-Clause | 24 |

The two dual/multi-licensed packages are `nette/schema` v1.3.6 and `nette/utils` v4.1.5. Their metadata permits selection among the listed licensing options; release distribution must preserve applicable upstream notices.

### npm

**Package entries with licence metadata:** 153

| Licence | Locked package entries |
|---|---:|
| MIT | 113 |
| MPL-2.0 | 24 |
| ISC | 7 |
| Apache-2.0 | 5 |
| BSD-3-Clause | 1 |
| 0BSD | 2 |
| MIT OR CC0-1.0 | 1 |

The MPL-2.0 entries are the `lightningcss` package and its platform builds, including nested Vite copies. These are build-tool dependencies, not application source code. Distribution of generated assets must still retain applicable third-party notices.

The unused `@fontsource/anton` package (OFL-1.1) was removed after checking the application JS, Vite configuration, and active authored CSS files: no import or font-family reference used it. The display stack in `resources/css/app.css` uses Space Grotesk/Manrope font faces already declared by that stylesheet. No OFL-1.1 npm package remains in the current lockfile.

### Lockfile reconciliation

- The previous recorded npm licence inventory was stale.
- `package.json` and `package-lock.json` were changed together in commit `f73174de5b38b5826e6685050e2ede6a949e2520`.
- Current-head `npm ci` and frontend build must pass before this inventory is treated as runtime-verified.
- The source inventory is not legal approval; final distribution notice review remains open.

## Release handling

1. Keep `composer.lock` and `package-lock.json` fixed for a release candidate.
2. Preserve required upstream copyright/licence notices in the release artifact or accompanying notices.
3. Do not claim the top-level notice register is an exhaustive source-code attribution list.
4. Re-run this audit whenever locked dependencies change.
5. Legal/commercial owner review remains required for final distribution terms.

## Status

**Engineering licence inventory: UPDATED AT SOURCE; latest-main CI verification pending.**

This inventory does not close legal review, proprietary distribution terms, trademark clearance, or final release certification.
