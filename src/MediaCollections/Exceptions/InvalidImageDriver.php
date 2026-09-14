<?php

namespace Spatie\MediaLibrary\MediaCollections\Exceptions;

use Exception;

class InvalidImageDriver extends Exception
{
    public static function unknown(string $driverName): self
    {
        return new static("Image driver `{$driverName}` is not configured. Add it to the `media-library.image_drivers` config or register it with ImageDriverManager::extend().");
    }

    public static function cannotGenerateFiles(string $driverName): self
    {
        return new static("Image driver `{$driverName}` does not generate conversion files. It can only be used for conversions that are delivered by url.");
    }
}
