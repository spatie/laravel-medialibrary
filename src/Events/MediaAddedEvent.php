<?php

namespace Spatie\MediaLibrary\Events;

use Illuminate\Queue\SerializesModels;
use Spatie\MediaLibrary\Media;

class MediaAddedEvent
{

    use SerializesModels;

    /**
     * @var \Spatie\MediaLibrary\Media
     */
    protected $media;

    /**
     * MediaHasBeenStoredEvent constructor.
     *
     * @param \Spatie\MediaLibrary\Media $media
     */
    public function __construct(Media $media)
    {
        $this->media = $media;
    }

    /**
     * @return Media
     */
    public function getMedia()
    {
        return $this->media;
    }

}
