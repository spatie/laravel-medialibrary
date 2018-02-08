<?php

namespace Spatie\MediaLibrary\FileAdder;

use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\FileUnacceptableForCollection;
use Spatie\MediaLibrary\File as PendingFile;
use Spatie\MediaLibrary\Models\Media;
use Spatie\MediaLibrary\Helpers\File;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\Filesystem\Filesystem;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded;
use Spatie\MediaLibrary\HasMedia\HasMedia;
use Spatie\MediaLibrary\MediaCollection\MediaCollection;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\File as SymfonyFile;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\UnknownType;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\FileIsTooBig;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\DiskDoesNotExist;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\FileDoesNotExist;
use Spatie\MediaLibrary\Jobs\GenerateResponsiveImages;
use Spatie\MediaLibrary\ImageGenerators\FileTypes\Image as ImageGenerator;

class FileAdder
{
    /** @var \Illuminate\Database\Eloquent\Model subject */
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

    /** @var callable */
    protected $afterFileHasBeenAdded;

    /**
     * @param Filesystem $fileSystem
     */
    public function __construct(Filesystem $fileSystem)
    {
        $this->filesystem = $fileSystem;

        $this->fileNameSanitizer = function ($fileName) {
            return $this->defaultSanitizer($fileName);
        };

        $this->afterFileHasBeenAdded = function () {
        };
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $subject
     *
     * @return FileAdder
     */
    public function setSubject(Model $subject)
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
    public function setFile($file)
    {
        $this->file = $file;

        if (is_string($file)) {
            $this->pathToFile = $file;
            $this->setFileName(pathinfo($file, PATHINFO_BASENAME));
            $this->mediaName = pathinfo($file, PATHINFO_FILENAME);

            return $this;
        }

        if ($file instanceof UploadedFile) {
            $this->pathToFile = $file->getPath() . '/' . $file->getFilename();
            $this->setFileName($file->getClientOriginalName());
            $this->mediaName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

            return $this;
        }

        if ($file instanceof SymfonyFile) {
            $this->pathToFile = $file->getPath() . '/' . $file->getFilename();
            $this->setFileName(pathinfo($file->getFilename(), PATHINFO_BASENAME));
            $this->mediaName = pathinfo($file->getFilename(), PATHINFO_FILENAME);

            return $this;
        }

        throw UnknownType::create();
    }

    /**
     * When adding the file to the media library, the original file
     * will be preserved.
     *
     * @return $this
     */
    public function preservingOriginal()
    {
        $this->preserveOriginal = true;

        return $this;
    }

    /**
     * Set the name of the media object.
     *
     * @param string $name
     *
     * @return $this
     */
    public function usingName(string $name)
    {
        return $this->setName($name);
    }

    /**
     * Set the name of the media object.
     *
     * @param string $name
     *
     * @return $this
     */
    public function setName(string $name)
    {
        $this->mediaName = $name;

        return $this;
    }

    /**
     * Set the name of the file that is stored on disk.
     *
     * @param string $fileName
     *
     * @return $this
     */
    public function usingFileName(string $fileName)
    {
        return $this->setFileName($fileName);
    }

    /**
     * Set the name of the file that is stored on disk.
     *
     * @param string $fileName
     *
     * @return $this
     */
    public function setFileName(string $fileName)
    {
        $this->fileName = $fileName;

        return $this;
    }

    /**
     * Set the metadata.
     *
     * @param array $customProperties
     *
     * @return $this
     */
    public function withCustomProperties(array $customProperties)
    {
        $this->customProperties = $customProperties;

        return $this;
    }

    /**
     * Set properties on the model.
     *
     * @param array $properties
     *
     * @return $this
     */
    public function withProperties(array $properties)
    {
        $this->properties = $properties;

        return $this;
    }

    /**
     * Set attributes on the model.
     *
     * @param array $properties
     *
     * @return $this
     */
    public function withAttributes(array $properties)
    {
        return $this->withProperties($properties);
    }


    /**
     * Generate responsive images.
     *
     * @return $this
     */
    public function withResponsiveImages()
    {
        $this->generateResponsiveImages = true;

        return $this;
    }

    /**
     * Add the given additional headers when copying the file to a remote filesystem.
     *
     * @param array $customRemoteHeaders
     *
     * @return $this
     */
    public function addCustomHeaders(array $customRemoteHeaders)
    {
        $this->filesystem->addCustomRemoteHeaders($customRemoteHeaders);

        return $this;
    }

    /**
     * Perform the given callable after the file has been added.
     */
    public function afterFileHasBeenAdded(callable $callable)
    {
        $this->afterFileHasBeenAdded = $callable;

        return $this;
    }

    /**
     * @param string $collectionName
     *
     * @return \Spatie\MediaLibrary\Media
     *
     * @throws FileCannotBeAdded
     * @throws \Spatie\MediaLibrary\Exceptions\FileCannotBeAdded
     */
    public function toMediaCollectionOnCloudDisk(string $collectionName = 'default')
    {
        return $this->toMediaCollection($collectionName, config('filesystems.cloud'));
    }

    /**
     * @param string $collectionName
     * @param string $diskName
     *
     * @return \Spatie\MediaLibrary\Media
     *
     * @throws FileCannotBeAdded
     * @throws \Spatie\MediaLibrary\Exceptions\FileCannotBeAdded
     */
    public function toMediaCollection(string $collectionName = 'default', string $diskName = ''): Media
    {
        if (!is_file($this->pathToFile)) {
            throw FileDoesNotExist::create($this->pathToFile);
        }

        if (filesize($this->pathToFile) > config('medialibrary.max_file_size')) {
            throw FileIsTooBig::create($this->pathToFile);
        }

        $mediaClass = config('medialibrary.media_model');
        $media = new $mediaClass();

        $media->name = $this->mediaName;

        $this->fileName = ($this->fileNameSanitizer)($this->fileName);

        $media->file_name = $this->fileName;

        $media->disk = $this->determineDiskName($diskName, $collectionName);

        if (is_null(config("filesystems.disks.{$media->disk}"))) {
            throw DiskDoesNotExist::create($media->disk);
        }

        $media->collection_name = $collectionName;

        $media->mime_type = File::getMimetype($this->pathToFile);
        $media->size = filesize($this->pathToFile);
        $media->custom_properties = $this->customProperties;
        $media->manipulations = [];
        $media->responsive_images = [];

        $media->fill($this->properties);

        $this->attachMedia($media);

        return $media;
    }

    /**
     * @param string $diskName
     * @param string $collectionName
     *
     * @return string
     */
    protected function determineDiskName(string $diskName, $collectionName): string
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

        return config('medialibrary.default_filesystem');
    }

