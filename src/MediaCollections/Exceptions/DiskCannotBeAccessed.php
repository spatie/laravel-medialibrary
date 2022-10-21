<?php

namespace Spatie\MediaLibrary\MediaCollections\Exceptions;

class DiskCannotBeAccessed extends FileCannotBeAdded
{
    public static function create(string $diskName): self
    {
        return new static("filesystem disk named `{$diskName}` cannot be accessed");
    }
}
