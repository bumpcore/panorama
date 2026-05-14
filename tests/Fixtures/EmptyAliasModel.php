<?php

declare(strict_types=1);

namespace BumpCore\Panorama\Tests\Fixtures;

use BumpCore\Panorama\BackedByQuery;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;

final class EmptyAliasModel extends Model
{
    use BackedByQuery;

    public function newSource(): Builder
    {
        return Invoice::query()->toBase();
    }

    public function querySourceAlias(): string
    {
        return '';
    }
}
