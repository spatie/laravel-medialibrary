<?php

namespace Spatie\Medialibrary\Exceptions\FileCannotBeAdded;

use Spatie\Medialibrary\Exceptions\FileCannotBeAdded\FileCannotBeAdded;

class RequestDoesNotHaveFile extends FileCannotBeAdded
{
    public static function create($key): self
    {
        return new static("The current request does not have a file in a key named `{$key}`");
    }
}
