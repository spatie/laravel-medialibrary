<?php

namespace Spatie\Medialibrary\MediaCollections\Events;

use Illuminate\Queue\SerializesModels;
use Spatie\Medialibrary\HasMedia;

class CollectionHasBeenCleared
{
    use SerializesModels;

    public HasMedia $model;

    public string $collectionName;

    public function __construct(HasMedia $model, string $collectionName)
    {
        $this->model = $model;

        $this->collectionName = $collectionName;
    }
}
