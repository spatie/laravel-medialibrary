<?php

namespace Spatie\MediaLibrary\UrlGenerator;

use Spatie\MediaLibrary\Media;

class UrlGeneratorFactory
{
    public static function createForMedia(Media $media)
    {
        $urlGeneratorClass = 'Spatie\MediaLibrary\UrlGenerator\\'.ucfirst($media->getDiskDriverName()).'UrlGenerator';

        $customClass = config('laravel-medialibrary.custom_url_generator_class');

        if ($customClass != '' && class_exists($customClass) && is_subclass_of($customClass, UrlGenerator::class)) {
            $urlGeneratorClass = $customClass;
        }

        $urlGenerator = app($urlGeneratorClass);

        $urlGenerator->setMedia($media);

        return $urlGenerator;
    }
}
