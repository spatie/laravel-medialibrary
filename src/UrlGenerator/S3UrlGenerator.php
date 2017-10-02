<?php

namespace Spatie\MediaLibrary\UrlGenerator;

use DateTimeInterface;
use Illuminate\Filesystem\FilesystemManager;
use Illuminate\Contracts\Config\Repository as Config;

class S3UrlGenerator extends BaseUrlGenerator
{
    /** @var \Illuminate\Filesystem\FilesystemManager */
    protected $filesystemManager;

    public function __construct(Config $config, FilesystemManager $filesystemManager)
    {
        $this->filesystemManager = $filesystemManager;

        parent::__construct($config);
    }

    /**
     * Get the url for the profile of a media item.
     *
     * @return string
     */
    public function getUrl(): string
    {
        $url = $this->getPathRelativeToRoot();

        $url = $this->rawUrlEncodeFilename($url);

        return config('medialibrary.s3.domain').'/'.$url;
    }

    /**
     * Get the temporary url for the profile of a media item.
     *
     * @param \DateTimeInterface $expiration
     * @param array $options
     *
     * @return string
     */
    public function getTemporaryUrl(DateTimeInterface $expiration, array $options = []): string
    {
        return $this
            ->filesystemManager
            ->disk($this->media->disk)
            ->temporaryUrl($this->getPath(), $expiration, $options);
    }

    /**
     * Get the url for the profile of a media item.
     *
     * @return string
     */
    public function getPath(): string
    {
        return $this->getPathRelativeToRoot();
    }
}
