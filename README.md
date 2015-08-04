# Associate files with Eloquent models

[![Latest Version](https://img.shields.io/github/release/spatie/laravel-medialibrary.svg?style=flat-square)](https://github.com/spatie/laravel-medialibrary/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Build Status](https://img.shields.io/travis/spatie/laravel-medialibrary/master.svg?style=flat-square)](https://travis-ci.org/spatie/laravel-medialibrary)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/27cf455a-0555-4bcf-abae-16b5f7860d09/mini.png)](https://insight.sensiolabs.com/projects/27cf455a-0555-4bcf-abae-16b5f7860d09)
[![Quality Score](https://img.shields.io/scrutinizer/g/spatie/laravel-medialibrary.svg?style=flat-square)](https://scrutinizer-ci.com/g/spatie/laravel-medialibrary)
[![Total Downloads](https://img.shields.io/packagist/dt/spatie/laravel-medialibrary.svg?style=flat-square)](https://packagist.org/packages/spatie/laravel-medialibrary)

This Laravel 5.1 package provides an easy way to associate all sorts of files with Eloquent models. 
Storage of the files is handled by [Laravel's Filesystem](http://laravel.com/docs/5.1/filesystem), 
so you can easily use something like S3. Additionally the package can create image manipulations 
on images and pdfs that have been added in the medialibrary.

Spatie is webdesign agency in Antwerp, Belgium. You'll find an overview of all our open source projects [on our website](https://spatie.be/opensource).

## Requirements
To create derived images [GD](http://php.net/manual/en/book.image.php) should be installed on your server.
For the creation of thumbnails of pdf's you should also install [Imagick](http://php.net/manual/en/imagick.setresolution.php).

On Ubuntu you can install Imagick by issuing this command:
```bash
sudo apt-get install imagemagick php5-imagick
``` 

## Installation

You can install this package via composer using this command:

```bash
composer require spatie/laravel-medialibrary
```

Next, you must install the service provider:

```php
// config/app.php
'providers' => [
    ...
    Spatie\MediaLibrary\MediaLibraryServiceProvider::class,
];
```

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
     * The filesystems on which to store added files and derived images. Choose one or more
     * of the filesystems you configured in app/config/filesystems.php
     */
    'defaultFilesystem' => 'media',

    /*
     * The maximum file size of an item in bytes. Adding a file
     * that is larger will result in an exception.
     */
    'max_file_size' => 1024 * 1024 * 10,

    /*
    * This queue will used to generate derived images.
    * Leave empty to use the default queue.
    */
    'queue_name' => '',
    
    's3' => [
        /*
         * The domain that should be prepended when generating urls.
         */
        'domain' => 'https://xxxxxxx.s3.amazonaws.com',
    ],
];
```

And finally you should add a disk to `app/config/filesystems.php`. This would be a typical configuration:

```php
    ...
	'disks' => [
        'media' => [
            'driver' => 'local',
            'root'   => public_path().'/media',
        ],
    ...    
```

All files of the medialibrary will be stored on that disk. If you are planning on
working with the image manipulations you should configure a queue on your service 
with the name specified in the config file.

## Basic usage

In essence the medialibrary is very simple. All files added to the library are associated with record in the db.
All examples in this readme assume that you have already have a news model set up. 
Of course this package will work with any Eloquent model.

To relate media to a model, the model must implement this interface and trait:

```php
namespace App\Models;

use Spatie\MediaLibrary\HasMediaTrait;
use Spatie\MediaLibrary\HasMedia;

class News extends Model implements HasMedia
{
	use HasMediaTrait;
   ...
}
```


###Associating a file
You can add associate a file with a model like this:
```php
$newsItem = News::find(1);
$newsItem->addMedia($pathToFile)->toMediaLibrary();
```
The file will now be associated with the `newsItem`. Adding a file will move your file to a configured disk.

If you want to preserve the file at the original location, you can call `preservingOriginal`:
```php
$newsItem->addMedia($pathToFile)->preservingOriginal()->toMediaLibrary();
```

###Retrieving media

To retrieve files you can use the ```getMedia```-method:
```php
$mediaItems = $newsItem->getMedia();
```

The method returns an collection with `Media`-objects.

You can retrieve the url to the file associated with `Media`-object with:

```php
$publicUrl = $mediaItems[0]->getUrl();
```

A media-object also has a name. By default it is the name of the file.
```php
echo $mediaItems[0]->name // display the name
$mediaItems[0]->name = 'newName'
$mediaItems[0]->save(); // the new name gets saved. Activerecord ftw!
```

Using the media-object the name of uploaded file can be changed.
```php
$mediaItems[0]->file_name = 'newFileName.jpg'
$mediaItems[0]->save(); // Saving will also rename the file on the filesystem.
```

You can also get the size of the file:
```php
$mediaItems[0]->size // returns the size in bytes
$mediaItems[0]->humanReadableSize // returns the size in a human readable form (eg. 1,5 MB)
```

You can remove something from the library simply calling `delete` on the media-object:
```php
$mediaItems[0]->delete();
```

When a media item gets deleted all related files will be removed from the filesystem.

Deleting a model with associated media will also delete all associated files.
```php
$newsItem->delete(); // all associated files will be deleted as well
```

If you want to remove all associated media in a specific collection you can use this method:
```php
$newsItem->clearMediaCollection(); // all media will be deleted
```

## Working with collections
If you have different types of files that you want to associate,
you can put them in their own collection.

```php
$newsItem = News::find(1);
$newsItem->addMedia($pathToImage)->toCollection('images');
$newsItem->addMedia($pathToAnotherImage)->toCollection('images');
$newsItem->addMedia($pathToPdfFile)->toCollection('downloads');
$newsItem->addMedia($pathToAnExcelFile)->toCollection('downloads');
```

All media in a specific collection can be retrieved like this:
```php
$newsItem->getMedia('images'); // returns media objects for all files in the images collection
$newsItem->getMedia('downloads'); // returns media objects for all files in the downloads collection
```

A collection can have any name you want. If you don't specify a name, the file will get added to a
`default`-collection.

You can clear out a specific collection just be passing the name to `clearMediaCollection`:
```php
$newsItem->clearMediaCollection('images');
```

## Working with images


###Defining conversions
Imagine you are making a site with a list of all news-items. Wouldn't it be nice to show 
the user a thumb of image associated with the news-item? When adding an image to a media collection, 
these derived images can be created automatically.

If you want to use this functionality your models should implement the `hasMediaConversions` interface instead
of `hasMedia`: 

```php
...
use Spatie\MediaLibrary\HasMediaConversions;

class News extends Model implements HasMediaConversions
{
	use HasMediaTrait;
   ...
}
```

You can let the package know that it should create a derived by registering a media conversion on the model.

```php
//in your news model
public function registerMediaConversions()
{
    $this->addMediaConversion('thumb')
        ->setManipulations(['w' => 368, 'h' => 232])
        ->performOnCollections('images');
}
```

When associating a jpg-, png-, or pdf-file, to the model the package will, besides storing the original image, 
create a derived image for every media conversion that was added. By default, the output will be a jpg-file. 

Internally [Glide](http://glide.thephpleague.com) is used to manipulate images. You can use any parameter you find 
in [their image API](http://glide.thephpleague.com/0.3/api/size/). So if you want to output another image format you can specify `png`or `gif` using the `fm`-key in an an imageprofile.

By default, a conversion will be performed on the queue that you specified 
in the configuration. You can also avoid the usage of the queue by calling `nonQueued()` on a conversion.

You can add as many conversions on a model as you want. Conversion can also be performed on multiple collections. To do so
you can just leave of the `performOnCollections`-call.  If you pass `*` to  `performOnCollections` the
conversion will be applied to every collection as well.

Here's an example where some of these options are demonstrated.

```php
//in your news model
public function registerMediaConversions()
{
    $this->addMediaConversion('thumb')
        ->setManipulations(['w' => 368, 'h' => 232,'filt' => 'greyscale', 'fm' => 'png'])
        ->performOnCollections('images', 'anotherCollection') // the conversion will be performed on multiple collections
        ->nonQueued(); // this conversion will not be queued
        
    //a second media conversion    
    $this->addMediaConversion('adminThumb')
        ->setManipulations(['w' => 50, 'h' => 50, 'sharp'=> 15])
        ->performOnCollections('*'); // perform the conversion on every collection

    //a third media conversion that will be performed on every collection
    $this->addMediaConversion('big')
        ->setManipulations(['w' => 500, 'h' => 500]);
}
```


###Using convenience methods to set a manipulation
The `setManipulations`-function expects an array with parameters that
are available in [the Glide image API](http://glide.thephpleague.com/0.3/api/size/).
Instead of using that function you can use the convenience functions.

This media conversion
```php
$this->addMediaConversion('thumb')
     ->setManipulations(['w' => 500]);
```
is equivalent to:
```php
$this->addMediaConversion('thumb')
     ->setWidth(500);
```

These are all available convencience methods:
```php
/**
 * Set the target width.
 * Matches with Glide's 'w'-parameter.
 */
public function setWidth($width)

/**
 * Set the target height.
 * Matches with Glide's 'h'-parameter.
 */
public function setHeight($height)

/**
 * Set the target format.
 * Matches with Glide's 'fm'-parameter.
 */
public function setFormat($format)

/**
 * Set the target fit.
 * Matches with Glide's 'fit'-parameter.
 */
public function setFit($fit)

/**
 * Set the target rectangle.
 * Matches with Glide's 'rect'-parameter.
 */
public function setRectangle($width, $height, $x, $y)
``` 

###Retrieving derived images
Here's example that shows you how to get the url's to the derived images:

```php
$mediaItems = $newsItem->getMedia('images');
$mediaItems[0]->getUrl('thumb');
```

Because getting an url to the first mediaItem in a collection is such a common scenario
the `getFirstMediaUrl`- convenience-method is provided. The first parameter is the name
of the collection, the second the name of a conversion.

```php
$urlToFirstListImage = $newsItem->getFirstMediaUrl('images', 'thumb');
```

###Regenerating images
When you change a conversion on your model, all images that were previously generated will not
updated automatically. To regenerate all images related to the News model you can 
issue this artisan command:
```bash
$ php artisan medialibrary:regenerate news
```
Leaving off `news` will regenerate all images.

###Using custom properties
When adding a file to the medialibrary you can pass an array with custom properties:
```php
$newsItem
    ->addMedia($pathToFile)
    ->withCustomProperties(['mime-type' => 'image/jpeg'])
    ->toMediaLibrary();
```

##Working with multiple filesystems
By default all files are stored on the disk whose name is specified in `defaultFilesystem` in the
config file. 

Files can also be stored [on any filesystem that is configured in your Laravel app](http://laravel.com/docs/5.0/filesystem#configuration).
When adding a file to the medialibrary you can choose on which disk the file should be stored. 
This is useful when for example you have some small files that should be stored locally and 
some big files that you want to save on s3.

The `toCollectionOnMedia`- and `toMediaLibraryOnDisk`-functions accept a disk name as a 
second parameter. If you have a disk named s3 you can do this:
```php
$newsItem->addMedia($pathToAFile)->toCollectionOnDisk('images', 's3');
```
This file will be stored on the disk named s3.


##Advanced usage
###Generating custom urls
When `getUrl()` is called the task of generating that url is passed to an implementation of `Spatie\MediaLibraryUrlGenerator`. 
The package contains a `LocalUrlGenerator` that can generate url's for a medialibrary that 
is stored above the public path. Also included is an `S3UrlGenerator` for when you're using S3 
to store your files. 

If you are storing your media files in a private directory or use a different filesystem,
you can write your own `UrlGenerator`. Your generator must adhere to the `Spatie\MediaLibraryUrlGenerator`.
When you also extend `Spatie\MediaLibraryUrlGenerator\BaseGenerator` you must only implement 
one method: `getUrl()` that should return the url. You can call `getPathRelativeToRoot()` to
get the relative path to the root of your disk.

The code of the included `S3UrlGenerator` should help make things more clear:
 ```php
 namespace Spatie\MediaLibrary\UrlGenerator;
 
 use Spatie\MediaLibrary\Exceptions\UrlCouldNotBeDeterminedException;
 
 class S3UrlGenerator extends BaseUrlGenerator implements UrlGenerator
 {
     /**
      * Get the url for the profile of a media item.
      *
      * @return string
      *
      * @throws UrlCouldNotBeDeterminedException
      */
     public function getUrl()
     {
         return config('laravel-medialibrary.s3.domain').'/'.$this->getPathRelativeToRoot();
     }
 }
 ```

###Storing manipulations on a media object
 
A media object has a property `manipulations`. You can set it to an array of 
which the keys must be conversion names and the values manipulation arrays. 

When saving the media object, the package will regenerate all files and use the saved manipulation
as the first manipulation when creating a derived image.

For example:
```php
// in your news model
...
public function registerMediaConversions()
{
    $this->addMediaConversion('thumb')
        ->setManipulations(['w' => 500, 'h'=>500])
        ->performOnCollections('myCollection');
}
```
```php
// somewhere in your project and assuming you've already added some images to myCollection.
$mediaItems = $newsItem->getMedia('images');
$mediaItems[0]->manipulations = ['thumb' => ['mode' => 'filt' => 'greyscale']]
$mediaItems[0]->save(); // this will cause the thumb conversion to be regenerated. The result will be a greyscale image.
```
Calling `save()` in this example will regenerate the thumb-image. The output will be a
greyscale image that has a both width and height of 500 pixels.

## Testing

You can run the tests with:

```bash
vendor/bin/phpunit
```
##Upgrading
###From v1 to v2
Because v2 is a complete rewrite a simple upgrade path is not available.
If you want to upgrade completely remove the v1 package and follow install instructions of v2.

###From v2 to v3
You can upgrade from v2 to v3 by performing these renames in your model that has media.

- `Spatie\MediaLibrary\HasMediaTrait` has been renamed to `Spatie\MediaLibrary\HasMedia\HasMediaTrait`. 
- `Spatie\MediaLibrary\HasMedia` has been renamed to `Spatie\MediaLibrary\HasMedia\Interfaces\HasMediaConversion`
- `Spatie\MediaLibrary\HasMediaWithoutConversions` has been renamed to `Spatie\MediaLibrary\HasMedia\Interfaces\HasMedia`

In the config file you should rename the `filesystem`-option to `defaultFilesystem`.

In the db the `temp`-column must be removed. Add these columns:
- disk (varchar, 255)
- custom_properties (text)
You should set the value of disk column in all rows to the name the defaultFilesystem specified in the config file.

Note that this behaviour has changed:
- when calling `getMedia()` without providing a collection name all media will be returned (whereas previously only media
from the default collection would be returned)
- calling `hasMedia()` without a collection name returns true if any given collection contains files (wheres previously
it would only return try if files were present in the default collection)
- the `addMedia`-function has been replaced by a fluent interface. 

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email [freek@spatie.be](mailto:freek@spatie.be) instead of using the issue tracker.

## Credits

- [Freek Van der Herten](https://github.com/freekmurze)
- [All Contributors](../../contributors)

## About Spatie
Spatie is webdesign agency in Antwerp, Belgium. You'll find an overview of all our open source projects [on our website](https://spatie.be/opensource).

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
