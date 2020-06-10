<?php

namespace Spatie\MediaLibrary\MediaCollections\Exceptions;

use Exception;

class InvalidUrl extends Exception
{
    public static function doesNotStartWithProtocol(string $url)
    {
        return new static("Could not add `{$url}` because it does not start with either `http://` or `https://`");
    }
}
