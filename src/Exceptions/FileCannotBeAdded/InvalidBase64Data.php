<?php

namespace Spatie\MediaLibrary\Exceptions\FileCannotBeAdded;

use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded;

class InvalidBase64Data extends FileCannotBeAdded
{
    public static function create()
    {
        return new static('Invalid base64 data provided');
    }
}
