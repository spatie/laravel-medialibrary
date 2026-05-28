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

- [PDF](/docs/laravel-medialibrary/v11/converting-other-file-types/using-image-generators#pdf)
- [SVG](/docs/laravel-medialibrary/v11/converting-other-file-types/using-image-generators#svg)
- [Video](/docs/laravel-medialibrary/v11/converting-other-file-types/using-image-generators#video)

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

**Security note.** SVG files are XML documents. When Imagick renders an SVG it can, depending on your ImageMagick build and delegate configuration, resolve external entities or remote references contained in the file. If you accept SVG uploads from untrusted users, harden your ImageMagick `policy.xml` (for example, by disabling the `SVG`, `URL`, and `HTTPS` coders), keep ImageMagick and its delegates up to date, and consider sanitizing or rejecting SVG uploads at the application layer. See the [ImageMagick security policy documentation](https://imagemagick.org/script/security-policy.php) for details.

## Video

The video image generator uses the [PHP-FFMpeg](https://github.com/PHP-FFMpeg/PHP-FFMpeg) package that you can install via Composer:

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
