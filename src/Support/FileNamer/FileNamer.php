<?php

namespace Spatie\MediaLibrary\Support\FileNamer;

use Spatie\MediaLibrary\Conversions\Conversion;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

abstract class FileNamer
{
    abstract public function getFileName(string $fileName): string;

    abstract public function getConversionFileName(string $fileName, Conversion $conversion): string;

    public function getTemporaryFileName(Media $media, string $extension): string
    {
        return "{$this->getFileName($media->file_name)}.{$extension}";
    }

    public function getExtension(string $baseImage): string
    {
        return pathinfo($baseImage, PATHINFO_EXTENSION);
    }
}
