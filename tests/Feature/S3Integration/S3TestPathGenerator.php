<?php

namespace Spatie\Medialibrary\Tests\Feature\S3Integration;

use Spatie\Medialibrary\MediaCollections\Models\Media;
use Spatie\Medialibrary\Support\PathGenerator\PathGenerator;

class S3TestPathGenerator implements PathGenerator
{
    /*
     * Get the path for the given media, relative to the root storage path.
     */
    public function getPath(Media $media): string
    {
        return $this->getBasePath($media).'/';
    }

    /*
     * Get the path for conversions of the given media, relative to the root storage path.
     */
    public function getPathForConversions(Media $media): string
    {
        return $this->getBasePath($media).'/conversions/';
    }

    public function getPathForResponsiveImages(Media $media): string
    {
        return $this->getBasePath($media).'/responsive-images/';
    }

    /*
     * Get a (unique) base path for the given media.
     */
    protected function getBasePath(Media $media): string
    {
        return (S3IntegrationTest::getS3BaseTestDirectory()).'/'.$media->getKey();
    }
}
