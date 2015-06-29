<?php

namespace Spatie\MediaLibrary;

use Spatie\MediaLibrary\Traits\HasMediaInterface;

class MediaLibraryRepository
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
     * Get all media in the collection
     *
     * @param \Spatie\MediaLibrary\Traits\HasMediaInterface $model
     * @param string $collectionName
     * @param array $filters
     *
     * @return mixed
     */
    public function getCollection(HasMediaInterface $model, $collectionName, $filters = [])
    {
        $mediaItems = $this->loadMedia($model, $collectionName);

        $media = $this->addUrlsToMedia($mediaItems);

        $media = $this->applyFiltersToMedia($media, $filters);

        return $media;
    }

    /**
     * Load media by collectionName.
     *
     * @param HasMediaInterface $model
     * @param string $collectionName
     * @return mixed
     */
    private function loadMedia(HasMediaInterface $model, $collectionName)
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
     * Check if the media is preloaded.
     *
     * @param HasMediaInterface $model
     *
     * @return bool
     */
    private function mediaIsPreloaded(HasMediaInterface $model)
    {
        return isset($model->media);
    }


    /**
     * Add URL to a single media item.
     *
     * @param $mediaItem
     *
     * @return mixed
     */
    private function addUrlsToMedia(Media $mediaItem)
    {
        foreach ($this->fileSystem->getFilePathsForMedia($mediaItem) as $profileName => $filePath) {
            $mediaItem->addImageProfileURL($profileName, str_replace(public_path(), '', $filePath));
        }
        return $mediaItem;
    }


}
