<?php

namespace Spatie\MediaLibrary\Events;

use Illuminate\Queue\SerializesModels;
use Spatie\MediaLibrary\Conversion\Conversion;
use Spatie\MediaLibrary\Models\Media;

class ConversionWillStart
{
    use SerializesModels;

    /** @var \Spatie\MediaLibrary\Models\Media */
    public $media;

    /** @var \Spatie\MediaLibrary\Conversion\Conversion */
    public $conversion;

    /** @var string */
    public $copiedOriginalFile;

    public function __construct(Media $media, Conversion $conversion, string $copiedOriginalFile)
    {
        $this->media = $media;

        $this->conversion = $conversion;

        $this->copiedOriginalFile = $copiedOriginalFile;
    }
}
