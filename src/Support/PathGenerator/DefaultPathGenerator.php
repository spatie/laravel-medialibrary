<?php

namespace Spatie\MediaLibrary\Support\PathGenerator;

use Spatie\MediaLibrary\MediaCollections\Models\Media;

class DefaultPathGenerator implements PathGenerator
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

    /*
     * Get the path for responsive images of the given media, relative to the root storage path.
     */
    public function getPathForResponsiveImages(Media $media): string
    {
        return $this->getBasePath($media).'/responsive-images/';
    }

    /*
     * Get a unique base path for the given media.
     */
    protected function getBasePath(Media $media): string
    {
        // First we will check if our model  has its own
        // method for retrieving its media. Because
        // the developer's time is precious, and
        // this may save him from having to
        // implement the interface in
        // most basic use cases!
        if (method_exists($media->model, 'getMediaBasePath')) {
            return $media->model->getMediaBasePath($media);
        }

        return $media->getKey();
    }
}
