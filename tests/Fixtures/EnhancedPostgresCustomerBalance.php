<?php

declare(strict_types=1);

namespace BumpCore\Panorama\Tests\Fixtures;

use BumpCore\Panorama\BackedByQuery;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Tpetry\PostgresqlEnhanced\Query\Builder as EnhancedPostgresBuilder;
use Tpetry\PostgresqlEnhanced\Query\Grammar as EnhancedPostgresGrammar;

final class EnhancedPostgresCustomerBalance extends Model
{
    use BackedByQuery;

    public $timestamps = false;

    protected $table = 'enhanced_postgres_customer_balances';

    protected $guarded = [];

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

    protected function newBaseQueryBuilder(): EnhancedPostgresBuilder
    {
        $connection = $this->getConnection();
        $grammar = new EnhancedPostgresGrammar($connection);

        return new EnhancedPostgresBuilder($connection, $grammar, $connection->getPostProcessor());
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'balance' => 'float',
        ];
    }
}
