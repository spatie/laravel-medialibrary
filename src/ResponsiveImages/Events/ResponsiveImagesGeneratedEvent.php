<?php

namespace Spatie\MediaLibrary\ResponsiveImages\Events;

use Illuminate\Queue\SerializesModels;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ResponsiveImagesGeneratedEvent
{
    use SerializesModels;

    public function __construct(public Media $media) {}
}
