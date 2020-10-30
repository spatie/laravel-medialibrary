---
title: Requirements
weight: 3
---

Laravel Media Library requires **PHP 7.4+** and **Laravel 7+**. 

This package uses `json` columns. MySQL 5.7 or higher is required.

The [exif extension](http://php.net/manual/en/exif.installation.php) is required (on most systems it will be installed by default). 
To create derived images **[GD](http://php.net/manual/en/book.image.php)** needs to be installed on your server. 
If you want to create PDF or SVG thumbnails **[Imagick](http://php.net/manual/en/imagick.setresolution.php)** and **[Ghostscript](https://www.ghostscript.com/)** are also required. 
For the creation of thumbnails of video files `ffmpeg` should be installed on your system.

If you're running into problems with Ghostscript and/or PDF to image generation have a look at [issues regarding Ghostscript](https://github.com/spatie/pdf-to-image/blob/master/README.md#issues-regarding-ghostscript).

## Older versions

If you do not meet the requirements, you can opt to use an older version of the package.
We only actively maintain the latest version of the media library.
