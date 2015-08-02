<?php

namespace Spatie\MediaLibrary;

use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Contracts\Filesystem\Filesystem as LaravelFilesystem;
use Spatie\MediaLibrary\Helpers\Gitignore;

class Filesystem
{
    /**
     * @var \Illuminate\Contracts\Filesystem\Filesystem
     */
    protected $disk;
    /**
     * @var \Illuminate\Contracts\Config\Repository
     */
    protected $config;

    /**
     * @param \Illuminate\Contracts\Filesystem\Filesystem $disk
     * @param \Illuminate\Contracts\Config\Repository     $config
     */
    public function __construct(LaravelFilesystem $disk, ConfigRepository $config)
    {
        $this->disk = $disk;
        $this->config = $config;
    }

    /**
     * Add a file to the mediaLibrary for the given media.
     *
     * @param string $file
     * @param \Spatie\MediaLibrary\Media $media
     * @param $targetFileName
     */
    public function add($file, Media $media, $targetFileName = '')
    {
        $this->copyToMediaLibrary($file, $media, '', $targetFileName);

        app(FileManipulator::class)->createDerivedFiles($media);
    }

    /**
     * Copy a file to the mediaLibrary for the given $media.
     *
     * @param string $file
     * @param \Spatie\MediaLibrary\Media $media
     * @param string $subDirectory
     * @param string $targetFileName
     */
    public function copyToMediaLibrary($file, Media $media, $subDirectory = '', $targetFileName = '')
    {
        $destination = $this->getMediaDirectory($media).'/'.($subDirectory != '' ? $subDirectory.'/' : '').
            ($targetFileName == '' ? pathinfo($file, PATHINFO_BASENAME) : $targetFileName);

        $this->disk->put($destination, fopen($file, 'r+'));
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
     * Rename a file for the given media.
     *
     * @param Media  $media
     * @param string $oldName
     *
     * @return bool
     */
    public function renameFile(Media $media, $oldName)
    {
        $oldFile = $this->getMediaDirectory($media).'/'.$oldName;
        $newFile = $this->getMediaDirectory($media).'/'.$media->file_name;

        $this->disk->move($oldFile, $newFile);

        return true;
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
