<?php

namespace Spatie\MediaLibrary;


use Spatie\MediaLibrary\HasMedia\Interfaces\HasMedia;
use Illuminate\Support\Collection;

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
     * @param HasMedia $model
     * @param string $collectionName
     * @param array $filters
     * @return Collection
     */
    public function getCollection(HasMedia $model, $collectionName, $filters = [])
    {
        $mediaCollection = $this->loadMedia($model, $collectionName);

        $mediaCollection = $this->applyFiltersToMediaCollection($mediaCollection, $filters);

        return Collection::make($mediaCollection);
    }

    /**
     * Load media by collectionName.
     *
     * @param HasMedia $model
     * @param string $collectionName
     * @return mixed
     */
    protected function loadMedia(HasMedia $model, $collectionName)
    {
        if ($this->mediaIsPreloaded($model)) {
            $media = $model->media->filter(function (Media $mediaItem) use ($collectionName) {

                if ($collectionName == '') return true;

                return $mediaItem->collection_name == $collectionName;

            })->sortBy(function (Media $media) {

                return $media->order_column;

            })->values();

            return $media;
        }

        $query = $model->media();
      if($collectionName != '')
      {
      $query = $query->where('collection_name', $collectionName);
      }
            $media = $query->orderBy('order_column')
            ->get();

        return $media;
    }

    /**
     * Determine if media is already preloaded on this model.
     *
     * @param HasMedia $model
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
     * @param array                          $filters
     *
     * @return mixed
     */
    protected function applyFiltersToMediaCollection(Collection $media, array $filters)
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
     */
    public function getByModelType($modelType)
    {
        $this->model->where('model_type', $modelType)->get();
    }
}
