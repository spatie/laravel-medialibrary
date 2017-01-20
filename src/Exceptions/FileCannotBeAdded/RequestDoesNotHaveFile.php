<?php

namespace Spatie\MediaLibrary\Exceptions\FileCannotBeAdded;

use Exception;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded;
use Spatie\MediaLibrary\Helpers\File;
use Illuminate\Database\Eloquent\Model;

class RequestDoesNotHaveFile extends FileCannotBeAdded
{
    public static function create($key)
    {
        return new static("The current request does not have a file in a key named `{$key}`");
    }
}