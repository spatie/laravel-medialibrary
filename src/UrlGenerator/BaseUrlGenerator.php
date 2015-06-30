<?php

namespace Spatie\MediaLibrary\UrlGenerator;

abstract class BaseUrlGenerator
{
    /**
     * @var \Spatie\MediaLibrary\Media
     */
    protected $media;

    /**
     * @var string
     */
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
     * @param \Spatie\MediaLibrary\Media $media
     *
     * @return $this
     */
    public function setMedia($media)
    {
        $this->media = $media;

        return $this;
    }

    /**
     * @param string $profileName
     *
     * @return $this
     */
    public function setProfileName($profileName)
    {
        $this->profileName = $profileName;

        return $this;
    }
}
