---
title: Using a custom media downloader
weight: 6
---

When you call `addMediaFromUrl()`, Media Library downloads the file with PHP's `fopen` by default. If the file lives behind a firewall or requires custom headers, you can swap in your own downloader.

Create a class implementing `Spatie\MediaLibrary\Downloaders\Downloader` whose `getTempFile()` method downloads the URL and returns the path to a temporary local file, then register it under `media_downloader` in `config/media-library.php`:

```php
namespace App\Support;

use Spatie\MediaLibrary\Downloaders\Downloader;
use Spatie\MediaLibrary\MediaCollections\Exceptions\UnreachableUrl;

class CustomDownloader implements Downloader
{
    public function getTempFile(string $url): string
    {
        $temporaryFile = tempnam(sys_get_temp_dir(), 'media-library');
        $fh = fopen($temporaryFile, 'w');

        $curl = curl_init($url);
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FAILONERROR => true,
            CURLOPT_FILE => $fh,
            CURLOPT_TIMEOUT => 35,
            CURLOPT_HTTPHEADER => [
                'Content-Type: image/*',
                'User-Agent: MyApp/1.0',
            ],
        ]);

        if (curl_exec($curl) === false) {
            curl_close($curl);
            fclose($fh);
            throw UnreachableUrl::create($url);
        }

        curl_close($curl);
        fclose($fh);

        return $temporaryFile;
    }
}
```

If you only need to disable SSL verification in a local development environment while using `DefaultDownloader`, set `media_downloader_ssl` to `false` in `config/media-library.php` (never disable SSL verification in production).

## Using Laravel's HTTP client downloader

Media Library also ships with `HttpFacadeDownloader`, which uses Laravel's built-in `Http` client. This makes faking external downloads in tests effortless:

```php
// config/media-library.php

'media_downloader' => Spatie\MediaLibrary\Downloaders\HttpFacadeDownloader::class,
```

Now you can test code that calls `addMediaFromUrl()`:

```php
$url = 'https://example.com/images/mountain.jpg';

$yourModel
    ->addMediaFromUrl($url)
    ->toMediaCollection();
```

using `Http::fake()`:

```php
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

Http::fake([
    'https://example.com/images/mountain.jpg' => Http::response('::file::'),
]);

// Run your code...

Http::assertSent(function (Request $request) {
    return $request->url() === 'https://example.com/images/mountain.jpg';
});
```
