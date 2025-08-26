---
title: Base installation
weight: 4
---

Media Library can be installed via Composer:

If you only use the base package issue this command:

```bash
composer require "spatie/laravel-medialibrary"
```

If you have a license for Media Library Pro, you should install `spatie/laravel-media-library-pro` instead. Please refer to our [Media Library Pro installation instructions](https://spatie.be/docs/laravel-medialibrary/v11/handling-uploads-with-media-library-pro/installation) to continue.

## Preparing the database

You need to publish the migration to create the `media` table:

```bash
php artisan vendor:publish --provider="Spatie\MediaLibrary\MediaLibraryServiceProvider" --tag="medialibrary-migrations"
```

After that, you need to run migrations.

```bash
php artisan migrate
```

## Publishing the config file

Publishing the config file is optional:

```bash
php artisan vendor:publish --provider="Spatie\MediaLibrary\MediaLibraryServiceProvider" --tag="medialibrary-config"
```

This is the default content of the config file:

```php
return [

    /*
     * The disk on which to store added files and derived images by default. Choose
     * one or more of the disks you've configured in config/filesystems.php.
     */
    'disk_name' => env('MEDIA_DISK', 'public'),

    /*
     * The maximum file size of an item in bytes.
     * Adding a larger file will result in an exception.
     */
    'max_file_size' => 1024 * 1024 * 10,
    
    /*
     * This queue connection will be used to generate derived and responsive images.
     * Leave empty to use the default queue connection.
     */
    'queue_connection_name' => '',

    /*
     * This queue will be used to generate derived and responsive images.
     * Leave empty to use the default queue.
     */
    'queue_name' => '',

    /*
     * By default all conversions will be performed on a queue.
     */
    'queue_conversions_by_default' => env('QUEUE_CONVERSIONS_BY_DEFAULT', true),

    /*
     * The fully qualified class name of the media model.
     */
    'media_model' => Spatie\MediaLibrary\MediaCollections\Models\Media::class,

    /*
     * The fully qualified class name of the model used for temporary uploads.
     *
     * This model is only used in Media Library Pro (https://medialibrary.pro)
     */
    'temporary_upload_model' => Spatie\MediaLibraryPro\Models\TemporaryUpload::class,

    /*
     * When enabled, Media Library Pro will only process temporary uploads there were uploaded
     * in the same session. You can opt to disable this for stateless usage of
     * the pro components.
     */
    'enable_temporary_uploads_session_affinity' => true,

    /*
     * When enabled, Media Library pro will generate thumbnails for uploaded file.
     */
    'generate_thumbnails_for_temporary_uploads' => true,

    /*
     * This is the class that is responsible for naming generated files.
     */
    'file_namer' => Spatie\MediaLibrary\Support\FileNamer\DefaultFileNamer::class,

    /*
     * The class that contains the strategy for determining a media file's path.
     */
    'path_generator' => Spatie\MediaLibrary\Support\PathGenerator\DefaultPathGenerator::class,

    /*
     * When urls to files get generated, this class will be called. Use the default
     * if your files are stored locally above the site root or on s3.
     */
    'url_generator' => Spatie\MediaLibrary\Support\UrlGenerator\DefaultUrlGenerator::class,

    /*
     * Whether to activate versioning when urls to files get generated.
     * When activated, this attaches a ?v=xx query string to the URL.
     */
    'version_urls' => false,

    /*
     * The media library will try to optimize all converted images by removing
     * metadata and applying a little bit of compression. These are
     * the optimizers that will be used by default.
     */
    'image_optimizers' => [
        Spatie\ImageOptimizer\Optimizers\Jpegoptim::class => [
            '-m85', // set maximum quality to 85%
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
        Spatie\ImageOptimizer\Optimizers\Cwebp::class => [
            '-m 6', // for the slowest compression method in order to get the best compression.
            '-pass 10', // for maximizing the amount of analysis pass.
            '-mt', // multithreading for some speed improvements.
            '-q 90', //quality factor that brings the least noticeable changes.
        ],
        Spatie\ImageOptimizer\Optimizers\Avifenc::class => [
            '-a cq-level=23', // constant quality level, lower values mean better quality and greater file size (0-63).
            '-j all', // number of jobs (worker threads, "all" uses all available cores).
            '--min 0', // min quantizer for color (0-63).
            '--max 63', // max quantizer for color (0-63).
            '--minalpha 0', // min quantizer for alpha (0-63).
            '--maxalpha 63', // max quantizer for alpha (0-63).
            '-a end-usage=q', // rate control mode set to Constant Quality mode.
            '-a tune=ssim', // SSIM as tune the encoder for distortion metric.
        ],
    ],

    /*
     * These generators will be used to create an image of media files.
     */
    'image_generators' => [
        Spatie\MediaLibrary\Conversions\ImageGenerators\Image::class,
        Spatie\MediaLibrary\Conversions\ImageGenerators\Webp::class,
        Spatie\MediaLibrary\Conversions\ImageGenerators\Avif::class,
        Spatie\MediaLibrary\Conversions\ImageGenerators\Pdf::class,
        Spatie\MediaLibrary\Conversions\ImageGenerators\Svg::class,
        Spatie\MediaLibrary\Conversions\ImageGenerators\Video::class,
    ],

    /*
     * The path where to store temporary files while performing image conversions.
     * If set to null, storage_path('media-library/temp') will be used.
     */
    'temporary_directory_path' => null,

    /*
     * The engine that should perform the image conversions.
     * Should be either `gd` or `imagick`.
     */
    'image_driver' => env('IMAGE_DRIVER', 'gd'),

    /*
     * FFMPEG & FFProbe binaries paths, only used if you try to generate video
     * thumbnails and have installed the php-ffmpeg/php-ffmpeg composer
     * dependency.
     */
    'ffmpeg_path' => env('FFMPEG_PATH', '/usr/bin/ffmpeg'),
    'ffprobe_path' => env('FFPROBE_PATH', '/usr/bin/ffprobe'),

    /*
     * Here you can override the class names of the jobs used by this package. Make sure
     * your custom jobs extend the ones provided by the package.
     */
    'jobs' => [
        'perform_conversions' => Spatie\MediaLibrary\Conversions\Jobs\PerformConversionsJob::class,
        'generate_responsive_images' => Spatie\MediaLibrary\ResponsiveImages\Jobs\GenerateResponsiveImagesJob::class,
    ],

    /*
     * When using the addMediaFromUrl method you may want to replace the default downloader.
     * This is particularly useful when the url of the image is behind a firewall and
     * need to add additional flags, possibly using curl.
     */
    'media_downloader' => Spatie\MediaLibrary\Downloaders\DefaultDownloader::class,

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

    'responsive_images' => [
        /*
         * This class is responsible for calculating the target widths of the responsive
         * images. By default we optimize for filesize and create variations that each are 20%
         * smaller than the previous one. More info in the documentation.
         *
         * https://spatie.be/docs/laravel-medialibrary/v11/responsive-images/getting-started-with-responsive-images
         */
        'width_calculator' => Spatie\MediaLibrary\ResponsiveImages\WidthCalculator\FileSizeOptimizedWidthCalculator::class,

        /*
         * By default rendering media to a responsive image will add some javascript and a tiny placeholder.
         * This ensures that the browser can already determine the correct layout.
         */
        'use_tiny_placeholders' => true,

        /*
         * This class will generate the tiny placeholder used for progressive image loading. By default
         * the media library will use a tiny blurred jpg image.
         */
        'tiny_placeholder_generator' => Spatie\MediaLibrary\ResponsiveImages\TinyPlaceholderGenerator\Blurred::class,
    ],

    /*
     * When enabling this option, a route will be registered that will enable
     * the Media Library Pro Vue and React components to move uploaded files
     * in a S3 bucket to their right place.
     */
    'enable_vapor_uploads' => env('ENABLE_MEDIA_LIBRARY_VAPOR_UPLOADS', false),

    /*
     * When converting Media instances to response the media library will add
     * a `loading` attribute to the `img` tag. Here you can set the default
     * value of that attribute.
     *
     * Possible values: 'lazy', 'eager', 'auto' or null if you don't want to set any loading instruction.
     *
     * More info: https://css-tricks.com/native-lazy-loading/
     */
    'default_loading_attribute_value' => null,
    
     /*
     * You can specify a prefix for that is used for storing all media.
     * If you set this to `/my-subdir`, all your media will be stored in a `/my-subdir` directory.
     */
     'prefix' => env('MEDIA_PREFIX', ''),

    /*
     * When forcing lazy loading, media will be loaded even if you don't eager load media and you have
     * disabled lazy loading globally in the service provider.
     */
    'force_lazy_loading' => env('FORCE_MEDIA_LIBRARY_LAZY_LOADING', true),
];
```

## Adding a media disk

By default, the media library will store its files on Laravel's `public` disk. If you want a dedicated disk you should add a disk to `config/filesystems.php`. This would be a typical configuration:

```php
    ...
    'disks' => [
        ...

        'media' => [
            'driver' => 'local',
            'root'   => public_path('media'),
            'url'    => env('APP_URL').'/media',
            'visibility' => 'public',
            'throw' => false,
            
            /*
             * OPTIONAL:
             * 
             * When set to true, Media Library will always perform a server-side copy
             * when source and target files are on the same disk. This is useful
             * for certain remote drivers (e.g. R2, S3-compatible) where
             * direct streaming is not desirable eg: very large files and you want that
             * it should not be loaded into PHP memory.
             */
            'force_server_copy' => true,
        ],
    ...
```

Don't forget to `.gitignore` the directory of your configured disk, so the files won't end up in your git repo.

To store all media on that disk by default, you should set the `disk_name` config value in the `media-library` config file to the name of the disk you added.

```php
// config/media-library.php

return [
    'disk_name' => 'media',

    // ...
];
```

Want to use S3? Then follow Laravel's instructions on [how to add the S3 Flysystem driver](https://laravel.com/docs/filesystem#configuration). If possible, we recommend [using a remote filesystem like S3](https://twitter.com/taylorotwell/status/1153326292412129280) instead of your local filesystem to prevent security issues.

## Setting up a queue

If you are planning on working with image manipulations it's recommended to configure a queue on your server and specify it in the config file.

### Setting up optimization tools

Media library will use these tools to [optimize converted images](https://spatie.be/docs/laravel-medialibrary/v11/converting-images/optimizing-converted-images) if they are present on your system:

- [JpegOptim](http://freecode.com/projects/jpegoptim)
- [Optipng](http://optipng.sourceforge.net/)
- [Pngquant 2](https://pngquant.org/)
- [SVGO](https://github.com/svg/svgo)
- [Gifsicle](http://www.lcdf.org/gifsicle/)
- [Avifenc](https://github.com/AOMediaCodec/libavif/blob/main/doc/avifenc.1.md)

Here's how to install all the optimizers on Ubuntu:

```bash
sudo apt install jpegoptim optipng pngquant gifsicle libavif-bin
npm install -g svgo
```

If you don't want to install `npm` on your Ubuntu server, you can use `snap` which is installed by default:

```bash
sudo apt install jpegoptim optipng pngquant gifsicle libavif-bin
sudo snap install svgo
```

Here's how to install the binaries on MacOS (using [Homebrew](https://brew.sh/)):

```bash
brew install jpegoptim
brew install optipng
brew install pngquant
brew install svgo
brew install gifsicle
brew install libavif
```

## Installing Media Library Pro

[Media Library Pro](http://medialibrary.pro) is an optional add-on package that offers Blade, Vue, and React components to upload files to your application. It [integrates](https://spatie.be/docs/laravel-medialibrary/v11/handling-uploads-with-media-library-pro/introduction) beautifully with the laravel-medialibrary.

You can buy a license for Media Library Pro on [the product page](https://spatie.be/products/media-library-pro) at spatie.be.

To install Media Library Pro, you should follow [these instructions](https://spatie.be/docs/laravel-medialibrary/v11/handling-uploads-with-media-library-pro/installation).
