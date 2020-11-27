---
title: Using image generators
weight: 1
---

The media library has built-in support to convert images. To generate conversions of other media types – most notably PDFs and videos – the medialibrary uses image generators to create a derived image file of the media. 

Conversion of specific file type are defined in the exact same way as images:

```php
$this->addMediaConversion('thumb')
     ->width(368)
     ->height(232)
     ->performOnCollections('videos');
```

The media library includes image generators for the following file types:

- [PDF](/laravel-medialibrary/v8/converting-other-file-types/using-image-generators#pdf)
- [SVG](/laravel-medialibrary/v8/converting-other-file-types/using-image-generators#svg)
- [Video](/laravel-medialibrary/v8/converting-other-file-types/using-image-generators#video)

## PDF

The PDF generator requires [Imagick](http://php.net/manual/en/imagick.setresolution.php), [Ghostscript](https://www.ghostscript.com/), and [Spatie Pdf to Image](https://github.com/spatie/pdf-to-image). If you're running into issues with Ghostscript have a look at [issues regarding Ghostscript](https://github.com/spatie/pdf-to-image/blob/master/README.md#issues-regarding-ghostscript).

The pdf image generator allows you to choose at which page of the pdf, the thumbnail should be created using the `pdfPageNumber` on the conversion.

If the `pdfPageNumber` is not set on the conversion, the default value will be the first page of the pdf.

```php
$this->addMediaConversion('thumb')
     ->width(368)
     ->height(232)
     ->pdfPageNumber(2);
```

## SVG

The only requirement to perform a conversion of a SVG file is [Imagick](http://php.net/manual/en/imagick.setresolution.php).

## Video

The video image generator uses the [PHP-FFMpeg](https://github.com/PHP-FFMpeg/PHP-FFMpeg) package that you can install via composer:

```bash
composer require php-ffmpeg/php-ffmpeg
```

You'll also need to follow `FFmpeg` installation instructions on their [official website](https://ffmpeg.org/download.html).

The video image generator allows you to choose at which time of the video the derived file should be created using the `setExtractVideoFrameAtSecond` on the conversion.

```php
$this->addMediaConversion('thumb')
     ->width(368)
     ->height(232)
     ->extractVideoFrameAtSecond(20)
     ->performOnCollections('videos');
```

Once the conversion is created you can easily use the thumbnail in a html `video` tag for example:

```html
<video controls poster="{{ $video->getUrl('thumb') }}">
  <source src="{{ $video->getUrl() }}" type="video/mp4">
  Your browser does not support the video tag.
</video>
```
