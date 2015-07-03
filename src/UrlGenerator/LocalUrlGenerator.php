<?php

namespace Spatie\MediaLibrary\UrlGenerator;

use Spatie\MediaLibrary\Exceptions\UrlCouldNotBeDeterminedException;

class LocalUrlGenerator extends BaseUrlGenerator implements UrlGenerator
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
        if (!string($this->getStoragePath())->startsWith(public_path())) {
            throw new UrlCouldNotBeDeterminedException('The storage path is not part of the public path');
        }

        return $this->getBaseMediaDirectory().'/'.$this->getPathRelativeToRoot();
    }

    /**
     * Get the directory where all files of the media item are stored.
     *
     * @return string
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
        $diskRootPath = $this->config->get('filesystems.disks.'.$this->config->get('laravel-medialibrary.filesystem').'.root');

        return realpath($diskRootPath);
    }
}
