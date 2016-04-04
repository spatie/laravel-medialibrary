<?php

namespace Spatie\MediaLibrary\Exceptions;

use Exception;
use Spatie\MediaLibrary\Helpers\File;

class FileCannotBeAdded extends Exception
{
    public static function unknownType()
    {
        return new static('Only strings, FileObjects and UploadedFileObjects can be imported');
    }

    public static function fileIsTooBig(string $path)
    {
        $fileSize = File::getHumanReadableSize(filesize($path));

        $maxFileSize = File::getHumanReadableSize(config('laravel-medialibrary.max_file_size'));

        return new static("File `{$path}` has a size of {$fileSize} which is greater than the maximum allowed {$maxFileSize}");
    }

    public static function fileDoesNotExist(string $path)
    {
        return new static("File `{$path}` does not exist");
    }

    public static function unreachableUrl(string $url)
    {
        return new static("Url `{$url}` cannot be reached");
    }

    public static function diskDoesNotExist(string $diskName)
    {
        return new static("There is no filesystem disk named `{$diskName}` does not exist");
    }
}
