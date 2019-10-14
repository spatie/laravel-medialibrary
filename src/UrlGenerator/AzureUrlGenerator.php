<?php

namespace Spatie\MediaLibrary\UrlGenerator;

use DateTimeInterface;
use Illuminate\Filesystem\FilesystemManager;
use Illuminate\Contracts\Config\Repository as Config;

class AzureUrlGenerator extends BaseUrlGenerator
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

        if ($root = config('filesystems.disks.'.$this->media->disk.'.root')) {
            $url = $root.'/'.$url;
        }

        $url = $this->rawUrlEncodeFilename($url);

        $domain = config('filesystems.disks.'.$this->media->disk.'.url');//'https://whatsnum.blob.core.windows.net/whatsnum'; //env('AZURE_STORAGE_URL')

        return $domain.'/'.$url;
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
        $url = $this->pathGenerator->getPathForResponsiveImages($this->media);
        if ($root = config('filesystems.disks.'.$this->media->disk.'.root')) {
            $url = $root.'/'.$url;
        }

        $domain = config('filesystems.disks.'.$this->media->disk.'.url');//'https://name.blob.core.windows.net/container'; //env('AZURE_STORAGE_URL')
        return $domain.'/'.$url;
    }
}
