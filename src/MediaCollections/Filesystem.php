<?php

namespace Spatie\MediaLibrary\MediaCollections;

use Exception;
use Illuminate\Contracts\Filesystem\Factory;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\Conversions\ConversionCollection;
use Spatie\MediaLibrary\Conversions\FileManipulator;
use Spatie\MediaLibrary\MediaCollections\Events\MediaHasBeenAdded;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\File;
use Spatie\MediaLibrary\Support\PathGenerator\PathGeneratorFactory;
use Spatie\MediaLibrary\Support\RemoteFile;

class Filesystem
{
    protected Factory $filesystem;

    protected array $customRemoteHeaders = [];

    public function __construct(Factory $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    public function add(string $file, Media $media, ?string $targetFileName = null): void
    {
        $this->copyToMediaLibrary($file, $media, null, $targetFileName);

        event(new MediaHasBeenAdded($media));

        app(FileManipulator::class)->createDerivedFiles($media);
    }

    public function addRemote(RemoteFile $file, Media $media, ?string $targetFileName = null): void
    {
        $this->copyToMediaLibraryFromRemote($file, $media, null, $targetFileName);

        event(new MediaHasBeenAdded($media));

        app(FileManipulator::class)->createDerivedFiles($media);
    }

    public function copyToMediaLibraryFromRemote(RemoteFile $file, Media $media, ?string $type = null, ?string $targetFileName = null): void
    {
        $destinationFileName = $targetFileName ?: $file->getFilename();

        $destination = $this->getMediaDirectory($media, $type).$destinationFileName;

        if ($file->getDisk() === $media->disk) {
            $this->copyFileOnDisk($file->getKey(), $destination, $media->disk);

            return;
        }

        $storage = Storage::disk($file->getDisk());

        $diskDriverName = (in_array($type, ['conversions', 'responsiveImages']))
            ? $media->getConversionsDiskDriverName()
            : $media->getDiskDriverName();

        $headers = $diskDriverName === 'local'
            ? []
            : $this->getRemoteHeadersForFile(
                $file->getKey(),
                $media->getCustomHeaders(),
                $storage->mimeType($file->getKey())
            );

        $this->streamFileToDisk(
            $storage->getDriver()->readStream($file->getKey()),
            $destination,
            $media->disk,
            $headers
        );
    }

    protected function copyFileOnDisk(string $file, string $destination, string $disk): void
    {
        $this->filesystem->disk($disk)
            ->copy($file, $destination);
    }

    protected function streamFileToDisk($stream, string $destination, string $disk, array $headers): void
    {
        $this->filesystem->disk($disk)
            ->getDriver()->writeStream(
                $destination,
                $stream,
                $headers
            );
    }

    public function copyToMediaLibrary(string $pathToFile, Media $media, ?string $type = null, ?string $targetFileName = null)
    {
        $destinationFileName = $targetFileName ?: pathinfo($pathToFile, PATHINFO_BASENAME);

        $destination = $this->getMediaDirectory($media, $type).$destinationFileName;

        $file = fopen($pathToFile, 'r');

        $diskName = (in_array($type, ['conversions', 'responsiveImages']))
            ? $media->conversions_disk
            : $media->disk;

        $diskDriverName = (in_array($type, ['conversions', 'responsiveImages']))
            ? $media->getConversionsDiskDriverName()
            : $media->getDiskDriverName();
        
        if ($diskDriverName === 'local') {
            $this->filesystem
                ->disk($diskName)
                ->put($destination, $file);

            fclose($file);

            return;
        }

        $this->filesystem
            ->disk($diskName)
            ->put(
                $destination,
                $file,
                $this->getRemoteHeadersForFile($pathToFile, $media->getCustomHeaders()),
            );

        if (is_resource($file)) {
            fclose($file);
        }
    }

    public function addCustomRemoteHeaders(array $customRemoteHeaders): void
    {
        $this->customRemoteHeaders = $customRemoteHeaders;
    }

    public function getRemoteHeadersForFile(
        string $file,
        array $mediaCustomHeaders = [],
        string $mimeType = null
    ): array {
        $mimeTypeHeader = ['ContentType' => $mimeType ?: File::getMimeType($file)];

        $extraHeaders = config('media-library.remote.extra_headers');

        return array_merge(
            $mimeTypeHeader,
            $extraHeaders,
            $this->customRemoteHeaders,
            $mediaCustomHeaders
        );
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
            $chunk = fgets($stream, 1024);
            fwrite($targetFileStream, $chunk);
        }

        fclose($stream);

        fclose($targetFileStream);

        return $targetFile;
    }

