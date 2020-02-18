<?php

namespace Spatie\Medialibrary\Conversion;

use Spatie\Medialibrary\Models\Media;

class DefaultConversionFileNamer implements ConversionFileNamer
{
    public function getFileName(Conversion $conversion, Media $media): string
    {
        return "{$media->getFile()->name}-{$conversion->getName()}.{$media->getFile()->extension}";
    }
}
