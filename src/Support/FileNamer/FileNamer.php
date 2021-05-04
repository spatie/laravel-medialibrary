<?php

namespace Spatie\MediaLibrary\Support\FileNamer;

use Spatie\MediaLibrary\Conversions\Conversion;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

abstract class FileNamer
{
    public function originalFileName(string $fileName): string
    {
        return pathinfo($fileName, PATHINFO_FILENAME);
    }

    abstract public function conversionFileName(string $fileName, Conversion $conversion): string;

    abstract public function responsiveFileName(string $fileName): string;

    public function temporaryFileName(Media $media, string $extension): string
    {
        return "{$this->responsiveFileName($media->file_name)}.{$extension}";
    }

    public function extensionFromBaseImage(string $baseImage): string
    {
        return pathinfo($baseImage, PATHINFO_EXTENSION);
    }
}
