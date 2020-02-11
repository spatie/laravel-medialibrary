<?php

namespace Spatie\Medialibrary\Tests\Support\testfiles;

use Spatie\Medialibrary\Conversion\Conversion;
use Spatie\Medialibrary\Conversion\ConversionFileNamer;
use Spatie\Medialibrary\Conversion\DefaultConversionFileNamer;
use Spatie\Medialibrary\Models\Media;

class TestConversionFileNamer extends DefaultConversionFileNamer
{
    public function getName(Conversion $conversion, Media $media): string
    {
        $fileName = pathinfo($media->file_name, PATHINFO_FILENAME);

        $extension = $this->getExtension($conversion, $media);

        return "{$fileName}---{$conversion->getName()}.{$extension}";
    }
}
