<?php

namespace Programic\MediaLibrary\MediaCollections\Events;

use Illuminate\Queue\SerializesModels;
use Programic\MediaLibrary\HasMedia;

class CollectionHasBeenCleared
{
    use SerializesModels;

    public function __construct(public HasMedia $model, public string $collectionName)
    {
    }
}
