<?php

namespace Spatie\MediaLibrary\UrlGenerator;

abstract class BaseUrlGenerator
{
    protected $media;

    protected $profileName;

    protected $filesystemConfig;

    /**
     * @param mixed $media
     * @return $this
     */
    public function setMedia($media)
    {
        $this->media = $media;

        return $this;
    }

    /**
     * @param mixed $profileName
     * @return $this
     */
    public function setProfileName($profileName)
    {
        $this->profileName = $profileName;

        return $this;
    }

    /**
     * @param mixed $filesystemConfig
     */
    public function setFilesystemConfig($filesystemConfig)
    {
        $this->filesystemConfig = $filesystemConfig;
    }


}