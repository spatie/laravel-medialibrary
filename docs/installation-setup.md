---
title: Installation & setup
weight: 4
---

Medialibrary can be installed via composer:

```bash
$ composer require spatie/laravel-medialibrary:^5.0.0
```

Next, you need to register the service provider:

```php
// config/app.php
'providers' => [
    ...
    Spatie\MediaLibrary\MediaLibraryServiceProvider::class,
];
```

And publish and run the migration:

```bash
php artisan vendor:publish --provider="Spatie\MediaLibrary\MediaLibraryServiceProvider" --tag="migrations"
php artisan migrate
```

Publishing the config file is optional:

```bash
php artisan vendor:publish --provider="Spatie\MediaLibrary\MediaLibraryServiceProvider" --tag="config"
```

The config file contains a number of default values:

```php
return [

    /*
     * The filesystems on which to store added files and derived images by default. Choose
     * one or more of the filesystems you configured in app/config/filesystems.php
     */
    'defaultFilesystem' => 'media',

    /*
     * The maximum file size of an item in bytes. Adding a file
     * that is larger will result in an exception.
     */
    'max_file_size' => 1024 * 1024 * 10,

    /*
     * This queue will be used to generate derived images.
     * Leave empty to use the default queue.
     */
    'queue_name' => '',

    /*
     * The class name of the media model to be used.
     */
    'media_model' => Spatie\MediaLibrary\Media::class,

    /*
     * The engine that will perform the image conversions.
     * Should be either `gd` or `imagick`
     */
    'image_driver' => 'gd',

    /*
     * When urls to files get generated this class will be called. Leave empty
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
     * These generators will be used to created conversion of media files.
     */
    'image_generators' => [
        Spatie\MediaLibrary\ImageGenerators\FileTypes\Image::class,
        Spatie\MediaLibrary\ImageGenerators\FileTypes\Pdf::class,
        Spatie\MediaLibrary\ImageGenerators\FileTypes\Svg::class,
        Spatie\MediaLibrary\ImageGenerators\FileTypes\Video::class,
    ],

    /*
     * FFMPEG & FFProbe binaries path, only used if you try to generate video
     * thumbnails and have installed the php-ffmpeg/php-ffmpeg composer
     * dependency.
     */
    'ffmpeg_binaries' => '/usr/bin/ffmpeg',
    'ffprobe_binaries' => '/usr/bin/ffprobe',
];


```

Finally you should add a disk to `app/config/filesystems.php`. All files added to the media library will be stored on that disk, this would be a typical configuration:

```php
return [
    ...
    'disks' => [
        'media' => [
            'driver' => 'local',
            'root'   => public_path('media'),
        ],
    ... 
];   
```

Don't forget to ignore the directory of your media disk so the files won't end up in your git repo.

If you are planning on working with image manipulations it's recommended to configure a queue on your server and specify it in the config file. 

Want to use S3? Then follow Laravel's instructions on [how to add the S3 Flysystem driver](https://laravel.com/docs/5.4/filesystem#configuration).
