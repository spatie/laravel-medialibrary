<?php

namespace Spatie\MediaLibrary\UrlGenerator;

abstract class BaseUrlGenerator
{
    protected $media;

    /**
     * @param mixed $media
     * @return $this
     */
    public function setMedia($media)
    {
        $this->media = $media;

        return $this;
    }
}