<?php

namespace Spatie\Medialibrary\Support\UrlGenerator;

use Spatie\Medialibrary\Conversions\ConversionCollection;
use Spatie\Medialibrary\MediaCollections\Exceptions\InvalidUrlGenerator;
use Spatie\Medialibrary\MediaCollections\Models\Media;
use Spatie\Medialibrary\Support\PathGenerator\PathGeneratorFactory;

class UrlGeneratorFactory
{
    public static function createForMedia(Media $media, string $conversionName = ''): UrlGenerator
    {
        $urlGeneratorClass = config('medialibrary.url_generator');

        static::guardAgainstInvalidUrlGenerator($urlGeneratorClass);

        $urlGenerator = app($urlGeneratorClass);

        $pathGenerator = PathGeneratorFactory::create();

        $urlGenerator
            ->setMedia($media)
            ->setPathGenerator($pathGenerator);

        if ($conversionName !== '') {
            $conversion = ConversionCollection::createForMedia($media)->getByName($conversionName);

            $urlGenerator->setConversion($conversion);
        }

        return $urlGenerator;
    }

    public static function guardAgainstInvalidUrlGenerator(string $urlGeneratorClass)
    {
        if (! class_exists($urlGeneratorClass)) {
            throw InvalidUrlGenerator::doesntExist($urlGeneratorClass);
        }

        if (! is_subclass_of($urlGeneratorClass, UrlGenerator::class)) {
            throw InvalidUrlGenerator::isntAUrlGenerator($urlGeneratorClass);
        }
    }
}
