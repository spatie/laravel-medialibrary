---
title: Introduction
weight: 1
---

This package associates files of any kind with Eloquent models through a fluent API. The [Pro version of the package](https://medialibrary.pro) also offers Blade, Vue, and React components for handling uploads and managing media collections.

Here are a few quick examples:

```php
$yourModel = YourModel::find(1);
$yourModel->addMedia($pathToFile)->toMediaCollection('images');
```

It can also handle uploads straight from a request:

```php
$yourModel->addMediaFromRequest('image')->toMediaCollection('images');
```

Want to store large files on another filesystem? No problem:

```php
$yourModel->addMedia($smallFile)->toMediaCollection('downloads', 'local');
$yourModel->addMedia($bigFile)->toMediaCollection('downloads', 's3');
```

File storage is handled by [Laravel's Filesystem](https://laravel.com/docs/filesystem), so you can plug in any disk you've configured.

The package can also generate derived images such as thumbnails for images, videos, and PDFs. Once you've [prepared your model](/docs/laravel-medialibrary/v11/basic-usage/preparing-your-model), you can fetch them like this:

```php
$yourModel->getMedia('images')->first()->getUrl('thumb');
```

## Are you a visual learner?

We've recorded [a video course](https://spatie.be/courses/discovering-laravel-media-library) on how to use this package. It's the quickest way to get started with Media Library.

[![video course](/docs/laravel-medialibrary/v11/images/video-course.jpg)](https://spatie.be/courses/discovering-laravel-media-library/introducing-laravel-media-library)

## We have badges!

<section class="article_badges">
    <a href="https://github.com/spatie/laravel-medialibrary/releases"><img src="https://img.shields.io/github/release/spatie/laravel-medialibrary.svg?style=flat-square" alt="Latest Version"></a>
    <a href="https://github.com/spatie/laravel-medialibrary/blob/main/LICENSE.md"><img src="https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square" alt="Software License"></a>
    <a href="https://github.com/spatie/laravel-medialibrary/actions/workflows/run-tests.yml"><img src="https://img.shields.io/github/actions/workflow/status/spatie/laravel-medialibrary/run-tests.yml?branch=main&label=tests&style=flat-square" alt="Tests"></a>
    <a href="https://packagist.org/packages/spatie/laravel-medialibrary"><img src="https://img.shields.io/packagist/dt/spatie/laravel-medialibrary.svg?style=flat-square" alt="Total Downloads"></a>
</section>
