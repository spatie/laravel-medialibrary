<?php

namespace Spatie\MediaLibrary\Helpers;

use Spatie\TemporaryDirectory\TemporaryDirectory as BaseTemporaryDirectory;

class TemporaryDirectory
{
    public static function create(): BaseTemporaryDirectory
    {
        return new BaseTemporaryDirectory(static::getTemporaryDirectoryPath());
    }

    protected static function getTemporaryDirectoryPath(): string
    {
        $path = is_null(config('medialibrary.temporary_directory_path'))
            ? storage_path('medialibrary/temp')
            : config('medialibrary.temporary_directory_path');

        return $path.DIRECTORY_SEPARATOR.str_random(32);
    }
}
