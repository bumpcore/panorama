<?php

declare(strict_types=1);

namespace Bumpcore\Panorama\Eloquent;

use Bumpcore\Panorama\Exceptions\InvalidQuerySourceException;
use Bumpcore\Panorama\Support\QuerySourceState;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Query\Builder as QueryBuilder;
use ReflectionMethod;

class QuerySourceScope implements Scope
{
    /**
     * Wraps the model query in the configured Panorama source query.
     *
     * @param EloquentBuilder<*> $builder
     */
    #[\Override]
    public function apply(EloquentBuilder $builder, Model $model): void
    {
        if (! method_exists($model, 'buildPanoramaSource') || ! method_exists($model, 'panoramaSourceAlias')) {
            throw InvalidQuerySourceException::forModel($model::class);
        }

        $source = $this->buildSourceQuery($model, QuerySourceState::params($builder));
        $alias = $this->sourceAlias($model);

        if ($alias === '') {
            throw InvalidQuerySourceException::emptyAlias($model::class);
        }

        $query = $builder->getQuery();
        $query->bindings['from'] = [];
        $query->fromSub($source, $alias);
    }

    /**
     * Resolves and validates the source query returned by the model.
     *
     * @param array<string, mixed> $params
     */
    private function buildSourceQuery(Model $model, array $params): QueryBuilder
    {
        $source = (new ReflectionMethod($model, 'buildPanoramaSource'))->invoke($model, $params);

        if ($source instanceof EloquentBuilder) {
            return $source->toBase();
        }

        if ($source instanceof QueryBuilder) {
            return $source;
        }

        throw InvalidQuerySourceException::forSource($model::class, $source);
    }

    /**
     * Resolves the table alias used for the wrapped source query.
     */
    private function sourceAlias(Model $model): string
    {
        $alias = (new ReflectionMethod($model, 'panoramaSourceAlias'))->invoke($model);

        return is_string($alias) ? $alias : '';
    }
}
