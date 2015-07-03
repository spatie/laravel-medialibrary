<?php

namespace Spatie\MediaLibrary;

use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Contracts\Filesystem\Filesystem as LaravelFileSystem;
use Spatie\MediaLibrary\Helpers\Gitignore;

class FileSystem
{
    /**
     * @var \Illuminate\Contracts\Filesystem\Filesystem
     */
    protected $disk;
    /**
     * @var \Illuminate\Contracts\Config\Repository
     */
    protected $config;

    public function __construct(LaravelFileSystem $disk, ConfigRepository $config)
    {
        $this->disk = $disk;
        $this->config = $config;
    }

    /**
     * Add a file to the mediaLibrary for the given media.
     *
     * @param $file
     * @param \Spatie\MediaLibrary\Media $media
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

        $this->disk->getDriver()->putStream($destination, fopen($file, 'r+'));
    }

    /**
     * Copy a file from the mediaLibrary to the given targetFile.
     *
     * @param \Spatie\MediaLibrary\Media $media
     * @param string                     $targetFile
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
     * @param \Spatie\MediaLibrary\Media $media
     */
    public function removeFiles(Media $media)
    {
        $this->disk->deleteDirectory($this->getMediaDirectory($media));
    }

    /**
     * Return the directory where all files of the given media are stored.
     *
     * @param \Spatie\MediaLibrary\Media $media
     *
     * @return string
     */
    public function getMediaDirectory(Media $media)
    {
        $this->disk->put('.gitignore', Gitignore::getContents());

        $directory = $media->id;
        $this->disk->makeDirectory($directory);
        $this->disk->makeDirectory($directory.'/conversions');

        return $directory;
    }
}
