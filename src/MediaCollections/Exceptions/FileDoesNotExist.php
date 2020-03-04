<?php

namespace Spatie\Medialibrary\MediaCollections\Exceptions;

use Spatie\Medialibrary\MediaCollections\Exceptions\FileCannotBeAdded;

class FileDoesNotExist extends FileCannotBeAdded
{
    public static function create(string $path): self
    {
        return new static("File `{$path}` does not exist");
    }
}
