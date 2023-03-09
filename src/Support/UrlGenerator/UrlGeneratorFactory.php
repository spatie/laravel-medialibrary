<?php

namespace Spatie\MediaLibrary\Support\UrlGenerator;

use Spatie\MediaLibrary\Conversions\ConversionCollection;
use Spatie\MediaLibrary\MediaCollections\Exceptions\InvalidConversion;
use Spatie\MediaLibrary\MediaCollections\Exceptions\InvalidUrlGenerator;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\PathGenerator\PathGeneratorFactory;

class UrlGeneratorFactory
{
    /**
     * @throws InvalidUrlGenerator
     * @throws InvalidConversion
     */
    public static function createForMedia(Media $media, string $conversionName = ''): UrlGenerator
    {
        $urlGeneratorClass = config('media-library.url_generator');

        static::guardAgainstInvalidUrlGenerator($urlGeneratorClass);

        /** @var UrlGenerator $urlGenerator */
        $urlGenerator = app($urlGeneratorClass);

        $pathGenerator = PathGeneratorFactory::create($media);

        $urlGenerator
            ->setMedia($media)
            ->setPathGenerator($pathGenerator);

        if ($conversionName !== '') {
            $conversion = ConversionCollection::createForMedia($media)->getByName($conversionName);

            $urlGenerator->setConversion($conversion);
        }

        return $urlGenerator;
    }

    /**
     * @throws InvalidUrlGenerator
     */
    public static function guardAgainstInvalidUrlGenerator(string $urlGeneratorClass): void
    {
        if (! class_exists($urlGeneratorClass)) {
            throw InvalidUrlGenerator::doesntExist($urlGeneratorClass);
        }

        if (! is_subclass_of($urlGeneratorClass, UrlGenerator::class)) {
            throw InvalidUrlGenerator::doesNotImplementUrlGenerator($urlGeneratorClass);
        }
    }
}
