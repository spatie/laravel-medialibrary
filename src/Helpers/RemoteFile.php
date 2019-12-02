<?php

namespace Spatie\MediaLibrary\Helpers;

class RemoteFile
{
    /**
     * The relative path to the file.
     *
     * @var string
     */
    protected $key;

    /**
     * The disk the file exists on.
     *
     * @var string
     */
    protected $disk;

    /**
     * Constructor method.
     *
     * @return void
     */
    public function __construct($key, $disk)
    {
        $this->key = $key;
        $this->disk = $disk;
    }

    /**
     * Get the key.
     *
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * Get the disk.
     *
     * @return string
     */
    public function getDisk(): string
    {
        return $this->disk;
    }

    /**
     * Get the filename (including extension).
     *
     * @return string
     */
    public function getFilename()
    {
        return basename($this->key);
    }

    /**
     * Get the name (excluding extension).
     *
     * @return string
     */
    public function getName()
    {
        return pathinfo($this->getFilename(), PATHINFO_FILENAME);
    }
}
