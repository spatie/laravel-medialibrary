<?php

namespace Spatie\MediaLibrary\Tests\TestSupport\testfiles;

use Spatie\MediaLibrary\Conversions\Conversion;
use Spatie\MediaLibrary\Conversions\DefaultConversionFileNamer;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class TestConversionFileNamer extends DefaultConversionFileNamer
{
    public function getFileName(Conversion $conversion, Media $media): string
    {
        $fileName = pathinfo($media->file_name, PATHINFO_FILENAME);

        return "{$fileName}---{$conversion->getName()}";
    }
}
