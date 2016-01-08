<?php

namespace Spatie\MediaLibrary;

use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Filesystem\Factory;
use Spatie\MediaLibrary\Events\MediaHasBeenAdded;
use Spatie\MediaLibrary\Helpers\File;
use Spatie\MediaLibrary\PathGenerator\PathGeneratorFactory;

class Filesystem
{
    /**
     * @var \Illuminate\Contracts\Filesystem\Factory
     */
    protected $filesystem;

    /**
     * @var \Illuminate\Contracts\Config\Repository
     */
    protected $config;

    /**
     * @var \Illuminate\Contracts\Events\Dispatcher
     */
    protected $events;

    /**
     * @param Factory                                 $filesystems
     * @param \Illuminate\Contracts\Config\Repository $config
     */
    public function __construct(Factory $filesystems, ConfigRepository $config, Dispatcher $events)
    {
        $this->filesystem = $filesystems;
        $this->config = $config;
        $this->events = $events;
    }

    /**
     * Add a file to the mediaLibrary for the given media.
     *
     * @param string                     $file
     * @param \Spatie\MediaLibrary\Media $media
     * @param string                     $targetFileName
     */
    public function add($file, Media $media, $targetFileName = '')
    {
        $this->copyToMediaLibrary($file, $media, false, $targetFileName);

        $this->events->fire(new MediaHasBeenAdded($media));

        app(FileManipulator::class)->createDerivedFiles($media);
    }

    /**
     * Copy a file to the mediaLibrary for the given $media.
     *
     * @param string                     $file
     * @param \Spatie\MediaLibrary\Media $media
     * @param bool                       $conversions
     * @param string                     $targetFileName
     */
    public function copyToMediaLibrary($file, Media $media, $conversions = false, $targetFileName = '')
    {
        $destination = $this->getMediaDirectory($media, $conversions).
            ($targetFileName == '' ? pathinfo($file, PATHINFO_BASENAME) : $targetFileName);

        if ($media->getDiskDriverName() === 'local') {
            $this->filesystem
                ->disk($media->disk)
                ->put($destination, fopen($file, 'r+'));
        } else {
            $this->filesystem
                ->disk($media->disk)
                ->getDriver()
                ->put($destination, fopen($file, 'r+'), $this->getRemoteHeadersForFile($file));
        }
    }

    /**
     * Get the headers to be used when copying the
     * given file to a remote filesytem.
     *
     * @param string $file
     *
     * @return array
     */
    public function getRemoteHeadersForFile($file)
    {
        $mimeTypeHeader = ['ContentType' => File::getMimeType($file)];

        $extraHeaders = $this->config->get('laravel-medialibrary.remote.extra_headers');

        return array_merge($mimeTypeHeader, $extraHeaders);
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

        $stream = $this->filesystem->disk($media->disk)->readStream($sourceFile);
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
        $this->filesystem->disk($media->disk)->deleteDirectory($this->getMediaDirectory($media));
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

        $this->filesystem->disk($media->disk)->move($oldFile, $newFile);

        return true;
    }

    /**
     * Return the directory where all files of the given media are stored.
     *
     * @param \Spatie\MediaLibrary\Media $media
     *
     * @return string
     */
    public function getMediaDirectory(Media $media, $conversion = false)
    {
        $pathGenerator = PathGeneratorFactory::create();

        $directory = $conversion ?
            $pathGenerator->getPathForConversions($media) :
            $pathGenerator->getPath($media);

        $this->filesystem->disk($media->disk)->makeDirectory($directory);

        return $directory;
    }
}
