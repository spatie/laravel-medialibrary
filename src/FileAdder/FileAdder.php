<?php

namespace Spatie\MediaLibrary\FileAdder;

use Spatie\MediaLibrary\Media;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Cache\Repository;
use Spatie\MediaLibrary\FilesystemInterface;
use Symfony\Component\HttpFoundation\File\File;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileAdder
{
    /**
     * @var \Illuminate\Database\Eloquent\Model subject
     */
    protected $subject;

    /**
     * @var FilesystemInterface
     */
    protected $filesystem;

    /**
     * @var Repository
     */
    protected $config;

    /**
     * @var bool
     */
    protected $preserveOriginal = false;

    /**
     * @var string|\Symfony\Component\HttpFoundation\File\UploadedFile
     */
    protected $file;

    /**
     * @var array
     */
    protected $properties = [];

    /**
     * @var array
     */
    protected $customProperties = [];

    /**
     * @var string
     */
    protected $pathToFile;

    /**
     * @var string
     */
    protected $fileName;

    /**
     * @var string
     */
    protected $mediaName;

    /**
     * @var string
     */
    protected $diskName = '';

    /**
     * @param FilesystemInterface $fileSystem
     * @param Repository $config
     */
    public function __construct(FilesystemInterface $fileSystem, Repository $config)
    {
        $this->filesystem = $fileSystem;
        $this->config = $config;
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

    /**
     * Set the file that needs to be imported.
     *
     * @param string|\Symfony\Component\HttpFoundation\File\UploadedFile $file
     *
     * @return $this
     *
     * @throws \Spatie\MediaLibrary\Exceptions\FileCannotBeAdded
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

        if ($file instanceof File) {
            $this->pathToFile = $file->getPath().'/'.$file->getFilename();
            $this->setFileName(pathinfo($file->getFilename(), PATHINFO_BASENAME));
            $this->mediaName = pathinfo($file->getFilename(), PATHINFO_FILENAME);

            return $this;
        }

        throw FileCannotBeAdded::unknownType();
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
        $this->fileName = $this->sanitizeFileName($fileName);

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
     * Set the target media collection to default.
     * Will also start the import process.
     *
     * @param string $collectionName
     * @param string $diskName
     *
     * @return Media
     *
     * @throws FileDoesNotExist
     * @throws FileCannotBeAdded
     */
    public function toMediaLibrary(string $collectionName = 'default', string $diskName = '')
    {
        return $this->toCollectionOnDisk($collectionName, $diskName);
    }

    /**
     * Set the target media collection to default.
     * Will also start the import process.
     *
     * @param string $collectionName
     * @param string $diskName
     *
     * @return Media
     *
     * @throws FileDoesNotExist
     * @throws FileCannotBeAdded
     */
    public function toMediaLibraryOnDisk(string $collectionName = 'default', string $diskName = '')
    {
        return $this->toCollectionOnDisk($collectionName, $diskName);
    }

    /**
     * Set the collection name where to import the file.
     * Will also start the import process.
     *
     * @param string $collectionName
     * @param string $diskName
     *
     * @return Media
     *
     * @throws FileDoesNotExist
     * @throws FileCannotBeAdded
     */
    public function toCollection(string $collectionName = 'default', string $diskName = '')
    {
        return $this->toCollectionOnDisk($collectionName, $diskName);
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
    public function toCollectionOnDisk(string $collectionName = 'default', string $diskName = '')
    {
        if (! $this->subject->exists) {
            throw FileCannotBeAdded::modelDoesNotExist($this->subject);
        }

        if (! is_file($this->pathToFile)) {
            throw FileCannotBeAdded::fileDoesNotExist($this->pathToFile);
        }

        if (filesize($this->pathToFile) > config('laravel-medialibrary.max_file_size')) {
            throw FileCannotBeAdded::fileIsTooBig($this->pathToFile);
        }

        $mediaClass = config('laravel-medialibrary.media_model');
        $media = new $mediaClass();

        $media->name = $this->mediaName;
        $media->file_name = $this->fileName;
        $media->disk = $this->determineDiskName($diskName);

        $media->collection_name = $collectionName;

        $media->size = filesize($this->pathToFile);
        $media->custom_properties = $this->customProperties;
        $media->manipulations = [];

        $media->fill($this->properties);

        $this->subject->media()->save($media);

        $this->filesystem->add($this->pathToFile, $media, $this->fileName);

        if (! $this->preserveOriginal) {
            unlink($this->pathToFile);
        }

        return $media;
    }

    /**
     * Determine the disk to be used.
     *
     * @param string $diskName
     *
     * @return string
     *
     * @throws \Spatie\MediaLibrary\Exceptions\FileCannotBeAdded
     */
    protected function determineDiskName(string $diskName)
    {
        if ($diskName === '') {
            $diskName = config('laravel-medialibrary.defaultFilesystem');
        }

        if (is_null(config("filesystems.disks.{$diskName}"))) {
            throw FileCannotBeAdded::diskDoesNotExist($diskName);
        }

        return $diskName;
    }

    /**
     * Sanitize the given file name.
     *
     * @param $fileName
     *
     * @return string
     */
    protected function sanitizeFileName(string $fileName) : string
    {
        return str_replace(['#', '/', '\\'], '-', $fileName);
    }
}
