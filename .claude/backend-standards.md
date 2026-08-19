## PHP tooling

- Target PHP 8.1+ and Laravel 10 conventions.
- Use `declare(strict_types=1);` in PHP files where applicable.
- Follow Laravel Pint via `pint.json` and the Laravel preset.
- Keep PSR-12 style.
- Use typed parameters and return types wherever possible.
- Run `sail composer run lint` to check style and `sail composer run lint:fix` to fix it.
- Run `sail composer run larastan` for static analysis on backend changes.

## Collections

- Prefer `Collection` methods over native PHP array functions (`array_map`, `array_filter`, `array_values`, `array_reduce`, etc.).
- Import and use `Illuminate\Support\Collection` explicitly; fall back to the `collect()` global helper only when a class-level import is not practical.
- Return `Collection<TKey, TValue>` from methods when the caller will chain further collection operations; return `array` only when the call site requires a plain array.
- Use `->toJson()` instead of `json_encode()` when serialising a collection.

## PHPStan: typing collections and generics

When PHPStan reports errors caused by untyped or mixed-typed collections, prefer `@phpstan-type` local aliases over scattering `@var` annotations through the code:

1. Define the element shape once on the class with `@phpstan-type`, and optionally a second alias for the collection itself. Use the FQCN inside the alias body — PHPStan does not resolve `use` imports there.

```php
/**
 * @phpstan-type MaterialEntry array{material: string, total_weight_of_sales_kgs: mixed}
 * @phpstan-type MaterialCollection \Illuminate\Support\Collection<int, MaterialEntry>
 */
class MyClass
{
    /** @var MaterialCollection */
    protected Collection $items;
```

2. When assigning to a typed property from an Eloquent `map()` or similar chain, PHPStan's invariant generic check will reject the assignment even when the types look identical. Assign through a local variable with a `@var` assertion to satisfy the invariance check:

```php
/** @var MaterialCollection $items */
$items = SomeModel::query()->get()->map(fn ($row) => [...]);
$this->items = $items;
```

This is the preferred pattern. Do not add stray `@var` casts inside closures or sprinkle `(string)` casts to work around `mixed` — fix the type at the source instead.

## PHP conventions

- Keep business logic out of controllers when it belongs in services, actions, or domain classes.
- Use the existing `Domain\...` namespace for domain logic where appropriate.
- Prefer dependency injection and container-bound services over manual wiring.
- Avoid raw SQL queries unless explicitly approved.
- Prefer clean Laravel model APIs such as `Model::insert(...)` and `Model::upsert(...)` over extra builder noise like `newQuery()` when both are equivalent.
- Keep concerns separated: DTOs, services, actions, imports, and persistence layers each have a clear responsibility.
- When shared behavior is needed across multiple flows, prefer a dedicated service or support class over duplicating logic or coupling unrelated classes together.
- New HTTP create and update paths must use DTOs instead of passing raw request arrays into actions.
- New `MappedHeaderImport*` classes that extend `MappedHeaderImportBase` do **not** need to call `SanitizeInputPayload::execute()` — sanitization happens automatically inside `getMappingValue()`. Do not add explicit `execute()` calls in these classes.
- Import classes that access row values directly (not via `getMappingValue()`) must call `SanitizeInputPayload::execute()` or `sanitizeValue()` explicitly before writing to the database. See the CatalogueUpdate classes for examples.

### Domain Types Over Primitives

Prefer passing domain objects when that makes the method intent clearer. Do not pass IDs when the full domain model is the real input. For example, `processSale(Sale $sale)` is preferable to `processSale(int $saleId)`.

### Laravel Models

Use `@property` annotations for model fields rather than narrative prose lists in the class docblock. Annotate typed fields as `@property int $id`, `@property string $name`, etc. Annotate important relationships as `@property-read Client $client`. Do not write free-form descriptions of keys or field groups.

## PHPDoc

- Do not add redundant PHPDoc that repeats scalar type hints already present in the signature.
- Use PHPDoc where it adds information the signature cannot express: array shapes (`array<string>`), collection shapes, union return types, and non-obvious return structures.

## Input Sanitization

All string values entering the system are sanitized with `trim(strip_tags($value))` before persistence. Three layers exist — use the one that matches the write path:

**Layer 1 — DTO pipeline (HTTP requests)**
- Handled automatically by `SanitizeRequestStringValuesDataPipe` inside `BaseData::pipeline()`.
- Applies to any DTO that extends `BaseData` when instantiated from an HTTP `Request`.
- No manual sanitization needed in controllers or actions.

**Layer 2 — `getMappingValue()` (mapped import classes)**
- Handled automatically by `MappedHeaderImportBase::getMappingValue()`.
- Applies to all classes that extend `MappedHeaderImportBase` and read row values through `getMappingValue()`.
- Do **not** wrap payloads in `SanitizeInputPayload::execute()` in these classes — values are already sanitized.

**Layer 3 — Explicit `SanitizeInputPayload` call (direct row access)**
- Required when an import class reads `$row[$column]` directly without going through `getMappingValue()`.
- Examples: `ProductCatalogueUpdate`, `SupplierCatalogueUpdate`, `ComponentCatalogueUpdate`, `ComponentWithColumnsMappedHeaderImportToSheet`, `ProductDataImport`.
- Call `SanitizeInputPayload::execute([...])` for arrays or `SanitizeInputPayload::sanitizeValue($value)` for individual fields before any write.

**Core utilities:**
- `src/Domain/Shared/Support/SanitizeInputPayload.php` — `execute(array): array`, `sanitizeValue(mixed): mixed`
- `src/Domain/Shared/DataPipes/SanitizeRequestStringValuesDataPipe.php` — DTO pipeline pipe
- `src/Domain/Client/Imports/Shared/MappedHeaderImportBase.php` — `getMappingValue()` choke point

## Roles and Permissions

- Roles and permissions are managed via `spatie/laravel-permission`.
- Add new permissions in a migration — do not seed or hardcode them outside of migrations.
- Prefer gate checks on the route definition (e.g. `->middleware('can:permission-name')`) over controller-level checks.

## Routing and Endpoints

- All routes (both Inertia pages and JSON API endpoints) are defined in `routes/web.php`.
- JSON responses use `response()->json(...)` directly — no Laravel Resources or ResourceCollections.
- Wrap the primary payload in a `data` key: `['data' => $collection]`. For paginated results include a `total` key alongside: `['data' => $records, 'total' => $count]`.
- Use `message` for plain status responses and `error` for error responses.
- This is the current dominant convention — if Laravel Resources become the team standard, update this section accordingly.

## Numeric Precision: Floats vs BCMath

- Avoid using floating-point numbers for anything requiring exact precision (e.g. money), as they introduce rounding errors due to binary representation. Use BCMath (or equivalent JS) with string inputs to ensure deterministic, accurate decimal calculations.

## Migrations

- Never modify an existing migration. Always create a new migration to apply schema changes.
- Give migrations a descriptive name that reflects the schema change, not a ticket number.

## Jobs And Batch Processing

- Use named constants for batch sizes, timeouts, and similar operational values.
- Prefer batch-oriented Laravel solutions over row-by-row updates when working with large imports.
- If a bulk operation is update-only and Laravel does not provide a true bulk update API, use the cleanest Laravel-native approach available unless told otherwise.

## Performance

- Use cursor pagination for large data sets.
- Eager load relationships when accessing them across a collection — use `with()` to prevent N+1 queries.
