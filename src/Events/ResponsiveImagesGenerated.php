<?php

namespace Spatie\MediaLibrary\Events;

use Illuminate\Queue\SerializesModels;
use Spatie\MediaLibrary\Models\Media;

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
