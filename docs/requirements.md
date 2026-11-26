---
title: Requirements
weight: 5
---

Laravel Media Library requires **PHP 8.2+** and **Laravel 12+**, along with the `exif`, `fileinfo`, and `json` PHP extensions (most PHP setups already include these).

To create image conversions, you need at least one supported image driver installed on your server: **[GD](https://www.php.net/manual/en/book.image.php)** (default), **[Imagick](https://www.php.net/manual/en/book.imagick.php)**, or **vips** (configured via `image_driver` in `config/media-library.php`).

Extra dependencies for non-image formats:

- **PDF thumbnails**: [Imagick](https://www.php.net/manual/en/imagick.setresolution.php), [Ghostscript](https://www.ghostscript.com/), and `spatie/pdf-to-image`
- **SVG thumbnails**: [Imagick](https://www.php.net/manual/en/imagick.setresolution.php)
- **Video thumbnails**: `ffmpeg` and `php-ffmpeg/php-ffmpeg`

If you run into trouble with Ghostscript when converting PDFs, check [issues regarding Ghostscript](https://github.com/spatie/pdf-to-image/blob/main/README.md#issues-regarding-ghostscript).

## Older versions

If your project doesn't meet these requirements, you can use an older version of the package. We only actively maintain the latest major version of Media Library.
