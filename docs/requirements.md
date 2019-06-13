---
title: Requirements
weight: 3
---

The Medialibrary package requires **PHP 7.0+** and **Laravel 5.4.0+**. 

This package uses `json` columns. MySQL 5.7 or higher is required.

To create derived images **[GD](http://php.net/manual/en/book.image.php)** needs to be installed on your server. If you want to create PDF or SVG thumbnails **[Imagick](http://php.net/manual/en/imagick.setresolution.php)** is also required. For the creation of thumbnails of video files `ffmpeg` should be installed on your system.

Using Laravel version 5.1, 5.2 or 5.3? Head over to [V4 of this package](https://docs.spatie.be/laravel-medialibrary/v4/introduction).

If you're stuck on PHP 5 take a look at [V3 of this package](https://docs.spatie.be/laravel-medialibrary/v3/introduction).
