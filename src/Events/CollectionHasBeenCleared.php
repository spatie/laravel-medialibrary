<?php

namespace Spatie\MediaLibrary\Events;

use Illuminate\Queue\SerializesModels;
use Spatie\MediaLibrary\HasMedia\Interfaces\HasMedia;

class CollectionHasBeenCleared
{
    use SerializesModels;

    /** @var \Spatie\MediaLibrary\HasMedia\Interfaces\HasMedia */
    public $model;

    /** @var string */
    public $collectionName;

    public function __construct(HasMedia $model, string $collectionName)
    {
        $this->model = $model;

        $this->collectionName = $collectionName;
    }
}
