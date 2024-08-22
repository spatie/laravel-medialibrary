<?php

namespace Spatie\MediaLibrary\Support\FileRemover;

use Exception;
use Illuminate\Contracts\Filesystem\Factory;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Filesystem;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class DefaultFileRemover implements FileRemover
{
    public function __construct(protected Filesystem $mediaFileSystem, protected Factory $filesystem) {}

    public function removeAllFiles(Media $media): void
    {

        if ($media->conversions_disk && $media->disk !== $media->conversions_disk) {
            $this->removeFromConversionsDirectory($media, $media->conversions_disk);
            $this->removeFromResponsiveImagesDirectory($media, $media->conversions_disk);
            $this->removeFromMediaDirectory($media, $media->conversions_disk);
        }

        $this->removeFromConversionsDirectory($media, $media->disk);
        $this->removeFromResponsiveImagesDirectory($media, $media->disk);
        $this->removeFromMediaDirectory($media, $media->disk);
    }

    public function removeFromMediaDirectory(Media $media, string $disk): void
    {
        $mediaDirectory = $this->mediaFileSystem->getMediaDirectory($media);

        collect([$mediaDirectory])
            ->each(function (string $directory) use ($media, $disk) {
                try {
                    $allFilePaths = $this->filesystem->disk($disk)->allFiles($directory);
                    $imagePaths = array_filter(
                        $allFilePaths,
                        function (string $path) use ($media) {
                            return Str::afterLast($path, '/') === $media->file_name;
                        }
                    );
                    foreach ($imagePaths as $imagePath) {
                        $this->filesystem->disk($disk)->delete($imagePath);
                    }

                    if (! $this->filesystem->disk($disk)->allFiles($directory)) {
                        $this->filesystem->disk($disk)->deleteDirectory($directory);
                    }
                } catch (Exception $exception) {
                    report($exception);
                }
            });

    }

    public function removeFromConversionsDirectory(Media $media, string $disk): void
    {
        $conversionsDirectory = $this->mediaFileSystem->getMediaDirectory($media, 'conversions');

        collect([$conversionsDirectory])
            ->each(function (string $directory) use ($media, $disk) {
                try {
                    $allFilePaths = $this->filesystem->disk($disk)->allFiles($directory);

                    $conversions = array_keys($media->generated_conversions ?? []);

                    $imagePaths = array_filter(
                        $allFilePaths,
                        function (string $path) use ($conversions, $media) {
                            foreach ($conversions as $conversion) {
                                if (Str::contains($path, pathinfo($media->file_name, PATHINFO_FILENAME).'-'.$conversion)) {
                                    return true;
                                }
                            }

                            return false;
                        }
                    );
                    foreach ($imagePaths as $imagePath) {
                        $this->filesystem->disk($disk)->delete($imagePath);
                    }

                    if (! $this->filesystem->disk($disk)->allFiles($directory)) {
                        $this->filesystem->disk($disk)->deleteDirectory($directory);
                    }
                } catch (Exception $exception) {
                    report($exception);
                }
            });
    }

    public function removeFromResponsiveImagesDirectory(Media $media, string $disk): void
    {
        $responsiveImagesDirectory = $this->mediaFileSystem->getMediaDirectory($media, 'responsiveImages');

        collect([$responsiveImagesDirectory])
            ->unique()
            ->each(function (string $directory) use ($media, $disk) {
                try {
                    $allFilePaths = $this->filesystem->disk($disk)->allFiles($directory);

                    $conversions = array_keys($media->generated_conversions ?? []);
                    $conversions[] = 'media_library_original';

                    $imagePaths = array_filter(
                        $allFilePaths,
                        function (string $path) use ($conversions, $media) {
                            foreach ($conversions as $conversion) {
                                if (Str::contains($path, pathinfo($media->file_name, PATHINFO_FILENAME).'___'.$conversion)) {
                                    return true;
                                }
                            }

                            return false;
                        }
                    );
                    foreach ($imagePaths as $imagePath) {
                        $this->filesystem->disk($disk)->delete($imagePath);
                    }

                    if (! $this->filesystem->disk($disk)->allFiles($directory)) {
                        $this->filesystem->disk($disk)->deleteDirectory($directory);
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
