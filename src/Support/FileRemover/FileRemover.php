<?php

namespace Spatie\MediaLibrary\Support\FileRemover;

use Illuminate\Contracts\Filesystem\Factory;
use Spatie\MediaLibrary\MediaCollections\Filesystem;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

interface FileRemover
{
    /*
     * Create a new file remover instance, using the media library file system and illuminate filesystem.
     */
    public function __construct(Filesystem $mediaFileSystem, Factory $filesystem);

    /*
     * Remove all files relating to the media model.
     */
    public function removeAllFiles(Media $media): void;

    /*
     * Remove all converted files relating to the media model.
     */
    public function removeResponsiveImages(Media $media, string $conversionName): void;

    /*
     * Remove all responsive image files relating to the media model.
     */
    public function removeFile(string $path, string $disk): void;

}