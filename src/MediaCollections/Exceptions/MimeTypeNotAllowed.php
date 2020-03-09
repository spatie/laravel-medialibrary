<?php

namespace Spatie\MediaLibrary\MediaCollections\Exceptions;

class MimeTypeNotAllowed extends FileCannotBeAdded
{
    public static function create(string $file, array $allowedMimeTypes): self
    {
        $mimeType = mime_content_type($file);

        $allowedMimeTypes = implode(', ', $allowedMimeTypes);

        return new static("File has a mime type of {$mimeType}, while only {$allowedMimeTypes} are allowed");
    }
}
