<?php

declare(strict_types=1);

namespace Bumpcore\Panorama\Exceptions;

class MissingQuerySourceException extends PanoramaException
{
    /**
     * Creates an exception for models that do not define a source query.
     */
    public static function forModel(string $modelClass): self
    {
        return new self(sprintf('Model [%s] must override newSource().', $modelClass));
    }
}
