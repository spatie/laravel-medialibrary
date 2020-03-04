<?php

namespace Spatie\Medialibrary\Conversions\Events;

use Illuminate\Queue\SerializesModels;
use Spatie\Medialibrary\Conversions\Conversion;
use Spatie\Medialibrary\MediaCollections\Models\Media;

class ConversionWillStart
{
    use SerializesModels;

    public Media $media;

    public Conversion $conversion;

    public string $copiedOriginalFile;

    public function __construct(Media $media, Conversion $conversion, string $copiedOriginalFile)
    {
        $this->media = $media;

        $this->conversion = $conversion;

        $this->copiedOriginalFile = $copiedOriginalFile;
    }
}
