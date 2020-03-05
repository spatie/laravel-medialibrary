<?php

namespace Spatie\MediaLibrary\Conversions;

use Spatie\MediaLibrary\MediaCollections\Models\Media;

abstract class ConversionFileNamer
{
    abstract public function getFileName(Conversion $conversion, Media $media): string;

    public function getExtension(Conversion $conversion, Media $media): string
    {
        $fileExtension = pathinfo($media->file_name, PATHINFO_EXTENSION);

        return $conversion->getResultExtension($fileExtension) ?: $fileExtension;
    }
}
