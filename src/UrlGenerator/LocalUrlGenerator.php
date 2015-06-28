<?php

namespace Spatie\MediaLibrary\UrlGenerator;


class LocalUrlGenerator extends BaseUrlGenerator implements UrlGeneratorInterface
{
    public function getUrl()
    {
        $baseDirectory = '/media/' . $this->media->id;

        if ($this->profileName == '')
        {
            return $baseDirectory . '/' . $this->media->file;
        }
        
        return  '/media/' . $this->media->id . '/' . $this->profileName . '.jpg';
    }
}