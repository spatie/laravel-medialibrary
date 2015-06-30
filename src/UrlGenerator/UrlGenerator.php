<?php

namespace Spatie\MediaLibrary\UrlGenerator;

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
}
