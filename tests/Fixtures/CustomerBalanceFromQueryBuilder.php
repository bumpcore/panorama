<?php

declare(strict_types=1);

namespace Bumpcore\Panorama\Tests\Fixtures;

use Bumpcore\Panorama\HasSource;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;

class CustomerBalanceFromQueryBuilder extends Model
{
    use HasSource;

    /**
     * Indicates that this query-backed fixture does not manage timestamps.
     */
    public $timestamps = false;

    /**
     * Uses a dedicated table alias for query builder source tests.
     */
    protected $table = 'customer_balance_query_builder';

    /**
     * Uses the customer id as the model key for hydration assertions.
     */
    protected $primaryKey = 'customer_id';

    /**
     * Indicates that the fixture key is supplied by the source query.
     */
    public $incrementing = false;

    /**
     * Allows tests to mass assign query-backed fixture attributes freely.
     */
    protected $guarded = [];

    /**
     * Builds a base query builder source for the requested customer.
     */
    public function newSource(int $customer_id): Builder
    {
        return Capsule::table('invoices')
            ->selectRaw('customer_id, sum(amount) as balance')
            ->where('customer_id', $customer_id)
            ->groupBy('customer_id');
    }
}
