<?php

namespace Spatie\MediaLibrary\UrlGenerator;

use Carbon\Carbon;
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
     * Get the url for a media item.
     *
     * @return string
     */
    public function getUrl(): string
    {
        $url = $this->getPathRelativeToRoot();

        $url = $this->rawUrlEncodeFilename($url);

        return $this->getDomainName().'/'.$url;
    }

    /**
     * Get the temporary url for a media item.
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

    /**
     * Get the url to the directory containing responsive images.
     * 
     * @return string
     */
    public function getResponsiveImagesDirectoryUrl(): string
    {
        return $this->getDomainName().'/'. $this->pathGenerator->getPathForResponsiveImages($this->media);
    }

    /**
     * Get the domain name based on the created at time of the file
     *
     * @return string
     */
    protected function getDomainName(): string
    {
        /* @var Carbon $createdAt */
        $createdAt = $this->media->created_at;
        $domain = config('medialibrary.s3.domain');
        $cdnDomain = config('medialibrary.s3.cdn_domain');
        $cdnDomainAfter = (int) config('medialibrary.s3.cdn_domain_after', 30);

        if (! empty($cdnDomain) && $createdAt->copy()->addMinutes($cdnDomainAfter)->isPast()) {
            $domain = $cdnDomain;
        }

        return $domain;
    }
}
