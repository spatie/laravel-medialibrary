<?php

namespace Spatie\MediaLibrary\Tests\TestSupport\TestPathGenerators;

use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\PathGenerator\DefaultPathGenerator;
use Spatie\MediaLibrary\Support\PathGenerator\PathGenerator;

class TestPathGeneratorConversionsInOriginalImageDirectory extends DefaultPathGenerator implements PathGenerator
{
    public function getPathForConversions(Media $media): string
    {
        return $this->getPath($media);
    }
}
