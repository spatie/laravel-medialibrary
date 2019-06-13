---
title: Requirements
weight: 3
---

The Medialibrary package requires **PHP 5.5.9+** and **Laravel 5.1.0+**. To create derived images **[GD](http://php.net/manual/en/book.image.php)** needs to be installed on your server. If you want to create PDF thumbnails **[Imagick](http://php.net/manual/en/imagick.setresolution.php)** is also required.

GD and Imagick can be installed using `apt-get` on Ubuntu and Debian:

```bash
$ apt-get install php5-gd imagemagick php5-imagick
```

Or with `yum` on CentOS:

```bash
$ yum install php55-gd ImageMagick ImageMagick-devel
```

Note: Root access to your server is probably required.
