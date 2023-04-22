<?php

namespace Spatie\MediaLibrary\Support\FileRemover;

use Exception;
use Illuminate\Support\Str;
use Illuminate\Contracts\Filesystem\Factory;
use Spatie\MediaLibrary\MediaCollections\Filesystem;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class DefaultFileRemover implements FileRemover
{
    public function __construct(private Filesystem $mediaFileSystem, private Factory $filesystem)
    {}

    public function removeAllFiles(Media $media): void
    {
        $mediaDirectory = $this->mediaFileSystem->getMediaDirectory($media);

        if ($media->disk !== $media->conversions_disk) {
            $this->filesystem->disk($media->disk)->deleteDirectory($mediaDirectory);
        }

        $conversionsDirectory = $this->mediaFileSystem->getMediaDirectory($media, 'conversions');

        $responsiveImagesDirectory = $this->mediaFileSystem->getMediaDirectory($media, 'responsiveImages');

        collect([$mediaDirectory, $conversionsDirectory, $responsiveImagesDirectory])
            ->unique()
            ->each(function (string $directory) use ($media) {
                try {
                    $this->filesystem->disk($media->conversions_disk)->deleteDirectory($directory);
                } catch (Exception $exception) {
                    report($exception);
                }
            });
    }

    public function removeConvertedImages(Media $media): void
    {
        //
    }

    public function removeResponsiveImages(Media $media, string $conversionName): void
    {
        $responsiveImagesDirectory = $this->mediaFileSystem->getResponsiveImagesDirectory($media);

        $allFilePaths = $this->filesystem->disk($media->disk)->allFiles($responsiveImagesDirectory);

        $responsiveImagePaths = array_filter(
            $allFilePaths,
            fn (string $path) => Str::contains($path, $conversionName)
        );

        $this->filesystem->disk($media->disk)->delete($responsiveImagePaths);
    }

    public function removeFile(string $path, string $disk): void
    {
        $this->filesystem->disk($disk)->delete($path);
    }
}