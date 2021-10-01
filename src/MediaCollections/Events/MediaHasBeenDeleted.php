<?php

namespace Spatie\MediaLibrary\MediaCollections\Events;

use Illuminate\Queue\SerializesModels;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaHasBeenDeleted
{
    use SerializesModels;

    public Media $media;

    public function __construct(Media $media)
    {
        $this->media = $media;
    }
}
