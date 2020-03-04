<?php

namespace Spatie\Medialibrary\Features\MediaCollections\Events;

use Illuminate\Queue\SerializesModels;
use Spatie\Medialibrary\Features\MediaCollections\Models\Media;

class MediaHasBeenAdded
{
    use SerializesModels;

    public Media $media;

    public function __construct(Media $media)
    {
        $this->media = $media;
    }
}
