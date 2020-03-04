<?php

namespace Spatie\Medialibrary\Tests\Support\testfiles;

use Spatie\Medialibrary\Features\Conversions\Conversion;
use Spatie\Medialibrary\Features\Conversions\ConversionFileNamer;
use Spatie\Medialibrary\Features\Conversions\DefaultConversionFileNamer;
use Spatie\Medialibrary\Features\MediaCollections\Models\Media;

class TestConversionFileNamer extends DefaultConversionFileNamer
{
    public function getFileName(Conversion $conversion, Media $media): string
    {
        $fileName = pathinfo($media->file_name, PATHINFO_FILENAME);

        return "{$fileName}---{$conversion->getName()}";
    }
}
