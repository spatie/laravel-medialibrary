<?php

namespace Spatie\MediaLibrary\UrlGenerator;

use Illuminate\Contracts\Config\Repository as Config;
use Spatie\MediaLibrary\Conversion\Conversion;
use Spatie\MediaLibrary\PathGenerator\PathGenerator;

abstract class BaseUrlGenerator
{
    /**
     * @var \Spatie\MediaLibrary\Media
     */
    protected $media;

    /**
     * @var \Spatie\MediaLibrary\Conversion\Conversion
     */
    protected $conversion;

    /**
     * @var \Spatie\MediaLibrary\PathGenerator\PathGenerator
     */
    protected $pathGenerator;

    /**
     * @var \Illuminate\Contracts\Config\Repository
     */
    protected $config;

    /**
     * @param \Illuminate\Contracts\Config\Repository $config
     */
    public function __construct(Config $config)
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
     * @param \Spatie\MediaLibrary\Conversion\Conversion $conversion
     *
     * @return $this
     */
    public function setConversion(Conversion $conversion)
    {
        $this->conversion = $conversion;

        return $this;
    }

    /**
     * @param \Spatie\MediaLibrary\PathGenerator\PathGenerator $pathGenerator
     *
     * @return $this
     */
    public function setPathGenerator(PathGenerator $pathGenerator)
    {
        $this->pathGenerator = $pathGenerator;

        return $this;
    }

    /**
     * Get the path to the requested file relative to the root of the media directory.
     *
     * @return string
     */
    public function getPathRelativeToRoot()
    {
        if (is_null($this->conversion)) {
            return $this->pathGenerator->getPath($this->media).$this->media->file_name;
        }

        return $this->pathGenerator->getPathForConversions($this->media).
            $this->conversion->getName().'.'.$this->conversion->getResultExtension($this->media->extension);
    }
}
