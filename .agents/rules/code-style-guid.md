---
trigger: always_on
---

# Clarion Internal Tool — Agent Guide

## Project Overview

Full-stack web application built with Laravel 10 (PHP 8.1+) and Vue 3 (TypeScript). Uses Inertia.js as the bridge between backend and frontend, although all new functionality should use JSON API endpoints.

## Tech Stack

- **Backend**: Laravel 10, PHP 8.1+, Inertia.js
- **Frontend**: Vue 3, TypeScript, Vite, TailwindCSS, Pinia
- **Database**: PostgreSQL (primary), Redis (cache/queues)
- **Real-time**: Pusher
- **Dev environment**: Laravel Sail (Docker) — all backend commands must use `sail`

## Commands

```bash
# Frontend
npm run dev              # Vite dev server
npm run build            # Production build
npm run typecheck        # TypeScript check (vue-tsc --noEmit)
npm run lint             # ESLint
npm run pretty           # Prettier auto-fix
npm run pretty:check     # Prettier check only
npm run test             # Vitest watch
npm run test:ci          # Vitest single run

# Backend
sail composer run lint        # Pint check (read-only)
sail composer run lint:fix    # Pint auto-fix
sail composer run larastan    # PHPStan static analysis
sail php artisan test         # PHP test suite (Pest)

# Full suite
./scripts/pre-commit-checks.sh --full
```

## Standards

### Working approach

- Inspect the surrounding code and repository conventions before making changes.
- Preserve existing user changes. Do not revert unrelated diffs.
- Prefer small, targeted edits over broad rewrites.
- Use ASCII by default unless the file already requires Unicode.
- Add comments sparingly. Prefer self-documenting code and only comment non-obvious intent.
- Use non-interactive git commands only.

### File formatting

- Follow `.editorconfig`.
- Use UTF-8, LF endings, a final newline, and trim trailing whitespace.
- Use 4 spaces by default.
- Use 2 spaces for `*.js`, `*.ts`, `*.vue`, `*.yml`, and `*.yaml`.
- Do not trim trailing whitespace in Markdown files.

### PHP

- Target PHP 8.1+ and Laravel 10 conventions.
- Use `declare(strict_types=1);` in PHP files where applicable.
- Follow Laravel Pint via `pint.json` and the Laravel preset.
- Keep PSR-12 style.
- Use typed parameters and return types wherever possible.
- Run `sail composer run lint` to check style and `sail composer run lint:fix` to fix it.
- Run `sail composer run larastan` for static analysis on backend changes.

### TypeScript, JavaScript, and Vue

- TypeScript is strict. Avoid implicit `any` and handle `null` and `undefined` explicitly.
- Use the `@/` alias for imports from `resources/js`.
- Follow ESLint flat config for Vue + TypeScript and Standard-style rules.
- Always use TypeScript for new frontend code. JavaScript is allowed for legacy areas but may be converted for extensive changes.
- Run `npm run typecheck` for frontend type safety.
- Run `npm run test:ci` for frontend test verification when frontend behavior changes.
- Run `npm run pretty` to format frontend files

### Validation and hooks

- Prefer repository commands over ad hoc alternatives.
- Use Sail-prefixed commands for backend code. Frontend commands can be run with or without sail
- The repo-managed pre-commit flow is `./scripts/pre-commit-checks.sh --full`.
- Relevant checks include:
    - `sail composer run lint`
    - `sail composer run larastan`
    - `sail php artisan test`
    - `npm run typecheck`
    - `npm run test:ci`
    - `npm run pretty`

### Self-documenting Code

- Code should communicate intent through precise naming, clear structure, and expressive types so that comments explaining what the code does are rarely needed.
- Prefer intention-revealing method names, small focused functions, and domain types over primitives; reserve comments for explaining why a decision exists, not how the code works. If you find yourself adding comments, stop and ask why.
- Don't document parameters or return types already specified. Where documentation is necessary, use strictly PHPDoc types.

### General code style

