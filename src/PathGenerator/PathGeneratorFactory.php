<?php

namespace Spatie\MediaLibrary\PathGenerator;

class PathGeneratorFactory
{
    public static function create()
    {
        $pathGeneratorClass = BasePathGenerator::class;
        $customPathClass = config('laravel-medialibrary.custom_path_generator_class');

        if ($customPathClass && class_exists($customPathClass) && is_subclass_of($customPathClass, PathGenerator::class)) {
            $pathGeneratorClass = $customPathClass;
        }

        return app($pathGeneratorClass);
    }
}
