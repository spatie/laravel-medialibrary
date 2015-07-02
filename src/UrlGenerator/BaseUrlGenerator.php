<?php

namespace Spatie\MediaLibrary\UrlGenerator;

use Spatie\MediaLibrary\Conversion\Conversion;

abstract class BaseUrlGenerator
{
    /**
     * @var \Spatie\MediaLibrary\Media
     */
    protected $media;

    /**
     * @var Conversion
     */
    protected $conversion;

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
     * @param Conversion|null $conversion
     *
     * @return $this
     */
    public function setConversion(Conversion $conversion)
    {
        $this->conversion = $conversion;

        return $this;
    }
}
