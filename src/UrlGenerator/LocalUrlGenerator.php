<?php

namespace Spatie\MediaLibrary\UrlGenerator;

use Spatie\MediaLibrary\Exceptions\UrlCouldNotBeDetermined;

class LocalUrlGenerator extends BaseUrlGenerator implements UrlGenerator
{
    /**
     * Get the url for the profile of a media item.
     *
     * @return string
     *
     * @throws \Spatie\MediaLibrary\Exceptions\UrlCouldNotBeDetermined
     */
    public function getUrl()
    {
        if (!string($this->getStoragePath())->startsWith(public_path())) {
            throw new UrlCouldNotBeDetermined('The storage path is not part of the public path');
        }

        $url = $this->getBaseMediaDirectory().'/'.$this->getPathRelativeToRoot();

        return $this->makeCompatibleForNonUnixHosts($url);
    }

    /**
     * Get the path for the profile of a media item.
     *
     * @return string
     */
    public function getPath()
    {
        return $this->getStoragePath().'/'.$this->getPathRelativeToRoot();
    }

    /**
     * Get the directory where all files of the media item are stored.
     *
     * @return \Spatie\String\Str
     */
    protected function getBaseMediaDirectory()
    {
        $baseDirectory = string($this->getStoragePath())->replace(public_path(), '');

        return $baseDirectory;
    }

    /**
     * Get the path where the whole medialibrary is stored.
     *
     * @return string
     */
    protected function getStoragePath()
    {
        $diskRootPath = $this->config->get('filesystems.disks.'.$this->media->disk.'.root');

        return realpath($diskRootPath);
    }

    /**
     * @param string $url
     *
     * @return string
     */
    protected function makeCompatibleForNonUnixHosts($url)
    {
        if (DIRECTORY_SEPARATOR != '/') {
            $url = str_replace(DIRECTORY_SEPARATOR, '/', $url);
        }

        return $url;
    }
}
