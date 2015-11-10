<?php

namespace Spatie\MediaLibrary\UrlGenerator;

use Spatie\MediaLibrary\Media;
use Spatie\MediaLibrary\PathGenerator\PathGeneratorFactory;

class UrlGeneratorFactory
{
    public static function createForMedia(Media $media)
    {
        $urlGeneratorClass = 'Spatie\MediaLibrary\UrlGenerator\\'.ucfirst($media->getDiskDriverName()).'UrlGenerator';

        $customUrlClass = config('laravel-medialibrary.custom_url_generator_class');

        $urlGenerator = self::isAValidUrlGeneratorClass($customUrlClass)
            ? app($customUrlClass)
            : app($urlGeneratorClass);

        $pathGenerator = PathGeneratorFactory::create();

        $urlGenerator->setMedia($media)->setPathGenerator($pathGenerator);

        return $urlGenerator;
    }

    /**
     * Determine if the the given class is a valid UrlGenerator.
     *
     * @param $customUrlClass
     *
     * @return bool
     */
    protected static function isAValidUrlGeneratorClass($customUrlClass)
    {
        if (!$customUrlClass) {
            return false;
        }

        if (!class_exists($customUrlClass)) {
            return false;
        }

        if (!is_subclass_of($customUrlClass, UrlGenerator::class)) {
            return false;
        }

        return true;
    }
}
