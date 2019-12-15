<?php

namespace Spatie\MediaLibrary\Filesystem;

use Illuminate\Contracts\Filesystem\Factory;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\Conversion\ConversionCollection;
use Spatie\MediaLibrary\Events\MediaHasBeenAdded;
use Spatie\MediaLibrary\FileManipulator;
use Spatie\MediaLibrary\Helpers\File;
use Spatie\MediaLibrary\Helpers\RemoteFile;
use Spatie\MediaLibrary\Models\Media;
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

    public function addRemote(RemoteFile $file, Media $media, ?string $targetFileName = null)
    {
        $this->copyToMediaLibraryFromRemote($file, $media, null, $targetFileName);

        event(new MediaHasBeenAdded($media));

        app(FileManipulator::class)->createDerivedFiles($media);
    }

    public function copyToMediaLibraryFromRemote(RemoteFile $file, Media $media, ?string $type = null, ?string $targetFileName = null)
    {
        $storage = Storage::disk($file->getDisk());

        $destinationFileName = $targetFileName ?: $file->getFilename();

        $destination = $this->getMediaDirectory($media, $type).$destinationFileName;

        $this->filesystem->disk($media->disk)
            ->getDriver()->writeStream(
                $destination,
                $storage->getDriver()->readStream($file->getKey()),
                $media->getDiskDriverName() === 'local'
                    ? [] : $this->getRemoteHeadersForFile(
                        $file->getKey(),
                        $media->getCustomHeaders(),
                        $storage->mimeType($file->getKey())
                    )
            );
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

    public function getRemoteHeadersForFile(string $file, array $mediaCustomHeaders = [], string $mimeType = null) : array
    {
        $mimeTypeHeader = ['ContentType' => $mimeType ?: File::getMimeType($file)];

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

    public function removeResponsiveImages(Media $media, string $conversionName = 'medialibrary_original')
    {
        $responsiveImagesDirectory = $this->getResponsiveImagesDirectory($media);

        $allFilePaths = $this->filesystem->disk($media->disk)->allFiles($responsiveImagesDirectory);

        $responsiveImagePaths = array_filter(
            $allFilePaths,
            function (string $path) use ($conversionName) {
                return Str::contains($path, $conversionName);
            }
        );

        $this->filesystem->disk($media->disk)->delete($responsiveImagePaths);
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

        $oldFile = $mediaDirectory.'/'.$oldFileName;
        $newFile = $mediaDirectory.'/'.$newFileName;

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
