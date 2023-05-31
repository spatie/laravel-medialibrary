<?php

namespace Programic\MediaLibrary\Tests\TestSupport\TestPathGenerators;

use Programic\MediaLibrary\MediaCollections\Models\Media;
use Programic\MediaLibrary\Support\PathGenerator\DefaultPathGenerator;
use Programic\MediaLibrary\Support\PathGenerator\PathGenerator;

class TestPathGeneratorConversionsInOriginalImageDirectory extends DefaultPathGenerator implements PathGenerator
{
    public function getPathForConversions(Media $media): string
    {
        return $this->getPath($media);
    }
}
