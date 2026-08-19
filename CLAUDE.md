# CLAUDE.md

This file provides guidance to Claude Code when working in this repository.

## Overview

Nucleus Starterkit is a generic consumer + provider SaaS boilerplate built on Laravel 12, Filament 4, and the Wave framework. It is intentionally domain-agnostic. Domain-specific modules have been stripped; the remaining modules (brain, kyc, onboarding, organisations, permit, providers) are industry-neutral and meant to be extended by forks.

## Development Commands

```bash
composer dev          # Start server + queue + logs + Vite concurrently
php artisan serve     # Laravel dev server only
npm run dev           # Vite only
npm run build         # Production build
```

```bash
php artisan migrate:fresh --seed   # Reset and seed (safe in dev)
php artisan test                   # Run all Pest tests
vendor/bin/pest                    # Pest directly
php artisan queue:work             # Process queued jobs
```

## Architecture

### Modules (`app-modules/`)

Each module is a local Composer package discovered via `internachi/modular`. PSR-4 namespace: `Nucleus\{ModuleName}`.

| Module          | Namespace               | Purpose                                                       |
| --------------- | ----------------------- | ------------------------------------------------------------- |
| `brain`         | `Nucleus\Brain`         | Conversational AI (Laravel AI SDK + Couchbase fallback to DB) |
| `kyc`           | `Nucleus\Kyc`           | Document upload + admin KYC review                            |
| `onboarding`    | `Nucleus\Onboarding`    | 3-step onboarding wizard; fires `OnboardingCompleted` event   |
| `organisations` | `Nucleus\Organisations` | Organisation records + Companies House lookup                 |
| `permit`        | `Nucleus\Permit`        | Granular RBAC via Spatie Permission                           |
| `providers`     | `Nucleus\Providers`     | Provider profiles, credential verification                    |
| `themeengine`   | `Nucleus\Themeengine`   | Chat UI that generates theme specs via the `brain` module      |

### Panels (Filament)

- `AdminPanelProvider` → `/admin` (admin role)
- `ProviderPanelProvider` → `/provider` (provider role)
- Both extend `BasePanelProvider`; soft config in `config/panels.php`

### Extension points

- **Credential verification**: bind your own `\Nucleus\Providers\Contracts\CredentialVerifier` implementation in `AppServiceProvider`. Default is `NullCredentialVerifier` (returns `manual_review`).
- **Post-onboarding hooks**: listen to `\Nucleus\Onboarding\Events\OnboardingCompleted`.
- **AI agents**: register role → agent class in `config/brain.php → agents`.
- **Theme generation**: the theme chat prompts the `theme` role through `BrainService::promptAs()`. Override `config/brain.php → agents.theme`, or point `config/themeengine.php → agent_role` at a different role, to swap in your own agent.
- **Extra profile fields**: use the `metadata` JSON column on `ProviderProfile` and `OnboardingSubmission`.

### Role redirects

`config/auth-redirects.php` controls where users land after login, checked in declared order (first matching role wins). Default fallback: `/onboarding`.

## Key Config Files

| File                        | Controls                                       |
| --------------------------- | ---------------------------------------------- |
| `config/auth-redirects.php` | Post-login redirects per role                  |
| `config/panels.php`         | Panel paths + brand + primary color            |
| `config/brain.php`          | AI agent class map + Couchbase config          |
| `config/themeengine.php`    | Theme agent role/class + prompt length limit   |
| `config/onboarding.php`     | Document approval toggle + completion redirect |
| `config/providers.php`      | Credential verifier class + auto-verify toggle |
| `config/wave.php`           | Wave/billing settings                          |

## Performance (inherited from Wave)

- User/plan/category data is cached (5–60 min depending on type).
- Clear user caches: `$user->clearUserCache()`
- Clear plan caches: `Plan::clearCache()`

## Activity Logging

```php
ActivityLog::log('event_name', 'Description', ['key' => 'value']);
```

## Project Overview

## Tech Stack

- **Backend**: Laravel 10, PHP 8.1+, Inertia.js
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

<!-- @.claude/shared-standards.md
@.claude/backend-standards.md -->

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
app-modules/          # Feature modules (self-contained)
resources/views/      # Blade templates (minimal — mainly the Inertia root)
routes/               # web.php, api.php, channels.php
database/
  migrations/         # Schema migrations
  seeders/            # Database seeders
tests/
  Feature/            # Laravel feature tests
  Unit/               # Laravel unit tests
```

## Environment

- App URL: `http://localhost`
- Mailpit (email): `http://localhost:8025`
- Database: PostgreSQL on port 5432
- Redis: port 6379
- Seeded logins: `admin@admin.com / password`,
