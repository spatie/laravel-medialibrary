<?php

namespace Spatie\MediaLibrary\Helpers;

use Spatie\MediaLibrary\MediaLibrary;
use Spatie\TemporaryDirectory\TemporaryDirectory as BaseTemporaryDirectory;

class TemporaryDirectory
{
    public static function create(): BaseTemporaryDirectory
    {
        return new BaseTemporaryDirectory(static::getTemporaryDirectoryPath());
    }

    protected static function getTemporaryDirectoryPath(): string
    {
        $path = MediaLibrary::config('temporary_directory_path') ?? storage_path('medialibrary/temp');

        return $path.DIRECTORY_SEPARATOR.str_random(32);
    }
}
