<?php

namespace Spatie\MediaLibrary\Filesystem;

use Spatie\MediaLibrary\Media;
use Spatie\MediaLibrary\Helpers\File;
use Spatie\MediaLibrary\FileManipulator;
use Illuminate\Contracts\Filesystem\Factory;
use Spatie\MediaLibrary\Events\MediaHasBeenAdded;
use Spatie\MediaLibrary\PathGenerator\PathGeneratorFactory;

class DefaultFilesystem implements Filesystem
{
    /** @var \Illuminate\Contracts\Filesystem\Factory */
    protected $filesystem;

    /** @var array */
    protected $customRemoteHeaders = [];

    public function __construct(Factory $filesystems)
    {
        $this->filesystem = $filesystems;
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
    public function copyToMediaLibrary(string $pathToFile, Media $media, bool $conversions = false, string $targetFileName = '')
    {
        $destination = $this->getMediaDirectory($media, $conversions).
            ($targetFileName == '' ? pathinfo($pathToFile, PATHINFO_BASENAME) : $targetFileName);

        $file = fopen($pathToFile, 'r');

        if ($media->getDiskDriverName() === 'local') {
            $this->filesystem
                ->disk($media->disk)
                ->put($destination, $file);

            fclose($file);

            return;
        }

        $this->filesystem
            ->disk($media->disk)
            ->put($destination, $file, $this->getRemoteHeadersForFile($pathToFile));

        if (is_resource($file)) {
            fclose($file);
        }
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

        $extraHeaders = config('medialibrary.remote.extra_headers');

        return array_merge($mimeTypeHeader, $extraHeaders, $this->customRemoteHeaders);
    }

    /*
     * Copy a file from the medialibrary to the given targetFile.
     */
    public function copyFromMediaLibrary(Media $media, string $targetFile): string
    {
        $sourceFile = $this->getMediaDirectory($media).'/'.$media->file_name;

        touch($targetFile);

        $stream = $this->filesystem->disk($media->disk)->readStream($sourceFile);

        $targetFileStream = fopen($targetFile, 'a');

        while (! feof($stream)) {
            $chunk = fread($stream, 1024);
            fwrite($targetFileStream, $chunk);
        }

        fclose($stream);

        fclose($targetFileStream);

        return $targetFile;
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
                $this->filesystem->disk($media->disk)->deleteDirectory($directory);
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

        $directory = $conversion
            ? $pathGenerator->getPathForConversions($media)
            : $pathGenerator->getPath($media);

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
