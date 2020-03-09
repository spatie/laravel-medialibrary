---
title: Introduction
weight: 1
---

This package can associate all sorts of files with Eloquent models. It provides a simple, fluent API to work with.

Here are some quick code examples:

```php
$yourModel = YourModel::find(1);
$yourModel->addMedia($pathToFile)->toMediaCollection('images');
```

It can also directly handle your uploads:

```php
$yourModel->addMediaFromRequest('image')->toMediaCollection('images');
```

Want to store some large files on another filesystem? No problem:

```php
$yourModel->addMedia($smallFile)->toMediaCollection('downloads', 'local');
$yourModel->addMedia($bigFile)->toMediaCollection('downloads', 's3');
```

The storage of the files is handled by [Laravel's Filesystem](http://laravel.com/docs/5.6/filesystem), so you can plug in any compatible filesystem.

The package can also generate derived images such as thumbnails for images, video's and pdf's. Once you've [set up your model](/laravel-medialibrary/v8/basic-usage/preparing-your-model), they're easily accessible:

```php
$yourModel->getMedia('images')->first()->getUrl('thumb');
```

## We have badges!

<section class="article_badges">
    <a href="https://github.com/spatie/laravel-medialibrary/releases"><img src="https://img.shields.io/github/release/spatie/laravel-medialibrary.svg?style=flat-square" alt="Latest Version"></a>
    <a href="https://github.com/spatie/laravel-medialibrary/blob/master/LICENSE.md"><img src="https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square" alt="Software License"></a>
    <a href="https://travis-ci.org/spatie/laravel-medialibrary"><img src="https://img.shields.io/travis/spatie/laravel-medialibrary/master.svg?style=flat-square" alt="Build Status"></a>
    <a href="https://scrutinizer-ci.com/g/spatie/laravel-medialibrary"><img src="https://img.shields.io/scrutinizer/g/spatie/laravel-medialibrary.svg?style=flat-square" alt="Quality Score"></a>
    <a href="https://packagist.org/packages/spatie/laravel-medialibrary"><img src="https://img.shields.io/packagist/dt/spatie/laravel-medialibrary.svg?style=flat-square" alt="Total Downloads"></a>
</section>
