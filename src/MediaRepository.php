<?php

namespace Spatie\MediaLibrary;

use Illuminate\Support\Collection;
use Spatie\MediaLibrary\HasMedia\Interfaces\HasMedia;

class MediaRepository
{
    /**
     * @var \Spatie\MediaLibrary\Media
     */
    protected $model;

    /**
     * @param \Spatie\MediaLibrary\Media $model
     */
    public function __construct(Media $model)
    {
        $this->model = $model;
    }

    /**
     * Get all media in the collection.
     *
     * @param HasMedia       $model
     * @param string         $collectionName
     * @param array|\Closure $filter
     *
     * @return Collection
     */
    public function getCollection(HasMedia $model, $collectionName, $filter = [])
    {
        $mediaCollection = $this->loadMedia($model, $collectionName);

        $mediaCollection = $this->applyFilterToMediaCollection($mediaCollection, $filter);

        return Collection::make($mediaCollection);
    }

    /**
     * Load media by collectionName.
     *
     * @param HasMedia $model
     * @param string   $collectionName
     *
     * @return mixed
     */
    protected function loadMedia(HasMedia $model, $collectionName)
    {
        if ($this->mediaIsPreloaded($model)) {
            $media = $model->media->filter(function (Media $mediaItem) use ($collectionName) {

                if ($collectionName == '') {
                    return true;
                }

                return $mediaItem->collection_name == $collectionName;

            })->sortBy(function (Media $media) {

                return $media->order_column;

            })->values();

            return $media;
        }

        $query = $model->media();

        if ($collectionName != '') {
            $query = $query->where('collection_name', $collectionName);
        }

        $media = $query
            ->orderBy('order_column')
            ->get();

        return $media;
    }

    /**
     * Determine if media is already preloaded on this model.
     *
     * @param HasMedia $model
     *
     * @return bool
     */
    protected function mediaIsPreloaded(HasMedia $model)
    {
        return isset($model->media);
    }

    /**
     * Apply given filters on media.
     *
     * @param \Illuminate\Support\Collection $media
     * @param array|\Closure                 $filter
     *
     * @return Collection
     */
    protected function applyFilterToMediaCollection(Collection $media, $filter)
    {
        if (is_array($filter)) {
            $filter = $this->getDefaultFilterFunction($filter);
        }

        return $media->filter($filter);
    }

    /**
     * Get all media.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function all()
    {
        return $this->model->all();
    }

    /**
     * Get all media for the given type.
     *
     * @param string $modelType
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getByModelType($modelType)
    {
        return $this->model->where('model_type', $modelType)->get();
    }

    /**
     * Get media by ids.
     *
     * @param array $ids
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getByIds(array $ids)
    {
        return $this->model->whereIn('id', $ids)->get();
    }

    /**
     * Get all media for the given type and collection name.
     *
     * @param string $modelType
     * @param string $collectionName
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getByModelTypeAndCollectionName($modelType, $collectionName)
    {
        return $this->model
            ->where('model_type', $modelType)
            ->where('collection_name', $collectionName)
            ->get();
    }

    /**
     * Get all media for the given type and collection name.
     *
     * @param string $collectionName
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getByCollectionName($collectionName)
    {
        return $this->model
            ->where('collection_name', $collectionName)
            ->get();
    }

    /**
     * Convert the given array to a filter function.
     *
     * @param $filters
     *
     * @return \Closure
     */
    protected function getDefaultFilterFunction($filters)
    {
        return function (Media $media) use ($filters) {

            $customProperties = $media->custom_properties;

            foreach ($filters as $property => $value) {
                if (!isset($customProperties[$property])) {
                    return false;
                }
                if ($customProperties[$property] != $value) {
                    return false;
                }
            }

            return true;
        };
    }
}
