<?php

declare(strict_types=1);

namespace BumpCore\Panorama\Exceptions;

final class MissingQuerySourceException extends PanoramaException
{
    public static function forModel(string $modelClass): self
    {
        return new self(sprintf('Model [%s] must override newSource().', $modelClass));
    }
}
