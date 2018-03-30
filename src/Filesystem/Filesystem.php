<?php

namespace Spatie\MediaLibrary\Filesystem;

use Spatie\MediaLibrary\Helpers\File;
use Spatie\MediaLibrary\Models\Media;
use Spatie\MediaLibrary\FileManipulator;
use League\Flysystem\FilesystemInterface;
use Illuminate\Contracts\Filesystem\Factory;
use Illuminate\Filesystem\FilesystemAdapter;
use Spatie\MediaLibrary\Events\MediaHasBeenAdded;
use Spatie\MediaLibrary\Conversion\ConversionCollection;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Spatie\MediaLibrary\PathGenerator\PathGeneratorFactory;

class Filesystem
{
    /** @var \Illuminate\Contracts\Filesystem\Factory */
    protected $filesystem;

    /** @var array */
    protected $customRemoteHeaders = [];

    public function __construct(Factory $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    public function add(string $file, Media $media, ?string $targetFileName = null)
    {
        $this->copyToMediaLibrary($file, $media, null, $targetFileName);

        event(new MediaHasBeenAdded($media));

        app(FileManipulator::class)->createDerivedFiles($media);
    }

    public function copyToMediaLibrary(string $pathToFile, Media $media, ?string $type = null, ?string $targetFileName = null)
    {
        $destinationFileName = $targetFileName ?: pathinfo($pathToFile, PATHINFO_BASENAME);

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
            ->put($destination, $file, $this->getRemoteHeadersForFile($pathToFile, $media->getCustomHeaders()));

        if (is_resource($file)) {
            fclose($file);
        }
    }

    public function addCustomRemoteHeaders(array $customRemoteHeaders)
    {
        $this->customRemoteHeaders = $customRemoteHeaders;
    }

    public function getRemoteHeadersForFile(string $file, array $mediaCustomHeaders = []) : array
    {
        $mimeTypeHeader = ['ContentType' => File::getMimeType($file)];

        $extraHeaders = config('medialibrary.remote.extra_headers');

        return array_merge($mimeTypeHeader, $extraHeaders, $this->customRemoteHeaders, $mediaCustomHeaders);
    }

    public function getStream(Media $media)
    {
        $sourceFile = $this->getMediaDirectory($media).$media->file_name;

        return $this->filesystem->disk($media->disk)->readStream($sourceFile);
    }

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

    public function removeAllFiles(Media $media)
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

    public function syncFileNames(Media $media)
    {
        $this->renameMediaFile($media);

        $this->renameConversionFiles($media);
    }

    protected function renameMediaFile(Media $media)
    {
        $newFileName = $media->file_name;
        $oldFileName = $media->getOriginal('file_name');

        $mediaDirectory = $this->getMediaDirectory($media);

        $oldFile = $mediaDirectory.$oldFileName;
        $newFile = $mediaDirectory.$newFileName;

        $this->filesystem->disk($media->disk)->move($oldFile, $newFile);
    }

    protected function renameConversionFiles(Media $media)
    {
        $newFileName = $media->file_name;
        $oldFileName = $media->getOriginal('file_name');

        $conversionDirectory = $this->getConversionDirectory($media);

        $conversionCollection = ConversionCollection::createForMedia($media);

        foreach ($media->getMediaConversionNames() as $conversionName) {
            $conversion = $conversionCollection->getByName($conversionName);

            $oldFile = $conversionDirectory.$conversion->getConversionFile($oldFileName);
            $newFile = $conversionDirectory.$conversion->getConversionFile($newFileName);

            $disk = $this->filesystem->disk($media->disk);

            // A media conversion file might be missing, waiting to be generated, failed etc.
            if (! $disk->exists($oldFile)) {
                continue;
            }

            $disk->move($oldFile, $newFile);
        }
    }

    public function syncDisk(Media $media)
    {
        // Retrieve the disks to move between
        $newDisk = $this->filesystem->disk(
            $newDiskIdentifier = $media->disk
        );
        $oldDisk = $this->filesystem->disk(
            $oldDiskIdentifier = $media->getOriginal('disk')
        );

        // Use the original filename, renaming is the next step
        $fileName = $media->getOriginal('file_name');

        // Determine the old media and conversion directories with the old disk
        $media->disk = $oldDiskIdentifier;
        $oldConversionDirectory = $this->getConversionDirectory($media);
        $oldMediaDirectory = $this->getMediaDirectory($media);
        $media->disk = $newDiskIdentifier;

        // Do the actual moving
        $this->moveBetweenDisks(
            $oldDisk,
            $oldMediaDirectory.$fileName,
            $newDisk,
            $this->getMediaDirectory($media).$fileName
        );

        // Also move all conversions to the new disk
        $conversionCollection = ConversionCollection::createForMedia($media);
        $newConversionDirectory = $this->getConversionDirectory($media);

        foreach ($media->getMediaConversionNames() as $conversionName) {
            $conversion = $conversionCollection->getByName($conversionName);

            try {
                $this->moveBetweenDisks(
                    $oldDisk,
                    $oldConversionDirectory.$conversion->getConversionFile($fileName),
                    $newDisk,
                    $newConversionDirectory.$conversion->getConversionFile($fileName)
                );
            } catch (FileNotFoundException $e) {
                // A media conversion file might be missing, waiting to be generated, failed etc.
            }
        }
    }

    protected function moveBetweenDisks(\Illuminate\Contracts\Filesystem\Filesystem $oldDisk, $oldFile, \Illuminate\Contracts\Filesystem\Filesystem $newDisk, $newFile = null)
    {
        $newFile = $newFile ?? $oldFile;

        if (! $oldDisk->exists($oldFile)) {
            throw new FileNotFoundException;
        }

        $oldFileStream = null;

        // Try to extract a stream, if we know how
        if ($oldDisk instanceof FilesystemAdapter) {
            $oldDiskDriver = $oldDisk->getDriver();
            if ($oldDiskDriver instanceof FilesystemInterface) {
                $oldFileStream = $oldDiskDriver->readStream($oldFile);
            }
        }

        // Use the stream if exists or get the full content
        $oldSource = $oldFileStream ?? $oldDisk->get($oldFile);

        // Filesystem knows how to handle both streams and content
        $newDisk->put($newFile, $oldSource);

        // Delete the old file resource
        $oldDisk->delete($oldFile);
    }

    public function getMediaDirectory(Media $media, ?string $type = null) : string
    {
        $pathGenerator = PathGeneratorFactory::create();

        if (! $type) {
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

    public function getConversionDirectory(Media $media) : string
    {
        return $this->getMediaDirectory($media, 'conversions');
    }

    public function getResponsiveImagesDirectory(Media $media) : string
    {
        return $this->getMediaDirectory($media, 'responsiveImages');
    }
}
