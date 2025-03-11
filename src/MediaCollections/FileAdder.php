<?php

namespace Spatie\MediaLibrary\MediaCollections;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\Macroable;
use Spatie\MediaLibrary\Conversions\ImageGenerators\Image as ImageGenerator;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Exceptions\DiskCannotBeAccessed;
use Spatie\MediaLibrary\MediaCollections\Exceptions\DiskDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileNameNotAllowed;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileUnacceptableForCollection;
use Spatie\MediaLibrary\MediaCollections\Exceptions\UnknownType;
use Spatie\MediaLibrary\MediaCollections\File as PendingFile;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\ResponsiveImages\Jobs\GenerateResponsiveImagesJob;
use Spatie\MediaLibrary\Support\File;
use Spatie\MediaLibrary\Support\RemoteFile;
use Spatie\MediaLibraryPro\Models\TemporaryUpload;
use Symfony\Component\HttpFoundation\File\File as SymfonyFile;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @template TMedia of \Spatie\MediaLibrary\MediaCollections\Models\Media = \Spatie\MediaLibrary\MediaCollections\Models\Media
 */
class FileAdder
{
    use Macroable;

    protected ?HasMedia $subject = null;

    protected bool $preserveOriginal = false;

    /** @var UploadedFile|RemoteFile|SymfonyFile|string */
    protected $file;

    protected array $properties = [];

    protected array $customProperties = [];

    protected array $manipulations = [];

    protected string $pathToFile = '';

    protected string $fileName = '';

    protected string $mediaName = '';

    protected string $diskName = '';

    protected ?string $onQueue = null;

    protected ?int $fileSize = null;

    protected string $conversionsDiskName = '';

    protected ?Closure $fileNameSanitizer;

    protected bool $generateResponsiveImages = false;

    protected array $customHeaders = [];

    public ?int $order = null;

    public function __construct(
        protected ?Filesystem $filesystem
    ) {
        $this->fileNameSanitizer = fn ($fileName) => $this->defaultSanitizer($fileName);
    }

    /**
     * @return $this
     */
    public function setSubject(Model $subject): self
    {
        /** @var HasMedia $subject */
        $this->subject = $subject;

        return $this;
    }

    /**
     * @return $this
     */
    public function setFile($file): self
    {
        $this->file = $file;

        if (is_string($file)) {
            $this->pathToFile = $file;
            $this->setFileName(pathinfo($file, PATHINFO_BASENAME));
            $this->mediaName = pathinfo($file, PATHINFO_FILENAME);

            return $this;
        }

        if ($file instanceof RemoteFile) {
            $this->pathToFile = $file->getKey();
            $this->setFileName($file->getFilename());
            $this->mediaName = $file->getName();

            return $this;
        }

        if ($file instanceof UploadedFile) {
            $this->pathToFile = $file->getPath().'/'.$file->getFilename();
            $this->setFileName($file->getClientOriginalName());
            $this->mediaName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

            return $this;
        }

        if ($file instanceof SymfonyFile) {
            $this->pathToFile = $file->getPath().'/'.$file->getFilename();
            $this->setFileName(pathinfo($file->getFilename(), PATHINFO_BASENAME));
            $this->mediaName = pathinfo($file->getFilename(), PATHINFO_FILENAME);

            return $this;
        }

        if ($file instanceof (config('media-library.temporary_upload_model'))) {
            return $this;
        }

        throw UnknownType::create();
    }

    /**
     * @return $this
     */
    public function preservingOriginal(bool $preserveOriginal = true): self
    {
        $this->preserveOriginal = $preserveOriginal;

        return $this;
    }

    /**
     * @return $this
     */
    public function usingName(string $name): self
    {
        return $this->setName($name);
    }

    /**
     * @return $this
     */
    public function setName(string $name): self
    {
        $this->mediaName = $name;

        return $this;
    }

    /**
     * @return $this
     */
    public function setOrder(?int $order): self
    {
        $this->order = $order;

        return $this;
    }

    /**
     * @return $this
     */
    public function usingFileName(string $fileName): self
    {
        return $this->setFileName($fileName);
    }

    /**
     * @return $this
     */
    public function setFileName(string $fileName): self
    {
        $this->fileName = $fileName;

        return $this;
    }

