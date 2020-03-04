<?php

namespace Spatie\Medialibrary\MediaCollections\Exceptions;

use Spatie\Medialibrary\MediaCollections\Exceptions\FileCannotBeAdded;
use Spatie\Medialibrary\Support\File;

class FileIsTooBig extends FileCannotBeAdded
{
    public static function create(string $path, int $size = null): self
    {
        $fileSize = File::getHumanReadableSize($size ?: filesize($path));

        $maxFileSize = File::getHumanReadableSize(config('medialibrary.max_file_size'));

        return new static("File `{$path}` has a size of {$fileSize} which is greater than the maximum allowed {$maxFileSize}");
    }
}
