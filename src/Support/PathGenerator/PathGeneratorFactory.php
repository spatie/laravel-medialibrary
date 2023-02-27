<?php

namespace Spatie\MediaLibrary\Support\PathGenerator;

use Illuminate\Database\Eloquent\Relations\Relation;
use Spatie\MediaLibrary\MediaCollections\Exceptions\InvalidPathGenerator;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class PathGeneratorFactory
{
    public static function create(Media $media): PathGenerator
    {
        $pathGeneratorClass = self::getPathGeneratorClass($media);

        static::guardAgainstInvalidPathGenerator($pathGeneratorClass);

        return app($pathGeneratorClass);
    }

    protected static function getPathGeneratorClass(Media $media)
    {
        $defaultPathGeneratorClass = config('media-library.path_generator');

        foreach (config('media-library.custom_path_generators', []) as $modelClass => $customPathGeneratorClass) {
            if (
                // model doesn't have morphMap
                is_a($media->model_type, $modelClass, true)
                // config is set via morphMap alias
                || $media->model_type === $modelClass
                // config is set via morphMap class name
                || is_a((string)Relation::getMorphedModel($media->model_type), $modelClass, true)
            ) {
                return $customPathGeneratorClass;
            }
        }

        return $defaultPathGeneratorClass;
    }

    protected static function guardAgainstInvalidPathGenerator(string $pathGeneratorClass): void
    {
        if (! class_exists($pathGeneratorClass)) {
            throw InvalidPathGenerator::doesntExist($pathGeneratorClass);
        }

        if (! is_subclass_of($pathGeneratorClass, PathGenerator::class)) {
            throw InvalidPathGenerator::doesNotImplementPathGenerator($pathGeneratorClass);
        }
    }
}
