<?php

namespace Spatie\Medialibrary\Features\Conversions;

use Spatie\Medialibrary\Features\MediaCollections\Models\Media;

abstract class ConversionFileNamer
{
    abstract public function getFileName(Conversion $conversion, Media $media): string;

    public function getExtension(Conversion $conversion, Media $media): string
    {
        $fileExtension = pathinfo($media->file_name, PATHINFO_EXTENSION);

        return $conversion->getResultExtension($fileExtension) ?: $fileExtension;
    }
}
