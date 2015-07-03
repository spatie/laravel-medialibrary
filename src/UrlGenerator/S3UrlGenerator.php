<?php

namespace Spatie\MediaLibrary\UrlGenerator;

use Spatie\MediaLibrary\Exceptions\UrlCouldNotBeDeterminedException;

class S3UrlGenerator extends BaseUrlGenerator implements UrlGenerator
{
    /**
     * Get the url for the profile of a media item.
     *
     * @return string
     *
     * @throws UrlCouldNotBeDeterminedException
     */
    public function getUrl()
    {
        return config('laravel-medialibrary.s3.domain').'/'.$this->getPathRelativeToRoot();
    }
}
