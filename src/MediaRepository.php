<?php

namespace Spatie\MediaLibrary;

use Illuminate\Support\Collection;
use Spatie\MediaLibrary\Traits\HasMediaInterface;

class MediaRepository
{
    /**
     * @var \Spatie\MediaLibrary\Media
     */
    protected $model;

    public function __construct(Media $model)
    {
        $this->model = $model;
    }

    /**
     * Get all media in the collection.
     *
     * @param \Spatie\MediaLibrary\Traits\HasMediaInterface $model
     * @param string                                        $collectionName
     * @param array                                         $filters
     *
     * @return Media[]
     */
    public function getCollection(HasMediaInterface $model, $collectionName, $filters = [])
    {
        $mediaCollection = $this->loadMedia($model, $collectionName);

        $mediaCollection = $this->applyFiltersToMediaCollection($mediaCollection, $filters);

        return $mediaCollection;
    }

    /**
     * Load media by collectionName.
     *
     * @param HasMediaInterface $model
     * @param string            $collectionName
     *
     * @return mixed
     */
    protected function loadMedia(HasMediaInterface $model, $collectionName)
    {
        if ($this->mediaIsPreloaded($model)) {
            $media = $model->media->filter(function (Media $mediaItem) use ($collectionName) {
                return $mediaItem->collection_name == $collectionName;
            })->sortBy(function (Media $media) {
                return $media->order_column;
            })->values();

            return $media;
        }

        $media = $model->media()
            ->where('collection_name', $collectionName)
            ->orderBy('order_column')
            ->get();

        return $media;
    }

    /**
     * Determine if media is already preloaded on this model.
     *
     * @param $model
     *
     * @return bool
     */
    protected function mediaIsPreloaded($model)
    {
        return isset($model->media);
    }

    /**
     * Apply given filters on media.
     *
     * @param $media
     * @param $filters
     *
     * @return mixed
     */
    protected function applyFiltersToMediaCollection(Collection $media, $filters)
    {
        foreach ($filters as $filterProperty => $filterValue) {
            $media = $media->filter(function (Media $media) use ($filterProperty, $filterValue) {
                return $media->$filterProperty == $filterValue;
            });
        }

        return $media;
    }

    /**
     * Get all media.
     *
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function all()
    {
        return $this->model->all();
    }

    /**
     * Get all media for the given type.
     *
     * @param string $modelType
     */
    public function getByModelType($modelType)
    {
        $this->model->where('model_type', $modelType)->get();
    }
}
