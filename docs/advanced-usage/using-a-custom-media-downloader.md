---
title: Using a custom media downloader
weight: 6
---

By default, when using the `addMediaFromUrl` method, the package internally uses `fopen` to download the media. In some cases though, the media can be behind a firewall or you need to attach specific headers to get access.

To do that, you can specify your own Media Downloader. Simply create a class that included a `getTempFile` method.

For example, you may want to use curl as a way to download media.

```php
use Spatie\MediaLibrary\MediaCollections\Exceptions\UnreachableUrl;

class CustomDownloader {

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
