# Nucleus Starterkit

A generic, industry-agnostic SaaS boilerplate built on Laravel 12 + Filament 4 + Wave. It implements the common **consumer + provider** two-sided model found in healthcare, legal, financial, real-estate, education, and similar industries — but with every domain-specific detail stripped out so you can fork it and add your own.

## Two-sided pattern

| Role | Description |
|---|---|
| **Admin** | Platform operator — manages all users, reviews KYC, configures settings |
| **Provider** | Credentialed second party (doctor, lawyer, advisor, etc.) — gets their own panel at `/provider` |
| **Consumer** | End-user — goes through onboarding, KYC, interacts with an AI assistant |

Roles are Spatie-permission roles. Redirect paths after login are configured in `config/auth-redirects.php`.

## Modules

| Module | Description |
|---|---|
| `providers` | Generic provider profiles with a pluggable credential verifier |
| `onboarding` | 3-step onboarding wizard (personal → address → sign); fires `OnboardingCompleted` event for forks to hook into |
| `kyc` | Identity verification — document uploads, admin review, approval email |
| `organisations` | Organisation management with Companies House lookup (UK) |
| `brain` | AI assistant — conversational agents backed by Laravel AI + Couchbase |
| `permit` | Granular RBAC via Spatie Permission, managed in the Filament admin |

## Quick start

```bash
git clone <this-repo> my-project && cd my-project

composer install
npm install

cp .env.example .env
php artisan key:generate

php artisan migrate:fresh --seed
php artisan storage:link
```

Development server (all services concurrently):

```bash
composer dev
```

Panels:
- Admin: `http://localhost:8000/admin`
- Provider: `http://localhost:8000/provider`
- Consumer onboarding: `http://localhost:8000/onboarding`

## Forking guide

### 1 — Name your roles

Edit `database/seeders/RolesTableSeeder.php` and `config/auth-redirects.php` with your domain's role names (e.g. `doctor` / `patient`, `lawyer` / `client`).

### 2 — Swap the credential verifier

By default `NullCredentialVerifier` is bound, which marks every provider as `manual_review`. Bind your own verifier in `AppServiceProvider`:

```php
$this->app->bind(
    \Nucleus\Providers\Contracts\CredentialVerifier::class,
    \App\Verifiers\MyRegistryVerifier::class,
);
```

### 3 — Extend onboarding

Listen to `\Nucleus\Onboarding\Events\OnboardingCompleted` to run industry-specific logic (auto-assign a provider, create records, send notifications, etc.):

```php
// in EventServiceProvider
OnboardingCompleted::class => [MyOnboardingListener::class],
```

### 4 — Add AI tools

Register your own agent in `config/brain.php`:

```php
'agents' => [
    'consumer' => \App\Ai\Agents\MyConsumerAgent::class,
    'provider' => \App\Ai\Agents\MyProviderAgent::class,
],
```

Use the bundled `ExampleAgent` (`app-modules/brain/src/Ai/Agents/ExampleAgent.php`) as a reference.

### 5 — Add industry-specific fields

Use the `metadata` JSON column on `ProviderProfile` and `OnboardingSubmission` for extra attributes rather than adding columns — this keeps the base schema forward-compatible across forks.

## Configuration

| File | Purpose |
|---|---|
| `config/auth-redirects.php` | Post-login redirect per role |
| `config/panels.php` | Panel paths, brand names, primary colors |
| `config/brain.php` | AI agent class map, Couchbase connection |
| `config/onboarding.php` | Document approval toggle, completion redirect |
| `config/providers.php` | Credential verifier class, auto-verify toggle |

## Tech stack

- **Backend:** PHP 8.3+, Laravel 12, Livewire 3
- **Frontend:** Alpine.js, Tailwind CSS 4, Vite
- **Admin panel:** Filament 4
- **Database:** SQLite (default), Couchbase (AI conversation storage)
- **AI:** Laravel AI SDK with custom agents and tools
- **Payments:** Stripe (via Wave)
- **Testing:** Pest + PHPUnit

## External integrations

- **Companies House API** — Company lookups (UK; swap or remove if not relevant)
- **Address lookup** — UK postcode search via GetAddress / Ideal Postcodes (optional)
- **Stripe** — Subscription billing
- **Couchbase** — AI conversation persistence (falls back to relational DB if unavailable)

## Environment variables

```env
# Address lookup (optional)
GETADDRESS_API_KEY=

# Companies House (optional — UK only)
COMPANIES_HOUSE_API_KEY=

# Couchbase (optional — falls back to DB)
COUCHBASE_CONNECTION_STRING=couchbase://localhost
COUCHBASE_BUCKET=brain_conversations
COUCHBASE_USERNAME=Administrator
COUCHBASE_PASSWORD=password

# Stripe
STRIPE_KEY=
STRIPE_SECRET=
```

## License

MIT
