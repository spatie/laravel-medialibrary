---
title: Using a custom media downloader
weight: 6
---

By default, when using the `addMediaFromUrl` method, the package internally uses `fopen` to download the media. In some cases though, the media can be behind a firewall or you need to attach specific headers to get access.

To do that, you can specify your own Media Downloader by creating a class that implements the `Downloader` interface. This method must fetch the resource and return the location of the temporary file.

For example, consider the following example which uses curl with custom headers to fetch the media.

```php
use Programic\MediaLibrary\Downloaders\Downloader;
use Programic\MediaLibrary\MediaCollections\Exceptions\UnreachableUrl;

class CustomDownloader implements Downloader {

    public function getTempFile($url){
        $temporaryFile = tempnam(sys_get_temp_dir(), 'media-library');
        $fh = fopen($temporaryFile, 'w');

        $curl = curl_init($url);
        $options = [
            CURLOPT_RETURNTRANSFER  => true,
            CURLOPT_FAILONERROR     => true,
            CURLOPT_FILE            => $fh,
            CURLOPT_TIMEOUT         => 35,
        ];
        $headers = [
            'Content-Type: image/*',
            'User-Agent: Mozilla/5.0 (X11; Ubuntu; Linux i686; rv:28.0) Gecko/20100101 Firefox/28.0',
        ];
        curl_setopt_array($curl, $options);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        if (false === curl_exec($curl)) {
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

## Using the Laravel Downloader

You may configure the medialibrary config to use a downloader compatible more
with Laravel that makes use of the built-in HTTP client. This is the quickest way
to mock any requests made to external URLs.

```php
    // config/media-library.php

    /*
     * When using the addMediaFromUrl method you may want to replace the default downloader.
     * This is particularly useful when the url of the image is behind a firewall and
     * need to add additional flags, possibly using curl.
     */
    'media_downloader' => Programic\MediaLibrary\Downloaders\HttpFacadeDownloader::class,
```

This then makes it easier in tests to mock the download of files.

```php
$url = 'http://medialibrary.spatie.be/assets/images/mountain.jpg';
$yourModel
   ->addMediaFromUrl($url)
   ->toMediaCollection();
```

with a test like this:

```php
Http::fake([
    // Stub a response where the body will be the contents of the file
    'http://medialibrary.spatie.be/assets/images/mountain.jpg' => Http::response('::file::'),
]);

// Execute code for the test

// Then check that a request for the file was made
Http::assertSent(function (Request $request) {
    return $request->url() == 'http://medialibrary.spatie.be/assets/images/mountain.jpg';
});

// We may also assert that the contents of any files created
// will contain `::file::`
```
