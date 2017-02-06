<?php

namespace Spatie\MediaLibrary\Events;

use Spatie\MediaLibrary\Media;
use Illuminate\Queue\SerializesModels;

class MediaHasBeenAdded
{
    use SerializesModels;

    /** @var \Spatie\MediaLibrary\Media */
    public $media;

    /* @param \Spatie\MediaLibrary\Media $media */
    public function __construct(Media $media)
    {
        $this->media = $media;
    }
}
