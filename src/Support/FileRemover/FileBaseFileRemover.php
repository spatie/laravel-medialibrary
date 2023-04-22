<?php

namespace Spatie\MediaLibrary\Support\FileRemover;

use Illuminate\Contracts\Filesystem\Factory;
use Spatie\MediaLibrary\MediaCollections\Filesystem;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\FileRemover\FileRemover;
use Spatie\MediaLibrary\Support\FileRemover\DefaultFileRemover;

class FileBaseFileRemover extends DefaultFileRemover implements FileRemover
{
    public function __construct(private Filesystem $mediaFileSystem, private Factory $filesystem)
    {}

    public function removeAllFiles(Media $media): void
    {
        $this->removeFile($this->mediaFileSystem->getMediaDirectory($media). $media->file_name, $media->disk);

        $this->removeConvertedImages($media);
    }

    public function removeConvertedImages(Media $media): void
    {
        $conversionsDirectory = $this->mediaFileSystem->getConversionDirectory($media);
        $mediaDirectory = $this->mediaFileSystem->getMediaDirectory($media);

        collect($media->getMediaConversionNames())->each(function ($conversionName) use ($media) {
            $this->removeFile(
                path: $media->getPathRelativeToRoot($conversionName),
                disk: $media->conversions_disk
            );

            $this->mediaFileSystem->removeResponsiveImages($media, $conversionName);
        });
    }

    public function removeFile(string $path, string $disk): void
    {
        $this->filesystem->disk($disk)->delete($path);
    }
}