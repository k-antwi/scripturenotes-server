# PensionTrack Project Memory

## Project Overview
PensionTrack is a Laravel 12 SaaS app (built on Wave framework) for finding and managing UK pensions.
Always run commands via `ddev exec` (e.g. `ddev exec php artisan ...`).

## Tech Stack
- Laravel 12 + Wave SaaS framework
- Laravel Folio (page routing via `resources/themes/anchor/pages/`)
- Livewire v3 + Alpine.js
- Tailwind CSS v4
- Filament v4 (admin panel)
- Phosphor icons (`x-phosphor-*` components)
- `internachi/modular` for modules (`app-modules/`, namespace `PensionTrack`)

## Module Structure
Modules live in `app-modules/` with namespace `PensionTrack\ModuleName\`.
After creating: run `ddev exec composer update pension-track/module-name`.

### Pension Module (`app-modules/pension/`)
- **Models**: `PensionTrack\Pension\Models\Pension`, `PensionTrack\Pension\Models\PensionProvider`
- **Livewire**: `PensionList`, `FindPension`, `AddPension`, `EditPension`
- **Views**: `pension::livewire.*` in `app-modules/pension/resources/views/livewire/`
- **Migrations**: in `app-modules/pension/database/migrations/` (auto-loaded by service provider)
- **Service Provider**: registers Folio views + Livewire components

## Theme Structure
- Theme: `anchor` at `resources/themes/anchor/`
- Pages (Folio): `resources/themes/anchor/pages/`
- Components: `resources/themes/anchor/components/`
  - `x-layouts.app` — authenticated app layout (sidebar)
  - `x-layouts.marketing` — public marketing layout
  - `x-app.container`, `x-app.heading`, `x-app.sidebar-link` — app UI primitives
- Partials: `resources/themes/anchor/partials/`

## Pension Pages (Folio)
| URL | File | Named Route |
|-----|------|-------------|
| `/pensions` | `pages/pensions/index.blade.php` | `pensions.index` |
| `/pensions/create` | `pages/pensions/create.blade.php` | `pensions.create` |
| `/pensions/find` | `pages/pensions/find.blade.php` | `pensions.find` |
| `/pensions/{id}` | `pages/pensions/[id].blade.php` | `pensions.show` |
| `/pensions/{id}/edit` | `pages/pensions/[id]/edit.blade.php` | `pensions.edit` |

## Key Patterns
- Folio theme pages use `use function Laravel\Folio\{middleware, name};` at top
- Named routes from theme Folio pages work via the fallback route (not in `folio:list`)
- Livewire components registered in `PensionServiceProvider::boot()` via `Livewire::component()`
- Module views registered with namespace `pension` → referenced as `pension::livewire.*`
- Use `@livewire('pension.component-name')` syntax in Blade pages

## Pension Model Fields
status: active | dormant | lost | found | transferred
pension_type: workplace | personal | sipp | state | other

### KYC Module (`app-modules/kyc/`)
- **Model**: `PensionTrack\Kyc\Models\KycVerification` — tracks per-user KYC status
- **Livewire**: `KycStatus` — polls every 2s, drives time-based state machine, handles failed opt-in form
- **Middleware**: `PensionTrack\Kyc\Middleware\RequireKyc` — alias `kyc`, gates all auth routes
- **Layout**: `x-layouts.kyc` — minimal full-screen layout (logo + log out only)
- **Page**: `/kyc/verify` → `pages/kyc/verify.blade.php` (named `kyc.verify`)
- **Views**: `kyc::livewire.*`
- KYC statuses: `pending` → `email_verified` → `identity_verifying` → `identity_slow` → `verified` | `failed` → `failed_submitted`
- Admin user seeded as pre-verified so they bypass KYC on fresh seed
- To apply KYC gate: add `middleware('kyc')` to Folio pages that need it
