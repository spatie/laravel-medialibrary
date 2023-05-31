<?php

namespace Programic\MediaLibrary\Tests\TestSupport;

use Programic\MediaLibrary\MediaCollections\Models\Media;

class TestUuidPathGenerator extends TestPathGenerator
{
    public function getPath(Media $media): string
    {
        return "{$media->uuid}/";
    }
}
