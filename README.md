# laravel-medialibrary

[![Latest Version](https://img.shields.io/github/release/freekmurze/laravel-medialibrary.svg?style=flat-square)](https://github.com/freekmurze/laravel-medialibrary/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![SensioLabsInsight](https://img.shields.io/sensiolabs/i/27cf455a-0555-4bcf-abae-16b5f7860d09.svg)](https://insight.sensiolabs.com/projects/27cf455a-0555-4bcf-abae-16b5f7860d09)
[![Quality Score](https://img.shields.io/scrutinizer/g/freekmurze/laravel-medialibrary.svg?style=flat-square)](https://scrutinizer-ci.com/g/freekmurze/laravel-medialibrary)
[![Total Downloads](https://img.shields.io/packagist/dt/spatie/laravel-medialibrary.svg?style=flat-square)](https://packagist.org/packages/spatie/:laravel-medialibrary)

This packages makes it easy to add and manage media associated with models.

## Install

Require the package through Composer

``` bash
$ composer require spatie/laravel-medialibrary
```

Register the service provider and the MediaLibrary facade.

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

Next publish the configuration

``` bash
$ php artisan vendor:publish --provider="Spatie\MediaLibrary\MediaLibraryServiceProvider"
```

You can separately publish the config or the migration using the ```config``` or ```migrations``` tag.

Next run the migration for the Media table

```bash
$ php artisan migrate
```

The ```publicPath``` key in the configuration is where the generated images are stored. This is set to a sensible default already.

The ```globalImageProfiles``` is a way to set global image profiles. (These can be overwritten by a models image profiles).

Example of globalImageProfiles:

```php
...
'globalImageProfiles' => [
    'small' => ['w' => '150', 'h' => '150'],
    'grey' => ['filt' => 'greyscale],
],
```

## Usage

Models have to use the MediaLibraryModelTrait to gain access to the needed methods.

### Overview of methods

All examples  assume ```$user = User::find(1);```

#### getMedia

Return all media from a certain collection belonging to a $user.

```php
$user->getMedia('images');
```

getMedia has an optionals $filters argument.

#### getFirstMedia

Returns only the first media-record from a certain collection belonging to a $user.

```php
$user->getFirstMedia('images');
```

#### getFirstMediaURL

Returns the URL of the first media-item with given collectionName and profile

```php
$user->getFirstMediaURL('images', 'small');
```

#### addMedia

Add a media-record using a file and a collectionName.

```php
$user->addMedia('testImage.jpg', 'images');
```

addMedia has optional $preserveOriginal and $addAsTemporary arguments.

#### removeMedia

Remove a media-record ( and associated generated files) by its id

```php
$user->removeMedia(1);
```

#### updateMedia

Update the media-records with given information ( and automatically reorder them).

```php
$user->updateMedia([
    ['id' => 1, 'name' => 'updatedName'],
], 'images');
```

#### Facade

You can also opt to use the MediaLibrary-facade directly (which the trait uses).

##### add();

```php
MediaLibrary::add($file, MediaLibraryModelInterface $model, $collectionName, $preserveOriginal = false, $addAsTemporary = false);
```

The same as addMedia but the model is an argument.

##### remove();

```php
MediaLibrary::remove($id);
```
The same as removeMedia but without a bit of validation.

##### order();

```php
MediaLibrary::order($orderArray, MediaLibraryModelInterface $model);
```

Reorders media-records (order_column) for a given model by the $orderArray.
$orderArray should look like ```[1 => 4, 2 => 3, ... ]``` where the key is the media-records id and the value is what value order_column should get.

##### getCollection();

```php
MediaLibrary::getCollection(MediaLibraryModelInterface $model, $collectionName, $filters);
```

Same as getMedia without the default $filters set to 'temp' => 1

##### cleanUp();

```php
MediaLibrary::cleanUp();
```

Deletes all temporary media-records and associated files older than a day.

##### regenerateDerivedFiles();

```php
MediaLibrary::regenerateDerivedFiles($media);
```

Removes all derived files for a media-record and regenerates them.

### Simple example

We have a User-model. A user must be able to have pdf files associated with them.


Firstly, make use of the MediaLibraryModelTrait in your model.

```php
class User extends Model {
    
    use MediaLibraryModelTrait;
    ...
}
```

Next you can add the files to the user like this:

```php
$user->addMedia($pathToFile, 'pdfs');
```

Remove it like this:

```php
$user->removeMedia($id);
//$id is the media-records id.
```
This will also delete the file so use with care.

Update it like this:

```php
$updatedMedia = [
    ['id' => 1, 'name' => 'newName'],
];

$user->updateMedia($updatedMedia, 'pdfs');
```

Get media-records like this:

```php
$media = $user->getMedia('pdfs');
```
Now you can loop over these to get the url's to the files.

```php
foreach($media as $profileName => $mediaItem)
{
    $fileURL = $mediaItem->getAllProfileURLs();
}

// $fileURL will be ['original' => '/path/to/file.pdf]
```

### In-depth example

#### Preparation

Let's say we have a User-model that needs to have images associated with it.

After installing the package (_migration, config, facade, service provider_)
we add the MediaLibraryModelTrait to our User model.

This gives you access to all needed methods.

```php
class User extends Model {
    
    use MediaLibraryModelTrait;
    ...
}
```

If you use this package for images ( _like this example_) the model should have the public $imageProfiles member.

_Example:_

```php
public $imageProfiles = [
        'small'  => ['w' => '150', 'h' => '150', 'filt' => 'greyscale', 'shouldBeQueued' => false],
        'medium' => ['w' => '450', 'h' => '450'],
        'large'  => ['w' => '750', 'h' => '750' , 'shouldBeQueued' => true],
    ];
```

The shouldBeQueued-key is optional and will default to true if absent.

The MediaLibrary utilizes Glide so take a look at Glide's [image api](http://glide.thephpleague.com/).

#### Adding media

Say our user uploads an image to the application that needs to have the versions specified in the User-model.

Firstly 'get' the user.

```php
$user = User::find(1);
``` 

Then, use the trait to 'add the media'.

```php
$pathToUploadedImage = storage_path('uploadedImage.jpg');
$user->addMedia($pathToUploadedImage, 'images');
```

This will generate all images specified in the imageProfiles and insert a record into the Media-table.
The images will be placed in the path set in the publicPath in the config.


#### Updating media

Say we want to update some media records.

We need to give an array containing an array for each record that needs to be updated.

```php
$updatedMedia = [
    ['id' => 1, 'name' => 'newName'],
    ['id' => 2, 'collection_name' => 'newCollectionName'],
];

$user->updateMedia($updatedMedia, 'images');
```
If the given collectionName doesn't check out an exception will be thrown.
Media-record with id 1 will have its name updated and media-records with id 2 will have its collection_name updated.

#### Removing media

```php
$user->removeMedia(1);
```

Remove a media-record and its associated files with removeMedia() and the id of the media-records as a parameter.

#### Displaying Media

Displaying media by passing 'media' to a view:

```php
// In controller
$user = User::find(1);

$media = $user->getMedia('images');

return view('a_view')
    ->with(compact('media');

```

In your view, this would display all media from the images collection for a certain $user

```php
@foreach($media as $mediaItem)

    @foreach($mediaItem->getAllProfileURLs() as $profileName => $imageURL)
    
        <img src="{{ url($imageURL) }}">
    
    @endforeach

@endforeach
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
