<?php

namespace Spatie\MediaLibrary\Tests\Support\PathGenerator;

use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\PathGenerator\PathGenerator;

class SeparatedPathGenerator implements PathGenerator
{
    public function getPath(Media $media): string
    {
        return "media/0/{$media->id}/";
    }

    public function getPathForConversions(Media $media): string
    {
        return "media/conversions/{$media->id}/";
    }

    public function getPathForResponsiveImages(Media $media): string
    {
        return "media/responsive-images/{$media->id}/";
    }
}
