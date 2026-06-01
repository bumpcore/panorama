<?php

declare(strict_types=1);

namespace Bumpcore\Panorama\Tests\Fixtures;

use Bumpcore\Panorama\HasSource;
use Illuminate\Database\Eloquent\Model;

class InvalidSourceModel extends Model
{
    use HasSource;

    /**
     * Points this fixture at an isolated table for invalid source tests.
     */
    protected $table = 'invalid_sources';

    /**
     * Returns an invalid source value so validation failures can be tested.
     *
     * @return array<string, string>
     */
    public function newSource(): array
    {
        return ['not' => 'a query'];
    }
}
