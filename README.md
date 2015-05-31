# Associate files with Eloquent models

[![Latest Version](https://img.shields.io/github/release/spatie/laravel-medialibrary.svg?style=flat-square)](https://github.com/spatie/laravel-medialibrary/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/27cf455a-0555-4bcf-abae-16b5f7860d09/mini.png)](https://insight.sensiolabs.com/projects/27cf455a-0555-4bcf-abae-16b5f7860d09)
[![Quality Score](https://img.shields.io/scrutinizer/g/spatie/laravel-medialibrary.svg?style=flat-square)](https://scrutinizer-ci.com/g/spatie/laravel-medialibrary)
[![Total Downloads](https://img.shields.io/packagist/dt/spatie/laravel-medialibrary.svg?style=flat-square)](https://packagist.org/packages/spatie/laravel-medialibrary)

This Laravel 5 package provides an easy way to associate all sorts of files with Eloquent models. Additionally it can create image manipulations on images that have been added in the medialibrary.

## Installation

You can install this package via composer using:

```bash
composer require spatie/laravel-medialibrary
```

Next, you must install the service provider and the facade. 

```php
// config/app.php
'providers' => [
    ...
    'Spatie\MediaLibrary\MediaLibraryServiceProvider',
];
```

```php
// config/app.php
'aliases' => [
    ...
    'MediaLibrary' => 'Spatie\MediaLibrary\MediaLibraryFacade',
];
```

To publish the config file to app/config/laravel-medialibrary.php run:

```bash
php artisan vendor:publish --provider="Spatie\MediaLibrary\MediaLibraryServiceProvider"
```

After the migration has been published you can create the media-table you by running the migrations.

```bash
php artisan migrate
```

This is the contents of the published config file:

```php
return [

    /*
     * The medialibrary will use this directory to store added files and derived images.
     * If you are planning on using the url to the derived images, make sure
     * you specify a directory inside Laravel's public path.
     * 
     * The package will automatically add a .gitignore file to this directory
     * so you don't end of committing these files in your repo.
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
     * See the README of the package for an example.
     */
    'globalImageProfiles' => [],
    
    /*
     * The medialibrary will used this queue to generate derived images.
     * Leave empty to use the default queue.
     */
    'queueName' => 'media_queue',
];
```

If you are planning on working with the image manipulations you should configure a queue on your service with the name specified in the config file.

## Basic usage


In essence the medialibrary is very simple. All files added to the library are associated a record in the db. All examples in this readme assume that you have already have a news model set up. Of course this package will work with any Eloquent model.

To relate media to a model, the model must implement this interface and trait:

```php
namespace App\Models;

use Spatie\MediaLibrary\MediaLibraryModel\MediaLibraryModelInterface;
use Spatie\MediaLibrary\MediaLibraryModel\MediaLibraryModelTrait;

class News extends implements MediaLibraryModelInterface
{

	use MediaLibraryModelTrait;
   ...
}
```

###Using the facade
Using the facade you can add items to the library like this:
```php
$collectionName = 'myFirstCollection'
$newsItem = News::find(1);
MediaLibrary::add($pathToAFile, $newsItem, $collectionName);
```
Adding a file will move your file to a directory managed by the medialibrary.

To retrieve files you can use the ```getCollection```-method:
```php
$mediaItems = MediaLibrary::getCollection($newsItem, $collectionName);
```

The method returns an array with `Media`-objects that are in the collection for the given model.

You can retrieve the url to the file associated with `Media`-object with:

```php
$publicURL = $mediaItems[0]->getURL('original');
```

You can remove something from the library by passing the a media id to the remove method of the facade:

```php
MediaLibrary::remove($mediaItems[0]->id)
```

If you delete a record all related files will be removed from the filesystem.

```php
$newsItem->delete(); //all associated files will be deleted as well
```

###Using the model
Nearly all the methods of the facade are also available on the model itself.

```php
$newsItem = News::find(2);
$collectionName = 'anotherFineCollection';
$newsItem->addMedia($pathToAFile, $collectionName);


$mediaItems = $newsMedia->getMedia($collectionName);
$publicURL = $mediaItems[0]->getURL('original');

//remove a single mediaItem
$newsItem->removeMedia($mediaItems[0]->id);
```

You can also remove all items in a collection.
```php
$newsItem->addMedia($pathToAFile, $collectionName);
$newsItem->addMedia($pathToAnotherFile, $collectionName);
$newsItem->addMedia($pathToYetAnotherFile, $collectionName);
//all media in the collection will be removed
$newsItem->removeMediaCollection($collectionName);
```

## Working with images
###Defining profiles
Imagine you are making a site with a list of all news-items. Wouldn't it be nice to show the user a thumb of image associated with the news-item? When adding images to the medialibrary, it can create these derived images for you.

You can let the medialibrary know that it should make a derived image by implementing the `getImageProfileProperties()`-method on the model.

```php
//in your news model
public function getImageProfileProperties()
{
    return [
        'list'=> ['w'=>200, 'h'=>200],
        'detail'=> ['w'=>1600, 'h'=>800],
    ];
}
```

When associating a jpg-file or png-file to the library it will, besides storing the original image, create a derived image for every key in the array. Of course "list" and "detail" are only examples. You can use any string you like as a key. The example above uses a width and height manipulation.

Internally the medialibrary uses [Glide](http://glide.thephpleague.com) to manipulate images. You can use any parameter you find in [their image API](http://glide.thephpleague.com/api/size/).

If your Laravel app is configured to use queues, the derived images will be generated in a queued job. If you don't want this you can specify use the `shouldBeQueued`-option like this:

```php
//in your news model
public function getImageProfileProperties()
{
    return [
        'list'=> ['w'=>200, 'h'=>200, 'shouldBeQueued' => false],
        'detail'=> ['w'=>1600, 'h'=>800, 'shouldBeQueued' => false],
    ];
}
```

By default the derived images will be stored as `jpg`'s. If you want another image format you can specify `png`or `gif` using the `fm`-key in an an imageprofile. For example:
```php
//in your news model
public function getImageProfileProperties()
{
    return [
        'list'=> ['w'=>200, 'h'=>200, 'shouldBeQueued' => false, 'fm' => 'png'],
        'detail'=> ['w'=>1600, 'h'=>800, 'shouldBeQueued' => false, 'fm' => 'gif'],
    ];
}
```

###Retrieving derived images
Here's example that shows you how to get the url's to the derived images:

```php
$newsItem = News::find(3);
$collectionName = 'anotherFineCollection';
$newsItem->addMedia($pathToAFile, $collectionName);

$mediaItems = $newsItem->getMedia($collectionName)
$urlToOriginalUploadedImage = $mediaItems[0]->getOriginalURL();
$urlToListImage = $mediaItems[0]->getURL('list');
$urlToDetailImage = $mediaItems[0]->getURL('detail');
```

Because getting an url to the first mediaItem in a collection is such a common scenario this convenience-method is provided:

```php
$urlToFirstListImage = $newsItem->getFirstMediaURL('list');
```


###Generate a derived image without defining a profile
You can also generate a derived image on the fly by passing an array with parameters from the [Glide API](http://glide.thephpleague.com/api/size/) into the `getURL`-function:

```php
$mediaItem->getURL(['w' => 450, 'h' => 200, 'filt' => 'greyscale']);
```

This call will generate an url that, when hit, will generate the derived image.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email [freek@spatie.be](mailto:freek@spatie.be) instead of using the issue tracker.

## Credits

- [Freek Van der Herten](https://github.com/freekmurze)
- [Matthias De Winter](https://github.com/MatthiasDeWinter)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
