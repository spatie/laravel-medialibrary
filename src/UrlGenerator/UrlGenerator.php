<?php

namespace Spatie\MediaLibrary\UrlGenerator;

use Spatie\MediaLibrary\PathGenerator\PathGenerator;

interface UrlGenerator
{
    /**
     * Get the url for the profile of a media item.
     *
     * @return string
     *
     * @throws \Spatie\MediaLibrary\Exceptions\UrlCouldNotBeDetermined
     */
    public function getUrl();

    /**
     * Get the full url for the profile of a media item.
     *
     * @return string
     *
     * @throws \Spatie\MediaLibrary\Exceptions\UrlCouldNotBeDetermined
     */
    public function getFullUrl();

    /**
     * Set the path generator class.
     *
     * @param \Spatie\MediaLibrary\PathGenerator\PathGenerator $pathGenerator
     *
     * @return mixed
     */
    public function setPathGenerator(PathGenerator $pathGenerator);
}
