<?php

namespace Spatie\MediaLibrary\Exceptions\FileCannotBeAdded;

use Exception;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded;
use Spatie\MediaLibrary\Helpers\File;
use Illuminate\Database\Eloquent\Model;

class DiskDoesNotExist extends FileCannotBeAdded
{
    public static function create(string $diskName)
    {
        return new static("There is no filesystem disk named `{$diskName}`");
    }
}