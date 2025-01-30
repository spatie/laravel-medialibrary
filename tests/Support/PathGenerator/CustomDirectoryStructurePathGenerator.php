<?php

namespace Programic\MediaLibrary\Tests\Support\PathGenerator;

use Programic\MediaLibrary\MediaCollections\Models\Media;
use Programic\MediaLibrary\Support\PathGenerator\PathGenerator;

class CustomDirectoryStructurePathGenerator implements PathGenerator
{
    public function getPath(Media $media): string
    {
        return 'my-images/';
    }

    public function getPathForConversions(Media $media): string
    {
        return $this->getPath($media).'c/';
    }

    public function getPathForResponsiveImages(Media $media): string
    {
        return $this->getPath($media).'cri/';
    }
}
