<?php

declare(strict_types=1);

namespace BumpCore\Panorama\Tests\Fixtures;

use BumpCore\Panorama\BackedByQuery;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Staudenmeir\LaravelCte\Query\Builder as CteBuilder;

final class CteCustomerBalance extends Model
{
    use BackedByQuery;

    public $timestamps = false;

    protected $table = 'cte_customer_balances';

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

    protected function newBaseQueryBuilder(): CteBuilder
    {
        $connection = $this->getConnection();

        return new CteBuilder($connection, null, $connection->getPostProcessor());
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
