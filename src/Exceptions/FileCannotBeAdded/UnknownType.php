<?php

namespace Spatie\MediaLibrary\Exceptions\FileCannotBeAdded;

use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded;

class UnknownType extends FileCannotBeAdded
{
    public static function create()
    {
        return new static('Only strings, FileObjects and UploadedFileObjects can be imported');
    }
}
