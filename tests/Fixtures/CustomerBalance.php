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

    protected $casts = [
        'balance' => 'float',
        'invoice_count' => 'integer',
    ];

    /**
     * @return Builder<Invoice>
     */
    public function newSource(int $customer_id, ?string $status = null): Builder
    {
        return Invoice::query()
            ->selectRaw('customer_id, sum(amount) as balance, count(*) as invoice_count')
            ->where('customer_id', $customer_id)
            ->when($status !== null, static fn (Builder $query): Builder => $query->where('status', $status))
            ->groupBy('customer_id');
    }

    /**
     * @return BelongsTo<Customer, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
