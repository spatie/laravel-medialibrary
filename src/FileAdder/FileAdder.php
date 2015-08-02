<?php

namespace Spatie\MediaLibrary\FileAdder;

use Illuminate\Contracts\Cache\Repository;
use Spatie\MediaLibrary\Exceptions\FileCannotBeImported;
use Spatie\MediaLibrary\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\Exceptions\FileTooBig;
use Spatie\MediaLibrary\Filesystem;
use Spatie\MediaLibrary\Media;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileAdder
{
    /**
     * @var \Illuminate\Database\Eloquent\Model subject
     */
    protected $subject;

    /**
     * @var Filesystem
     */
    protected $fileSystem;
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
     * @param Filesystem $fileSystem
     * @param Repository $config
     */
    public function __construct(Filesystem $fileSystem, Repository $config)
    {
        $this->fileSystem = $fileSystem;
        $this->config = $config;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $subject
     *
     * @return FileAdder
     */
    public function setSubject($subject)
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
     * @throws FileCannotBeImported
     */
    public function setFile($file)
    {
        $this->file = $file;

        if (is_string($file)) {
            $this->pathToFile = $file;
            $this->fileName = pathinfo($file, PATHINFO_BASENAME);
            $this->mediaName = pathinfo($file, PATHINFO_FILENAME);

            return $this;
        }

        if ($file instanceof UploadedFile) {
            $this->pathToFile = $file->getPath().'/'.$file->getFilename();
            $this->fileName = $file->getClientOriginalName();
            $this->mediaName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

            return $this;
        }

        throw new FileCannotBeImported('Only strings and UploadedFileObjects can be imported');
    }

    /**
     * When adding the file the medialibrary, the original file
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
     * Set the metadata.
     *
     * @param $customProperties
     */
    public function withCustomProperties($customProperties)
    {
        $this->customProperties = $customProperties;
    }

    /**
     * Set the target media collection to default.
     * Will also start the import process.
     *
     * @return Media
     *
     * @throws FileDoesNotExist
     * @throws FileTooBig
     */
    public function toMediaLibrary()
    {
        return $this->toCollection('default');
    }

    /**
     * Set the collection name where to import the file.
     * Will also start the import process.
     *
     * @param $collectionName
     *
     * @return Media
     *
     * @throws FileDoesNotExist
     * @throws FileTooBig
     */
    public function toCollection($collectionName)
    {
        if (!is_file($this->pathToFile)) {
            throw new FileDoesNotExist();
        }

        if (filesize($this->pathToFile) > config('laravel-medialibrary.max_file_size')) {
            throw new FileTooBig();
        }

        $media = new Media();

        $media->name = $this->mediaName;
        $media->file_name = $this->fileName;

        $media->collection_name = $collectionName;

        $media->size = filesize($this->pathToFile);
        $media->custom_properties = $this->customProperties;
        $media->manipulations = [];

        $media->save();

        $this->subject->media()->save($media);

        $this->fileSystem->add($this->pathToFile, $media);

        if (! $this->preserveOriginal) {
            unlink($this->pathToFile);
        }

        return $media;
    }
}
