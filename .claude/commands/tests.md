Write or update tests for the described behaviour, then verify the suite is green.

## Usage

`/tests [description of what to test]`

If no description is given, infer from recent code changes (e.g. `git diff HEAD~1`) what needs coverage.

## Steps

1. Identify whether the target is backend (PHP/Laravel) or frontend (Vue/TypeScript), or both.
2. Read the code under test in full before writing anything.
3. Check existing tests for the same file or feature — extend them rather than duplicating setup.
4. Write the tests following the rules below.
5. Run the relevant suite to verify everything passes:
   - Backend: `sail php artisan test --filter <TestClass>`
   - Frontend: `npm run test:ci`
6. Fix any failures, then run the full suite to check for regressions:
   - Backend: `sail php artisan test`
   - Frontend: `npm run test:ci`

---

## Definitions

### Feature/integration tests

- Test from the entry point (controller or page component)
- Mock **only** what cannot run in pest/jsdom or code that sits outside the codebase
- Aim to test final side effects, eg, database changes, text rendering, etc
- Prefer over unit tests for most use-cases

### Unit tests

- Test a single unit or coupled set of units
- Used when testing heavy or non-trivial logic
- Mock what is necessary

## Backend tests (Pest / Laravel)

### File placement

- Feature tests → `tests/Feature/<Domain>/`
- Unit tests → `tests/Unit/`
- New test files use the `.pest.php` extension and top-level `test()` calls.
- Existing class-based `.php` files may be extended as-is.

### Naming

All test names must follow the pattern exactly:

```
'Given [context] When [action] Then [expected outcome]'
```

```php
test('Given an unauthenticated user When they visit the dashboard Then they are redirected to login', function () {
    $response = $this->get('/dashboard');
    $response->assertRedirect('/login');
});
```

- Use `test()`, never `it()`.
- Names are sentence-case prose — no underscores in the string.

### Setup and teardown

- Use `uses(RefreshDatabase::class)` at the top of Pest files that hit the database.
- Use `beforeEach()` / `afterEach()` for shared state.
- Prefer factories over raw `DB::insert()`.

### Assertions

- Prefer Laravel/PHPUnit-style assertions: `$response->assertStatus(200)`, `$this->assertDatabaseHas(...)`, `$this->assertTrue(...)`.
- Avoid fluent `expect(...)` unless the assertion has no PHPUnit equivalent.

### Jobs

- Use `dispatchSync($job)` to run a job synchronously in a test — do not call `$job->handle()` directly.

### Faking

- `Queue::fake()` / `Event::fake()` / `Notification::fake()` are fine.
- Do not mock the database. Use `RefreshDatabase` and real queries.
- Do not mock Eloquent models.

### Example feature test

```php
<?php

declare(strict_types=1);

use Domain\Shared\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('Given a valid user When they submit the login form Then they are redirected to the dashboard', function () {
    $user = User::factory()->create();

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertRedirect('/dashboard');
    $this->assertAuthenticatedAs($user);
});
```

---

## Frontend tests (Vitest)

### File placement

Tests live **next to the code they verify**:

- `resources/js/Composables/useFoo.spec.ts` for composables
- `resources/js/Components/Bar/Baz.spec.ts` for components
- `resources/js/Stores/useFooStore.spec.ts` for Pinia stores

### Naming

Same Given/When/Then pattern, inside a `describe` block named after the unit:

```ts
describe("usePeriodFormatter", () => {
  test("Given a valid period string When subdivision is read Then it returns the correct value", () => {
    ...
  });
});
```

- Use `test()`, never `it()`.

### Mocking rules

- Mock **only** what cannot run in jsdom: browser APIs (`IntersectionObserver`, `ResizeObserver`, `matchMedia`), Inertia's `usePage` / `router`, and network calls (`fetch`, `axios`).
- Mount real child components where possible — stub layout wrappers and unrelated heavy components only.
- Use `vi.hoisted()` for mock factories that need to be evaluated before imports:

```ts
const { usePageMock } = vi.hoisted(() => ({
  usePageMock: vi.fn(),
}));

vi.mock("@inertiajs/vue3", async () => {
  const actual = await vi.importActual<typeof import("@inertiajs/vue3")>("@inertiajs/vue3");
  return { ...actual, usePage: usePageMock };
});
```

- Always call `vi.restoreAllMocks()` in `afterEach`.

### Pinia stores

```ts
import { createPinia, setActivePinia } from "pinia";

beforeEach(() => {
  setActivePinia(createPinia());
});
```

### Component rendering

Use `@testing-library/vue`:

```ts
import { render, screen } from "@testing-library/vue";

test("Given props are set When the component renders Then the label is visible", () => {
  render(MyComponent, { props: { label: "Hello" } });
  expect(screen.getByText("Hello")).toBeInTheDocument();
});
```

### DOM matching

Use `testing-library/jest-dom`:

```ts
import { render, screen } from "@testing-library/vue";

test("Given props are set When the component renders Then the button is enabled", () => {
  render(MyComponent, { props: { label: "Hello" } });
  expect(screen.getByText("Hello")).toBeEnabled();
});
```

### Composable testing

Prefer the testing of the components that consume composables/stores, reserving composable tests for heavy and/or non-trivial logic

```ts
import { describe, test, expect, afterEach, vi } from "vitest";
import { useMyComposable } from "./useMyComposable";

describe("useMyComposable", () => {
  afterEach(() => {
    vi.restoreAllMocks();
  });

  test("Given initial state When value is read Then it returns the default", () => {
    const { value } = useMyComposable();
    expect(value.value).toBe(null);
  });
});
```

---

## Rules

- Never write a test that always passes regardless of the implementation.
- Cover the happy path and the most likely failure paths — do not aim for exhaustive edge-case coverage unless the domain demands it.
- Keep setup minimal and inline — only extract shared setup when three or more tests repeat the exact same code.
- Do not add explanatory comments inside tests; the Given/When/Then name carries the intent.
- Do not test framework behaviour (e.g. that Laravel redirects unauthenticated users) — only test application-specific logic.
