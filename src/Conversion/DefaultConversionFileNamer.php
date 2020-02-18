<?php

namespace Spatie\Medialibrary\Conversion;

use Spatie\Medialibrary\Models\Media;

class DefaultConversionFileNamer extends ConversionFileNamer
{
    public function getFileName(Conversion $conversion, Media $media): string
    {
        $fileName = pathinfo($media->file_name, PATHINFO_FILENAME);

        return "{$fileName}-{$conversion->getName()}";
    }
}
