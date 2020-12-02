<?php

namespace Spatie\MediaLibrary\Tests\TestSupport;

use Spatie\MediaLibrary\MediaCollections\Models\Media;

class TestUuidPathGenerator extends TestPathGenerator
{
    public function getPath(Media $media): string
    {
        return "{$media->uuid}/";
    }
}
