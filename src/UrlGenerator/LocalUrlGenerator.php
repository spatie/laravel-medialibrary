<?php

namespace Spatie\MediaLibrary\UrlGenerator;

use Spatie\MediaLibrary\Exceptions\UrlCouldNotBeDeterminedException;

class LocalUrlGenerator extends BaseUrlGenerator implements UrlGenerator
{
    public function getUrl()
    {

        //nog disk path voor storage path zetten

        if (! string($this->getStoragePath())->startsWith(public_path())) {
            throw new UrlCouldNotBeDeterminedException('The storage path is not part of the public path');
        }

        if ($this->profileName == '') {
            return $this->getBaseMediaDirectory() . '/' . $this->media->file_name;
        }

        return $this->getBaseMediaDirectory() . '/conversions/' . $this->profileName . '.jpg';
    }

    /**
     * @return string
     */
    protected function getBaseMediaDirectory()
    {
        $baseDirectory = string($this->getStoragePath())->replace(public_path(), '') . '/' . $this->media->id;

        return $baseDirectory;
    }

    protected function getStoragePath()
    {
        $diskRootPath = $this->config->get('filesystems.disks.' . $this->config->get('laravel-medialibrary.filesystem') . '.root');

        $configuredPath = $this->config->get('laravel-medialibrary.storage_path');

        return realpath($diskRootPath . '/' . $configuredPath);
    }
}