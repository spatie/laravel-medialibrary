<?php

namespace Spatie\Medialibrary\Conversion;

use Spatie\Medialibrary\Models\Media;

interface ConversionFileNamer
{
    public function getName(Conversion $conversion, Media $media): string;
}
