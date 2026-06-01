<?php

declare(strict_types=1);

namespace Bumpcore\Panorama\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    /**
     * Indicates that the invoice fixture does not manage timestamps.
     */
    public $timestamps = false;

    /**
     * Allows tests to mass assign invoice fixture attributes freely.
     */
    protected $guarded = [];
}
