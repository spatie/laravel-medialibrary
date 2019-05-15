<?php

namespace Spatie\MediaLibrary\FileAdder;

use Spatie\MediaLibrary\Helpers\File;
use Spatie\MediaLibrary\Models\Media;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia\HasMedia;
use Spatie\MediaLibrary\File as PendingFile;
use Spatie\MediaLibrary\Filesystem\Filesystem;
use Spatie\MediaLibrary\Jobs\GenerateResponsiveImages;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Spatie\MediaLibrary\MediaCollection\MediaCollection;
use Symfony\Component\HttpFoundation\File\File as SymfonyFile;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\UnknownType;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\FileIsTooBig;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\DiskDoesNotExist;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\FileDoesNotExist;
use Spatie\MediaLibrary\ImageGenerators\FileTypes\Image as ImageGenerator;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\FileUnacceptableForCollection;

class FileAdder
{
    /** @var \Illuminate\Database\Eloquent\Model|null subject */
    protected $subject;

    /** @var \Spatie\MediaLibrary\Filesystem\Filesystem */
    protected $filesystem;

    /** @var bool */
    protected $preserveOriginal = false;

    /** @var string|\Symfony\Component\HttpFoundation\File\UploadedFile */
    protected $file;

    /** @var array */
    protected $properties = [];

    /** @var array */
    protected $customProperties = [];

    /** @var array */
    protected $manipulations = [];

    /** @var string */
    protected $pathToFile;

    /** @var string */
    protected $fileName;

    /** @var string */
    protected $mediaName;

    /** @var string */
    protected $diskName = '';

    /** @var null|callable */
    protected $fileNameSanitizer;

    /** @var bool */
    protected $generateResponsiveImages = false;

    /** @var array */
    protected $customHeaders = [];

    /**
     * @param Filesystem $fileSystem
     */
    public function __construct(Filesystem $fileSystem)
    {
        $this->filesystem = $fileSystem;

        $this->fileNameSanitizer = function ($fileName) {
            return $this->defaultSanitizer($fileName);
        };
    }

    /**
     * Get the media model class.
     *
     * @return string
     */
    public function mediaModel()
    {
        return config('medialibrary.media_model');
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model|null $subject
     *
     * @return FileAdder
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;

        return $this;
    }

    /*
     * Set the file that needs to be imported.
     *
     * @param string|\Symfony\Component\HttpFoundation\File\UploadedFile $file
     *
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

        throw UnknownType::create();
    }

    public function preservingOriginal(): self
    {
        $this->preserveOriginal = true;

        return $this;
    }

    public function usingName(string $name): self
    {
        return $this->setName($name);
    }

    public function setName(string $name): self
    {
        $this->mediaName = $name;

        return $this;
    }

    public function usingFileName(string $fileName): self
    {
        return $this->setFileName($fileName);
    }

    public function setFileName(string $fileName): self
    {
        $this->fileName = $fileName;

        return $this;
    }

    public function withCustomProperties(array $customProperties): self
    {
        $this->customProperties = $customProperties;

        return $this;
    }

    public function withManipulations(array $manipulations): self
    {
        $this->manipulations = $manipulations;

        return $this;
    }

    public function withProperties(array $properties): self
    {
        $this->properties = $properties;

        return $this;
    }

    public function withAttributes(array $properties): self
    {
        return $this->withProperties($properties);
    }

    public function withResponsiveImages(): self
    {
        $this->generateResponsiveImages = true;

        return $this;
    }

    public function addCustomHeaders(array $customRemoteHeaders): self
    {
        $this->customHeaders = $customRemoteHeaders;

        $this->filesystem->addCustomRemoteHeaders($customRemoteHeaders);

        return $this;
    }

    public function toMediaCollectionOnCloudDisk(string $collectionName = 'default'): Media
    {
        return $this->toMediaCollection($collectionName, config('filesystems.cloud'));
    }

    public function toMediaCollection(string $collectionName = 'default', string $diskName = ''): Media
    {
        if (! is_file($this->pathToFile)) {
            throw FileDoesNotExist::create($this->pathToFile);
        }

        if (filesize($this->pathToFile) > config('medialibrary.max_file_size')) {
            throw FileIsTooBig::create($this->pathToFile);
        }

        $mediaClass = $this->mediaModel();
        /** @var \Spatie\MediaLibrary\Models\Media $media */
        $media = new $mediaClass();

