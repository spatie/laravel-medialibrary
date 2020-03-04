<?php

namespace Spatie\Medialibrary\MediaCollections\Exceptions;

use Spatie\Medialibrary\MediaCollections\Exceptions\FileCannotBeAdded;

class DiskDoesNotExist extends FileCannotBeAdded
{
    public static function create($diskName): self
    {
        return new static("There is no filesystem disk named `{$diskName}`");
    }
}
