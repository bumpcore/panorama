<?php

declare(strict_types=1);

namespace Bumpcore\Panorama\Tests\Fixtures;

use Bumpcore\Panorama\HasSource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Staudenmeir\LaravelCte\Query\Builder as CteBuilder;

class CteCustomerBalance extends Model
{
    use HasSource;

    /**
     * Indicates that this CTE fixture does not manage timestamps.
     */
    public $timestamps = false;

    /**
     * Uses a dedicated table alias for CTE source tests.
     */
    protected $table = 'cte_customer_balances';

    /**
     * Allows tests to mass assign CTE fixture attributes freely.
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
     * Creates the CTE-aware base query builder for this fixture.
     */
    #[\Override]
    protected function newBaseQueryBuilder(): CteBuilder
    {
        $connection = $this->getConnection();

        return new CteBuilder($connection, null, $connection->getPostProcessor());
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