    /**
     * @return $this
     */
    public function setFileSize(int $fileSize): self
    {
        $this->fileSize = $fileSize;

        return $this;
    }

    /**
     * @return $this
     */
    public function withCustomProperties(array $customProperties): self
    {
        $this->customProperties = $customProperties;

        return $this;
    }

    /**
     * @return $this
     */
    public function storingConversionsOnDisk(string $diskName): self
    {
        $this->conversionsDiskName = $diskName;

        return $this;
    }

    /**
     * @return $this
     */
    public function onQueue(?string $queue = null): self
    {
        $this->onQueue = $queue;

        return $this;
    }

    /**
     * @return $this
     */
    public function withManipulations(array $manipulations): self
    {
        $this->manipulations = $manipulations;

        return $this;
    }

    /**
     * @return $this
     */
    public function withProperties(array $properties): self
    {
        $this->properties = $properties;

        return $this;
    }

    /**
     * @return $this
     */
    public function withAttributes(array $properties): self
    {
        return $this->withProperties($properties);
    }

    /**
     * @return $this
     */
    public function withResponsiveImages(): self
    {
        $this->generateResponsiveImages = true;

        return $this;
    }

    /**
     * @return $this
     */
    public function withResponsiveImagesIf($condition): self
    {
        $this->generateResponsiveImages = (bool) (is_callable($condition) ? $condition() : $condition);

        return $this;
    }

    /**
     * @return $this
     */
    public function addCustomHeaders(array $customRemoteHeaders): self
    {
        $this->customHeaders = $customRemoteHeaders;

        $this->filesystem->addCustomRemoteHeaders($customRemoteHeaders);

        return $this;
    }

    /**
     * @return TMedia
     */
    public function toMediaCollectionOnCloudDisk(string $collectionName = 'default'): Media
    {
        return $this->toMediaCollection($collectionName, config('filesystems.cloud'));
    }

    /**
     * @return TMedia
     */
    public function toMediaCollectionFromRemote(string $collectionName = 'default', string $diskName = ''): Media
    {
        $storage = Storage::disk($this->file->getDisk());

        if (! $storage->exists($this->pathToFile)) {
            throw FileDoesNotExist::create($this->pathToFile);
        }

        $this->fileSize ??= $storage->size($this->pathToFile);

        if ($this->fileSize > config('media-library.max_file_size')) {
            throw FileIsTooBig::create($this->pathToFile, $storage->size($this->pathToFile));
        }

        $mediaClass = $this->subject?->getMediaModel() ?? config('media-library.media_model');
        /** @var Media $media */
        $media = new $mediaClass;

        $media->name = $this->mediaName;

        $sanitizedFileName = ($this->fileNameSanitizer)($this->fileName);
        $fileName = app(config('media-library.file_namer'))->originalFileName($sanitizedFileName);
        $this->fileName = $this->appendExtension($fileName, pathinfo($sanitizedFileName, PATHINFO_EXTENSION));

        $media->file_name = $this->fileName;

        $media->disk = $this->determineDiskName($diskName, $collectionName);
        $this->ensureDiskExists($media->disk);
        $media->conversions_disk = $this->determineConversionsDiskName($media->disk, $collectionName);
        $this->ensureDiskExists($media->conversions_disk);

        $media->collection_name = $collectionName;

        $media->mime_type = $storage->mimeType($this->pathToFile);
        $media->size = $this->fileSize;
        $media->custom_properties = $this->customProperties;

        $media->generated_conversions = [];
        $media->responsive_images = [];

        $media->manipulations = $this->manipulations;

        if (filled($this->customHeaders)) {
            $media->setCustomHeaders($this->customHeaders);
        }

        $media->fill($this->properties);

        $this->attachMedia($media);

        return $media;
    }

