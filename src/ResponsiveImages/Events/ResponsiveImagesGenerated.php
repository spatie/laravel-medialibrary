<?php

namespace Programic\MediaLibrary\ResponsiveImages\Events;

use Illuminate\Queue\SerializesModels;
use Programic\MediaLibrary\MediaCollections\Models\Media;

class ResponsiveImagesGenerated
{
    use SerializesModels;

    public function __construct(public Media $media)
    {
    }
}
