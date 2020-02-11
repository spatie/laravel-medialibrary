<?php

namespace Spatie\MediaLibrary\Helpers;

class RemoteFile
{
    protected string $key;

    protected string $disk;

    public function __construct($key, $disk)
    {
        $this->key = $key;
        $this->disk = $disk;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getDisk(): string
    {
        return $this->disk;
    }

    public function getFilename(): string
    {
        return basename($this->key);
    }

    public function getName(): string
    {
        return pathinfo($this->getFilename(), PATHINFO_FILENAME);
    }
}
