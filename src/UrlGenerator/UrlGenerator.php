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
     * @throws UrlCouldNotBeDeterminedException
     */
    public function getUrl();

    /**
     * Set the path generator class.
     *
     * @param \Spatie\MediaLibrary\PathGenerator\PathGenerator $pathGenerator
     *
     * @return mixed
     */
    public function setPathGenerator(PathGenerator $pathGenerator);
}
