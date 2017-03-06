<?php

namespace Spatie\MediaLibrary\FileAdder;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class FileAdderGroup
{
    /** @var  FileAdder[] */
    protected $fileAdders;

    /** @var  \Illuminate\Database\Eloquent\Model */
    protected $subject;

    /**
     * @param \Illuminate\Database\Eloquent\Model $subject
     *
     * @return $this
     */
    public function setSubject(Model $subject)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * Set array of files that need to be added.
     *
     * @param array $files
     *
     * @return $this
     */
    public function setFiles(array $files)
    {
        $this->fileAdders = [];

        foreach($files as $file) {
            $this->fileAdders[] = app(FileAdderFactory::class)->create($this->subject, $file);
        }

        return $this;
    }

    public function __call($methodName, $parameters)
    {
        foreach($this->fileAdders as $fileAdder) {
            $fileAdder->$methodName(...$parameters);
        }

        return $this;
    }

    /**
     * @param string $collectionName
     * @param string $diskName
     *
     * @return Collection
     */
    public function toMediaLibrary(string $collectionName = 'default', string $diskName = '')
    {
        return collect($this->fileAdders)
            ->map(function (FileAdder $fileAdder) use ($collectionName, $diskName) {
                return $fileAdder->toMediaLibrary($collectionName, $diskName);
            });
    }

    /**
     * @param string $collectionName
     *
     * @return Collection
     *
     */
    public function toMediaLibraryOnCloudDisk(string $collectionName = 'default')
    {
        return $this->toMediaLibrary($collectionName, config('filesystems.cloud'));
    }
}
