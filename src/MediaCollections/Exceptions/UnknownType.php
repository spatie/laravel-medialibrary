<?php

namespace Spatie\MediaLibrary\MediaCollections\Exceptions;

use Spatie\MediaLibrary\MediaCollections\Exceptions\FileCannotBeAdded;

class UnknownType extends FileCannotBeAdded
{
    public static function create(): self
    {
        return new static('Only strings, FileObjects and UploadedFileObjects can be imported');
    }
}
