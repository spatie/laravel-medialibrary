<?php

namespace Spatie\MediaLibrary\MediaCollections\Exceptions;

use Spatie\MediaLibrary\MediaCollections\Exceptions\FileCannotBeAdded;

class InvalidBase64Data extends FileCannotBeAdded
{
    public static function create(): self
    {
        return new static('Invalid base64 data provided');
    }
}