    /**
     * @return TMedia
     */
    public function toMediaCollection(string $collectionName = 'default', string $diskName = ''): Media
    {
        $sanitizedFileName = ($this->fileNameSanitizer)($this->fileName);
        $fileName = app(config('media-library.file_namer'))->originalFileName($sanitizedFileName);
        $this->fileName = $this->appendExtension($fileName, pathinfo($sanitizedFileName, PATHINFO_EXTENSION));

        if ($this->file instanceof RemoteFile) {
            return $this->toMediaCollectionFromRemote($collectionName, $diskName);
        }

        if ($this->file instanceof (config('media-library.temporary_upload_model'))) {
            return $this->toMediaCollectionFromTemporaryUpload($collectionName, $diskName, $this->fileName);
        }

        if (! is_file($this->pathToFile)) {
            throw FileDoesNotExist::create($this->pathToFile);
        }

        $this->fileSize ??= filesize($this->pathToFile);

        if ($this->fileSize > config('media-library.max_file_size')) {
            throw FileIsTooBig::create($this->pathToFile);
        }

        $mediaClass = $this->subject?->getMediaModel() ?? config('media-library.media_model');
        /** @var Media $media */
        $media = new $mediaClass;

        $media->name = $this->mediaName;

        $media->file_name = $this->fileName;

        $media->disk = $this->determineDiskName($diskName, $collectionName);
        $this->ensureDiskExists($media->disk);

        $media->conversions_disk = $this->determineConversionsDiskName($media->disk, $collectionName);
        $this->ensureDiskExists($media->conversions_disk);

        $media->collection_name = $collectionName;

        $media->mime_type = File::getMimeType($this->pathToFile);
        $media->size = $this->fileSize;

        if (! is_null($this->order)) {
            $media->order_column = $this->order;
        }

        $media->custom_properties = $this->customProperties;

        $media->generated_conversions = [];
        $media->responsive_images = [];

        $media->manipulations = $this->manipulations;

        if (filled($this->customHeaders)) {
            $media->setCustomHeaders($this->customHeaders);
        }

        $media->fill($this->properties);

        $this->attachMedia($media);

        return $media;
    }

    /**
     * @return TMedia
     */
    public function toMediaLibrary(string $collectionName = 'default', string $diskName = ''): Media
    {
        return $this->toMediaCollection($collectionName, $diskName);
    }

    protected function determineDiskName(string $diskName, string $collectionName): string
    {
        if ($diskName !== '') {
            return $diskName;
        }

        if ($collection = $this->getMediaCollection($collectionName)) {
            $collectionDiskName = $collection->diskName;

            if ($collectionDiskName !== '') {
                return $collectionDiskName;
            }
        }

        return config('media-library.disk_name');
    }

    protected function determineConversionsDiskName(string $originalsDiskName, string $collectionName): string
    {
        if ($this->conversionsDiskName !== '') {
            return $this->conversionsDiskName;
        }

        if ($collection = $this->getMediaCollection($collectionName)) {
            $collectionConversionsDiskName = $collection->conversionsDiskName;

            if ($collectionConversionsDiskName !== '') {
                return $collectionConversionsDiskName;
            }
        }

        return $originalsDiskName;
    }

    protected function ensureDiskExists(string $diskName): void
    {
        if (is_null(config("filesystems.disks.{$diskName}"))) {
            throw DiskDoesNotExist::create($diskName);
        }
    }

    public function defaultSanitizer(string $fileName): string
    {
        $sanitizedFileName = preg_replace('#\p{C}+#u', '', $fileName);

        $sanitizedFileName = str_replace(['#', '/', '\\', ' '], '-', $sanitizedFileName);

        $phpExtensions = [
            '.php', '.php3', '.php4', '.php5', '.php7', '.php8', '.phtml', '.phar',
        ];

        if (Str::endsWith(strtolower($sanitizedFileName), $phpExtensions)) {
            throw FileNameNotAllowed::create($fileName, $sanitizedFileName);
        }

        return $sanitizedFileName;
    }

    /**
     * @return $this
     */
    public function sanitizingFileName(callable $fileNameSanitizer): self
    {
        $this->fileNameSanitizer = $fileNameSanitizer;

        return $this;
    }

    protected function attachMedia(Media $media): void
    {
        if (! $this->subject->exists) {
            $this->subject->prepareToAttachMedia($media, $this);

            $class = $this->subject::class;

            $class::created(function ($model) {
                $model->processUnattachedMedia(function (Media $media, self $fileAdder) use ($model) {
                    $this->processMediaItem($model, $media, $fileAdder);
                });
            });

            return;
        }

        $this->processMediaItem($this->subject, $media, $this);
    }

