<?php

namespace Spatie\MediaLibrary\Exceptions\FileCannotBeAdded;

use Exception;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded;
use Spatie\MediaLibrary\Helpers\File;
use Illuminate\Database\Eloquent\Model;

class FileDoesNotExist extends FileCannotBeAdded
{
    public static function create(string $path)
    {
        return new static("File `{$path}` does not exist");
    }
}