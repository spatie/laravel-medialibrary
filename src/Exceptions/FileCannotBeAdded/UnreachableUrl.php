<?php

namespace Spatie\MediaLibrary\Exceptions\FileCannotBeAdded;

use Exception;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded;
use Spatie\MediaLibrary\Helpers\File;
use Illuminate\Database\Eloquent\Model;

class UnreachableUrl extends FileCannotBeAdded
{
    public static function create(string $url)
    {
        return new static("Url `{$url}` cannot be reached");
    }
}