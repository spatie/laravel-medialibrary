<?php

namespace Spatie\MediaLibrary\PathGenerator;

use Spatie\MediaLibrary\Exceptions\InvalidPathGenerator;

class PathGeneratorFactory
{
    public static function create()
    {
        $pathGeneratorClass = BasePathGenerator::class;

        $customPathClass = config('medialibrary.custom_path_generator_class');

        if ($customPathClass) {
            $pathGeneratorClass = $customPathClass;
        }

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
