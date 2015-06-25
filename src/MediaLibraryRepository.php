<?php

namespace Spatie\MediaLibrary;

use Spatie\MediaLibrary\Media;
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
     */
    public function getCollection(HasMediaInterface $model, $collectionName, $filters = [])
    {
        $mediaItems = $this->loadMedia($model, $collectionName);

        $media = $this->addURLsToMediaProfile($mediaItems);

        $media = $this->applyFiltersToMedia($media, $filters);

        return $media;
    }


}
