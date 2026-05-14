<?php

declare(strict_types=1);

namespace BumpCore\Panorama;

use BumpCore\Panorama\Contracts\QueryBackedModel;
use BumpCore\Panorama\Eloquent\QueryBackedBuilder;
use BumpCore\Panorama\Exceptions\MissingQuerySourceException;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin Model
 * @mixin QueryBackedModel
 */
trait BackedByQuery
{
    /**
     * Creates a Panorama-aware Eloquent builder for the model.
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @return QueryBackedBuilder<Model>
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
     * Builds the backing source query with validated builder params.
     *
     * @internal
     *
     * @param array<string, mixed> $params
     */
    public function buildPanoramaSource(array $params): mixed
    {
        return call_user_func_array([$this, 'newSource'], $params);
    }

    /**
     * Returns the source alias through a concrete method PHPStan can analyze.
     *
     * @internal
     */
    public function panoramaSourceAlias(): string
    {
        return $this->querySourceAlias();
    }
}
