<?php

namespace Spatie\Medialibrary\MediaCollections\Events;

use Illuminate\Queue\SerializesModels;
use Spatie\Medialibrary\MediaCollections\Models\Media;

class MediaHasBeenAdded
{
    use SerializesModels;

    public Media $media;

    public function __construct(Media $media)
    {
        $this->media = $media;
    }
}
