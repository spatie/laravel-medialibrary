<?php

namespace Spatie\MediaLibrary;

use Illuminate\Database\Eloquent\Collection as DbCollection;
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
     * @param array|callable $filter
     *
     * @return Collection
     */
    public function getCollection(HasMedia $model, string $collectionName, $filter = []) : Collection
    {
        $mediaCollection = $this->loadMedia($model, $collectionName);

        $mediaCollection = $this->applyFilterToMediaCollection($mediaCollection, $filter);

        return collect($mediaCollection);
    }

    /**
     * Load media by collectionName.
     *
     * @param HasMedia $model
     * @param string   $collectionName
     *
     * @return mixed
     */
    protected function loadMedia(HasMedia $model, string $collectionName)
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

    /*
     * Determine if media is already preloaded on this model.
     */
    protected function mediaIsPreloaded(HasMedia $model) : bool
    {
        return isset($model->media);
    }

    /**
     * Apply given filters on media.
     *
     * @param \Illuminate\Support\Collection $media
     * @param array|callable                 $filter
     *
     * @return Collection
     */
    protected function applyFilterToMediaCollection(Collection $media, $filter) : Collection
    {
        if (is_array($filter)) {
            $filter = $this->getDefaultFilterFunction($filter);
        }

        return $media->filter($filter);
    }

    /**
     * Get all media.
     */
    public function all() : DbCollection
    {
        return $this->model->all();
    }

    /*
     * Get all media for the given type.
     */
    public function getByModelType(string $modelType) : DbCollection
    {
        return $this->model->where('model_type', $modelType)->get();
    }

    /*
     * Get media by ids.
     */
    public function getByIds(array $ids) : DbCollection
    {
        return $this->model->whereIn('id', $ids)->get();
    }

    /*
     * Get all media for the given type and collection name.
     */
    public function getByModelTypeAndCollectionName(string $modelType, string $collectionName) : DbCollection
    {
        return $this->model
            ->where('model_type', $modelType)
            ->where('collection_name', $collectionName)
            ->get();
    }

    /*
     * Get all media for the given type and collection name.
     */
    public function getByCollectionName(string $collectionName) : DbCollection
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
    protected function getDefaultFilterFunction(array $filters)
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
