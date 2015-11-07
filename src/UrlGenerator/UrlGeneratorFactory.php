<?php

namespace Spatie\MediaLibrary\UrlGenerator;

use Spatie\MediaLibrary\Media;
use Spatie\MediaLibrary\PathGenerator\BasePathGenerator;
use Spatie\MediaLibrary\PathGenerator\PathGenerator;
use Spatie\MediaLibrary\PathGenerator\PathGeneratorFactory;

class UrlGeneratorFactory
{
    public static function createForMedia(Media $media)
    {
        $urlGeneratorClass = 'Spatie\MediaLibrary\UrlGenerator\\'.ucfirst($media->getDiskDriverName()).'UrlGenerator';

        $customUrlClass = config('laravel-medialibrary.custom_url_generator_class');

        if ($customUrlClass && class_exists($customUrlClass) && is_subclass_of($customUrlClass, UrlGenerator::class)) {
            $urlGeneratorClass = $customUrlClass;
        }

        $urlGenerator = app($urlGeneratorClass);

        $pathGenerator = PathGeneratorFactory::create();

        $urlGenerator->setMedia($media)->setPathGenerator($pathGenerator);

        return $urlGenerator;
    }
}
