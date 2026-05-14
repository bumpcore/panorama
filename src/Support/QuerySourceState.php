<?php

declare(strict_types=1);

namespace BumpCore\Panorama\Support;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use InvalidArgumentException;
use WeakMap;

/**
 * @internal
 */
final class QuerySourceState
{
    /**
     * @var WeakMap<object, array<string, mixed>>|null
     */
    private static ?WeakMap $params = null;

    /**
     * @param EloquentBuilder<*> $builder
     * @param array<array-key, mixed> $params
     * @param array<array-key, mixed> $namedParams
     */
    public static function mergeParams(EloquentBuilder $builder, array $params, array $namedParams): void
    {
        $merged = array_merge(
            self::params($builder),
            self::normalizeParams($params),
            self::normalizeParams($namedParams),
        );

        self::storeParams($builder, $merged);

        $builder->onClone(static function (EloquentBuilder $clone) use ($builder): void {
            self::storeParams($clone, self::params($builder));
        });
    }

    /**
     * @param EloquentBuilder<*> $builder
     * @return array<string, mixed>
     */
    public static function params(EloquentBuilder $builder): array
    {
        return self::map()[$builder] ?? [];
    }

    /**
     * @param EloquentBuilder<*> $builder
     * @param array<string, mixed> $params
     */
    private static function storeParams(EloquentBuilder $builder, array $params): void
    {
        self::map()[$builder] = $params;
    }

    /**
     * @return WeakMap<object, array<string, mixed>>
     */
    private static function map(): WeakMap
    {
        return self::$params ??= new WeakMap();
    }

    /**
     * @param array<array-key, mixed> $params
     * @return array<string, mixed>
     */
    private static function normalizeParams(array $params): array
    {
        foreach (array_keys($params) as $key) {
            if (! is_string($key)) {
                throw new InvalidArgumentException('Panorama query params must use string keys.');
            }
        }

        return $params;
    }
}
