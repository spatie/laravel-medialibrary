<?php

namespace Spatie\MediaLibrary\Conversions\ImageGenerators;

use Illuminate\Support\Collection;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ImageGeneratorFactory
{
    public static function getImageGenerators(): Collection
    {
        return collect(config('media-library.image_generators'))
            ->map(function ($imageGeneratorClassName, $key) {
                $imageGeneratorConfig = [];

                if (! is_numeric($key)) {
                    $imageGeneratorConfig = $imageGeneratorClassName;
                    $imageGeneratorClassName = $key;
                }
                return app($imageGeneratorClassName, $imageGeneratorConfig);
            });
    }

    public static function forExtension(?string $extension): ?ImageGenerator
    {
        return static::getImageGenerators()
            ->first(fn (ImageGenerator $imageGenerator) => $imageGenerator->canHandleExtension(strtolower($extension)));
    }

    public static function forMimeType(?string $mimeType): ?ImageGenerator
    {
        return static::getImageGenerators()
            ->first(fn (ImageGenerator $imageGenerator) => $imageGenerator->canHandleMime($mimeType));
    }

    public static function forMedia(Media $media): ?ImageGenerator
    {
        return static::getImageGenerators()
            ->first(fn (ImageGenerator $imageGenerator) => $imageGenerator->canConvert($media));
    }
}
