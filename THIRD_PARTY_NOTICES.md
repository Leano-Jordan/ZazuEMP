# Third-Party Notices

## Purpose

This file records third-party software, assets and other material used by ZazuEMP.

Third-party material is not automatically owned by the ZazuEMP project.

## Direct dependency register

Versions below are the exact versions currently locked on `main`, not the wider semver ranges in the manifests.

| Component | Locked version | Licence | Source | Notes |
|---|---:|---|---|---|
| Laravel Framework | 13.43.0 | MIT | laravel/framework | Application framework; retain upstream licence notice/terms. |
| Laravel Tinker | 3.2.0 | MIT | laravel/tinker | Development/runtime utility. |
| FakerPHP/Faker | 1.24.1 | MIT | FakerPHP/Faker | Development/test dependency. |
| Laravel Pail | 1.3.0 | MIT | laravel/pail | Development log viewer. |
| Laravel PAO | 1.1.6 | MIT | laravel/pao | Development/operations tooling. |
| Laravel Pint | 1.32.3 | MIT | laravel/pint | PHP formatter. |
| PHPUnit | 13.4.8 | BSD-3-Clause | phpunit/phpunit | Development/test dependency. |
| Mockery | 1.6.15 | BSD-3-Clause | mockery/mockery | Development/test dependency. |
| Collision | 8.9.6 | MIT | nunomaduro/collision | Development/test tooling. |
| Vite | 8.3.8 | MIT | vitejs/vite | Frontend build tooling; generated bundles may include transitive dependencies under additional licences. |
| Tailwind CSS | 4.3.5 | MIT | tailwindlabs/tailwindcss | Frontend styling tooling. |
| @tailwindcss/vite | 4.3.5 | MIT | tailwindlabs/tailwindcss | Vite integration for Tailwind CSS. |
| laravel-vite-plugin | 3.2.1 | MIT | laravel/vite-plugin | Laravel/Vite integration. |
| @playwright/test | 1.65.1 | Apache-2.0 | microsoft/playwright | Development/browser acceptance dependency. |
| @types/node | 26.6.6 | MIT | DefinitelyTyped/DefinitelyTyped | Development type definitions; CI currently runs Node 22. |
| concurrently | 10.0.7 | MIT | open-cli-tools/concurrently | Development process orchestration. |
| @laravel/multiplex | 0.4.5 | MIT | kolossal-io/laravel-multiplex | Optional dependency; retain upstream notices if distributed. |

## Rules

Before adding a dependency or external asset:

1. Identify the exact component and version.
2. Record its licence.
3. Check compatibility with the project's proprietary distribution model.
4. Preserve required copyright and licence notices.
5. Record material obligations or restrictions.
6. Prefer dependencies with clear provenance and sustainable maintenance.

The register must be updated when dependencies are introduced or locked versions change.

### Release-level licence control

This register is a direct-dependency summary, not a complete transitive dependency audit. Before a commercial release, inspect `composer.lock` and `package-lock.json`, verify exact installed versions and licences, and preserve required notices. See `docs/RELEASE_DEPENDENCY_LICENCE_AUDIT.md` for the current transitive inventory.

## Licence caution

A dependency's licence may impose obligations that affect distribution, source availability, notices, attribution or commercial use. Review the actual licence before adopting it.

## Locked dependency audit

The transitive engineering inventory was refreshed on 2026-11-23 against the current locked Composer and npm manifests. It includes 77 Composer runtime packages, 34 Composer development packages and 147 npm package entries, all with declared licence metadata. This inventory is engineering evidence only; final legal/distribution review remains separate.
