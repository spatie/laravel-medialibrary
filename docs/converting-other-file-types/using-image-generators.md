---
title: Using image generators
weight: 1
---

Media Library converts standard images out of the box. For other file types like PDFs, SVGs, and videos, it uses **image generators** to extract a base image first, then runs your normal conversions on it.

You define conversions for these file types the exact same way as for regular images:

```php
$this->addMediaConversion('thumb')
    ->width(368)
    ->height(232)
    ->performOnCollections('videos');
```

The package ships with image generators for:

- [PDF](/docs/laravel-medialibrary/v11/converting-other-file-types/using-image-generators#pdf)
- [SVG](/docs/laravel-medialibrary/v11/converting-other-file-types/using-image-generators#svg)
- [Video](/docs/laravel-medialibrary/v11/converting-other-file-types/using-image-generators#video)
- WebP and AVIF (enabled automatically when your PHP/GD or Imagick build supports them)

## PDF

The PDF generator requires [Imagick](https://www.php.net/manual/en/imagick.setresolution.php), [Ghostscript](https://www.ghostscript.com/), and [spatie/pdf-to-image](https://github.com/spatie/pdf-to-image). If you hit Ghostscript errors, check [issues regarding Ghostscript](https://github.com/spatie/pdf-to-image/blob/main/README.md#issues-regarding-ghostscript).

By default, Media Library generates the thumbnail from the first page. Use `pdfPageNumber()` on your conversion to pick a different page:

```php
$this->addMediaConversion('thumb')
    ->width(368)
    ->height(232)
    ->pdfPageNumber(2);
```

## SVG

Converting SVG files only requires [Imagick](https://www.php.net/manual/en/imagick.setresolution.php).

Be careful if you let users upload SVGs. Because SVGs are XML, Imagick may try to load external references inside them depending on your server's ImageMagick configuration. If you accept untrusted SVGs, harden your ImageMagick `policy.xml` (for example, by disabling the `SVG`, `URL`, and `HTTPS` coders), keep ImageMagick and its delegates up to date, or sanitize/reject SVGs before storing them. See the [ImageMagick security policy documentation](https://imagemagick.org/script/security-policy.php) for details.

## Video

The video generator uses [PHP-FFMpeg](https://github.com/PHP-FFMpeg/PHP-FFMpeg):

```bash
composer require php-ffmpeg/php-ffmpeg
```

You also need `ffmpeg` and `ffprobe` installed on your server (see [FFmpeg's download page](https://ffmpeg.org/download.html)). You can configure binary paths, timeouts, and thread limits via `ffmpeg_path`, `ffprobe_path`, `ffmpeg_timeout`, and `ffmpeg_threads` in `config/media-library.php`.

Use `extractVideoFrameAtSecond()` to pick which timestamp to capture (defaults to `0`, supports floats):

```php
$this->addMediaConversion('thumb')
    ->width(368)
    ->height(232)
    ->extractVideoFrameAtSecond(20)
    ->performOnCollections('videos');
```

Once generated, you can use the thumbnail as a `poster` on a `<video>` tag:

```html
<video controls poster="{{ $video->getUrl('thumb') }}">
    <source src="{{ $video->getUrl() }}" type="video/mp4">
    Your browser does not support the video tag.
</video>
```
