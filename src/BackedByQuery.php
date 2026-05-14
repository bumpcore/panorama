<?php

declare(strict_types=1);

namespace BumpCore\Panorama;

use BumpCore\Panorama\Eloquent\QueryBackedBuilder;
use BumpCore\Panorama\Exceptions\MissingQuerySourceException;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin Model
 */
trait BackedByQuery
{
    /**
     * Creates a Panorama-aware Eloquent builder for the model.
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @return QueryBackedBuilder<static>
     */
    public function newEloquentBuilder($query): QueryBackedBuilder
    {
        return new QueryBackedBuilder($query);
    }

    /**
     * Defines the backing source query for this model.
     *
     * Models using this trait are expected to override this method. Params
     * supplied through withParams() are passed as named arguments.
     *
     * @throws MissingQuerySourceException
     */
    public function newSource(mixed ...$params): mixed
    {
        unset($params);

        throw MissingQuerySourceException::forModel(static::class);
    }

    /**
     * Returns the SQL alias used for the wrapped source query.
     */
    public function querySourceAlias(): string
    {
        return $this->getTable();
    }

    /**
     * Starts a query with source params already attached.
     *
     * @param array<string, mixed> $params
     * @return QueryBackedBuilder<static>
     */
    public static function queryWithParams(array $params = [], mixed ...$namedParams): QueryBackedBuilder
    {
        /** @var QueryBackedBuilder<static> $query */
        $query = static::query();

        return $query->withParams($params, ...$namedParams);
    }
}