    public function defaultSanitizer(string $fileName): string
    {
        return str_replace(['#', '/', '\\', ' '], '-', $fileName);
    }

    public function sanitizingFileName(callable $fileNameSanitizer)
    {
        $this->fileNameSanitizer = $fileNameSanitizer;

        return $this;
    }

    protected function attachMedia(Media $media)
    {
        if (!$this->subject->exists) {
            $this->subject->prepareToAttachMedia($media, $this);

            $class = get_class($this->subject);

            $class::created(function ($model) {
                $model->processUnattachedMedia(function (Media $media, FileAdder $fileAdder) use ($model) {
                    $this->processMediaItem($model, $media, $fileAdder);
                });
            });

            return;
        }

        $this->processMediaItem($this->subject, $media, $this);
    }


    protected function processMediaItem(HasMedia $model, Media $media, FileAdder $fileAdder)
    {
        $this->guardAgainstDisallowedFileAdditions($media, $model);

        $model->media()->save($media);

        $this->filesystem->add($fileAdder->pathToFile, $media, $fileAdder->fileName);

        if (!$fileAdder->preserveOriginal) {
            unlink($fileAdder->pathToFile);
        }

        if ($this->generateResponsiveImages && (new ImageGenerator())->canConvert($media)) {
            $job = new GenerateResponsiveImages($media);

            if ($customQueue = config('medialibrary.queue_name')) {
                $job->onQueue($customQueue);
            }

            dispatch($job);
        }

        if (optional($this->getMediaCollection($media->collection_name))->singleFile) {
            $model->clearMediaCollectionExcept($media->collection_name, $media);
        }

        ($this->afterFileHasBeenAdded)();
    }

    protected function getMediaCollection(string $collectionName):  ?MediaCollection
    {
        $this->subject->registerMediaCollections();

        return collect($this->subject->mediaCollections)
            ->first(function (MediaCollection $collection) use ($collectionName) {
                return $collection->name === $collectionName;
            });
    }

    protected function guardAgainstDisallowedFileAdditions(Media $media)
    {
        $file = PendingFile::createFromMedia($media);

        if (! $collection = $this->getMediaCollection($media->collection_name)) {
            return;
        }

        if (! ($collection->acceptsFile)($file, $this->subject)) {
            throw FileUnacceptableForCollection::create($file, $collection, $this->subject);
        }
    }
}