- Prefer intention-revealing names over explanatory comments.
- Use verb-led function names.
- Name booleans so they read as predicates, with prefixes `is`, `has`, `can`, `should`, `does`, `did`, or `will`.
- Prefer that all other variables and functions read as predicates
- Keep imports at the top of the file.
- Remove unused imports and dead code.
- Match existing architecture instead of introducing new patterns without a reason.

### PHP conventions

- Keep business logic out of controllers when it belongs in services, actions, or domain classes.
- Use the existing `Domain\\...` namespace for domain logic where appropriate.
- Prefer dependency injection and container-bound services over manual wiring.
- Keep tests explicit and readable, with focused setup and concrete assertions.

### Vue and frontend conventions

- Always use Vue 3 Composition API for new code, typically with composables named `use...`.
- Always use TypeScript for new code
- Preserve existing Options API components/vanilla JS unless there is a clear reason to migrate them.
- Use PascalCase for component filenames.
- Keep shared UI in `resources/js/Components` and feature code grouped under `resources/js/Composables` or other existing domain folders.
- Continue using Tailwind utility classes in templates.
- Prefer explicit types for props, helper inputs, and exported values in TypeScript files.
- Use small composables for reusable frontend behavior.
- Props must be explicitly typed
- State management via Pinia
- Boolean props follow the same rules as boolean variables
- Event handlers are onElementEvent, where Element is a logical identifier and Event is the event type

### Testing conventions

- Backend tests live under `tests/Unit` and `tests/Feature`.
- Frontend tests commonly sit next to the code they verify, for example `*.spec.ts`.
- Test names should describe behavior clearly. Always use Given/When/Then phrasing.
- Use 'test' function calls instead of 'it' function calls

### Roles and Permissions

- Roles and permissions are managed via `spatie/laravel-permission`.
- Add new permissions in a migration — do not seed or hardcode them outside of migrations.
- Prefer gate checks on the route definition (e.g. `->middleware('can:permission-name')`) over controller-level checks.

### Routing and Endpoints

- All routes (both Inertia pages and JSON API endpoints) are defined in `routes/web.php`.
- Prefer model route binding over manual ID fetching
- There is currently no enforced standard for JSON response structure — follow the shape of existing endpoints in the same area.

### Laravel Request Validation

- Use enums validation where applicable
- Use seperated validation arguments instead of a single string

### Pagination

- Never paginate in-memory, use database pagination

### Numeric Precision: Floats vs BCMath

- Avoid using floating-point numbers for anything requiring exact precision (e.g. money), as they introduce rounding errors due to binary representation. Use BCMath (or equivalent JS) with string inputs to ensure deterministic, accurate decimal calculations.

### Performance

- Use cursor pagination

## Source Of Truth

If guidance here becomes stale, prefer the repository's actual tooling and config files:

- `.editorconfig`
- `pint.json`
- `phpstan.neon.dist`
- `eslint.config.js`
- `prettier.config.ts`
- `tsconfig.json`
- `scripts/pre-commit-checks.sh`
- `README.md`

## Directory Structure

```
app/                  # Laravel backend (Controllers, Models, Jobs, Events...)
resources/js/         # Vue frontend
  Components/         # Shared/reusable components
  Modules/            # Feature modules (self-contained)
  Pages/              # Inertia page components
  Stores/             # Pinia stores
  Composables/        # Vue composables
  Types/              # Shared TypeScript types
resources/views/      # Blade templates (minimal — mainly the Inertia root)
routes/               # web.php, api.php, channels.php
database/
  migrations/         # Schema migrations
  seeders/            # Database seeders
tests/
  Feature/            # Laravel feature tests
  Unit/               # Laravel unit tests
src/Domain/           # Domain logic (separate namespace)
```

## Environment

- App URL: `http://localhost`
- Mailpit (email): `http://localhost:8025`
- Database: PostgreSQL on port 5432
- Redis: port 6379
- Seeded logins: `admin@example.com / admin`, `data@example.com / data`
