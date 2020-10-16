<?php

namespace Spatie\MediaLibrary\Support\FileNamer;

use Spatie\MediaLibrary\Conversions\Conversion;

class DefaultFileNamer extends FileNamer
{
    public function getFileName(string $fileName): string
    {
        return pathinfo($fileName, PATHINFO_FILENAME);
    }

    public function addConversionToFileName(string $fileName, Conversion $conversion): string
    {
        return "{$fileName}-{$conversion->getName()}";
    }
}
