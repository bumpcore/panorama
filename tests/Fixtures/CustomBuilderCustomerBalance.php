<?php

declare(strict_types=1);

namespace Bumpcore\Panorama\Tests\Fixtures;

use Bumpcore\Panorama\HasSource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;

class CustomBuilderCustomerBalance extends Model
{
    use HasSource;

    /**
     * Indicates that this query-backed fixture does not manage timestamps.
     */
    public $timestamps = false;

    /**
     * Uses a dedicated table alias for custom builder source tests.
     */
    protected $table = 'custom_builder_customer_balances';

    /**
     * Allows tests to mass assign custom builder fixture attributes freely.
     */
    protected $guarded = [];

    /**
     * Builds a grouped invoice balance source for the optional customer.
     */
    public function newSource(?int $customer_id = null): QueryBuilder
    {
        $query = Invoice::query()->getQuery();
        $query->selectRaw('customer_id, sum(amount) as balance');

        if ($customer_id !== null) {
            $query->where('customer_id', $customer_id);
        }

        $query->groupBy('customer_id');

        return $query;
    }

    /**
     * Creates the custom Eloquent builder used by this fixture.
     *
     * @param QueryBuilder $query
     */
    #[\Override]
    public function newEloquentBuilder($query): CustomCustomerBalanceBuilder
    {
        return new CustomCustomerBalanceBuilder($query);
    }

    /**
     * Defines type casts for hydrated aggregate columns.
     *
     * @return array<string, string>
     */
    #[\Override]
    protected function casts(): array
    {
        return [
            'balance' => 'float',
        ];
    }
}
