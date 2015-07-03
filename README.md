# Associate files with Eloquent models

[![Latest Version](https://img.shields.io/github/release/spatie/laravel-medialibrary.svg?style=flat-square)](https://github.com/spatie/laravel-medialibrary/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/27cf455a-0555-4bcf-abae-16b5f7860d09/mini.png)](https://insight.sensiolabs.com/projects/27cf455a-0555-4bcf-abae-16b5f7860d09)
[![Quality Score](https://img.shields.io/scrutinizer/g/spatie/laravel-medialibrary.svg?style=flat-square)](https://scrutinizer-ci.com/g/spatie/laravel-medialibrary)
[![Total Downloads](https://img.shields.io/packagist/dt/spatie/laravel-medialibrary.svg?style=flat-square)](https://packagist.org/packages/spatie/laravel-medialibrary)

This Laravel 5.1 package provides an easy way to associate all sorts of files with Eloquent models. Storage of the files
is handled by [Laravel's Filesystem](http://laravel.com/docs/5.1/filesystem), 
so you can easily use something like S3. Additionally the package can create image manipulations on images that have been added in the medialibrary.

## Requirements
To create derived images [GD](http://php.net/manual/en/book.image.php) should be installed on your server. If you want to create
thumbnails of pdf's you should also install [Imagick](http://php.net/manual/en/imagick.setresolution.php).

## Installation

You can install this package via composer using:

```bash
composer require spatie/laravel-medialibrary
```

Next, you must install the service provider. 

```php
// config/app.php
'providers' => [
    ...
    'Spatie\MediaLibrary\MediaLibraryServiceProvider',
];
```

You can publish the migration with:
```bash
php artisan vendor:publish --provider="Spatie\MediaLibrary\MediaLibraryServiceProvider" --tag="migrations"
```

After the migration has been published you can create the media-table you by running the migrations.

```bash
php artisan migrate
```

This is the contents of the published config file:

```php

return [

    /*
     * The filesystems you on which to store added files and derived images. Choose one or more
     * of the filesystems you configured in app/config/filesystems.php
     */
    'filesystem' => 'media',

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

And finally you should add a disk  to `app/config/filesystems.php`. This would be a typical configuration:

```php
    ...
	'disks' => [

        'media' => [
            'driver' => 'local',
            'root'   => public_path().'/media',
        ],
    ...    
```


        
All files of the medialibrary will be stored on that disk.        

If you are planning on working with the image manipulations you should configure a queue on your service with the name specified in the config file.

## Basic usage


In essence the medialibrary is very simple. All files added to the library are associated a record in the db.
All examples in this readme assume that you have already have a news model set up. 
Of course this package will work with any Eloquent model.

To relate media to a model, the model must implement this interface and trait:

```php
namespace App\Models;

use Spatie\MediaLibrary\Traits\HasMedia;
use Spatie\MediaLibrary\Traits\HasMediaInterface;

class News extends Model implements HasMediaInterface
{

	use HasMedia;
   ...
}
```


###Associating a file
Using the facade you can add items to the library like this:
```php
$newsItem = News::find(1);
$news->addMedia($pathToFile, 'images')
```
The file will now be associated with the `newsItem`. Adding a file will move your file to a configured disk.
In the above example the file was added to the `images`-collection of the `newsItem`. You can give a collection
any name you want.

###Retrieving media

To retrieve files you can use the ```getMedia```-method:
```php
$mediaItems = $news->getMedia('images');
```

The method returns an collection with `Media`-objects.

You can retrieve the url to the file associated with `Media`-object with:

```php
$publicUrl = $mediaItems[0]->getUrl();
```

A media-object also has a name. By default it is the name of the file.
```php
echo $mediaItems[0]->name //display the name
$mediaItems[0]->name = 'newName'
$mediaItems[0]->save(); //the new name gets saved. Activerecord ftw!
```

You can also get the size of the file:
```php
$mediaItems[0]->getSize() // returns the size in bytes
$mediaItems[0]->getHumanReadableSize() //returns the size in a human readable form (eg. 1,5 MB)
```

You can remove something from the library simply calling `delete` on the media-object:
```php
$mediaItems[0]->delete()
```

If you delete a media item all related files will be removed from the filesystem.

Deleting a model with associated media will also delete all associated files.
```php
$newsItem->delete(); //all associated files will be deleted as well
```

If you want to remove all associated media in a specific collection you can use this method:
```
$newsItem->clearMediaCollection('images') // all media in the images-collection will be deleted
```

## Working with images
###Defining conversions
Imagine you are making a site with a list of all news-items. Wouldn't it be nice to show 
the user a thumb of image associated with the news-item? When adding an image to a media collection, 
these derived images can be created automatically.

You can let the package know that it should create a derived by registering a media conversion on the model.

```php
//in your news model
public function registerMediaConversions()
{
    $this->addMediaConversion('thumb')
        ->setManipulations(['w' => 368, 'h' => 232'])
        ->performOnCollections('images');
}
```

When associating a jpg-, png-, or pdf-file, to the model the package will, besides storing the original image, 
create a derived image for every media conversion that was added. By default, the output will be a jpg-file. 

If you want another image format you can specify `png`or `gif` using the `fm`-key in an an imageprofile.

By default, a conversion will be performed on the queue that you specified 
in the configuration.  

Internally [Glide](http://glide.thephpleague.com) is used to manipulate images. You can use any parameter you find 
in [their image API](http://glide.thephpleague.com/api/size/).

You can add as many conversions on a model as you want. Media conversion can also be performed on multiple collections. If you pass
`*` to  `performOnCollections` the conversion will be applied to every collection.

Here's an example where some of these options are demonstrated.

```php
//in your news model
public function registerMediaConversions()
{
    $this->addMediaConversion('thumb')
        ->setManipulations(['w' => 368, 'h' => 232,'filt' => 'greyscale', 'fm' => 'png'])
        ->performOnCollections('images', 'anotherCollection'); // the conversion will be performed on multiple collections
        ->nonQueued(); // this conversion will not be queued
        
    //a second media conversion    
    $this->addMediaConversion('adminThumb')
        ->setManipulations(['w' => 50, 'h' => 50, 'sharp'=> 15])
        ->performOnCollections('*'); // perform the conversion on every collection
}
```

###Retrieving derived images
Here's example that shows you how to get the url's to the derived images:

```php
$mediaItems = $news->getMedia('images');
$mediaItems[0]->getUrl('thumb');
```

Because getting an url to the first mediaItem in a collection is such a common scenario the `getFirstMediaUrl`- convenience-method is provided. The first parameter is the name of the collection, the second the name of an imageprofile.

```php
$urlToFirstListImage = $newsItem->getFirstMediaUrl('images', 'thumb');
```

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

When saving the media object, the package will regenerate all files an use the saved manipulation
as the first manipulation when creating a derived image.

For example:
```php
//in your news model
...
public function registerMediaConversions()
{
    $this->addMediaConversion('thumb')
        ->setManipulations(['w' => 500, 'h'=>500])
        ->performOnCollections('myCollection');
}
...

//somewhere in your project and assuming you've already added some images in myCollection.
$mediaItems = $news->getMedia('images');
$mediaItems[0] = $news->manipulations = ['thumb' => ['mode' => 'filt' => 'greyscale']]
$mediaItems[0]->save(); //this will cause the thumb conversion to be regenerated. The
```
Calling `save()` in this example will regenerate the thumb-image. The output will be a
greyscale image that has a width and height of 500 pixels.



 
 









## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email [freek@spatie.be](mailto:freek@spatie.be) instead of using the issue tracker.

## Credits

- [Freek Van der Herten](https://github.com/freekmurze)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
