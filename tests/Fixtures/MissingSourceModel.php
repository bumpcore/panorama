<?php

declare(strict_types=1);

namespace BumpCore\Panorama\Tests\Fixtures;

use BumpCore\Panorama\BackedByQuery;
use Illuminate\Database\Eloquent\Model;

final class MissingSourceModel extends Model
{
    use BackedByQuery;

    protected $table = 'missing_sources';
}
