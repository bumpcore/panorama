<?php

declare(strict_types=1);

namespace Bumpcore\Panorama\Tests\Fixtures;

use Bumpcore\Panorama\HasSource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;

class EmptyAliasModel extends Model
{
    use HasSource;

    /**
     * Provides a valid source query while the alias remains invalid.
     */
    public function newSource(): Builder
    {
        return Invoice::query()->toBase();
    }

    /**
     * Returns an empty alias so alias validation failures can be tested.
     */
    public function querySourceAlias(): string
    {
        return '';
    }
}
