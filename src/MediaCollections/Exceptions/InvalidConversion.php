<?php

namespace Spatie\MediaLibrary\MediaCollections\Exceptions;

use Exception;

class InvalidConversion extends Exception
{
    public static function unknownName(string $name): self
    {
        return new static("There is no conversion named `{$name}`");
    }
}
