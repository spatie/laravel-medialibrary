<?php

namespace Spatie\MediaLibrary\Filesystem;

use Spatie\MediaLibrary\Helpers\File;
use Spatie\MediaLibrary\Models\Media;
use Spatie\MediaLibrary\FileManipulator;
use Illuminate\Contracts\Filesystem\Factory;
use Spatie\MediaLibrary\Events\MediaHasBeenAdded;
use Spatie\MediaLibrary\Conversion\ConversionCollection;
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
        $sourceFile = $this->getMediaDirectory($media).'/'.$media->file_name;

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

    public function syncFiles(Media $media)
    {
        $this->syncMediaFile($media);

        $this->syncConversionFiles($media);
    }

    protected function syncMediaFile(Media $media)
    {
        $newFileName = $media->file_name;
        $oldFileName = $media->getOriginal('file_name');

        $newDisk = $media->disk;
        $oldDisk = $media->getOriginal('disk');

        $mediaDirectory = $this->getMediaDirectory($media);
        $oldMediaDirectory = $this->getMediaDirectory($media->setAttribute('disk', $oldDisk));
        $media->setAttribute('disk', $newDisk);

        $oldFile = $oldMediaDirectory.$oldFileName;
        $newFile = $mediaDirectory.$newFileName;

        $this->move($oldDisk, $oldFile, $newDisk, $newFile);
    }

    protected function syncConversionFiles(Media $media)
    {
        $newFileName = $media->file_name;
        $oldFileName = $media->getOriginal('file_name');

        $newDisk = $media->disk;
        $oldDisk = $media->getOriginal('disk');

        $conversionDirectory = $this->getConversionDirectory($media);
        $oldConversionDirectory = $this->getConversionDirectory($media->setAttribute('disk', $oldDisk));
        $media->setAttribute('disk', $newDisk);

        $conversionCollection = ConversionCollection::createForMedia($media);

        foreach ($media->getMediaConversionNames() as $conversionName) {
            $conversion = $conversionCollection->getByName($conversionName);

            $oldFile = $oldConversionDirectory.$conversion->getConversionFile($oldFileName);
            $newFile = $conversionDirectory.$conversion->getConversionFile($newFileName);

            // A media conversion file might be missing, waiting to be generated, failed etc.
            if (! $this->filesystem->disk($oldDisk)->exists($oldFile)) {
                continue;
            }

            $this->move($oldDisk, $oldFile, $newDisk, $newFile);
        }
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

    protected function move($fromDisk, $fromPath, $toDisk, $toPath)
    {
        if ($fromDisk == $toDisk) {
            $this->filesystem->disk($fromDisk)->move($fromPath, $toPath);

            return;
        }

        $this->filesystem->disk($toDisk)->getDriver()->writeStream(
            $toPath,
            $this->filesystem->disk($fromDisk)->getDriver()->readStream($fromPath)
        );

        $this->filesystem->disk($fromDisk)->delete($fromPath);
    }
}
