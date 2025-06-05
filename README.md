<div align="left">
    <a href="https://spatie.be/open-source?utm_source=github&utm_medium=banner&utm_campaign=laravel-medialibrary">
      <picture>
        <source media="(prefers-color-scheme: dark)" srcset="https://spatie.be/packages/header/laravel-medialibrary/html/dark.webp">
        <img alt="Logo for laravel-medialibrary" src="https://spatie.be/packages/header/laravel-medialibrary/html/light.webp">
      </picture>
    </a>

<h1>Associate files with Eloquent models</h1>

[![Latest Version](https://img.shields.io/github/release/spatie/laravel-medialibrary.svg?style=flat-square)](https://github.com/spatie/laravel-medialibrary/releases)
[![run-tests](https://github.com/spatie/laravel-medialibrary/actions/workflows/run-tests.yml/badge.svg)](https://github.com/spatie/laravel-medialibrary/actions/workflows/run-tests.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/spatie/laravel-medialibrary.svg?style=flat-square)](https://packagist.org/packages/spatie/laravel-medialibrary)
    
</div>

This package can associate all sorts of files with Eloquent models. It provides a
simple API to work with. To learn all about it, head over to [the extensive documentation](https://spatie.be/docs/laravel-medialibrary).

Here are a few short examples of what you can do:

```php
$newsItem = News::find(1);
$newsItem->addMedia($pathToFile)->toMediaCollection('images');
```

It can handle your uploads directly:

```php
$newsItem->addMedia($request->file('image'))->toMediaCollection('images');
```

Want to store some large files on another filesystem? No problem:

```php
$newsItem->addMedia($smallFile)->toMediaCollection('downloads', 'local');
$newsItem->addMedia($bigFile)->toMediaCollection('downloads', 's3');
```

The storage of the files is handled by [Laravel's Filesystem](https://laravel.com/docs/filesystem),
so you can use any filesystem you like. Additionally, the package can create image manipulations
on images and pdfs that have been added in the media library.

Spatie is a webdesign agency in Antwerp, Belgium. You'll find an overview of all our open source projects [on our website](https://spatie.be/opensource).

## Support us

[<img src="https://github-ads.s3.eu-central-1.amazonaws.com/laravel-medialibrary.jpg?t=2" width="419px" />](https://spatie.be/github-ad-click/laravel-medialibrary)

We invest a lot of resources into creating [best in class open source packages](https://spatie.be/open-source). You can support us by [buying one of our paid products](https://spatie.be/open-source/support-us).

We highly appreciate you sending us a postcard from your hometown, mentioning which of our package(s) you are using. You'll find our address on [our contact page](https://spatie.be/about-us). We publish all received postcards on [our virtual postcard wall](https://spatie.be/open-source/postcards).

## Documentation

You'll find the documentation on [https://spatie.be/docs/laravel-medialibrary](https://spatie.be/docs/laravel-medialibrary/v11).

Find yourself stuck using the package? Found a bug? Do you have general questions or suggestions for improving the media library? Feel free to [create an issue on GitHub](https://github.com/spatie/laravel-medialibrary/issues), we'll try to address it as soon as possible.

If you've found a bug regarding security please mail [security@spatie.be](mailto:security@spatie.be) instead of using the issue tracker.

## Testing

You can run the tests with:

```bash
./vendor/bin/pest
```

You can run the Github actions locally with [act](https://github.com/nektos/act). To run the tests locally, run:

```
act -j run-tests
```

To run tests for a specific PHP/Laravel version, run:

```
act -j run-tests --matrix php:8.3 --matrix laravel:"11.*" --matrix dependency-version:prefer-stable 
```

Available `matrix` options are available in the [workflow file](.github/workflows/run-tests.yml).

## Upgrading

Please see [UPGRADING](UPGRADING.md) for details.

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](https://github.com/spatie/.github/blob/main/CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email [security@spatie.be](mailto:security@spatie.be) instead of using the issue tracker.

## Credits

- [Freek Van der Herten](https://github.com/freekmurze)
- [All Contributors](../../contributors)

A big thank you to [Nicolas Beauvais](https://github.com/nicolasbeauvais) for helping out with the issues on this repo.

Special thanks to [Caneco](https://twitter.com/caneco) for the original logo.

## Alternatives

- [laravel-mediable](https://github.com/plank/laravel-mediable)
- [laravel-stapler](https://github.com/CodeSleeve/laravel-stapler)
- [media-manager](https://github.com/talvbansal/media-manager)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
