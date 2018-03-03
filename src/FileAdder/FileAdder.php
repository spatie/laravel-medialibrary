<?php

namespace Spatie\MediaLibrary\FileAdder;

use Spatie\MediaLibrary\Media;
use Spatie\MediaLibrary\Helpers\File;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\Filesystem\Filesystem;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded;
use Spatie\MediaLibrary\HasMedia\Interfaces\HasMedia;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\File as SymfonyFile;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\UnknownType;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\FileIsTooBig;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\DiskDoesNotExist;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\FileDoesNotExist;

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
     * Set the manipulations.
     *
     * @param array $manipulations
     *
     * @return $this
     */
    public function withManipulations(array $manipulations)
    {
        $this->manipulations = $manipulations;

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
    public function toMediaCollection(string $collectionName = 'default', string $diskName = '')
    {
        if (! is_file($this->pathToFile)) {
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
        $media->disk = $this->determineDiskName($diskName);

        $media->collection_name = $collectionName;

        $media->mime_type = File::getMimetype($this->pathToFile);
        $media->size = filesize($this->pathToFile);
        $media->custom_properties = $this->customProperties;
        $media->manipulations = $this->manipulations;

        $media->fill($this->properties);

        $this->attachMedia($media);

        return $media;
    }

    /**
     * @param string $diskName
     *
     * @return string
     *
     * @throws \Spatie\MediaLibrary\Exceptions\FileCannotBeAdded
     */
    protected function determineDiskName(string $diskName)
    {
        if ($diskName === '') {
            $diskName = config('medialibrary.default_filesystem');
        }

        if (is_null(config("filesystems.disks.{$diskName}"))) {
            throw DiskDoesNotExist::create($diskName);
        }

        return $diskName;
    }

    /**
     * @param $fileName
     *
     * @return string
     */
    public function defaultSanitizer(string $fileName): string
    {
        return str_replace(['#', '/', '\\'], '-', $fileName);
    }

    /**
     * Sanitize the fileName of the file using a callable.
     *
     * @param callable $fileNameSanitizer
     *
     * @return $this
     */
    public function sanitizingFileName(callable $fileNameSanitizer)
    {
        $this->fileNameSanitizer = $fileNameSanitizer;

        return $this;
    }

    /**
     * @param Media $media
     */
    protected function attachMedia(Media $media)
    {
        if (! $this->subject->exists) {
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

    /**
     * @param HasMedia $model
     * @param Media $media
     * @param FileAdder $fileAdder
     */
    protected function processMediaItem(HasMedia $model, Media $media, self $fileAdder)
    {
        $model->media()->save($media);

        $this->filesystem->add($fileAdder->pathToFile, $media, $fileAdder->fileName);

        if (! $fileAdder->preserveOriginal) {
            unlink($fileAdder->pathToFile);
        }
    }
}
