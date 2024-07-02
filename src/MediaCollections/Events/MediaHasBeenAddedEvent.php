<?php

namespace Spatie\MediaLibrary\MediaCollections\Events;

use Illuminate\Queue\SerializesModels;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaHasBeenAddedEvent
{
    use SerializesModels;

    public function __construct(public Media $media) {}
}
