<?php

namespace Spatie\MediaLibrary\MediaCollections\Exceptions;

use Exception;

class VirtualConversionHasNoFile extends Exception
{
    public static function create(string $conversionName): self
    {
        return new static("Conversion `{$conversionName}` is delivered by url and has no file on disk. Use getUrl() instead of getPath().");
    }
}
