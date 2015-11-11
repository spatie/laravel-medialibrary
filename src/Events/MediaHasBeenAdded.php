<?php

namespace Spatie\MediaLibrary\Events;

use Illuminate\Queue\SerializesModels;
use Spatie\MediaLibrary\Media;

class MediaHasBeenAdded
{
    use SerializesModels;

    /**
     * @var \Spatie\MediaLibrary\Media
     */
    public $media;

    /**
     * MediaHasBeenStoredEvent constructor.
     *
     * @param \Spatie\MediaLibrary\Media $media
     */
    public function __construct(Media $media)
    {
        $this->media = $media;
    }
}
