<?php

namespace Spatie\MediaLibrary\Tests\PathGenerator;

use Spatie\MediaLibrary\Media;
use Spatie\MediaLibrary\PathGenerator\PathGenerator;

class CustomPathGenerator implements PathGenerator
{
    /*
     * Get the path for the given media, relative to the root storage path.
     */
    public function getPath(Media $media) : string
    {
        return md5($media->id).'/';
    }

    /*
     * Get the path for conversions of the given media, relative to the root storage path.

     * @return string
     */
    public function getPathForConversions(Media $media) : string
    {
        return $this->getPath($media).'c/';
    }
}
