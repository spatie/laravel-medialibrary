---
title: Getting started with responsive images
weight: 1
---

Websites are viewed on various devices with widely differing screen sizes and connection speeds. When serving images it's best not to use the same image for all devices. A large image might be fine on a desktop computer with a fast internet connection, but on a small mobile device with limited bandwith, the download might take a long time.

The medialibrary has support for generating the necessary files and html markup for responsive images. In addition the medialibrary also has support for progressive image loading.

Try a [demo of the concept](/laravel-medialibrary/v7/responsive-images/demo) here.

### Introducing the srcset attribute

The most common way to display a picture is by using an `img` element with a `src` attribute.

```html
<img src="my-image.jpg">
```

Using this markup the browser will always display `my-image.jpg` regardless of screen size.

As described in the [HTML specification](https://html.spec.whatwg.org/multipage/embedded-content.html#attr-img-srcset) you can also use a `srcset` attribute to indicate different versions of your image and their respective width. 

```html
<img src="large.jpg"
     srcset="large.jpg 2400w, medium.jpg 1200w, small.jpg 600w">
```

### A pragmatic sizes approach

The `srcset` attribute is commonly accompanied by the `sizes` attribute to tell the browser beforehand how a picture will be rendered for different breakpoints.

```html
<img src="large.jpg"
     srcset="large.jpg 2400w, medium.jpg 1200w, small.jpg 600w"
     sizes="(min-width: 1200px) 50vw,
            100vw">
```
When using `srcset` and `sizes`, the browser will automatically figure out which image is best to use. 

Say your browser is 1200 pixels wide, the `sizes` attribute demands for an image half the size (50vw = 600px). You'll end up with the `small.jpg` version. But presume you have a retina screen with pixel density 2, the browser knows we'd need the `medium.jpg` to render the image crisply.

The `sizes` attribute requires a lot of work though: you'd need to co-ordinate between your responsive CSS and the output HTML to set the right `sizes` for every image and every layout scenario.
If you leave out the `sizes` attribute, the browser will presume that the image will be rendered full width, which is also not optimal in many cases — eg. thumbnails.

Now the medialibrary takes a pragmatic approach in this, so you don't ever have to think about the `sizes` attribute and can experiment freely with different page layouts: we set the `sizes` intially to `1px` to load the smallest picture first, and after load adjust `sizes` to the rendered width of the image with JavaScript. 
We set this width in a `vw` value: if you make your browser wider, an even better version will be loaded. 

At the same time we can use this technique to use this smallest picture as the placeholder.

### Progressive image loading

When visiting a [Medium](https://medium.com/) blog you might have noticed (on a slower connection) that before a full image is displayed a blurred version of the image is shown. The blurred image is replace by a high res one as soon as that big version has been downloaded. The blurred image is actually a very tiny image that's being sized up.

The advantage of displaying a blurred version is that a visitor has a hint of what is going to be displayed very early on and that your page layout is ready right away.

The medialibrary comes with support for progressive image loading out of the box. The tiny blurred image will automatically be generated whenever you leverage responsive images. By replacing the `sizes` attribute on load with JavaScript, the tiny placeholder will be swapped with a bigger version as soon as it is downloaded.

This placeholder is base64-encoded as SVG inside the `srcset` attribute, so it is available in the initial response right away without extra network request.
The SVG has the exact same ratio as the original image, so the layout should not flicker during the swap.

If you want to leverage responsive images but don't want the progressive image loading, you can set the `responsive_images.use_tiny_placeholders` key in the `medialibrary` config file to `false`.

### Generating the necessary images

In the `srcset` attribute of `img` various image files can be specified. The medialibrary can automatically generate those images for you.

When adding an images to the medialibrary simply use the `withResponsiveImages` function.

```php
$yourModel
   ->addMedia($yourImageFile)
   ->withResponsiveImages()
   ->toMediaCollection();
```

Behind the scenes the medialibrary will generate multiple size variations of your image. To learn which variations are generated and how to customize head over [here](/laravel-medialibrary/v7/responsive-images/using-your-own-width-calculator).


### Displaying responsive images

To display a responsive image simply output a `Media` object in a blade view.

```html
{{-- in a Blade view --}}
<h1>My responsive images</h1>
{{ $yourModel->getFirstMedia() }}
```

Per image attached to your model the resulting html will look more or less like this:
```html
<img srcset="/media/1/responsive-images/testimage___medialibrary_original_188_105.png 188w, /media/1/responsive-images/testimage___medialibrary_original_158_88.png 158w, /media/1/responsive-images/testimage___medialibrary_original_132_74.png 132w, /media/1/responsive-images/testimage___medialibrary_original_110_61.png 110w, data:image/svg+xml;base64,PCFET0NUWVBFIHN2ZyBQVUJMSUMgIi0vL1czQy8vRFREIFNWRyAxLjEvL0VOIiAiaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkIj4KPHN2ZyB2ZXJzaW9uPSIxLjEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHhtbDpzcGFjZT0icHJlc2VydmUiIHg9IjAiCiB5PSIwIiB2aWV3Qm94PSIwIDAgMTkyMCAxMDgwIj4KCTxpbWFnZSB3aWR0aD0iMTkyMCIgaGVpZ2h0PSIxMDgwIiB4bGluazpocmVmPSJkYXRhOmltYWdlL2pwZWc7YmFzZTY0LC85ai80QUFRU2taSlJnQUJBUUFBQVFBQkFBRC8vZ0E3UTFKRlFWUlBVam9nWjJRdGFuQmxaeUIyTVM0d0lDaDFjMmx1WnlCSlNrY2dTbEJGUnlCMk9UQXBMQ0J4ZFdGc2FYUjVJRDBnT1RBSy85c0FRd0FEQWdJREFnSURBd01EQkFNREJBVUlCUVVFQkFVS0J3Y0dDQXdLREF3TENnc0xEUTRTRUEwT0VRNExDeEFXRUJFVEZCVVZGUXdQRnhnV0ZCZ1NGQlVVLzlzQVF3RURCQVFGQkFVSkJRVUpGQTBMRFJRVUZCUVVGQlFVRkJRVUZCUVVGQlFVRkJRVUZCUVVGQlFVRkJRVUZCUVVGQlFVRkJRVUZCUVVGQlFVRkJRVUZCUVUvOEFBRVFnQUVnQWdBd0VpQUFJUkFRTVJBZi9FQUI4QUFBRUZBUUVCQVFFQkFBQUFBQUFBQUFBQkFnTUVCUVlIQ0FrS0MvL0VBTFVRQUFJQkF3TUNCQU1GQlFRRUFBQUJmUUVDQXdBRUVRVVNJVEZCQmhOUllRY2ljUlF5Z1pHaENDTkNzY0VWVXRId0pETmljb0lKQ2hZWEdCa2FKU1luS0NrcU5EVTJOemc1T2tORVJVWkhTRWxLVTFSVlZsZFlXVnBqWkdWbVoyaHBhbk4wZFhaM2VIbDZnNFNGaG9lSWlZcVNrNVNWbHBlWW1acWlvNlNscHFlb3FhcXlzN1MxdHJlNHVickN3OFRGeHNmSXljclMwOVRWMXRmWTJkcmg0dVBrNWVibjZPbnE4Zkx6OVBYMjkvajUrdi9FQUI4QkFBTUJBUUVCQVFFQkFRRUFBQUFBQUFBQkFnTUVCUVlIQ0FrS0MvL0VBTFVSQUFJQkFnUUVBd1FIQlFRRUFBRUNkd0FCQWdNUkJBVWhNUVlTUVZFSFlYRVRJaktCQ0JSQ2thR3h3UWtqTTFMd0ZXSnkwUW9XSkRUaEpmRVhHQmthSmljb0tTbzFOamM0T1RwRFJFVkdSMGhKU2xOVVZWWlhXRmxhWTJSbFptZG9hV3B6ZEhWMmQzaDVlb0tEaElXR2g0aUppcEtUbEpXV2w1aVptcUtqcEtXbXA2aXBxckt6dExXMnQ3aTV1c0xEeE1YR3g4akp5dExUMU5YVzE5aloydUxqNU9YbTUranA2dkx6OVBYMjkvajUrdi9hQUF3REFRQUNFUU1SQUQ4QStRZkVPaE1sd05veUs2andibzhjZHUyOEFaSGVvdkxlOWZMOUtTNFM1Z1haREp0cnFxWXlsVGJsSGNpblNyVmw3S1pTOFhhSWthbVJlUlhFU1FCVHhYcC8yS1M0MHIvU01zUUs1TzQ4T003c1VIQjZDdDNWcFZLZnRObVJTOXRTcmV4ZXlPcnRmOVZTUy82NUtLSytHcS94WkgwK0czUnRPUDhBaVduNlZsd0FiUnhSUlhxUi9nSTRLdjhBdkQ5VC85az0iPgoJPC9pbWFnZT4KPC9zdmc+ 32w" onload="if(this.dataset.sized===undefined){this.sizes=Math.ceil(this.getBoundingClientRect().width/window.innerWidth*100)+'vw';this.dataset.sized=''}" sizes="1px" src="/media/1/testimage.png" data-sized="">
```

### Generating responsive images for conversions

You can also generate responsive images for any [image conversions](https://docs.spatie.be/laravel-medialibrary/v7/converting-images/defining-conversions) you define. Simply use `withResponsiveImages` when defining a conversion.

Here's an example where we define a conversion to make a greyscale version and generate responsive, greyscaled images.


```php
namespace App;

use Spatie\MediaLibrary\HasMedia\HasMedia;
use Spatie\MediaLibrary\Models\Media;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;

class YourModel extends Model implements HasMedia
{
    use HasMediaTrait;

    /**
     * Register the conversions that should be performed.
     *
     * @return array
     */
    public function registerMediaConversions(Media $media = null)
    {
        $this
            ->addMediaConversion('my-conversion')
            ->greyscale()
            ->withResponsiveImages();
    }
}
```

To generate the converted greyscale file and the responsive images simply add a file:


```php
$yourModel->addMedia($yourImage)->toMediaCollection();
```

In a controller you can pass a media object to a view.

```php
// in a controller

public function index()
{
    $media = $yourModel->getFirstMedia();

    return view('my-view', compact('media'));
}
```

To generate the `img` tag with the filled in `srcset` simply use `$media` as an invokable. Use the name of your conversion as the first parameter.

```html
<h1>My greyscale responsive image</h1>
{{ $media('my-conversion') }}
```
