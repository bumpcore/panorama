<?php

declare(strict_types=1);

namespace BumpCore\Panorama\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;

final class Customer extends Model
{
    public $timestamps = false;

    protected $guarded = [];
}
