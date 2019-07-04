<?php

namespace Spatie\MediaLibrary\Exceptions\FileCannotBeAdded;

use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded;

class MimeTypeNotAllowed extends FileCannotBeAdded
{
    public static function create($file, array $allowedMimeTypes)
    {
        $mimeType = mime_content_type($file);

        $allowedMimeTypes = implode(', ', $allowedMimeTypes);

        return new static("File has a mimetype of {$mimeType}, while only {$allowedMimeTypes} are allowed");
    }
}
