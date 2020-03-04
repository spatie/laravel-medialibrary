<?php

namespace Spatie\Medialibrary\ImageGenerators;

use Illuminate\Support\Collection;
use Spatie\Medialibrary\Models\Media;

class ImageGeneratorFactory
{
    public static function getImageGenerators(): Collection
    {
        return collect(config('medialibrary.image_generators'))
            ->map(fn(string $imageGeneratorClassName) => app($imageGeneratorClassName));
    }

    public static function forExtension(?string $extension): ?ImageGenerator
    {
        return static::getImageGenerators()
            ->first(fn(ImageGenerator $imageGenerator) => $imageGenerator->canHandleExtension(strtolower($extension)));
    }

    public static function forMimeType(?string $mimeType): ?ImageGenerator
    {
        return static::getImageGenerators()
            ->first(fn(ImageGenerator $imageGenerator) => $imageGenerator->canHandleMime($mimeType));
    }

    public static function forMedia(Media $media): ?ImageGenerator
    {
        return static::getImageGenerators()
            ->first(fn(ImageGenerator $imageGenerator) => $imageGenerator->canConvert($media));
    }
}
