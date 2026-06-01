<?php

declare(strict_types=1);

namespace Bumpcore\Panorama\Tests\Fixtures;

use Bumpcore\Panorama\HasSource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Tpetry\PostgresqlEnhanced\Query\Builder as EnhancedPostgresBuilder;
use Tpetry\PostgresqlEnhanced\Query\Grammar as EnhancedPostgresGrammar;

class EnhancedPostgresCustomerBalance extends Model
{
    use HasSource;

    /**
     * Indicates that this PostgreSQL enhanced fixture does not manage timestamps.
     */
    public $timestamps = false;

    /**
     * Uses a dedicated table alias for PostgreSQL enhanced source tests.
     */
    protected $table = 'enhanced_postgres_customer_balances';

    /**
     * Allows tests to mass assign PostgreSQL enhanced fixture attributes freely.
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
     * Creates the PostgreSQL enhanced base query builder for this fixture.
     */
    #[\Override]
    protected function newBaseQueryBuilder(): EnhancedPostgresBuilder
    {
        $connection = $this->getConnection();
        $grammar = new EnhancedPostgresGrammar($connection);

        return new EnhancedPostgresBuilder($connection, $grammar, $connection->getPostProcessor());
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
