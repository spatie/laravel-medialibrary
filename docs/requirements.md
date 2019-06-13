---
title: Requirements
weight: 3
---

The Medialibrary package requires **PHP 7.1+** and **Laravel 5.5.0+**. 

This package uses `json` columns. MySQL 5.7 or higher is required.

The [exif extension](http://php.net/manual/en/exif.installation.php) is required (on most systems it will be installed by default). 
To create derived images **[GD](http://php.net/manual/en/book.image.php)** needs to be installed on your server. 
If you want to create PDF or SVG thumbnails **[Imagick](http://php.net/manual/en/imagick.setresolution.php)** and **[Ghostscript](https://www.ghostscript.com/)** are also required. 
For the creation of thumbnails of video files `ffmpeg` should be installed on your system.

If you're running into problems with Ghostscript and/or PDF to image generation have a look at [issues regarding Ghostscript](https://github.com/spatie/pdf-to-image/blob/master/README.md#issues-regarding-ghostscript).

## Older versions

We only support the latest version. If you do not meet the requirements, you can opt to use an older version of the package.

Laravel 5.5 and PHP 7.0 users can use [V6 of this package](https://docs.spatie.be/laravel-medialibrary/v6/introduction).

Laravel 5.4 users can use [V5 of this package](https://docs.spatie.be/laravel-medialibrary/v5/introduction).

Using Laravel version 5.1, 5.2 or 5.3? Head over to [V4 of this package](https://docs.spatie.be/laravel-medialibrary/v4/introduction).

If you're stuck on PHP 5 take a look at [V3 of this package](https://docs.spatie.be/laravel-medialibrary/v3/introduction).

We only actively maintain the latest version of the medialibrary.
