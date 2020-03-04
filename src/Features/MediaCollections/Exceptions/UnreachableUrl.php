<?php

namespace Spatie\Medialibrary\Features\MediaCollections\Exceptions;

use Spatie\Medialibrary\Features\MediaCollections\Exceptions\FileCannotBeAdded;

class UnreachableUrl extends FileCannotBeAdded
{
    public static function create(string $url): self
    {
        return new static("Url `{$url}` cannot be reached");
    }
}
