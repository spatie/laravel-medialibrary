<?php

namespace Spatie\Medialibrary\Features\Conversions;

use Spatie\Medialibrary\Features\MediaCollections\Models\Media;

class DefaultConversionFileNamer extends ConversionFileNamer
{
    public function getFileName(Conversion $conversion, Media $media): string
    {
        $fileName = pathinfo($media->file_name, PATHINFO_FILENAME);

        return "{$fileName}-{$conversion->getName()}";
    }
}
