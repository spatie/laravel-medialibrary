<?php

namespace Spatie\MediaLibrary\Exceptions\FileCannotBeAdded;

use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded;

class DiskDoesNotExist extends FileCannotBeAdded
{
    public static function create($diskName)
    {
        return new static("There is no filesystem disk named `{$diskName}`");
    }
}
