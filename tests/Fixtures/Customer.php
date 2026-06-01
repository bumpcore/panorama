<?php

declare(strict_types=1);

namespace Bumpcore\Panorama\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Customer extends Model
{
    /**
     * Indicates that the test customer fixture does not manage timestamps.
     */
    public $timestamps = false;

    /**
     * Allows tests to mass assign fixture attributes freely.
     */
    protected $guarded = [];

    /**
     * Defines the query-backed balance relation used by integration tests.
     *
     * @return HasOne<CustomerBalance, $this>
     */
    public function balance(): HasOne
    {
        return $this->hasOne(CustomerBalance::class);
    }
}
