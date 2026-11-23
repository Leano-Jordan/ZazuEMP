# Zazu EMP — Release Dependency Licence Audit

**Audit date:** 2026-11-23  
**Basis:** current `composer.lock` and `package-lock.json` on `main`  
**Scope:** locked Composer packages and npm packages, including development/build dependencies

## Result

All **147 npm package entries** and all **111 Composer lock packages** expose declared licence metadata. Counts below were recalculated from the current lockfiles after the 2026-11-23 dependency refresh.

### Composer runtime

**Packages:** 77

| Licence | Locked packages |
|---|---:|
| MIT | 69 |
| BSD-3-Clause | 5 |
| BSD-3-Clause / GPL-2.0-only / GPL-3.0-only | 2 |
| Apache-2.0 | 1 |

### Composer development

**Packages:** 34

| Licence | Locked packages |
|---|---:|
| MIT | 9 |
| BSD-3-Clause | 25 |

The two dual/multi-licensed packages are `nette/schema` v1.4.0 and `nette/utils` v4.1.6. Their metadata permits selection among the listed licensing options; release distribution must preserve applicable upstream notices.

### npm

**Package entries with licence metadata:** 147

| Licence | Locked package entries |
|---|---:|
| MIT | 119 |
| MPL-2.0 | 12 |
| ISC | 6 |
| Apache-2.0 | 5 |
| BSD-3-Clause | 1 |
| 0BSD | 2 |
| BlueOak-1.0.0 | 1 |
| MIT OR CC0-1.0 | 1 |

The MPL-2.0 entries are `lightningcss` v1.33.0 and its platform builds, including nested Vite copies. These are build-tool dependencies, not application source code. Distribution of generated assets must still retain applicable third-party notices.

The `BlueOak-1.0.0` entry is `graceful-fs` v4.2.12. The `MIT OR CC0-1.0` entry is `type-fest` v5.10.0. Retain their upstream licence metadata and notices in the release inventory.

The unused `@fontsource/anton` package (OFL-1.1) remains removed: active application JS/CSS/Vite source did not import or reference it. The display stack in `resources/css/app.css` uses Space Grotesk/Manrope font faces declared by that stylesheet. No OFL-1.1 npm package remains in the current lockfile.

### Direct dependency snapshot

| Ecosystem | Package | Locked version |
|---|---|---:|
| Composer | laravel/framework | 13.43.0 |
| Composer | laravel/tinker | 3.2.0 |
| Composer (dev) | phpunit/phpunit | 13.4.8 |
| Composer (dev) | laravel/pao | 1.1.6 |
| Composer (dev) | laravel/pail | 1.3.0 |
| Composer (dev) | laravel/pint | 1.32.3 |
| npm | vite | 8.3.8 |
| npm | tailwindcss | 4.3.5 |
| npm | @tailwindcss/vite | 4.3.5 |
| npm | laravel-vite-plugin | 3.2.1 |
| npm | @playwright/test | 1.65.1 |
| npm | concurrently | 10.0.7 |
| npm (optional) | @laravel/multiplex | 0.4.5 |

The manifest ranges remain intentionally semver-compatible; the lockfiles are the reproducible installed-version source.

### Lockfile reconciliation

- Direct Composer requirements match their locked packages and satisfy the declared constraints.
- Direct npm development/optional dependencies match their lockfile package entries and satisfy the declared ranges.
- Composer runtime/dev counts and npm package/licence counts were recalculated from the lockfiles on 2026-11-23; the October 19 counts are superseded.
- CI runs `composer audit --locked` and a strict `npm audit --audit-level=high`.
- GitHub advisory GHSA-fc5q-39xc-vjpf / CVE-2026-112366 was withdrawn on 2026-11-18 because it was determined not to be a security issue. The obsolete local exception has been removed; the audit gate now evaluates current advisories without a package-specific bypass.

## Release handling

1. Keep `composer.lock` and `package-lock.json` fixed for a release candidate.
2. Preserve required upstream copyright/licence notices in the release artifact or accompanying notices.
3. Do not claim the top-level notice register is an exhaustive source-code attribution list.
4. Re-run this audit whenever locked dependencies change.
5. Legal/commercial owner review remains required for final distribution terms.

## Status

**Engineering licence inventory: refreshed against current lockfiles; exact-head CI verification pending.**

This inventory does not close legal review, proprietary distribution terms, trademark clearance, or final release certification.
