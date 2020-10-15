<?php

namespace Spatie\MediaLibrary\Support\FileNamer;

use Spatie\MediaLibrary\MediaCollections\Models\Media;

abstract class FileNamer
{
    abstract public function getFileName(string $fileName): string;

    public function addPropertiesToFileName(string $fileName, string $conversionName, int $width, int $height, string $extension): string
    {
        return "{$this->getFileName($fileName)}___{$conversionName}_{$width}_{$height}.{$extension}";
    }

    public function getTemporaryFileName(Media $media, string $extension): string
    {
        return "{$this->getFileName($media->file_name)}.{$extension}";
    }

    public function getExtension(string $baseImage): string
    {
        return pathinfo($baseImage, PATHINFO_EXTENSION);
    }
}
