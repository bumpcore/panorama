<?php

declare(strict_types=1);

namespace BumpCore\Panorama\Tests\Fixtures;

use BumpCore\Panorama\BackedByQuery;
use Illuminate\Database\Eloquent\Model;

final class InvalidSourceModel extends Model
{
    use BackedByQuery;

    protected $table = 'invalid_sources';

    /**
     * @return array<string, string>
     */
    public function newSource(): array
    {
        return ['not' => 'a query'];
    }
}
