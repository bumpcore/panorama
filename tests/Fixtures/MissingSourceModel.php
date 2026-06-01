<?php

declare(strict_types=1);

namespace Bumpcore\Panorama\Tests\Fixtures;

use Bumpcore\Panorama\HasSource;
use Illuminate\Database\Eloquent\Model;

class MissingSourceModel extends Model
{
    use HasSource;

    /**
     * Points this fixture at an isolated table for missing source tests.
     */
    protected $table = 'missing_sources';
}
