<?php

namespace Spatie\MediaLibrary\UrlGenerator;

use Spatie\MediaLibrary\Exceptions\UrlCannotBeDetermined;
use Spatie\String\Str;

class LocalUrlGenerator extends BaseUrlGenerator implements UrlGenerator
{
    /**
     * Get the url for the profile of a media item.
     *
     * @return string
     *
     * @throws \Spatie\MediaLibrary\Exceptions\UrlCannotBeDetermined
     */
    public function getUrl() : string
    {
        if (!string($this->getStoragePath())->startsWith(public_path())) {
            throw UrlCannotBeDetermined::mediaNotPubliclyAvailable($this->getStoragePath(), public_path());
        }

        $url = $this->getBaseMediaDirectory().'/'.$this->getPathRelativeToRoot();

        return $this->makeCompatibleForNonUnixHosts($url);
    }

    /**
     * Get the full url for the profile of a media item.
     *
     * @return string
     *
     * @throws \Spatie\MediaLibrary\Exceptions\UrlCannotBeDetermined
     */
    public function getFullUrl() : string
    {
        return url($this->getUrl());
    }

    /*
     * Get the path for the profile of a media item.
     */
    public function getPath() : string
    {
        return $this->getStoragePath().'/'.$this->getPathRelativeToRoot();
    }

    /*
     * Get the directory where all files of the media item are stored.
     */
    protected function getBaseMediaDirectory() : Str
    {
        $baseDirectory = string($this->getStoragePath())->replace(public_path(), '');

        return $baseDirectory;
    }

    /*
     * Get the path where the whole medialibrary is stored.
     */
    protected function getStoragePath() : string
    {
        $diskRootPath = $this->config->get('filesystems.disks.'.$this->media->disk.'.root');

        return realpath($diskRootPath);
    }

    protected function makeCompatibleForNonUnixHosts(string $url) : string
    {
        if (DIRECTORY_SEPARATOR != '/') {
            $url = str_replace(DIRECTORY_SEPARATOR, '/', $url);
        }

        return $url;
    }
}
