<?php

namespace Spatie\MediaLibrary\PathGenerator;

use Spatie\MediaLibrary\Media;

class BasePathGenerator implements PathGenerator
{
    /**
     * Get the path for the given media, relative to the root storage path.
     *
     * @param Media $media
     *
     * @return string
     */
    public function getPath(Media $media)
    {
        return $this->getBasePath($media).'/';
    }

    /**
     * Get the path for conversions of the given media, relative to the root storage path.
     *
     * @param \Spatie\MediaLibrary\Media $media
     *
     * @return string
     */
    public function getPathForConversions(Media $media)
    {
        return $this->getBasePath($media).'/conversions/';
    }

    /**
     * Get a (unique) base path for the given media.
     *
     * @param \Spatie\MediaLibrary\Media $media
     *
     * @return string
     */
    protected function getBasePath(Media $media)
    {
        return $media->getKey();
    }
}
