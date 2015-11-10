<?php

namespace Spatie\MediaLibrary\Events;

use Illuminate\Queue\SerializesModels;
use Spatie\MediaLibrary\Conversion\Conversion;
use Spatie\MediaLibrary\Media;

class ConversionCompleteEvent
{

    use SerializesModels;

    /**
     * @var \Spatie\MediaLibrary\Media
     */
    protected $media;

    /**
     * @var \Spatie\MediaLibrary\Conversion\Conversion
     */
    protected $conversion;

    /**
     * ConversionHasFinishedEvent constructor.
     *
     * @param \Spatie\MediaLibrary\Media                 $media
     * @param \Spatie\MediaLibrary\Conversion\Conversion $conversion
     */
    public function __construct(Media $media, Conversion $conversion)
    {
        $this->media = $media;
        $this->conversion = $conversion;
    }

    /**
     * @return Media
     */
    public function getMedia()
    {
        return $this->media;
    }

    /**
     * @return Conversion
     */
    public function getConversion()
    {
        return $this->conversion;
    }
}
