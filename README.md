# laravel-medialibrary

[![Latest Version](https://img.shields.io/github/release/freekmurze/laravel-medialibrary.svg?style=flat-square)](https://github.com/freekmurze/laravel-medialibrary/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Build Status](https://img.shields.io/travis/freekmurze/laravel-medialibrary/master.svg?style=flat-square)](https://travis-ci.org/freekmurze/laravel-medialibrary)
[![Quality Score](https://img.shields.io/scrutinizer/g/freekmurze/laravel-medialibrary.svg?style=flat-square)](https://scrutinizer-ci.com/g/freekmurze/laravel-medialibrary)
[![Total Downloads](https://img.shields.io/packagist/dt/spatie/laravel-medialibrary.svg?style=flat-square)](https://packagist.org/packages/spatie/:laravel-medialibrary)

This packages makes it easy to add and manage media associated with models.

## Install

Require the package through Composer

``` bash
$ composer require spatie/laravel-medialibrary
```

## Usage

Start by registering the service provider and the MediaLibrary facade.

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

The models which should utilize the MediaLibrary should implement the MediaModelInterface ( to enforce the getImageProfileProperties method)
and use the MediaLibraryModelTrait to gain access to the needed methods.

### Overview of methods

All examples will use $user.
 ( assume ```$user = User::find(1);```)

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

#### addMedia

Add a media-record using a file and a collectionName.

```php
$user->addMedia('testImage.jpg', 'images');
```

addMedia has optional $preserveOriginal and $addAsTemporary arguments.

#### removeMedia

Remove a media-records ( and generated files) by its id

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

### In-depth example

Coming soon

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email :author_email instead of using the issue tracker.

## Credits

- [Freek Van der Herten](https://github.com/freekmurze)
- [Matthias De Winter](https://github.com/MatthiasDeWinter)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.