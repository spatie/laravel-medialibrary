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

    public function __construct(Factory $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    /*
     * Add a file to the mediaLibrary for the given media.
     */
    public function add(string $file, Media $media, string $targetFileName = '')
    {
        $this->copyToMediaLibrary($file, $media, '', $targetFileName);

        event(new MediaHasBeenAdded($media));

        app(FileManipulator::class)->createDerivedFiles($media);
    }

    /*
     * Copy a file to the medialibrary for the given $media.
     */
    public function copyToMediaLibrary(string $pathToFile, Media $media, string $type = '', string $targetFileName = '')
    {
        $destinationFileName = $targetFileName == ''
            ? pathinfo($pathToFile, PATHINFO_BASENAME)
            : $targetFileName;

        $destination = $this->getMediaDirectory($media, $type).$destinationFileName;

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

    public function getStream(Media $media)
    {
        $sourceFile = $this->getMediaDirectory($media).'/'.$media->file_name;

        return $this->filesystem->disk($media->disk)->readStream($sourceFile);
    }

    /*
     * Copy a file from the medialibrary to the given targetFile.
     */
    public function copyFromMediaLibrary(Media $media, string $targetFile): string
    {
        touch($targetFile);

        $stream = $this->getStream($media);

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

        $conversionsDirectory = $this->getMediaDirectory($media, 'conversions');

        $responsiveImagesDirectory = $this->getMediaDirectory($media, 'responsiveImages');

        collect([$mediaDirectory, $conversionsDirectory, $responsiveImagesDirectory])
            ->each(function ($directory) use ($media) {
                $this->filesystem->disk($media->disk)->deleteDirectory($directory);
            });
    }

    public function removeFile(Media $media, string $path)
    {
        $this->filesystem->disk($media->disk)->delete($path);
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
    public function getMediaDirectory(Media $media, string $type = '') : string
    {
        $pathGenerator = PathGeneratorFactory::create();

        if ($type === '') {
            $directory = $pathGenerator->getPath($media);
        }

        if ($type === 'conversions') {
            $directory = $pathGenerator->getPathForConversions($media);
        }

        if ($type === 'responsiveImages') {
            $directory = $pathGenerator->getPathForResponsiveImages($media);
        }

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
        return $this->getMediaDirectory($media, 'conversions');
    }

    /*
     * Return the directory where all responsive images of the given media are stored.
     */
    public function getResponsiveImagesDirectory(Media $media) : string
    {
        return $this->getMediaDirectory($media, 'responsiveImages');
    }
}
