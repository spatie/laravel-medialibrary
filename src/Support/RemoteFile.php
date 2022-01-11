<?php

namespace Spatie\MediaLibrary\Support;

class RemoteFile
{
    public function __construct(protected string $key, protected string $disk)
    {
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
