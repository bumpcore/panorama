# Panorama

Panorama gives Eloquent models a query-backed source. Add one trait, define a
`newSource()` method, and the model can be queried like a normal read model
while its data comes from a subquery.

```php
use Bumpcore\Panorama\HasSource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class CustomerBalance extends Model
{
    use HasSource;

    protected $primaryKey = 'customer_id';

    public function newSource(int $customer_id): Builder
    {
        return DB::table('invoices')
            ->selectRaw('customer_id, sum(balance) as balance')
            ->where('customer_id', $customer_id)
            ->groupBy('customer_id');
    }
}

$balances = CustomerBalance::query()
    ->where('balance', '>', 0)
    ->withParams(customer_id: $customer->getKey())
    ->get();
```

`withParams()` behaves like an Eloquent query scope: it can be called anywhere
in the chain before execution. The params are passed as named arguments to
`newSource()`.

Panorama does not replace Eloquent's builder. It registers a normal global scope
and a normal local scope, so custom builders and query-builder extensions can
still participate in the query. The test suite includes compatibility coverage
for:

- `staudenmeir/laravel-cte`
- `tpetry/laravel-postgresql-enhanced`
- consumer-defined custom Eloquent builders

## Requirements

Panorama targets Laravel versions that are currently supported:

- Laravel 12 on PHP 8.2 through 8.5
- Laravel 13 on PHP 8.3 through 8.5

The package constraint is `illuminate/database:^12.0 || ^13.0` and `php:^8.2`.

## Development

```bash
composer install
composer test
composer test:coverage
composer analyse
composer cs:check
```