        $media->name = $this->mediaName;

        $this->fileName = ($this->fileNameSanitizer)($this->fileName);

        $media->file_name = $this->fileName;

        $media->disk = $this->determineDiskName($media, $diskName, $collectionName);

        if (is_null(config("filesystems.disks.{$media->disk}"))) {
            throw DiskDoesNotExist::create($media->disk);
        }

        $media->collection_name = $collectionName;

        $media->mime_type = File::getMimetype($this->pathToFile);
        $media->size = filesize($this->pathToFile);
        $media->custom_properties = $this->customProperties;

        $media->responsive_images = [];

        $media->manipulations = $this->manipulations;

        if (filled($this->customHeaders)) {
            $media->setCustomHeaders($this->customHeaders);
        }

        $media->fill($this->properties);

        if ($this->subject) {
            $this->attachMedia($media);
        } else {
            $this->processMediaItem($this, $media);
        }

        return $media;
    }

    protected function determineDiskName(Media $media, string $diskName, string $collectionName): string
    {
        if ($diskName !== '') {
            return $diskName;
        }

        if ($collection = $this->getMediaCollection($media, $collectionName)) {
            $collectionDiskName = $collection->diskName;

            if ($collectionDiskName !== '') {
                return $collectionDiskName;
            }
        }

        return config('medialibrary.disk_name');
    }

    public function defaultSanitizer(string $fileName): string
    {
        return str_replace(['#', '/', '\\', ' '], '-', $fileName);
    }

    public function sanitizingFileName(callable $fileNameSanitizer): self
    {
        $this->fileNameSanitizer = $fileNameSanitizer;

        return $this;
    }

    protected function attachMedia(Media $media)
    {
        if (! $this->subject->exists) {
            $this->subject->prepareToAttachMedia($media, $this);

            $class = get_class($this->subject);

            $class::created(function ($model) {
                $model->processUnattachedMedia(function (Media $media, FileAdder $fileAdder) use ($model) {
                    $this->processMediaItem($fileAdder, $media, $model);
                });
            });

            return;
        }

        $this->processMediaItem($this, $media, $this->subject);
    }

    /**
     * Process the media item.
     *
     * @param  FileAdder      $fileAdder
     * @param  Media          $media
     * @param  HasMedia|null  $model
     * @throws FileUnacceptableForCollection
     * @return void
     */
    protected function processMediaItem(self $fileAdder, Media $media, $model = null)
    {
        $this->guardAgainstDisallowedFileAdditions($media);

        if ($model) {
            $model->media()->save($media);
        } else {
            $media->save();
        }

        $this->filesystem->add($fileAdder->pathToFile, $media, $fileAdder->fileName);

        if (! $fileAdder->preserveOriginal) {
            unlink($fileAdder->pathToFile);
        }

        if ($this->generateResponsiveImages && (new ImageGenerator())->canConvert($media)) {
            $generateResponsiveImagesJobClass = config('medialibrary.jobs.generate_responsive_images', GenerateResponsiveImages::class);

            $job = new $generateResponsiveImagesJobClass($media);

            if ($customQueue = config('medialibrary.queue_name')) {
                $job->onQueue($customQueue);
            }

            dispatch($job);
        }

        if (optional($this->getMediaCollection($media))->singleFile) {
            $media->clearMediaCollection($model, $media);
        }
    }

    /**
     * Get a media collection by its name, or via the Media model.
     *
     * @param Media $media
     * @param string|null $collectionName
     * @return MediaCollection|null
     */
    protected function getMediaCollection(Media $media, $collectionName = null): ?MediaCollection
    {
        $collectionName = $collectionName ?? $media->collection_name;

        $media->registerMediaCollections();

        $collections = $media->mediaCollections;

        if ($this->subject) {
            $this->subject->registerMediaCollections();
            $collections = array_merge($collections, $this->subject->mediaCollections);
        }

        return collect($collections)->first(function (MediaCollection $collection) use ($collectionName) {
            return $collection->name === $collectionName;
        });
    }

    protected function guardAgainstDisallowedFileAdditions(Media $media)
    {
        $file = PendingFile::createFromMedia($media);

        if (! $collection = $this->getMediaCollection($media)) {
            return;
        }

        if (! ($collection->acceptsFile)($file, $this->subject)) {
            throw FileUnacceptableForCollection::create($file, $collection, $this->subject);
        }
    }
}
