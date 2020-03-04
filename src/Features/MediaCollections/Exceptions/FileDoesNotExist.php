<?php

namespace Spatie\Medialibrary\Features\MediaCollections\Exceptions;

use Spatie\Medialibrary\Features\MediaCollections\Exceptions\FileCannotBeAdded;

class FileDoesNotExist extends FileCannotBeAdded
{
    public static function create(string $path): self
    {
        return new static("File `{$path}` does not exist");
    }
}
