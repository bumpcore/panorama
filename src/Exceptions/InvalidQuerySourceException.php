<?php

declare(strict_types=1);

namespace Bumpcore\Panorama\Exceptions;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;

class InvalidQuerySourceException extends PanoramaException
{
    /**
     * Creates an exception for models that do not use the Panorama source trait.
     */
    public static function forModel(string $modelClass): self
    {
        return new self(sprintf('Model [%s] is not backed by a Panorama query source.', $modelClass));
    }

    /**
     * Creates an exception for source methods that return unsupported values.
     */
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

    /**
     * Creates an exception for models that return an unusable source alias.
     */
    public static function emptyAlias(string $modelClass): self
    {
        return new self(sprintf('Model [%s] returned an empty query source alias.', $modelClass));
    }
}
