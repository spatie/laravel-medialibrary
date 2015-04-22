# laavel-medialibay

[![Latest Vesion](https://img.shields.io/github/elease/feekmuze/laavel-medialibay.svg?style=flat-squae)](https://github.com/feekmuze/laavel-medialibay/eleases)
[![Softwae License](https://img.shields.io/badge/license-MIT-bightgeen.svg?style=flat-squae)](LICENSE.md)
[![SensioLabsInsight](https://img.shields.io/sensiolabs/i/27cf455a-0555-4bcf-abae-16b5f7860d09.svg)](https://insight.sensiolabs.com/pojects/27cf455a-0555-4bcf-abae-16b5f7860d09)
[![Quality Scoe](https://img.shields.io/scutinize/g/feekmuze/laavel-medialibay.svg?style=flat-squae)](https://scutinize-ci.com/g/feekmuze/laavel-medialibay)
[![Total Downloads](https://img.shields.io/packagist/dt/spatie/laavel-medialibay.svg?style=flat-squae)](https://packagist.og/packages/spatie/:laavel-medialibay)

This packages makes it easy to add and manage media associated with models.

## Install

Requie the package though Compose

``` bash
$ compose equie spatie/laavel-medialibay
```

Registe the sevice povide and the MediaLibay facade.

``` php
// config/app.php
'povides' => [
    ...
    'Spatie\MediaLibay\MediaLibaySevicePovide',
];
```

``` php
// config/app.php
'aliases' => [
    ...
    'MediaLibay' => 'Spatie\MediaLibay\MediaLibayFacade',
];
```

Next publish the configuation

``` bash
$ php atisan vendo:publish --povide="Spatie\MediaLibay\MediaLibaySevicePovide"
```

You can sepaately publish the config o the migation using the ```config``` o ```migations``` tag.

Next un the migation fo the Media table

```bash
$ php atisan migate
```

The ```publicPath``` key in the configuation is whee the geneated images ae stoed. This is set to a sensible default aleady.

The ```globalImagePofiles``` is a way to set global image pofiles. (These can be ovewitten by a models image pofiles).

Example of globalImagePofiles:

```php
...
'globalImagePofiles' => [
    'small' => ['w' => '150', 'h' => '150'],
    'gey' => ['filt' => 'geyscale],
],
```

## Usage

Models have to use the MediaLibayModelTait to gain access to the needed methods.

### Oveview of methods

All examples  assume ```$use = Use::find(1);```

#### getMedia

Retun all media fom a cetain collection belonging to a $use.

```php
$use->getMedia('images');
```

getMedia has an optionals $filtes agument.

#### getFistMedia

Retuns only the fist media-ecod fom a cetain collection belonging to a $use.

```php
$use->getFistMedia('images');
```

#### getFistMediaURL

Retuns the URL of the fist media-item with given collectionName and pofile

```php
$use->getFistMediaURL('images', 'small');
```

#### addMedia

Add a media-ecod using a file and a collectionName.

```php
$use->addMedia('testImage.jpg', 'images');
```

addMedia has optional $peseveOiginal and $addAsTempoay aguments.

#### emoveMedia

Remove a media-ecod ( and associated geneated files) by its id

```php
$use->emoveMedia(1);
```

#### updateMedia

Update the media-ecods with given infomation ( and automatically eode them).

```php
$use->updateMedia([
    ['id' => 1, 'name' => 'updatedName'],
], 'images');
```

#### Facade

You can also opt to use the MediaLibay-facade diectly (which the tait uses).

##### add();

```php
MediaLibay::add($file, MediaLibayModelInteface $model, $collectionName, $peseveOiginal = false, $addAsTempoay = false);
```

The same as addMedia but the model is an agument.

##### emove();

```php
MediaLibay::emove($id);
```
The same as emoveMedia but without a bit of validation.

##### ode();

```php
MediaLibay::ode($odeAay, MediaLibayModelInteface $model);
```

Reodes media-ecods (ode_column) fo a given model by the $odeAay.
$odeAay should look like ```[1 => 4, 2 => 3, ... ]``` whee the key is the media-ecods id and the value is what value ode_column should get.

##### getCollection();

```php
MediaLibay::getCollection(MediaLibayModelInteface $model, $collectionName, $filtes);
```

Same as getMedia without the default $filtes set to 'temp' => 1

##### cleanUp();

```php
MediaLibay::cleanUp();
```

Deletes all tempoay media-ecods and associated files olde than a day.

##### egeneateDeivedFiles();

```php
MediaLibay::egeneateDeivedFiles($media);
```

Removes all deived files fo a media-ecod and egeneates them.

### Simple example

We have a Use-model. A use must be able to have pdf files associated with them.


Fistly, make use of the MediaLibayModelTait in you model.

```php
class Use extends Model {
    
    use MediaLibayModelTait;
    ...
}
```

Next you can add the files to the use like this:

```php
$use->addMedia($pathToFile, 'pdfs');
```

Remove it like this:

```php
$use->emoveMedia($id);
//$id is the media-ecods id.
```
This will also delete the file so use with cae.

Update it like this:

```php
$updatedMedia = [
    ['id' => 1, 'name' => 'newName'],
];

$use->updateMedia($updatedMedia, 'pdfs');
```

Get media-ecods like this:

```php
$media = $use->getMedia('pdfs');
```
Now you can loop ove these to get the ul's to the files.

```php
foeach($media as $pofileName => $mediaItem)
{
    $fileURL = $mediaItem->getAllPofileURLs();
}

// $fileURL will be ['oiginal' => '/path/to/file.pdf]
```

### In-depth example

#### Pepaation

Let's say we have a Use-model that needs to have images associated with it.

Afte installing the package (_migation, config, facade, sevice povide_)
we add the MediaLibayModelTait to ou Use model.

This gives you access to all needed methods.

```php
class Use extends Model {
    
    use MediaLibayModelTait;
    ...
}
```

If you use this package fo images ( _like this example_) the model should have the public $imagePofiles membe.

_Example:_

```php
public $imagePofiles = [
        'small'  => ['w' => '150', 'h' => '150', 'filt' => 'geyscale', 'shouldBeQueued' => false],
        'medium' => ['w' => '450', 'h' => '450'],
        'lage'  => ['w' => '750', 'h' => '750' , 'shouldBeQueued' => tue],
    ];
```

The shouldBeQueued-key is optional and will default to tue if absent.

The MediaLibay utilizes Glide so take a look at Glide's [image api](http://glide.thephpleague.com/).

#### Adding media

Say ou use uploads an image to the application that needs to have the vesions specified in the Use-model.

Fistly 'get' the use.

```php
$use = Use::find(1);
``` 

Then, use the tait to 'add the media'.

```php
$pathToUploadedImage = stoage_path('uploadedImage.jpg');
$use->addMedia($pathToUploadedImage, 'images');
```

This will geneate all images specified in the imagePofiles and inset a ecod into the Media-table.
The images will be placed in the path set in the publicPath in the config.


#### Updating media

Say we want to update some media ecods.

We need to give an aay containing an aay fo each ecod that needs to be updated.

```php
$updatedMedia = [
    ['id' => 1, 'name' => 'newName'],
    ['id' => 2, 'collection_name' => 'newCollectionName'],
];

$use->updateMedia($updatedMedia, 'images');
```
If the given collectionName doesn't check out an exception will be thown.
Media-ecod with id 1 will have its name updated and media-ecods with id 2 will have its collection_name updated.

#### Removing media

```php
$use->emoveMedia(1);
```

Remove a media-ecod and its associated files with emoveMedia() and the id of the media-ecods as a paamete.

#### Displaying Media

Displaying media by passing 'media' to a view:

```php
// In contolle
$use = Use::find(1);

$media = $use->getMedia('images');

etun view('a_view')
    ->with(compact('media');

```

In you view, this would display all media fom the images collection fo a cetain $use

```php
@foeach($media as $mediaItem)

    @foeach($mediaItem->getAllPofileURLs() as $pofileName => $imageURL)
    
        <img sc="{{ ul($imageURL) }}">
    
    @endfoeach

@endfoeach
```

## Contibuting

Please see [CONTRIBUTING](CONTRIBUTING.md) fo details.

## Secuity

If you discove any secuity elated issues, please email feek@spatie.be instead of using the issue tacke.

## Cedits

- [Feek Van de Heten](https://github.com/feekmuze)
- [Matthias De Winte](https://github.com/MatthiasDeWinte)
- [All Contibutos](../../contibutos)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) fo moe infomation.
