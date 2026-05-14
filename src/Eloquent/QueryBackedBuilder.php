<?php

declare(strict_types=1);

namespace BumpCore\Panorama\Eloquent;

use BumpCore\Panorama\Contracts\QueryBackedModel;
use BumpCore\Panorama\Exceptions\InvalidQuerySourceException;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;
use InvalidArgumentException;

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
     * @param array<array-key, mixed> $params
     * @return $this
     */
    public function withParams(array $params = [], mixed ...$namedParams): static
    {
        $this->sourceParams = array_merge(
            $this->sourceParams,
            $this->normalizeParams($params),
            $this->normalizeParams($namedParams),
        );
        $this->sourceApplied = false;

        return $this;
    }

    /**
     * @param array<array-key, mixed>|string $columns
     * @return EloquentCollection<int, TModel>
     */
    public function get($columns = ['*']): EloquentCollection
    {
        $this->applyQuerySource();

        return parent::get($columns);
    }

    /**
     * @param array<array-key, mixed>|string $columns
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
        $this->assertPanoramaModel($model);
        $source = $this->buildSourceQuery($model);
        $alias = $model->panoramaSourceAlias();
        $query = $this->getQuery();

        if ($alias === '') {
            throw InvalidQuerySourceException::emptyAlias($model::class);
        }

        $query->bindings['from'] = [];
        $query->fromSub($source, $alias);

        $this->sourceApplied = true;
    }

    /**
     * @param Model&QueryBackedModel $model
     */
    private function buildSourceQuery(Model $model): QueryBuilder
    {
        $source = $model->buildPanoramaSource($this->sourceParams);

        if ($source instanceof EloquentBuilder) {
            return $source->toBase();
        }

        if ($source instanceof QueryBuilder) {
            return $source;
        }

        throw InvalidQuerySourceException::forSource($model::class, $source);
    }

    /**
     * @phpstan-assert Model&QueryBackedModel $model
     */
    private function assertPanoramaModel(Model $model): void
    {
        if (! method_exists($model, 'buildPanoramaSource') || ! method_exists($model, 'panoramaSourceAlias')) {
            throw InvalidQuerySourceException::forModel($model::class);
        }
    }

    /**
     * @param array<array-key, mixed> $params
     * @return array<string, mixed>
     */
    private function normalizeParams(array $params): array
    {
        foreach (array_keys($params) as $key) {
            if (! is_string($key)) {
                throw new InvalidArgumentException('Panorama query params must use string keys.');
            }
        }

        return $params;
    }
}
