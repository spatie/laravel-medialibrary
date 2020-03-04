<?php

namespace Spatie\Medialibrary\MediaCollections\Exceptions;

use Spatie\Medialibrary\MediaCollections\Exceptions\FileCannotBeAdded;

class UnknownType extends FileCannotBeAdded
{
    public static function create(): self
    {
        return new static('Only strings, FileObjects and UploadedFileObjects can be imported');
    }
}
