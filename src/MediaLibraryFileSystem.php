<?php

namespace Spatie\MediaLibrary;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Filesystem\Filesystem;

class MediaLibraryFileSystem
{
    /**
     * @var Filesystem
     */
    private $disk;
    /**
     * @var Repository
     */
    private $config;

    public function __construct(Filesystem $disk, Repository $config)
    {
        $this->disk = $disk;
        $this->config = $config;
    }

    public function addFileForMedia($file, $media)
    {
        $this->copyFileToMediaLibraryForMedia($file, $media);

        app(MediaLibraryFileManipulator::class)->createDerivedFiles($media);
    }

    public function copyFileToMediaLibraryForMedia($file, $media)
    {
        $destination = $this->getMediaDirectory($media) . '/' . pathinfo($file, PATHINFO_BASENAME);

        $this->disk->getDriver()->writeStream($destination, fopen($file, 'r+'));
    }

    public function removeFiles(Media $media)
    {
        $this->disk->deleteDirectory($this->getMediaDirectory($media));
    }

    public function getPaths(Media $media)
    {
        $result = [];

        $paths = $this->disk->allFiles($this->getMediaDirectory($media));

        foreach($paths as $path) {
            $result[$this->getProfileName($path)] = $path;
        }

        return $result;
    }

    public function getDriverType()
    {
        return $this->config->get('filesystems.' . $this->config('laravel-medialibrary.filesystem') . '.name');
    }

    protected function getProfileName($path)
    {
        return string($path)->between('', '_');
    }

    public function getMediaDirectory(Media $media)
    {
        if ($this->config['storage_path'] != '')  {
            return $this->config->get('laravel-medialibrary.storage_path') . '/' . $media->id;
        }

        return $media->id;
    }
}