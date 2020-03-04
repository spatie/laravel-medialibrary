<?php

namespace Spatie\Medialibrary\Exceptions\FileCannotBeAdded;

use Spatie\Medialibrary\Exceptions\FileCannotBeAdded\FileCannotBeAdded;

class FileDoesNotExist extends FileCannotBeAdded
{
    public static function create(string $path): self
    {
        return new static("File `{$path}` does not exist");
    }
}
