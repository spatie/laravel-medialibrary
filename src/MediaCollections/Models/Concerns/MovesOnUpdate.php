<?php

namespace Spatie\MediaLibrary\MediaCollections\Models\Concerns;

use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\PathGenerator\PathGeneratorFactory;

trait MovesOnUpdate
{
    public static function bootPathUuidSafe()
    {
        if (! config('media-library.moves_media_on_update')) {
            return;
        }

        static::updating(function (Media $media) {
            if ($media->uuid !== $media->getOriginal('uuid')) {
                $factory = PathGeneratorFactory::create();

                $oldMedia = (clone $media)->fill(['uuid' => $media->getOriginal('uuid')]);

                Storage::disk($media->disk)->move(
                    $factory->getPath($oldMedia),
                    $factory->getPath($media),
                );
            }
        });
    }

}
