<?php

declare(strict_types=1);

namespace BumpCore\Panorama\Tests\Fixtures;

use BumpCore\Panorama\BackedByQuery;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;

final class CustomBuilderCustomerBalance extends Model
{
    use BackedByQuery;

    public $timestamps = false;

    protected $table = 'custom_builder_customer_balances';

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

    /**
     * @param QueryBuilder $query
     */
    public function newEloquentBuilder($query): CustomCustomerBalanceBuilder
    {
        return new CustomCustomerBalanceBuilder($query);
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
