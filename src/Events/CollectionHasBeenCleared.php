<?php

namespace Spatie\MediaLibrary\Events;

use Illuminate\Queue\SerializesModels;
use Spatie\MediaLibrary\HasMedia\HasMedia;

class CollectionHasBeenCleared
{
    use SerializesModels;

    /** @var string */
    public $collectionName;

    /** @var HasMedia|null */
    public $model;

    /**
     * Create a new instance.
     *
     * @param string $collectionName
     * @param HasMedia|null $model
     * @return void
     */
    public function __construct(string $collectionName, $model = null)
    {
        $this->collectionName = $collectionName;

        $this->model = $model;
    }
}
