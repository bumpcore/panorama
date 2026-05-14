<?php

declare(strict_types=1);

namespace BumpCore\Panorama\Tests\Fixtures;

use BumpCore\Panorama\BackedByQuery;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;

final class CustomerBalanceFromQueryBuilder extends Model
{
    use BackedByQuery;

    public $timestamps = false;

    protected $table = 'customer_balance_query_builder';

    protected $primaryKey = 'customer_id';

    public $incrementing = false;

    protected $guarded = [];

    public function newSource(int $customer_id): Builder
    {
        return Capsule::table('invoices')
            ->selectRaw('customer_id, sum(amount) as balance')
            ->where('customer_id', $customer_id)
            ->groupBy('customer_id');
    }
}
