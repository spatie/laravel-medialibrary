<?php

namespace Spatie\MediaLibrary\Events;

use Illuminate\Queue\SerializesModels;
use Spatie\MediaLibrary\Conversion\Conversion;
use Spatie\MediaLibrary\Models\Media;

class ConversionHasBeenCompleted
{
    use SerializesModels;

    /** @var \Spatie\MediaLibrary\Models\Media */
    public $media;

    /** @var \Spatie\MediaLibrary\Conversion\Conversion */
    public $conversion;

    public function __construct(Media $media, Conversion $conversion)
    {
        $this->media = $media;

        $this->conversion = $conversion;
    }
}
