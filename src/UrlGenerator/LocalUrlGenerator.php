<?php

namespace Spatie\MediaLibrary\UrlGenerator;

use Spatie\MediaLibrary\Exceptions\UrlCouldNotBeDeterminedException;

class LocalUrlGenerator extends BaseUrlGenerator implements UrlGeneratorInterface
{
    public function getUrl()
    {
        if (!string($this->config->get('laravel-medialibrary.storage_path'))->startsWith(public_path())) {
            throw new UrlCouldNotBeDeterminedException('The storage path is not part of the public path');
        }

        if ($this->profileName == '') {
            return $this->getBaseDirectory() . '/' . $this->media->file;
        }

        return $this->getBaseDirectory() . '/' . $this->profileName . '.jpg';
    }

    /**
     * @return string
     */
    protected function getBaseDirectory()
    {
        $baseDirectory = string($this->config->get('laravel-medialibrary.storage_path'))->replace(public_path(), '') . '/media/' . $this->media->id;
        return $baseDirectory;
    }
}