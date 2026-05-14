<?php

declare(strict_types=1);

namespace BumpCore\Panorama\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

final class Customer extends Model
{
    public $timestamps = false;

    protected $guarded = [];

    /**
     * @return HasOne<CustomerBalance, $this>
     */
    public function balance(): HasOne
    {
        return $this->hasOne(CustomerBalance::class);
    }
}
