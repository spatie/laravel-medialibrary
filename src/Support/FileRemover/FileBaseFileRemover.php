<?php

namespace Spatie\MediaLibrary\Support\FileRemover;

use Spatie\MediaLibrary\MediaCollections\Models\Media;

class FileBaseFileRemover extends DefaultFileRemover
{
    public function removeAllFiles(Media $media): void
    {
        $this->removeFile($this->mediaFileSystem->getMediaDirectory($media).$media->file_name, $media->disk);

        $this->removeConvertedImages($media);
    }

    public function removeConvertedImages(Media $media): void
    {
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
