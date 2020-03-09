<?php

namespace Spatie\MediaLibrary\MediaCollections\Exceptions;

class UnreachableUrl extends FileCannotBeAdded
{
    public static function create(string $url): self
    {
        return new static("Url `{$url}` cannot be reached");
    }
}
