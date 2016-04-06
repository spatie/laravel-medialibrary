<?php

namespace Spatie\MediaLibrary\Exceptions;

use Exception;

class InvalidConversion extends Exception
{
    public static function unknownName(string $name)
    {
        return new static("There is no conversion named `{$name}`");
    }
}
