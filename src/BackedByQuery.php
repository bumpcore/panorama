<?php

declare(strict_types=1);

namespace BumpCore\Panorama;

use BumpCore\Panorama\Eloquent\QuerySourceScope;
use BumpCore\Panorama\Exceptions\MissingQuerySourceException;
use BumpCore\Panorama\Support\QuerySourceState;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin Model
 */
trait BackedByQuery
{
    /**
     * Registers the source query scope.
     */
    public static function bootBackedByQuery(): void
    {
        static::addGlobalScope(new QuerySourceScope());
    }

    /**
     * Adds or replaces params that will be passed to the model's newSource() method.
     *
     * @template TModel of Model
     *
     * @param EloquentBuilder<TModel> $query
     * @param array<array-key, mixed> $params
     * @return EloquentBuilder<TModel>
     */
    public function scopeWithParams(EloquentBuilder $query, array $params = [], mixed ...$namedParams): EloquentBuilder
    {
        QuerySourceState::mergeParams($query, $params, $namedParams);

        return $query;
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
