<?php

namespace Spatie\MediaLibrary\MediaCollections\Exceptions;

class UnknownType extends FileCannotBeAdded
{
    public static function create(): self
    {
        return new static('Only strings, FileObjects and UploadedFileObjects can be imported');
    }
}
