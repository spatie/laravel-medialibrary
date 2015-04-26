## This package is still under construction. Unless you have uncontrollable urge to use it, please have a little patience until we finish it

# A media library back end for Laravel 5 applications

[![Latest Version](https://img.shields.io/github/release/freekmurze/laravel-medialibrary.svg?style=flat-square)](https://github.com/freekmurze/laravel-medialibrary/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![SensioLabsInsight](https://img.shields.io/sensiolabs/i/27cf455a-0555-4bcf-abae-16b5f7860d09.svg)](https://insight.sensiolabs.com/projects/27cf455a-0555-4bcf-abae-16b5f7860d09)
[![Quality Score](https://img.shields.io/scrutinizer/g/freekmurze/laravel-medialibrary.svg?style=flat-square)](https://scrutinizer-ci.com/g/freekmurze/laravel-medialibrary)
[![Total Downloads](https://img.shields.io/packagist/dt/spatie/laravel-medialibrary.svg?style=flat-square)](https://packagist.org/packages/spatie/:laravel-medialibrary)

This package provides an easy way to associate all sorts of files with Eloquent models. Additionally it can create manipulations on images that have been added to the medialibrary.

## Install

You can install this package via composer using:

``` bash
composer require spatie/laravel-medialibrary
```

Next, you must install the service provider and the facade.

``` php
// config/app.php
'providers' => [
    ...
    'Spatie\MediaLibrary\MediaLibraryServiceProvider',
];
```

``` php
// config/app.php
'aliases' => [
    ...
    'MediaLibrary' => 'Spatie\MediaLibrary\MediaLibraryFacade',
];
```

To publish the config file to app/config/laravel-backup.php run:

``` bash
$ php artisan vendor:publish --provider="Spatie\MediaLibrary\MediaLibraryServiceProvider"
```

You can separately publish the config or the migration using the ```config``` or ```migrations``` tag.

After the migration has been published you can create the media-table you by running the migrations.

```bash
$ php artisan migrate
```

This is the contens of the published config file:
```
return [

    /*
     * The medialibrary will use this directory to store added files and derived images.
     * If you are planning on using the url to the derived images, make sure
     * you specify a directory inside Laravel's public path.
     */
    'publicPath' => public_path().'/media',

    /*
     * The maximum file size of an item in bytes. If you try to add a file
     * that is larger to the medialibrary it will result in an exception.
     */
    'maxFileSize' => 1024 * 1024 * 10,

    /*
     * These image profiles will applied on all used that implement
     * the MediaLibraryModelTrait.
     *
     * See the README of this package for an example.
     */
    'globalImageProfiles' => [],
];
```

## Basic usage


In essence the medialibrary is very simple. All files added to the library is associated with a record in the db. All examples in the readme assume that you have already have a news model setup (the package will work with any model).

First you should let the model that you want to relate to media implement this interface and trait:

```
namespace App\Models;

use Spatie\MediaLibrary\MediaLibraryModel\MediaLibraryModelInterface;
use Spatie\MediaLibrary\MediaLibraryModel\MediaLibraryModelTrait;

class News extends implements MediaLibraryModelInterface
{

	use MediaLibraryModelTrait
   ...
}
```

###Using the facade
Using the facade you can add items to the library like this:
```php
$collectionName = 'myFirstCollection'
$newsItem = News::find(1);
Medialibrary::add($pathToAFile, News::find(1), $collectionName);
```
Adding a file will move your file to a directory managed by the medialibrary.

To retrieve files you can use the ```getCollection```-method:
```php
$mediaItems = MediaLibrary::getCollection($newsItem, $collectionName);
```

The method returns an array with `Media`-objects that are in the collection for the given model.

You can retrieve the url to the file associated with `Media`-object with:
```php
$publicURL = $mediaItems[0]->getOriginalURL();
```

You can remove someting from the library by passing the a media id to the remove method of the facade:
```
MediaLibrary::remove($mediaItems[0]->id)
```

###Using the model
All the methods above are also available on the model itself.
```
$newsItem = News::find(2);
$collectionName = 'anotherFineCollection';
$newsItem->addMedia($pathToAFile, $collectionName);

$mediaItems = $newsMedia->getCollection($collectionName);
$publicURL = $mediaItems[0]->getOriginalURL();

$newsItem->removeMedia($mediaItems[0]->id);
```

## Working with images
Image you are making a site with a list of all news-items. Wouldn't it be nice to show the user a thumb of image associated with the news-item? When adding images to the medialibrary, it can create these derived images for you.

You can let the medialibrary know that it should make a derived image by implementing the `getImageProfileProperties()`-method on the model.

```php
//in your news model
public static function getImageProfileProperties()
{
    return [
        'list'=> ['w'=>200, 'h'=>200],
        'detail'=> ['w'=>1600, 'h'=>800],
    ];
}
```
When associating a jpg-file or png-file to the library it will, besides storing the original image, create a derived image for every key in the array. Of course "list" and "detail" are only examples. You can use any string you like as a key. The example above uses a width and height manipulation. Internally the medialibrary uses [Glide](http://glide.thephpleague.com) to manipulate images. You can use any parameter you find in [their image API](http://glide.thephpleague.com/api/size/).


Here's example that shows you how to get the url's to the derived images:

```php
$mediaItems = $newsItem->getCollection($collectionName)
$firstMediaItem = $mediaItems[0];
$urlToOrignalUploadedImage = $firstMediaItem->getOriginalURL();
$urlToListImage = $firstMediaItem->getURL('list');
$urlToDetailImage = $firstMediaItem->getURL('detail');
```



## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email freek@spatie.be instead of using the issue tracker.

## Credits

- [Freek Van der Herten](https://github.com/freekmurze)
- [Matthias De Winter](https://github.com/MatthiasDeWinter)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
