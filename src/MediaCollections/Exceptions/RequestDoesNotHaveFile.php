<?php

namespace Spatie\Medialibrary\MediaCollections\Exceptions;

use Spatie\Medialibrary\MediaCollections\Exceptions\FileCannotBeAdded;

class RequestDoesNotHaveFile extends FileCannotBeAdded
{
    public static function create($key): self
    {
        return new static("The current request does not have a file in a key named `{$key}`");
    }
}
