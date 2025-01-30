<?php

namespace Spatie\MediaLibrary\Support\FileRemover;

use Illuminate\Contracts\Filesystem\Factory;
use Spatie\MediaLibrary\MediaCollections\Filesystem;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

interface FileRemover
{
    public function __construct(Filesystem $mediaFileSystem, Factory $filesystem);

    /*
     * Remove all files relating to the media model.
     */
    public function removeAllFiles(Media $media): void;

    /*
     * Remove responsive files relating to the media model.
     */
    public function removeResponsiveImages(Media $media, string $conversionName): void;

    /*
     * Remove a file relating to the media model.
     */
    public function removeFile(string $path, string $disk): void;
}
