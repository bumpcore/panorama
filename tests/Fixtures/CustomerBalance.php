<?php

declare(strict_types=1);

namespace Bumpcore\Panorama\Tests\Fixtures;

use Bumpcore\Panorama\HasSource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerBalance extends Model
{
    use HasSource;

    /**
     * Indicates that this query-backed fixture does not manage timestamps.
     */
    public $timestamps = false;

    /**
     * Uses a dedicated table alias for customer balance source tests.
     */
    protected $table = 'customer_balances';

    /**
     * Uses the customer id as the model key for hydration assertions.
     */
    protected $primaryKey = 'customer_id';

    /**
     * Indicates that the fixture key is supplied by the source query.
     */
    public $incrementing = false;

    /**
     * Allows tests to mass assign customer balance attributes freely.
     */
    protected $guarded = [];

    /**
     * Builds a grouped invoice balance source for the optional filters.
     *
     * @return Builder<Invoice>
     */
    public function newSource(?int $customer_id = null, ?string $status = null): Builder
    {
        $query = Invoice::query();
        $baseQuery = $query->getQuery();

        $baseQuery->selectRaw('customer_id, sum(amount) as balance, count(*) as invoice_count');

        if ($customer_id !== null) {
            $baseQuery->where('customer_id', $customer_id);
        }

        if ($status !== null) {
            $baseQuery->where('status', $status);
        }

        $baseQuery->groupBy('customer_id');

        return $query;
    }

    /**
     * Defines the customer relation used to prove Eloquent relations still work.
     *
     * @return BelongsTo<Customer, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
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
            'invoice_count' => 'integer',
        ];
    }
}
