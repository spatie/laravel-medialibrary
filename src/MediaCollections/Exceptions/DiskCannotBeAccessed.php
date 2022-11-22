<?php

namespace Spatie\MediaLibrary\MediaCollections\Exceptions;

class DiskCannotBeAccessed extends FileCannotBeAdded
{
    public static function create(string $diskName): self
    {
        return new static("Disk named `{$diskName}` cannot be accessed");
    }
}
