## Bumpcore Panorama

Panorama provides query-backed Eloquent models. Use it when a model should keep
normal Eloquent querying, scopes, relations, eager loading, and builder chaining,
but its rows should come from a subquery, aggregate, CTE, or custom query builder
source instead of a concrete model table.

### Core Usage

- Add `Bumpcore\Panorama\HasSource` to the Eloquent model.
- Override `newSource()` and return either an `Illuminate\Database\Eloquent\Builder`
  or an `Illuminate\Database\Query\Builder`.
- Query the model normally with `Model::query()`; Panorama replaces the model
  query's `from` clause with the source query only when the Eloquent builder is
  applied.
- Use `withParams()` to pass source parameters. Parameters must use string keys
  because they are passed to `newSource()` as named arguments.
- Override `querySourceAlias()` only when the wrapped source needs an alias other
  than the model table name. The alias must not be empty.

### Example

@verbatim
<code-snippet name="Query-backed Eloquent model" lang="php">
use Bumpcore\Panorama\HasSource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class CustomerBalance extends Model
{
    use HasSource;

    protected $primaryKey = 'customer_id';

    public function newSource(int $customer_id, string $status): Builder
    {
        return DB::table('invoices')
            ->selectRaw('customer_id, sum(amount) as balance')
            ->where('customer_id', $customer_id)
            ->where('status', $status)
            ->groupBy('customer_id');
    }
}

$balance = CustomerBalance::query()
    ->withParams(customer_id: 1, status: 'open')
    ->where('balance', '>', 100)
    ->first();
</code-snippet>
@endverbatim

### Best Practices

- Keep `newSource()` focused on building the backing query; put consumer filters
  on the normal Eloquent query.
- Prefer named `withParams()` arguments for readability.
- Preserve normal Eloquent model configuration such as `$primaryKey`, casts,
  relations, local scopes, and custom Eloquent builders.
- Add tests for generated SQL/bindings when changing source wrapping behavior,
  and add relation/eager-loading tests when using query-backed models as
  relation targets.
