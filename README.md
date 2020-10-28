<p align="center"><img src="/art/socialcard.png" alt="Social Card of Laravel Media Library"></p>

# Associate files with Eloquent models

[![Latest Version](https://img.shields.io/github/release/spatie/laravel-medialibrary.svg?style=flat-square)](https://github.com/spatie/laravel-medialibrary/releases)
![GitHub Workflow Status](https://img.shields.io/github/workflow/status/spatie/laravel-medialibrary/run-tests?label=tests)
[![Total Downloads](https://img.shields.io/packagist/dt/spatie/laravel-medialibrary.svg?style=flat-square)](https://packagist.org/packages/spatie/laravel-medialibrary)

This package can associate all sorts of files with Eloquent models. It provides a
simple API to work with. To learn all about it, head over to [the extensive documentation](https://docs.spatie.be/laravel-medialibrary/v8).

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

The storage of the files is handled by [Laravel's Filesystem](https://laravel.com/docs/5.6/filesystem),
so you can use any filesystem you like. Additionally the package can create image manipulations
on images and pdfs that have been added in the media library.

Spatie is a webdesign agency in Antwerp, Belgium. You'll find an overview of all our open source projects [on our website](https://spatie.be/opensource).

## Support us

[<img src="https://github-ads.s3.eu-central-1.amazonaws.com/laravel-medialibrary.jpg?t=2" width="419px" />](https://spatie.be/github-ad-click/laravel-medialibrary)

We invest a lot of resources into creating [best in class open source packages](https://spatie.be/open-source). You can support us by [buying one of our paid products](https://spatie.be/open-source/support-us).

We highly appreciate you sending us a postcard from your hometown, mentioning which of our package(s) you are using. You'll find our address on [our contact page](https://spatie.be/about-us). We publish all received postcards on [our virtual postcard wall](https://spatie.be/open-source/postcards).

## Documentation

You'll find the documentation on [https://docs.spatie.be/laravel-medialibrary/v8](https://docs.spatie.be/laravel-medialibrary/v8).

Find yourself stuck using the package? Found a bug? Do you have general questions or suggestions for improving the media library? Feel free to [create an issue on GitHub](https://github.com/spatie/laravel-medialibrary/issues), we'll try to address it as soon as possible.

If you've found a bug regarding security please mail [freek@spatie.be](mailto:freek@spatie.be) instead of using the issue tracker.

## Testing

You can run the tests with:

```bash
vendor/bin/phpunit
```

## Upgrading

Please see [UPGRADING](UPGRADING.md) for details.

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email [freek@spatie.be](mailto:freek@spatie.be) instead of using the issue tracker.

## Credits

- [Freek Van der Herten](https://github.com/freekmurze)
- [All Contributors](../../contributors)

A big thank you to [Nicolas Beauvais](https://github.com/nicolasbeauvais) for helping out with the issues on this repo.

And a special thanks to [Caneco](https://twitter.com/caneco) for the logo ✨

## Alternatives

- [laravel-mediable](https://github.com/plank/laravel-mediable)
- [laravel-stapler](https://github.com/CodeSleeve/laravel-stapler)
- [media-manager](https://github.com/talvbansal/media-manager)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
