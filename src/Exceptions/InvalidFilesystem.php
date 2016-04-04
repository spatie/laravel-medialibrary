<?php

namespace Spatie\MediaLibrary\Exceptions;

use Exception;

class InvalidFilesystem extends Exception
{
    public static function doesNotExist(string $name)
    {
        return new static("There is no filesystem disk named `{$name}` does not exist");
    }
}
