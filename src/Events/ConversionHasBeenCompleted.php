<?php

namespace Spatie\Medialibrary\Events;

use Illuminate\Queue\SerializesModels;
use Spatie\Medialibrary\Conversion\Conversion;
use Spatie\Medialibrary\Models\Media;

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
