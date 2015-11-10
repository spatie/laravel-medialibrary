<?php

namespace Spatie\MediaLibrary\Events;

use Illuminate\Queue\SerializesModels;
use Spatie\MediaLibrary\HasMedia\Interfaces\HasMedia;

class CollectionHasBeenClearedEvent
{

    use SerializesModels;

    /**
     * @var string
     */
    protected $collectionName;

    /**
     * @var \Spatie\MediaLibrary\HasMedia\Interfaces\HasMedia
     */
    protected $model;

    /**
     * MediaHasBeenStoredEvent constructor.
     *
     * @param \Spatie\MediaLibrary\HasMedia\Interfaces\HasMedia $model
     * @param string                                            $collectionName
     */
    public function __construct(HasMedia $model, $collectionName)
    {
        $this->model = $model;
        $this->collectionName = $collectionName;
    }

    /**
     * @return HasMedia
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * @return string
     */
    public function getCollectionName()
    {
        return $this->collectionName;
    }

}
