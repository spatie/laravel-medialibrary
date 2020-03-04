<?php

namespace Spatie\Medialibrary\Features\MediaCollections\Exceptions;

use Spatie\Medialibrary\Features\MediaCollections\Exceptions\FileCannotBeAdded;

class DiskDoesNotExist extends FileCannotBeAdded
{
    public static function create($diskName): self
    {
        return new static("There is no filesystem disk named `{$diskName}`");
    }
}
