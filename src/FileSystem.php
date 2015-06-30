<?php

namespace Spatie\MediaLibrary;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Filesystem\Filesystem as LaravelFileSystem;

class FileSystem
{
    /**
     * @var Filesystem
     */
    private $disk;
    /**
     * @var Repository
     */
    private $config;

    public function __construct(LaravelFileSystem $disk, Repository $config)
    {
        $this->disk = $disk;
        $this->config = $config;
    }

    /**
     * Add a file to the mediaLibrary for the given media.
     *
     * @param $file
     * @param Media $media
     */
    public function add($file, Media $media)
    {
        $this->copyToMediaLibrary($file, $media);

        app(FileManipulator::class)->createDerivedFiles($media);
    }

    /**
     * Copy a file to the mediaLibrary for the given $media.
     *
     * @param $file
     * @param $media
     * @param string $subDirectory
     */
    public function copyToMediaLibrary($file, $media, $subDirectory = '')
    {
        $destination = $this->getMediaDirectory($media).'/'.($subDirectory != '' ? $subDirectory.'/' : '').pathinfo($file, PATHINFO_BASENAME);

        $this->disk->getDriver()->writeStream($destination, fopen($file, 'r+'));
    }

    /**
     * Copy a file from the mediaLibrary to the given targetFile.
     *
     * @param Media  $media
     * @param string $targetFile
     */
    public function copyFromMediaLibrary(Media $media, $targetFile)
    {
        $sourceFile = $this->getMediaDirectory($media).'/'.$media->file_name;

        touch($targetFile);

        $stream = $this->disk->getDriver()->readStream($sourceFile);
        file_put_contents($targetFile, stream_get_contents($stream), FILE_APPEND);
        fclose($stream);
    }

    /**
     * Remove all files for the given media.
     *
     * @param Media $media
     */
    public function removeFiles(Media $media)
    {
        $this->disk->deleteDirectory($this->getMediaDirectory($media));
    }

    /**
     * Return the directory where all files of the given media are stored.
     *
     * @param Media $media
     *
     * @return string
     */
    public function getMediaDirectory(Media $media)
    {
        $directory = $media->id;

        $this->disk->makeDirectory($directory);
        $this->disk->makeDirectory($directory.'/conversions');

        return $directory;
    }
}
