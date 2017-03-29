<?php

namespace Spatie\MediaLibrary;

use Spatie\MediaLibrary\Helpers\File;
use Illuminate\Contracts\Filesystem\Factory;
use Spatie\MediaLibrary\Events\MediaHasBeenAdded;
use Spatie\MediaLibrary\PathGenerator\PathGeneratorFactory;
use Illuminate\Contracts\Config\Repository as ConfigRepository;

class Filesystem implements FilesystemInterface
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
     * @var array
     */
    protected $customRemoteHeaders = [];

    /**
     * @param \Illuminate\Contracts\Filesystem\Factory $filesystems
     * @param \Illuminate\Contracts\Config\Repository  $config
     */
    public function __construct(Factory $filesystems, ConfigRepository $config)
    {
        $this->filesystem = $filesystems;
        $this->config = $config;
    }

    /*
     * Add a file to the mediaLibrary for the given media.
     */
    public function add(string $file, Media $media, string $targetFileName = '')
    {
        $this->copyToMediaLibrary($file, $media, false, $targetFileName);

        event(new MediaHasBeenAdded($media));

        app(FileManipulator::class)->createDerivedFiles($media);
    }

    /*
     * Copy a file to the medialibrary for the given $media.
     */
    public function copyToMediaLibrary(string $file, Media $media, bool $conversions = false, string $targetFileName = '')
    {
        $destination = $this->getMediaDirectory($media, $conversions).
            ($targetFileName == '' ? pathinfo($file, PATHINFO_BASENAME) : $targetFileName);

        if ($media->getDiskDriverName() === 'local') {
            $this->filesystem
                ->disk($media->disk)
                ->put($destination, fopen($file, 'r'));

            return;
        }

        $this->filesystem
            ->disk($media->disk)
            ->getDriver()
            ->putStream($destination, fopen($file, 'r'), $this->getRemoteHeadersForFile($file));
    }

    /**
     * Add custom remote headers on runtime.
     *
     * @param array $customRemoteHeaders
     */
    public function addCustomRemoteHeaders(array $customRemoteHeaders)
    {
        $this->customRemoteHeaders = $customRemoteHeaders;
    }

    /*
     * Get the headers to be used when copying the
     * given file to a remote filesytem.
     */
    public function getRemoteHeadersForFile(string $file) : array
    {
        $mimeTypeHeader = ['ContentType' => File::getMimeType($file)];

        $extraHeaders = $this->config->get('laravel-medialibrary.remote.extra_headers');

        return array_merge($mimeTypeHeader, $extraHeaders, $this->customRemoteHeaders);
    }

    /*
     * Copy a file from the medialibrary to the given targetFile.
     */
    public function copyFromMediaLibrary(Media $media, string $targetFile)
    {
        $sourceFile = $this->getMediaDirectory($media).'/'.$media->file_name;

        touch($targetFile);

        $stream = $this->filesystem->disk($media->disk)->readStream($sourceFile);
        file_put_contents($targetFile, stream_get_contents($stream), FILE_APPEND);
        fclose($stream);
    }

    /*
     * Remove all files for the given media.
     */
    public function removeFiles(Media $media)
    {
        $mediaDirectory = $this->getMediaDirectory($media);

        $conversionsDirectory = $this->getConversionDirectory($media);

        collect([$mediaDirectory, $conversionsDirectory])
            ->each(function ($directory) use ($media) {
                if ($this->filesystem->disk($media->disk)->has($directory)) {
                    $this->filesystem->disk($media->disk)->deleteDirectory($directory);
                }
            });
    }

    /*
     * Rename a file for the given media.
     */
    public function renameFile(Media $media, string $oldName)
    {
        $oldFile = $this->getMediaDirectory($media).'/'.$oldName;
        $newFile = $this->getMediaDirectory($media).'/'.$media->file_name;

        $this->filesystem->disk($media->disk)->move($oldFile, $newFile);
    }

    /*
     * Return the directory where all files of the given media are stored.
     */
    public function getMediaDirectory(Media $media, bool $conversion = false) : string
    {
        $pathGenerator = PathGeneratorFactory::create();

        $directory = $conversion ?
            $pathGenerator->getPathForConversions($media) :
            $pathGenerator->getPath($media);

        if (! in_array($media->getDiskDriverName(), ['s3'], true)) {
            $this->filesystem->disk($media->disk)->makeDirectory($directory);
        }

        return $directory;
    }

    /*
     * Return the directory where all conversions of the given media are stored.
     */
    public function getConversionDirectory(Media $media) : string
    {
        return $this->getMediaDirectory($media, true);
    }
}
