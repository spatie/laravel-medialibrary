<?php

namespace Spatie\MediaLibrary\Support\FileRemover;

use Exception;
use Illuminate\Contracts\Filesystem\Factory;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Filesystem;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class DefaultFileRemover implements FileRemover
{
    public function __construct(protected Filesystem $mediaFileSystem, protected Factory $filesystem)
    {
    }

    public function removeAllFiles(Media $media): void
    {
        $this->removeFromMediaDirectory($media);

        $this->removeFromConversionsDirectory($media);

        $this->removeFromResponsiveImagesDirectory($media);
    }

    public function removeFromMediaDirectory(Media $media): void
    {
        $mediaDirectory = $this->mediaFileSystem->getMediaDirectory($media);


        collect([$mediaDirectory])
            ->each(function (string $directory) use ($media) {
                try {
                    $allFilePaths = $this->filesystem->disk($media->conversions_disk)->allFiles($directory);
                    $imagePaths = array_filter(
                        $allFilePaths,
                        function (string $path) use ($media) {
                            return Str::contains($path, $media->name.".");
                        }
                    );
                    foreach ($imagePaths as $imagePath) {
                        $this->filesystem->disk($media->conversions_disk)->delete($imagePath);
                    }

                    if (!$this->filesystem->disk($media->conversions_disk)->allFiles($directory)) {
                        $this->filesystem->disk($media->conversions_disk)->deleteDirectory($directory);
                    }
                } catch (Exception $exception) {
                    report($exception);
                }
            });
    }

    public function removeFromConversionsDirectory(Media $media): void
    {
        $conversionsDirectory = $this->mediaFileSystem->getMediaDirectory($media, 'conversions');

        collect([$conversionsDirectory])
            ->each(function (string $directory) use ($media) {
                try {
                    $allFilePaths = $this->filesystem->disk($media->conversions_disk)->allFiles($directory);

                    $conversions = array_keys($media->generated_conversions);

                    $imagePaths = array_filter(
                        $allFilePaths,
                        function (string $path) use ($conversions, $media) {
                            foreach ($conversions as $conversion) {
                                if (Str::contains($path, $media->name . "-" . $conversion)) {
                                    return true;
                                }
                            }
                            return false;
                        }
                    );
                    foreach ($imagePaths as $imagePath) {
                        $this->filesystem->disk($media->conversions_disk)->delete($imagePath);
                    }

                    if (!$this->filesystem->disk($media->conversions_disk)->allFiles($directory)) {
                        $this->filesystem->disk($media->conversions_disk)->deleteDirectory($directory);
                    }
                } catch (Exception $exception) {
                    report($exception);
                }
            });
    }

    public function removeFromResponsiveImagesDirectory(Media $media): void
    {
        $responsiveImagesDirectory = $this->mediaFileSystem->getMediaDirectory($media, 'responsiveImages');

        collect([ $responsiveImagesDirectory])
            ->unique()
            ->each(function (string $directory) use ($media) {
                try {
                    $allFilePaths = $this->filesystem->disk($media->conversions_disk)->allFiles($directory);

                    $conversions = array_keys($media->generated_conversions);
                    $conversions[] = "media_library_original";

                    $imagePaths = array_filter(
                        $allFilePaths,
                        function (string $path) use ($conversions, $media) {
                            foreach ($conversions as $conversion) {
                                if (Str::contains($path, $media->name . "___" . $conversion)) {
                                    return true;
                                }
                            }
                            return false;
                        }
                    );
                    foreach ($imagePaths as $imagePath) {
                        $this->filesystem->disk($media->conversions_disk)->delete($imagePath);
                    }

                    if (!$this->filesystem->disk($media->conversions_disk)->allFiles($directory)) {
                        $this->filesystem->disk($media->conversions_disk)->deleteDirectory($directory);
                    }
                } catch (Exception $exception) {
                    report($exception);
                }
            });
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
