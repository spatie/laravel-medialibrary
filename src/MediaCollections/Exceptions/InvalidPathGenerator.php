<?php

namespace Spatie\MediaLibrary\MediaCollections\Exceptions;

use Exception;
use Spatie\MediaLibrary\Support\PathGenerator\PathGenerator;

class InvalidPathGenerator extends Exception
{
    public static function doesntExist(string $class): self
    {
        return new static("Class {$class} doesn't exist");
    }

    public static function isntAPathGenerator(string $class): self
    {
        $pathGeneratorClass = PathGenerator::class;

        return new static("Class {$class} must implement `$pathGeneratorClass}`");
    }
}
