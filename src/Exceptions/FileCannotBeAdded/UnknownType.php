<?php

namespace Spatie\MediaLibrary\Exceptions\FileCannotBeAdded;

use Exception;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded;
use Spatie\MediaLibrary\Helpers\File;
use Illuminate\Database\Eloquent\Model;

class UnknownType extends FileCannotBeAdded
{
    public static function create()
    {
        return new static('Only strings, FileObjects and UploadedFileObjects can be imported');
    }
}