    public function removeAllFiles(Media $media): void
    {
        $mediaDirectory = $this->getMediaDirectory($media);
        
        if ($media->disk !== $media->conversions_disk) {
            $this->filesystem->disk($media->disk)->deleteDirectory($mediaDirectory);
        }

        $conversionsDirectory = $this->getMediaDirectory($media, 'conversions');

        $responsiveImagesDirectory = $this->getMediaDirectory($media, 'responsiveImages');

        collect([$mediaDirectory, $conversionsDirectory, $responsiveImagesDirectory])
            ->each(function (string $directory) use ($media) {
                try {
                    $this->filesystem->disk($media->conversions_disk)->deleteDirectory($directory);
                } catch (Exception $exception) {
                    report($exception);
                }
            });
    }

    public function removeFile(Media $media, string $path): void
    {
        $this->filesystem->disk($media->disk)->delete($path);
    }

    public function removeResponsiveImages(Media $media, string $conversionName = 'media_library_original'): void
    {
        $responsiveImagesDirectory = $this->getResponsiveImagesDirectory($media);

        $allFilePaths = $this->filesystem->disk($media->disk)->allFiles($responsiveImagesDirectory);

        $responsiveImagePaths = array_filter(
            $allFilePaths,
            fn (string $path) => Str::contains($path, $conversionName)
        );

        $this->filesystem->disk($media->disk)->delete($responsiveImagePaths);
    }

    public function syncFileNames(Media $media): void
    {
        $this->renameMediaFile($media);

        $this->renameConversionFiles($media);
    }

    protected function renameMediaFile(Media $media): void
    {
        $newFileName = $media->file_name;
        $oldFileName = $media->getOriginal('file_name');

        $mediaDirectory = $this->getMediaDirectory($media);

        $oldFile = "{$mediaDirectory}/{$oldFileName}";
        $newFile = "{$mediaDirectory}/{$newFileName}";

        $this->filesystem->disk($media->disk)->move($oldFile, $newFile);
    }

    protected function renameConversionFiles(Media $media): void
    {
        $mediaWithOldFileName = config('media-library.media_model')::find($media->id);
        $mediaWithOldFileName->file_name = $mediaWithOldFileName->getOriginal('file_name');

        $conversionDirectory = $this->getConversionDirectory($media);

        $conversionCollection = ConversionCollection::createForMedia($media);

        foreach ($media->getMediaConversionNames() as $conversionName) {
            $conversion = $conversionCollection->getByName($conversionName);

            $oldFile = $conversionDirectory.$conversion->getConversionFile($mediaWithOldFileName);
            $newFile = $conversionDirectory.$conversion->getConversionFile($media);

            $disk = $this->filesystem->disk($media->conversions_disk);

            // A media conversion file might be missing, waiting to be generated, failed etc.
            if (! $disk->exists($oldFile)) {
                continue;
            }

            $disk->move($oldFile, $newFile);
        }
    }

    public function getMediaDirectory(Media $media, ?string $type = null): string
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

        $diskDriverName = in_array($type, ['conversions', 'responsiveImages'])
            ? $media->getConversionsDiskDriverName()
            : $media->getDiskDriverName();

        $diskName = in_array($type, ['conversions', 'responsiveImages'])
            ? $media->conversions_disk
            : $media->disk;

        if (! in_array($diskDriverName, ['s3'], true)) {
            $this->filesystem->disk($diskName)->makeDirectory($directory);
        }

        return $directory;
    }

    public function getConversionDirectory(Media $media): string
    {
        return $this->getMediaDirectory($media, 'conversions');
    }

    public function getResponsiveImagesDirectory(Media $media): string
    {
        return $this->getMediaDirectory($media, 'responsiveImages');
    }
}
