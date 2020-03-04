<?php

namespace Spatie\Medialibrary\MediaCollections\Exceptions;

use Spatie\Medialibrary\MediaCollections\Exceptions\FileCannotBeAdded;

class InvalidBase64Data extends FileCannotBeAdded
{
    public static function create(): self
    {
        return new static('Invalid base64 data provided');
    }
}
