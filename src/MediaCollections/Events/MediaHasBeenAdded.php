<?php

namespace Programic\MediaLibrary\MediaCollections\Events;

use Illuminate\Queue\SerializesModels;
use Programic\MediaLibrary\MediaCollections\Models\Media;

class MediaHasBeenAdded
{
    use SerializesModels;

    public function __construct(public Media $media)
    {
    }
}
