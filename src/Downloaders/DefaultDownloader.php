<?php

namespace Spatie\MediaLibrary\Downloaders;

use Spatie\MediaLibrary\MediaCollections\Exceptions\UnreachableUrl;

class DefaultDownloader implements Downloader
{
    public function getTempFile(string $url): string
    {
        $context = stream_context_create([
            'ssl' => [
                'verify_peer' => config('media-library.media_downloader_ssl'),
                'verify_peer_name' => config('media-library.media_downloader_ssl'),
            ],
            'http' => [
                'header' => 'User-Agent: Spatie MediaLibrary',
            ],
        ]);

        if (! $stream = @fopen($url, 'r', false, $context)) {
            throw UnreachableUrl::create($url);
        }

        $temporaryFile = tempnam(sys_get_temp_dir(), 'media-library');

        file_put_contents($temporaryFile, $stream);

        fclose($stream);

        return $temporaryFile;
    }
}
