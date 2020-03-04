<?php

namespace Spatie\Medialibrary\MediaCollections\Exceptions;

use Exception;
use Spatie\Medialibrary\Support\UrlGenerator\UrlGenerator;

class InvalidUrlGenerator extends Exception
{
    public static function doesntExist(string $class): self
    {
        return new static("Class {$class} doesn't exist");
    }

    public static function isntAUrlGenerator(string $class): self
    {
        $urlGeneratorClass = UrlGenerator::class;

        return new static("Class {$class} must implement `{$urlGeneratorClass}`");
    }
}
