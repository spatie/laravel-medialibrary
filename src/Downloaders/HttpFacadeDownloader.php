<?php

namespace Programic\MediaLibrary\Downloaders;

use Illuminate\Support\Facades\Http;
use Programic\MediaLibrary\MediaCollections\Exceptions\UnreachableUrl;

class HttpFacadeDownloader implements Downloader
{
    public function getTempFile(string $url): string
    {
        $temporaryFile = tempnam(sys_get_temp_dir(), 'media-library');

        Http::withUserAgent('Spatie MediaLibrary')
            ->throw(fn () => throw new UnreachableUrl($url))
            ->sink($temporaryFile)
            ->get($url);

        return $temporaryFile;
    }
}
