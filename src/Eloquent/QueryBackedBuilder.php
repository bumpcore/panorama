<?php

declare(strict_types=1);

namespace BumpCore\Panorama\Eloquent;

use BumpCore\Panorama\BackedByQuery;
use BumpCore\Panorama\Exceptions\InvalidQuerySourceException;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;

/**
 * @template TModel of Model
 *
 * @extends EloquentBuilder<TModel>
 */
final class QueryBackedBuilder extends EloquentBuilder
{
    /**
     * @var array<string, mixed>
     */
    private array $sourceParams = [];

    private bool $sourceApplied = false;

    /**
     * Adds or replaces params that will be passed to the model's newSource() method.
     *
     * @param array<string, mixed> $params
     * @return $this
     */
    public function withParams(array $params = [], mixed ...$namedParams): static
    {
        $this->sourceParams = array_merge($this->sourceParams, $params, $namedParams);
        $this->sourceApplied = false;

        return $this;
    }

    /**
     * @param array<int, string>|string $columns
     * @return EloquentCollection<int, TModel>
     */
    public function get($columns = ['*']): EloquentCollection
    {
        $this->applyQuerySource();

        return parent::get($columns);
    }

    /**
     * @param array<int, string>|string $columns
     * @return array<int, TModel>
     */
    public function getModels($columns = ['*']): array
    {
        $this->applyQuerySource();

        return parent::getModels($columns);
    }

    public function toBase(): QueryBuilder
    {
        $this->applyQuerySource();

        return parent::toBase();
    }

    public function toSql(): string
    {
        $this->applyQuerySource();

        return parent::toSql();
    }

    /**
     * @return list<mixed>
     */
    public function getBindings(): array
    {
        $this->applyQuerySource();

        return parent::getBindings();
    }

    /**
     * Applies the model's backing query as the underlying FROM source.
     */
    public function applyQuerySource(): void
    {
        if ($this->sourceApplied) {
            return;
        }

        $model = $this->getModel();
        $source = $this->buildSourceQuery($model);
        $query = $this->getQuery();

        $query->bindings['from'] = [];
        $query->fromSub($source, $this->sourceAlias($model));

        $this->sourceApplied = true;
    }

    private function buildSourceQuery(Model $model): QueryBuilder
    {
        if (! in_array(BackedByQuery::class, class_uses_recursive($model), true)) {
            throw InvalidQuerySourceException::forModel($model::class);
        }

        $source = $model->newSource(...$this->sourceParams);

        if ($source instanceof EloquentBuilder) {
            return $source->toBase();
        }

        if ($source instanceof QueryBuilder) {
            return $source;
        }

        throw InvalidQuerySourceException::forSource($model::class, $source);
    }

    private function sourceAlias(Model $model): string
    {
        /** @var object{querySourceAlias: callable(): string} $model */
        $alias = $model->querySourceAlias();

        if ($alias === '') {
            throw InvalidQuerySourceException::emptyAlias($model::class);
        }

        return $alias;
    }
}
