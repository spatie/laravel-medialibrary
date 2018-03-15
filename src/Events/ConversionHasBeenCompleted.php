<?php

namespace Spatie\MediaLibrary\Events;

use Spatie\MediaLibrary\Models\Media;
use Illuminate\Queue\SerializesModels;
use Spatie\MediaLibrary\Conversion\Conversion;

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
