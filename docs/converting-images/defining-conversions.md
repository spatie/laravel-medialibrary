---
title: Defining conversions
weight: 1
---

Imagine making a site with a list of all news items. Wouldn't it be nice to show the user a thumbnail of the image associated with the news item? When adding an image to a media collection, these derived images can be created automatically.

If you want to use this functionality your models should implement the `HasMediaConversions` interface instead of `HasMedia`. This interface expects an implementation of the `registerMediaConversions` method:

```php
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia\Interfaces\HasMediaConversions;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;

class NewsItem extends Model implements HasMediaConversions
{
    use HasMediaTrait;

    public function registerMediaConversions()
    {
        $this->addMediaConversion('thumb')
             ->setManipulations(['w' => 368, 'h' => 232])
             ->performOnCollections('images');
    }
}
```

When associating a jpg-, png-, or pdf-file, the package will—besides storing the original image—create a derived image for every media conversion that was added. By default, the output will be saved as a jpg-file.

Internally, [Glide](http://glide.thephpleague.com/) is used to manipulate the images. You can use any parameter from their image API. So if you want to output to another image format you 
can specify png or gif using the `fm`-key in an image profile.

By default, a conversion will be added to the queue that you've specified in the configuration. You can avoid the usage of the queue by calling `nonQueued` on a conversion.

You can add as many conversions on a model as you'd like. Conversions can also be performed on all collections by dropping the `performOnCollections`-call, or passing "*" as the collections parameter.

## Examples

```php
// In your NewsItem model

public function registerMediaConversions()
{
    // Perform a resize and filter on images from the 'images' and 'anotherCollection' collections
    // and save them as png files.
    $this->addMediaConversion('thumb')
         ->setManipulations(['w' => 368, 'h' => 232, 'filt' => 'greyscale', 'fm' => 'png'])
         ->performOnCollections('images', 'anotherCollection')
         ->nonQueued();

    // Perform a resize and sharpen on every collection
    $this->addMediaConversion('adminThumb')
         ->setManipulations(['w' => 50, 'h' => 50, 'sharp'=> 15])
         ->performOnCollections('*');

    // Perform a resize on every collection
    $this->addMediaConversion('big')
         ->setManipulations(['w' => 500, 'h' => 500]);
}
```

## Convenience Methods

Instead of specifying the glide parameters in the `setManipulations` method, you can also you use the built-in convenience methods.

This media conversion:

```php
$this->addMediaConversion('thumb')
     ->setManipulations(['w' => 500]);
```

is equivalent to:

```php
$this->addMediaConversion('thumb')
     ->setWidth(500);
```

For a list of all the convenience methods, visit the [Defining Conversions page in the API docs](/laravel-medialibrary/v3/api/defining-conversions/).
