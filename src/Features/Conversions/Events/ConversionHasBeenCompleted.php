<?php

namespace Spatie\Medialibrary\Features\Conversions\Events;

use Illuminate\Queue\SerializesModels;
use Spatie\Medialibrary\Features\Conversions\Conversion;
use Spatie\Medialibrary\Features\MediaCollections\Models\Media;

class ConversionHasBeenCompleted
{
    use SerializesModels;

    public Media $media;

    public Conversion $conversion;

    public function __construct(Media $media, Conversion $conversion)
    {
        $this->media = $media;

        $this->conversion = $conversion;
    }
}
