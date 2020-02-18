<?php

namespace Spatie\Medialibrary\Conversion;

use Spatie\Medialibrary\Models\Media;

interface ConversionFileNamer
{
    public function getFileName(Conversion $conversion, Media $media): string;
}
