<?php

namespace Programic\MediaLibrary\MediaCollections\Exceptions;

use Exception;
use Programic\MediaLibrary\Support\PathGenerator\PathGenerator;

class InvalidPathGenerator extends Exception
{
    public static function doesntExist(string $class): self
    {
        return new static("Path generator class `{$class}` doesn't exist");
    }

    public static function doesNotImplementPathGenerator(string $class): self
    {
        $pathGeneratorClass = PathGenerator::class;

        return new static("Path generator class `{$class}` must implement `$pathGeneratorClass}`");
    }
}
