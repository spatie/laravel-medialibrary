<?php

namespace Spatie\MediaLibrary\MediaCollections;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as DbCollection;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaRepository
{
    public function __construct(
        protected Media $model
    ) {
    }

    /**
     * Get all media in the collection.
     */
    public function getCollection(
        HasMedia $model,
        string $collectionName,
        array|callable $filter = []
    ): Collection {
        return $this->applyFilterToMediaCollection($model->loadMedia($collectionName), $filter);
    }

    /**
     * Apply given filters on media.
     */
    protected function applyFilterToMediaCollection(
        Collection $media,
        array|callable $filter
    ): Collection {
        if (is_array($filter)) {
            $filter = $this->getDefaultFilterFunction($filter);
        }

        return $media->filter($filter);
    }

    public function all(): DbCollection
    {
        return $this->query()->get();
    }

    public function getByModelType(string $modelType): DbCollection
    {
        return $this->query()->where('model_type', $modelType)->get();
    }

    public function getByIds(array $ids): DbCollection
    {
        return $this->query()->whereIn($this->model->getKeyName(), $ids)->get();
    }

    public function getByIdGreaterThan(int $startingFromId, bool $excludeStartingId = false, string $modelType = ''): DbCollection
    {
        return $this->query()
            ->where($this->model->getKeyName(), $excludeStartingId ? '>' : '>=', $startingFromId)
            ->when($modelType !== '', fn (Builder $q) => $q->where('model_type', $modelType))
            ->get();
    }

    public function getByModelTypeAndCollectionName(string $modelType, string $collectionName): DbCollection
    {
        return $this->query()
            ->where('model_type', $modelType)
            ->where('collection_name', $collectionName)
            ->get();
    }

    public function getByCollectionName(string $collectionName): DbCollection
    {
        return $this->query()
            ->where('collection_name', $collectionName)
            ->get();
    }

    public function getOrphans(): DbCollection
    {
        return $this->orphansQuery()
            ->get();
    }

    public function getOrphansByCollectionName(string $collectionName): DbCollection
    {
        return $this->orphansQuery()
            ->where('collection_name', $collectionName)
            ->get();
    }

    protected function query(): Builder
    {
        return $this->model->newQuery();
    }

    protected function orphansQuery(): Builder
    {
        return $this->query()
            ->whereDoesntHave(
                'model',
                fn (Builder $q) => $q->hasMacro('withTrashed') ? $q->withTrashed() : $q,
            );
    }

    protected function getDefaultFilterFunction(array $filters): Closure
    {
        return function (Media $media) use ($filters) {
            foreach ($filters as $property => $value) {
                if (! Arr::has($media->custom_properties, $property)) {
                    return false;
                }

                if (Arr::get($media->custom_properties, $property) !== $value) {
                    return false;
                }
            }

            return true;
        };
    }
}
