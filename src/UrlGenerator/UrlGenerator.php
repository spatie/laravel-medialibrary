<?php

namespace Spatie\MediaLibrary\UrlGenerator;

use Spatie\MediaLibrary\Media;
use Spatie\MediaLibrary\Conversion\Conversion;
use Spatie\MediaLibrary\PathGenerator\PathGenerator;

interface UrlGenerator
{
    /**
     * Get the url for the profile of a media item.
     *
     * @return string
     */
    public function getUrl(): string;

    /**
     * @param \Spatie\MediaLibrary\Media $media
     *
     * @return \Spatie\MediaLibrary\UrlGenerator\UrlGenerator
     */
    public function setMedia(Media $media): self;

    /**
     * @param \Spatie\MediaLibrary\Conversion\Conversion $conversion
     *
     * @return \Spatie\MediaLibrary\UrlGenerator\UrlGenerator
     */
    public function setConversion(Conversion $conversion): self;

    /**
     * Set the path generator class.
     *
     * @param \Spatie\MediaLibrary\PathGenerator\PathGenerator $pathGenerator
     *
     * @return \Spatie\MediaLibrary\UrlGenerator\UrlGenerator
     */
    public function setPathGenerator(PathGenerator $pathGenerator): self;
}
