Run Larastan, fix every reported error, then verify the run is clean.

## Steps

1. Run `sail composer run larastan` and collect all errors.
2. Fix each error using the patterns below.
3. Re-run `sail composer run larastan` to confirm zero errors.
4. Run `sail php artisan test` to confirm no regressions.

---

## Fix patterns

### Cannot cast `mixed` to `int`, `float`, or `string`

Never cast `mixed` directly. Guard first:

```php
// int
is_numeric($value) ? (int) $value : 0

// float
is_numeric($value) ? (float) $value : 0.0

// string
is_scalar($value) ? (string) $value : ''
```

For offset access on `mixed`:
```php
$arr = is_array($item['key'] ?? null) ? $item['key'] : [];
$val = is_numeric($arr['col'] ?? null) ? (float) $arr['col'] : 0.0;
```

### Cannot call method on `mixed` (e.g. `cache()->remember()`)

Replace the helper with the typed facade:

```php
// Before
cache()->remember($key, $ttl, fn () => ...);

// After
use Illuminate\Support\Facades\Cache;
Cache::remember($key, $ttl, fn () => ...);
```

Add `/** @var ExpectedType */` before the `Cache::remember` call if the return type is still `mixed`.

### Access to undefined property `DateTime::$year`

```php
// Before
$this->period->start_date->year

// After
(int) $this->period->start_date->format('Y')
```

### `->sum()` returns `mixed`

```php
/** @var float $total */
$total = $collection->sum(fn ($item): float => ...);
```

### `->first()` may return `null`

```php
$first = $collection->first();
assert($first !== null);
// now $first is narrowed
```

### `->pluck()` returns `Collection<int, mixed>`

```php
/** @var Collection<int, TargetType> $items */
$items = $collection->pluck('field');
```

### `->toArray()` loses the element type

Split the chain — annotate before `toArray()`:

```php
$sorted = $collection->sortByDesc('field')->values();

/** @var array<int, array{id: int, name: string}> $result */
$result = $sorted->toArray();

return $result;
```

### Computed SQL columns don't exist on the Eloquent model

When a query selects `DB::raw('SUM(...) as total_cost')`, the result rows are not typed as the model — annotate `$row` as `\stdClass` inside the closure, and annotate the outer variable with the precise array shape:

```php
/** @var array<int, array{id: int, sku: string, total: float}> $rows */
$rows = $query->get()->map(function ($row) {
    /** @var \stdClass $row */
    return [
        'id'    => (int) $row->id,
        'sku'   => is_scalar($row->sku ?? null) ? (string) $row->sku : '',
        'total' => round((float) $row->total_cost, 2),
    ];
})->toArray();
```

### `$request->input()` / `$request->route()` return `mixed`

```php
$raw = $request->input('limit', 20);
$limit = is_numeric($raw) ? (int) $raw : 20;

$raw = $request->input('cursor');
$cursor = is_string($raw) ? $raw : null;

$rawId = $request->route('client_id');
$id = is_numeric($rawId) ? (int) $rawId : 0;
```

### `@property` docblocks on FormRequest

Prefer a DTO (extending `BaseData`) over `@property` annotations on FormRequest where possible — see backend standards.

```php
/**
 * @property int $client_id
 * @property int $period_id
 * @property string $prn_fee_tier
 */
class MyRequest extends FormRequest { ... }
```

### Stale baseline entries

If the run reports `Ignored error pattern was not matched`, remove the stale entry from `phpstan-baseline.neon`.

---

## Rules

- Never use `/** @phpstan-ignore-next-line */` unless there is genuinely no correct fix.
- Never add any errors or increase the count of any errors in `phpstan-baseline.neon`
- Prefer guards (`is_numeric`, `is_array`, `is_scalar`) over annotations where they also improve runtime safety.
- Use `assert()` only for internal narrowing (post-`->first()`, post-null-check), not for external input.
