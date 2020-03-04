<?php

namespace Spatie\Medialibrary\ResponsiveImages\Events;

use Illuminate\Queue\SerializesModels;
use Spatie\Medialibrary\MediaCollections\Models\Media;

class ResponsiveImagesGenerated
{
    use SerializesModels;

    public Media $media;

    public function __construct(Media $media)
    {
        $this->media = $media;
    }
}
