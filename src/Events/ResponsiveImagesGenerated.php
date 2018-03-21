<?php

namespace Spatie\MediaLibrary\Events;

use Spatie\MediaLibrary\Models\Media;
use Illuminate\Queue\SerializesModels;

class ResponsiveImagesGenerated
{
    use SerializesModels;

    /** @var \Spatie\MediaLibrary\Models\Media */
    public $media;

    public function __construct(Media $media)
    {
        $this->media = $media;
    }
}