    protected function processMediaItem(HasMedia $model, Media $media, self $fileAdder): void
    {
        $this->guardAgainstDisallowedFileAdditions($media);

        $this->checkGenerateResponsiveImages($media);

        if (! $media->getConnectionName()) {
            $media->setConnection($model->getConnectionName());
        }

        $model->media()->save($media);

        if ($fileAdder->file instanceof RemoteFile) {
            $addedMediaSuccessfully = $this->filesystem->addRemote($fileAdder->file, $media, $fileAdder->fileName);
        } else {
            $addedMediaSuccessfully = $this->filesystem->add($fileAdder->pathToFile, $media, $fileAdder->fileName);
        }

        if (! $addedMediaSuccessfully) {
            $media->forceDelete();

            throw DiskCannotBeAccessed::create($media->disk);
        }

        if (! $fileAdder->preserveOriginal) {
            if ($fileAdder->file instanceof RemoteFile) {
                Storage::disk($fileAdder->file->getDisk())->delete($fileAdder->file->getKey());
            } else {
                if (file_exists($fileAdder->pathToFile)) {
                    unlink($fileAdder->pathToFile);
                }
            }
        }

        if ($this->generateResponsiveImages && (new ImageGenerator)->canConvert($media)) {
            $generateResponsiveImagesJobClass = config('media-library.jobs.generate_responsive_images', GenerateResponsiveImagesJob::class);

            $job = new $generateResponsiveImagesJobClass($media);

            if ($customConnection = config('media-library.queue_connection_name')) {
                $job->onConnection($customConnection);
            }

            if ($customQueue = ($this->onQueue ?? config('media-library.queue_name'))) {
                $job->onQueue($customQueue);
            }

            dispatch($job);
        }

        if ($collectionSizeLimit = optional($this->getMediaCollection($media->collection_name))->collectionSizeLimit) {
            /** @var HasMedia */
            $subject = $this->subject->fresh();
            $collectionMedia = $subject->getMedia($media->collection_name);

            if ($collectionMedia->count() > $collectionSizeLimit) {
                $model->clearMediaCollectionExcept($media->collection_name, $collectionMedia->slice(-$collectionSizeLimit, $collectionSizeLimit));
            }
        }
    }

    protected function getMediaCollection(string $collectionName): ?MediaCollection
    {
        $this->subject->registerMediaCollections();

        return collect($this->subject->mediaCollections)
            ->first(fn (MediaCollection $collection) => $collection->name === $collectionName);
    }

    protected function guardAgainstDisallowedFileAdditions(Media $media): void
    {
        $file = PendingFile::createFromMedia($media);

        if (! $collection = $this->getMediaCollection($media->collection_name)) {
            return;
        }

        if (! ($collection->acceptsFile)($file, $this->subject)) {
            throw FileUnacceptableForCollection::create($file, $collection, $this->subject);
        }

        if (! empty($collection->acceptsMimeTypes) && ! in_array($file->mimeType, $collection->acceptsMimeTypes)) {
            throw FileUnacceptableForCollection::create($file, $collection, $this->subject);
        }
    }

    protected function checkGenerateResponsiveImages(Media $media): void
    {
        $collection = optional($this->getMediaCollection($media->collection_name))->generateResponsiveImages;

        if ($collection) {
            $this->withResponsiveImages();
        }
    }

    protected function toMediaCollectionFromTemporaryUpload(string $collectionName, string $diskName, string $fileName = ''): Media
    {
        /** @var TemporaryUpload $temporaryUpload */
        $temporaryUpload = $this->file;

        /** @var Media */
        $media = $temporaryUpload->getFirstMedia();

        $media->name = $this->mediaName;
        $media->custom_properties = $this->customProperties;

        if (! is_null($this->order)) {
            $media->order_column = $this->order;
        }

        $media->setCustomHeaders($this->customHeaders);

        $media->save();

        return $temporaryUpload->moveMedia($this->subject, $collectionName, $diskName, $fileName);
    }

    protected function appendExtension(string $file, ?string $extension): string
    {
        return $extension
            ? $file.'.'.$extension
            : $file;
    }
}
