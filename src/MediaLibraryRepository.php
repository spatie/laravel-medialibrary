<?php

namespace Spatie\MediaLibrary;

use Spatie\MediaLibrary\Models\Media;

class MediaLibraryRepository
{

    /**
     * @var \Spatie\MediaLibrary\Models\Media
     */
    protected $model;

    public function __construct(Media $model)
    {
        $this->model = $model;
    }

    public function getCollection($collectionName)
}
