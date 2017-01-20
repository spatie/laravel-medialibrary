<?php

namespace Spatie\MediaLibrary\Exceptions\FileCannotBeAdded;

use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded;

class UnreachableUrl extends FileCannotBeAdded
{
    public static function create(string $url)
    {
        return new static("Url `{$url}` cannot be reached");
    }
}
