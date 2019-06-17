---
title: Defining conversions
weight: 1
---

When adding files to the medialibrary it can automatically create derived versions such as thumbnails and banners.

Media conversions will be executed whenever  a `jpg`, `png`, `svg`, `pdf`, `mp4 `, `mov` or `webm` file is added to the medialibrary. By default, the conversions will be saved as a `jpg` files. This can be overwritten using the `format()` or `keepOriginalImageFormat()` methods.

Internally, [spatie/image](https://docs.spatie.be/image/v1/) is used to manipulate the images. You can use [any manipulation function](https://docs.spatie.be/image) from that package.

## A single conversion

You should add a method called `registerMediaConversions` to your model. In that model you can define the media conversion. Here's an example:

```php
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\Models\Media;
use Spatie\MediaLibrary\HasMedia\HasMedia;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;

class NewsItem extends Model implements HasMedia
{
    use HasMediaTrait;

    public function registerMediaConversions(Media $media = null)
    {
        $this->addMediaConversion('thumb')
              ->width(368)
              ->height(232)
              ->sharpen(10);
    }
}
```

Let's add an image to the medialibrary.

```php
$media = NewsItem::first()->addMedia($pathToImage)->toMediaCollection();
```

Besides storing the original item, the medialibrary also has created a derived image.

```php
$media->getPath();  // the path to the where the original image is stored
$media->getPath('thumb') // the path to the converted image with dimensions 368x232

$media->getUrl();  // the url to the where the original image is stored
$media->getUrl('thumb') // the url to the converted image with dimensions 368x232
```

## Using multiple conversions

You can register as many media conversions as you want

```php
// in your model
use Spatie\Image\Manipulations;

    public function registerMediaConversions(Media $media = null)
    {
        $this->addMediaConversion('thumb')
              ->width(368)
              ->height(232)
              ->sharpen(10);

        $this->addMediaConversion('old-picture')
              ->sepia()
              ->border(10, 'black', Manipulations::BORDER_OVERLAY);
    }
```

Use the conversions like this:

```php
$media->getUrl('thumb') // the url to the thubmnail
$media->getUrl('old-picture') // the url to the sepia, bordered version
```

## Performing conversions on specific collections

By default a conversion will be performed on all files regardless of which [collection](/laravel-medialibrary/v7/working-with-media-collections/simple-media-collections) is used.  Conversions can also be performed on all specific collections by adding a call to  `performOnCollections`.

This is how that looks like in the model:

```php
// in your model
    public function registerMediaConversions(Media $media = null)
    {
        $this->addMediaConversion('thumb')
              ->width(368)
              ->height(232)
              ->performOnCollections('images', 'downloads');
    }
```


```php
// a thumbnail will be generated for this media item
$media = $newsItem->addMedia($pathToImage)->toMediaCollection('images');
$media->getUrl('thumb') // the url to the thubmnail

//but not for this one
$media = $newsItem->addMedia($pathToImage)->toMediaCollection('other collection');
$media->getUrl('thumb') // returns ''
```

## Queuing conversions

By default, a conversion will be added to the queue that you've [specified in the configuration](https://docs.spatie.be/laravel-medialibrary/v7/installation-setup). If you want your image to be created directly (and not on a queue) use `nonQueued` on a conversion.

```php
// in your model
public function registerMediaConversions(Media $media = null)
{
    $this->addMediaConversion('thumb')
            ->width(368)
            ->height(232)
            ->nonQueued();
}
```


## Using model properties in a conversion

When registering conversions inside the `registerMediaConversions` function you won't have access to your model properties by default. If you want to use a property of your model as input for defining a conversion you must set `registerMediaConversionsUsingModelInstance` to `
true` on your model.

```php
// in your model
    public $registerMediaConversionsUsingModelInstance = true;

    public function registerMediaConversions(Media $media = null)
    {
        $this->addMediaConversion('thumb')
              ->width($this->width)
              ->height($this->height)
              ->performOnCollections('images', 'downloads');
    }
```

Be aware that this can lead to a hit in performance. When processing media the medialibrary has to perform queries to fetch each separate model.
