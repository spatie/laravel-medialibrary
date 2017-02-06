<?php

namespace Spatie\MediaLibrary\Test\S3Integration;

use Spatie\MediaLibrary\Media;
use Spatie\MediaLibrary\PathGenerator\PathGenerator;
use Spatie\MediaLibrary\Test\FileAdder\S3IntegrationTest;

class S3TestPathGenerator implements PathGenerator
{
    /*
     * Get the path for the given media, relative to the root storage path.
     */
    public function getPath(Media $media) : string
    {
        return $this->getBasePath($media).'/';
    }

    /*
     * Get the path for conversions of the given media, relative to the root storage path.
     */
    public function getPathForConversions(Media $media) : string
    {
        return $this->getBasePath($media).'/conversions/';
    }

    /*
     * Get a (unique) base path for the given media.
     */
    protected function getBasePath(Media $media) : string
    {
        return (S3IntegrationTest::getS3BaseTestDirectory()).'/'.$media->getKey();
    }
}
