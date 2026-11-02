# bumpcore Panorama

bumpcore Panorama is a Laravel package for building Eloquent models from query
sources. It lets a model behave like a normal read model while its rows come
from a subquery, aggregate, CTE, or custom query builder source.

Use it when you need query-backed models for reports, projections, aggregate
tables, read-only relation targets, or custom database features without giving
up Eloquent relations and builder chaining.

## Table Of Contents

* [Version Table](#version-table)
* [Installation](#installation)
* [Quick Start](#quick-start)
* [HasSource](#hassource)
  + [Defining a Source](#defining-a-source)
  + [Passing Source Params](#passing-source-params)
  + [Changing the Source Alias](#changing-the-source-alias)
* [Eloquent Compatibility](#eloquent-compatibility)
* [Exceptions](#exceptions)
* [Testing](#testing)
* [Contribution](#contribution)
* [Changelog](#changelog)
* [Credits](#credits)
* [License](#license)

## Version Table

| bumpcore Panorama | Laravel       | PHP           |
|-------------------|---------------|---------------|
| 0.x               | ^12.0         | ^8.2          |
| 0.x               | ^13.0         | ^8.3          |

## Installation

Install the package with Composer:

```bash
composer require bumpcore/panorama
```

## Quick Start

Add the `HasSource` trait to an Eloquent model and define `newSource()`.

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
            ->selectRaw('customer_id, sum(amount) as balance')
            ->where('customer_id', $customer_id)
            ->groupBy('customer_id');
    }
}

$balances = CustomerBalance::query()
    ->where('balance', '>', 0)
    ->withParams(customer_id: $customer->getKey())
    ->get();
```

The model is still queried through Eloquent. Panorama only replaces the model's
`from` clause with the source query when the builder is executed.

## HasSource

`HasSource` registers a global scope and a local `withParams()` scope on the
model. The global scope wraps the model query in the source returned by
`newSource()`.

### Defining a Source

`newSource()` may return an Eloquent builder or a base query builder.

```php
use Bumpcore\Panorama\HasSource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class OpenInvoiceTotal extends Model
{
    use HasSource;

    public function newSource(): Builder
    {
        return Invoice::query()
            ->selectRaw('customer_id, sum(amount) as open_total')
            ->where('status', 'open')
            ->groupBy('customer_id');
    }
}
```

The source is not expected to hit a real table with the model name. It becomes
the derived table Panorama queries from.

### Passing Source Params

Use `withParams()` to pass named arguments to `newSource()`.

```php
$balance = CustomerBalance::query()
    ->withParams(customer_id: 1, status: 'open')
    ->first();
```

Array params are also supported:

```php
$balance = CustomerBalance::query()
    ->withParams([
        'customer_id' => 1,
        'status' => 'open',
    ])
    ->first();
```

Params must use string keys because they are passed as named arguments. Later
calls replace earlier values, just like normal builder state.

### Changing the Source Alias

By default, Panorama uses the model table name as the SQL alias for the wrapped
source. Override `querySourceAlias()` when the source should use a different
alias.

```php
class CustomerBalance extends Model
{
    use HasSource;

    public function querySourceAlias(): string
    {
        return 'customer_balances';
    }
}
```

The alias must not be an empty string.

## Eloquent Compatibility

Panorama keeps the consumer-facing Eloquent builder intact. You can still use:

* normal Eloquent builder methods;
* local scopes;
* relations and eager loading;
* `whereHas()` against query-backed relation models;
* custom Eloquent builders;
* external query builder extensions.

The test suite includes compatibility coverage for:

* `staudenmeir/laravel-cte`
* `tpetry/laravel-postgresql-enhanced`
* consumer-defined custom Eloquent builders

## Exceptions

All package-specific failures extend:

```php
Bumpcore\Panorama\Exceptions\PanoramaException
```

More specific exceptions are available:

* `InvalidQuerySourceException`
* `MissingQuerySourceException`

`InvalidQuerySourceException` is thrown when a model does not use the required
trait, when `newSource()` returns an unsupported value, or when the source alias
is empty.

`MissingQuerySourceException` is thrown when a model uses `HasSource` without
overriding `newSource()`.

## Testing

Install development dependencies:

```bash
composer install
```

Run the test suite:

```bash
composer test
```

Run static analysis:

```bash
composer analyse
```

Check code style:

```bash
composer cs:check
```

Run the 100% coverage gate:

```bash
composer test:coverage
```

Coverage requires PCOV, Xdebug, or phpdbg.

## Contribution

Contributions are welcome. If you find a bug or have a suggestion for
improvement, please open an issue or create a pull request.

Please include tests for behavioral changes and run the quality checks before
submitting a pull request:

```bash
composer cs:check
composer analyse
composer test
```

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for version history.

## Credits

* [Abdulkadir Cemiloglu](https://github.com/megastive19)
* [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
