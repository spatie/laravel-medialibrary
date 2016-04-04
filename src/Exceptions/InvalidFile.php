<?php

namespace Spatie\MediaLibrary\Exceptions;

use Exception;
use Spatie\MediaLibrary\Helpers\File;

class InvalidFile extends Exception
{
    public static function cannotBeImported()
    {
        return new static("Only strings, FileObjects and UploadedFileObjects can be imported");
    }
    
    public static function tooBig(string $path)
    {
        $fileSize = File::getHumanReadableSize(filesize($path));

        $maxFileSize = File::getHumanReadableSize(config('laravel-medialibrary.max_file_size'));
        
        return new static("File `{$path}` has a size of {$fileSize} which is greater than the maximum allowed {$maxFileSize}");
    }

    public static function doesNotExist(string $path)
    {
        return new static("File `{$path}` does not exist");
    }
}
