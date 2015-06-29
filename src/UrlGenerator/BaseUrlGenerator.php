<?php

namespace Spatie\MediaLibrary\UrlGenerator;

abstract class BaseUrlGenerator
{
    protected $media;

    protected $profileName;

    /**
     * @var \Illuminate\Contracts\Config\Repository
     */
    protected $config;


    public function __construct(\Illuminate\Contracts\Config\Repository $config)
    {
        $this->config = $config;
    }

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
}