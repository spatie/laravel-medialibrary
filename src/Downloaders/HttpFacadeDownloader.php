<?php

namespace Spatie\MediaLibrary\Downloaders;

use Illuminate\Support\Facades\Http;
use Spatie\MediaLibrary\MediaCollections\Exceptions\UnreachableUrl;

class HttpFacadeDownloader implements Downloader
{
    public function getTempFile(string $url): string
    {
        $temporaryFile = tempnam(sys_get_temp_dir(), 'media-library');

        $http = Http::withUserAgent('Spatie MediaLibrary')
            ->throw(fn() => throw new UnreachableUrl($url))
            ->sink($temporaryFile);

        if (! config('media-library.media_downloader_ssl', true)) {
            $http->withoutVerifying();
        }

        $http->get($url);

        return $temporaryFile;
    }
}
