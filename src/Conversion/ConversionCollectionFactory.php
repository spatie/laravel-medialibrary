<?php

namespace Spatie\MediaLibrary\Conversion;

use Spatie\MediaLibrary\Media;

class ConversionCollectionFactory
{
    /**
     * @param \Spatie\MediaLibrary\Media $media
     *
     * @return \Spatie\MediaLibrary\Conversion\ConversionCollection
     */
    public static function createForMedia(Media $media)
    {
        return (new ConversionCollection())->setMedia($media);
    }
}
