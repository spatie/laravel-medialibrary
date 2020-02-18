<?php

namespace Spatie\Medialibrary\Tests\Support\testfiles;

use Spatie\Medialibrary\Conversion\Conversion;
use Spatie\Medialibrary\Conversion\ConversionFileNamer;
use Spatie\Medialibrary\Conversion\DefaultConversionFileNamer;
use Spatie\Medialibrary\Models\Media;

class TestConversionFileNamer extends DefaultConversionFileNamer
{
    public function getFileName(Conversion $conversion, Media $media): string
    {
        $fileName = pathinfo($media->file_name, PATHINFO_FILENAME);

        return "{$fileName}---{$conversion->getName()}";
    }
}
