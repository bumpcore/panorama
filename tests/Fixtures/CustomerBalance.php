<?php

declare(strict_types=1);

namespace BumpCore\Panorama\Tests\Fixtures;

use BumpCore\Panorama\BackedByQuery;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class CustomerBalance extends Model
{
    use BackedByQuery;

    public $timestamps = false;

    protected $table = 'customer_balances';

    protected $primaryKey = 'customer_id';

    public $incrementing = false;

    protected $guarded = [];

    /**
     * @return Builder<Invoice>
     */
    public function newSource(int $customer_id, ?string $status = null): Builder
    {
        $query = Invoice::query();
        $baseQuery = $query->getQuery();

        $baseQuery->selectRaw('customer_id, sum(amount) as balance, count(*) as invoice_count');
        $baseQuery->where('customer_id', $customer_id);

        if ($status !== null) {
            $baseQuery->where('status', $status);
        }

        $baseQuery->groupBy('customer_id');

        return $query;
    }

    /**
     * @return BelongsTo<Customer, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'balance' => 'float',
            'invoice_count' => 'integer',
        ];
    }
}
