<?php

namespace Spatie\Medialibrary\Features\ResponsiveImages\Events;

use Illuminate\Queue\SerializesModels;
use Spatie\Medialibrary\Features\MediaCollections\Models\Media;

class ResponsiveImagesGenerated
{
    use SerializesModels;

    public Media $media;

    public function __construct(Media $media)
    {
        $this->media = $media;
    }
}
