<?php

namespace Spatie\Medialibrary\Support\PathGenerator;

use Spatie\Medialibrary\MediaCollections\Exceptions\InvalidPathGenerator;

class PathGeneratorFactory
{
    public static function create()
    {
        $pathGeneratorClass = config('medialibrary.path_generator');

        static::guardAgainstInvalidPathGenerator($pathGeneratorClass);

        return app($pathGeneratorClass);
    }

    protected static function guardAgainstInvalidPathGenerator(string $pathGeneratorClass)
    {
        if (! class_exists($pathGeneratorClass)) {
            throw InvalidPathGenerator::doesntExist($pathGeneratorClass);
        }

        if (! is_subclass_of($pathGeneratorClass, PathGenerator::class)) {
            throw InvalidPathGenerator::isntAPathGenerator($pathGeneratorClass);
        }
    }
}
