<?php

declare(strict_types=1);

namespace BumpCore\Panorama\Exceptions;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;

final class InvalidQuerySourceException extends PanoramaException
{
    public static function forModel(string $modelClass): self
    {
        return new self(sprintf('Model [%s] is not backed by a Panorama query source.', $modelClass));
    }

    public static function forSource(string $modelClass, mixed $source): self
    {
        return new self(sprintf(
            'Model [%s] newSource() must return an instance of [%s] or [%s]; [%s] returned.',
            $modelClass,
            EloquentBuilder::class,
            QueryBuilder::class,
            get_debug_type($source),
        ));
    }

    public static function emptyAlias(string $modelClass): self
    {
        return new self(sprintf('Model [%s] returned an empty query source alias.', $modelClass));
    }
}
