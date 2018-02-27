# Associate files with Eloquent models

[![Latest Version](https://img.shields.io/github/release/spatie/laravel-medialibrary.svg?style=flat-square)](https://github.com/spatie/laravel-medialibrary/releases)
[![Build Status](https://img.shields.io/travis/spatie/laravel-medialibrary/master.svg?style=flat-square)](https://travis-ci.org/spatie/laravel-medialibrary)
[![Quality Score](https://img.shields.io/scrutinizer/g/spatie/laravel-medialibrary.svg?style=flat-square)](https://scrutinizer-ci.com/g/spatie/laravel-medialibrary)
[![StyleCI](https://styleci.io/repos/33916850/shield)](https://styleci.io/repos/33916850)
[![Total Downloads](https://img.shields.io/packagist/dt/spatie/laravel-medialibrary.svg?style=flat-square)](https://packagist.org/packages/spatie/laravel-medialibrary)

This Laravel >=5.5 package can associate all sorts of files with Eloquent models. It provides a
simple API to work with. To learn all about it, head over to [the extensive documentation](https://docs.spatie.be/laravel-medialibrary/v6).

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
on images and pdfs that have been added in the medialibrary.

Spatie is a webdesign agency in Antwerp, Belgium. You'll find an overview of all our open source projects [on our website](https://spatie.be/opensource).

## Using an older version of Laravel?

This version of the medialibrary is compatible with Laravel 5.5 and newer.

If you use and older version of Laravel you can use an older version of the package. These aren't maintained anymore, but the should be pretty stable. We still accept small bugfixes.

- [Medialibrary v5 for Laravel 5.4](https://docs.spatie.be/laravel-medialibrary/v5)
- [Medialibray v4 for Laravel 5.1 - 5.3](https://docs.spatie.be/laravel-medialibrary/v4)

## Documentation

You'll find the documentation on [https://docs.spatie.be/laravel-medialibrary/v6](https://docs.spatie.be/laravel-medialibrary/v6).

Find yourself stuck using the package? Found a bug? Do you have general questions or suggestions for improving the media library? Feel free to [create an issue on GitHub](https://github.com/spatie/laravel-medialibrary/issues), we'll try to address it as soon as possible.

If you've found a bug regarding security please mail [freek@spatie.be](mailto:freek@spatie.be) instead of using the issue tracker.

## Requirements

To create derived images [GD](http://php.net/manual/en/book.image.php) should be installed on your server.
For the creation of thumbnails of svg's or pdf's you should also install [Imagick](http://php.net/manual/en/imagick.setresolution.php).

## Installation

You can install this package via composer using this command:

```bash
composer require spatie/laravel-medialibrary:^6.0.0
```

The package will automatically register itself.

You can publish the migration with:

```bash
php artisan vendor:publish --provider="Spatie\MediaLibrary\MediaLibraryServiceProvider" --tag="migrations"
```

After the migration has been published you can create the media-table by running the migrations:

```bash
php artisan migrate
```

You can publish the config-file with:

```bash
php artisan vendor:publish --provider="Spatie\MediaLibrary\MediaLibraryServiceProvider" --tag="config"
```

This is the contents of the published config file:

```php
return [

    /*
     * The filesystems on which to store added files and derived images by default. Choose
     * one or more of the filesystems you've configured in config/filesystems.php.
     */
    'default_filesystem' => 'public',

    /*
     * The maximum file size of an item in bytes. 
     * Adding a larger file will result in an exception.
     */
    'max_file_size' => 1024 * 1024 * 10,

    /*
     * This queue will be used to generate derived images.
     * Leave empty to use the default queue.
     */
    'queue_name' => '',

    /*
     * The class name of the media model that should be used.
     */
    'media_model' => Spatie\MediaLibrary\Media::class,

    /*
     * The engine that should perform the image conversions.
     * Should be either `gd` or `imagick`.
     */
    'image_driver' => 'gd',

    /*
     * When urls to files get generated, this class will be called. Leave empty
     * if your files are stored locally above the site root or on s3.
     */
    'custom_url_generator_class' => null,

    /*
     * The class that contains the strategy for determining a media file's path.
     */
    'custom_path_generator_class' => null,

    's3' => [
        /*
         * The domain that should be prepended when generating urls.
         */
        'domain' => 'https://xxxxxxx.s3.amazonaws.com',
    ],

    'remote' => [
        /*
         * Any extra headers that should be included when uploading media to
         * a remote disk. Even though supported headers may vary between
         * different drivers, a sensible default has been provided.
         *
         * Supported by S3: CacheControl, Expires, StorageClass,
         * ServerSideEncryption, Metadata, ACL, ContentEncoding
         */
        'extra_headers' => [
            'CacheControl' => 'max-age=604800',
        ],
    ],

    /*
     * These generators will be used to create an image of media files.
     */
    'image_generators' => [
        Spatie\MediaLibrary\ImageGenerators\FileTypes\Image::class,
        Spatie\MediaLibrary\ImageGenerators\FileTypes\Pdf::class,
        Spatie\MediaLibrary\ImageGenerators\FileTypes\Svg::class,
        Spatie\MediaLibrary\ImageGenerators\FileTypes\Video::class,
    ],

    /*
     * Medialibrary will try to optimize all converted images by removing
     * metadata and applying a little bit of compression. These are
     * the optimizers that will be used by default.
     */
    'image_optimizers' => [
        Spatie\ImageOptimizer\Optimizers\Jpegoptim::class => [
            '--strip-all', // this strips out all text information such as comments and EXIF data
            '--all-progressive', // this will make sure the resulting image is a progressive one
        ],
        Spatie\ImageOptimizer\Optimizers\Pngquant::class => [
            '--force', // required parameter for this package
        ],
        Spatie\ImageOptimizer\Optimizers\Optipng::class => [
            '-i0', // this will result in a non-interlaced, progressive scanned image
            '-o2', // this set the optimization level to two (multiple IDAT compression trials)
            '-quiet', // required parameter for this package
        ],
        Spatie\ImageOptimizer\Optimizers\Svgo::class => [
            '--disable=cleanupIDs', // disabling because it is known to cause troubles
        ],
        Spatie\ImageOptimizer\Optimizers\Gifsicle::class => [
            '-b', // required parameter for this package
            '-O3', // this produces the slowest but best results
        ],
    ],

    /*
     * The path where to store temporary files while performing image conversions.
     * If set to null, storage_path('medialibrary/temp') will be used.
     */
    'temporary_directory_path' => null,

    /*
     * FFMPEG & FFProbe binaries path, only used if you try to generate video
     * thumbnails and have installed the php-ffmpeg/php-ffmpeg composer
     * dependency.
     */
    'ffmpeg_binaries' => '/usr/bin/ffmpeg',
    'ffprobe_binaries' => '/usr/bin/ffprobe',
];
```

By default medialibrary will store it's files on Laravel's `public` disk. If you want a dedicated disk you should add a disk to `app/config/filesystems.php`. This would be a typical configuration:

```php
    ...
    'disks' => [
        ...

        'media' => [
            'driver' => 'local',
            'root'   => public_path().'/media',
            'url' => env('APP_URL') . '/media',
            'visibility' => 'public',
        ],
    ...
```

All files of the medialibrary will be stored on that disk. If you are planning on
working with the image manipulations you should configure a queue on your service
with the name specified in the config file.

## Lumen Support

Lumen configuration is slightly more involved but features and API are identical to Laravel.

Install using this command:

```bash
composer require spatie/laravel-medialibrary
```

Uncomment the following lines in the bootstrap file:

```php
// bootstrap/app.php:
$app->withFacades();
$app->withEloquent();
```

Configure the laravel-medialibrary service provider (and `AppServiceProvider` if not already enabled):

```php
// bootstrap/app.php:
$app->register(App\Providers\AppServiceProvider::class);
$app->register(Spatie\MediaLibrary\MediaLibraryServiceProvider::class);
```

Update the `AppServiceProvider` register method to bind the filesystem manager to the IOC container:

```php
// app/Providers/AppServiceProvider.php
public function register()
{
    $this->app->singleton('filesystem', function ($app) {
        return $app->loadComponent('filesystems', 'Illuminate\Filesystem\FilesystemServiceProvider', 'filesystem');
    });

    $this->app->bind('Illuminate\Contracts\Filesystem\Factory', function($app) {
        return new \Illuminate\Filesystem\FilesystemManager($app);
    });
}
```

Manually copy the package config file to `app\config\laravel-medialibrary.php` (you may need to
create the config directory if it does not already exist).

Copy the [Laravel filesystem config file](https://github.com/laravel/laravel/blob/v6.4.15/config/filesystems.php) into `app\config\filesystem.php`. You should add a disk configuration to the filesystem config matching the `default_filesystem` specified in the laravel-medialibrary config file.

Finally, update `boostrap/app.php` to load both config files:

```php
// bootstrap/app.php
$app->configure('medialibrary');
$app->configure('filesystems');
```

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

## Postcardware

You're free to use this package, but if it makes it to your production environment we highly appreciate you sending us a postcard from your hometown, mentioning which of our package(s) you are using.

Our address is: Spatie, Samberstraat 69D, 2060 Antwerp, Belgium.

We publish all received postcards [on our company website](https://spatie.be/en/opensource/postcards).

## Credits

- [Freek Van der Herten](https://github.com/freekmurze)
- [All Contributors](../../contributors)

A big thank you to [Nicolas Beauvais](https://github.com/nicolasbeauvais) for helping out with the issues on this repo.

## Alternatives

- [laravel-mediable](https://github.com/plank/laravel-mediable)
- [laravel-stapler](https://github.com/CodeSleeve/laravel-stapler)
- [media-manager](https://github.com/talvbansal/media-manager)

## Support us

Spatie is a webdesign agency based in Antwerp, Belgium. You'll find an overview of all our open source projects [on our website](https://spatie.be/opensource).

Does your business depend on our contributions? Reach out and support us on [Patreon](https://www.patreon.com/spatie). 
All pledges will be dedicated to allocating workforce on maintenance and new awesome stuff.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
