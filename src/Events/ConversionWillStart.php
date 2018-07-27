<?php

namespace Spatie\MediaLibrary\Events;

use Spatie\MediaLibrary\Models\Media;
use Illuminate\Queue\SerializesModels;
use Spatie\MediaLibrary\Conversion\Conversion;

class ConversionWillStart
{
    use SerializesModels;

    /** @var \Spatie\MediaLibrary\Models\Media */
    public $media;

    /** @var \Spatie\MediaLibrary\Conversion\Conversion */
    public $conversion;

    /** @var string */
    public $copiedOriginalFile;

    public function __construct(Media $media, Conversion $conversion, String $copiedOriginalFile)
    {
        $this->media = $media;

        $this->conversion = $conversion;

        $this->copiedOriginalFile = $copiedOriginalFile;
    }
}
