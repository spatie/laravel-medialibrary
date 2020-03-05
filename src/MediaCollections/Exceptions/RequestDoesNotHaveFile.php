<?php

namespace Spatie\MediaLibrary\MediaCollections\Exceptions;

class RequestDoesNotHaveFile extends FileCannotBeAdded
{
    public static function create(string $key): self
    {
        return new static("The current request does not have a file in a key named `{$key}`");
    }
}
