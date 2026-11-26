---
title: Getting started with responsive images
weight: 1
---

Websites are viewed on all kinds of screens and connections. Sending the same huge image everywhere wastes bandwidth on mobile devices, while sending a tiny image looks blurry on desktop.

Media Library can generate the image variations and HTML markup for responsive images automatically, including progressive image loading.

[Try a demo of the concept here.](/docs/laravel-medialibrary/v11/responsive-images/demo)

## Are you a visual learner?

Here's a video that walks through responsive images.

<iframe width="560" height="315" src="https://www.youtube.com/embed/-RPDEHV6Zlw" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>

Want to see more videos like this? Check out our [free video course on how to use Laravel Media Library](https://spatie.be/courses/discovering-laravel-media-library).

### Introducing the `srcset` attribute

Normally you render an image with a single `src` attribute:

```html
<img src="my-image.jpg">
```

With this markup, every browser downloads `my-image.jpg` regardless of screen size.

As defined in the [HTML specification](https://html.spec.whatwg.org/multipage/embedded-content.html#attr-img-srcset), you can add a `srcset` attribute to list multiple versions of the image alongside their widths:

```html
<img src="large.jpg"
     srcset="large.jpg 2400w, medium.jpg 1200w, small.jpg 600w">
```

### A pragmatic `sizes` approach

`srcset` is usually paired with `sizes` so the browser knows ahead of time how wide the image will appear at each breakpoint:

```html
<img src="large.jpg"
     srcset="large.jpg 2400w, medium.jpg 1200w, small.jpg 600w"
     sizes="(min-width: 1200px) 50vw,
            100vw">
```

With both attributes set, the browser picks the right file on its own.

Say your viewport is 1200px wide: `sizes` asks for `50vw` (600px), so the browser downloads `small.jpg`. On a 2x Retina screen, it knows to grab `medium.jpg` (1200px) so the image stays sharp.

Maintaining `sizes` by hand is tedious, though, because you have to keep your HTML in sync with every responsive CSS breakpoint. Leaving `sizes` out isn't great either, because the browser assumes `100vw` and downloads full-width images even for small thumbnails.

Media Library takes a pragmatic shortcut so you don't have to maintain `sizes` by hand: it renders `sizes="1px"` initially to load the tiny placeholder first, then uses a small inline `onload` script to set `sizes` to the image's actual rendered width in `vw`. If the user widens the browser window, the browser automatically loads a larger version.

### Progressive image loading

On slower connections, you've probably seen sites show a soft, blurred preview of an image before the full version finishes downloading. That preview is just a tiny image scaled up, so the user gets an instant hint of the image and the page layout doesn't jump.

Media Library supports this out of the box. Whenever you generate responsive images, it also creates a tiny blurred placeholder and embeds it as a base64 SVG inside `srcset`. Because it's inline in the HTML, there's no extra network request, and it matches the original aspect ratio so the layout doesn't shift when the real image swaps in.

If you want responsive images without the blurred placeholder, set `responsive_images.use_tiny_placeholders` to `false` in `config/media-library.php`.

> **Note on Content Security Policy (CSP):** Because the placeholder view uses an inline `onload` handler to calculate `sizes`, a strict CSP without `unsafe-inline` for scripts will block it. Setting `use_tiny_placeholders` to `false` removes both the placeholder and the inline script (note that the rendered `<img>` will also omit `sizes`, so browsers default to `100vw` unless you add `sizes` via `attributes()` or publish and customize the view).

### Generating the necessary images

Chain `withResponsiveImages()` (or `withResponsiveImagesIf()`) when adding an image:

```php
$yourModel
    ->addMedia($yourImageFile)
    ->withResponsiveImages()
    // or conditionally:
    ->withResponsiveImagesIf($condition) // accepts a boolean or closure
    ->toMediaCollection();
```

Behind the scenes, Media Library queues a job to generate multiple width variations. See [using your own width calculator](/docs/laravel-medialibrary/v11/responsive-images/using-your-own-width-calculator) to learn how those widths are chosen and how to customize them.

### Displaying responsive images

Output a `Media` model directly in a Blade view:

```blade
{{-- in a Blade view --}}
<h1>My responsive image</h1>
{{ $yourModel->getFirstMedia() }}
```

Per image attached to your model the resulting HTML will look more or less like this:
```html
<img srcset="/media/1/responsive-images/testimage___media_library_original_188_105.png 188w, /media/1/responsive-images/testimage___media_library_original_158_88.png 158w, /media/1/responsive-images/testimage___media_library_original_132_74.png 132w, /media/1/responsive-images/testimage___media_library_original_110_61.png 110w, data:image/svg+xml;base64,PCFET0NUWVBFIHN2ZyBQVUJMSUMgIi0vL1czQy8vRFREIFNWRyAxLjEvL0VOIiAiaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkIj4KPHN2ZyB2ZXJzaW9uPSIxLjEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHhtbDpzcGFjZT0icHJlc2VydmUiIHg9IjAiCiB5PSIwIiB2aWV3Qm94PSIwIDAgMTkyMCAxMDgwIj4KCTxpbWFnZSB3aWR0aD0iMTkyMCIgaGVpZ2h0PSIxMDgwIiB4bGluazpocmVmPSJkYXRhOmltYWdlL2pwZWc7YmFzZTY0LC85ai80QUFRU2taSlJnQUJBUUFBQVFBQkFBRC8vZ0E3UTFKRlFWUlBVam9nWjJRdGFuQmxaeUIyTVM0d0lDaDFjMmx1WnlCSlNrY2dTbEJGUnlCMk9UQXBMQ0J4ZFdGc2FYUjVJRDBnT1RBSy85c0FRd0FEQWdJREFnSURBd01EQkFNREJBVUlCUVVFQkFVS0J3Y0dDQXdLREF3TENnc0xEUTRTRUEwT0VRNExDeEFXRUJFVEZCVVZGUXdQRnhnV0ZCZ1NGQlVVLzlzQVF3RURCQVFGQkFVSkJRVUpGQTBMRFJRVUZCUVVGQlFVRkJRVUZCUVVGQlFVRkJRVUZCUVVGQlFVRkJRVUZCUVVGQlFVRkJRVUZCUVVGQlFVRkJRVUZCUVUvOEFBRVFnQUVnQWdBd0VpQUFJUkFRTVJBZi9FQUI4QUFBRUZBUUVCQVFFQkFBQUFBQUFBQUFBQkFnTUVCUVlIQ0FrS0MvL0VBTFVRQUFJQkF3TUNCQU1GQlFRRUFBQUJmUUVDQXdBRUVRVVNJVEZCQmhOUllRY2ljUlF5Z1pHaENDTkNzY0VWVXRId0pETmljb0lKQ2hZWEdCa2FKU1luS0NrcU5EVTJOemc1T2tORVJVWkhTRWxLVTFSVlZsZFlXVnBqWkdWbVoyaHBhbk4wZFhaM2VIbDZnNFNGaG9lSWlZcVNrNVNWbHBlWW1acWlvNlNscHFlb3FhcXlzN1MxdHJlNHVickN3OFRGeHNmSXljclMwOVRWMXRmWTJkcmg0dVBrNWVibjZPbnE4Zkx6OVBYMjkvajUrdi9FQUI4QkFBTUJBUUVCQVFFQkFRRUFBQUFBQUFBQkFnTUVCUVlIQ0FrS0MvL0VBTFVSQUFJQkFnUUVBd1FIQlFRRUFBRUNkd0FCQWdNUkJBVWhNUVlTUVZFSFlYRVRJaktCQ0JSQ2thR3h3UWtqTTFMd0ZXSnkwUW9XSkRUaEpmRVhHQmthSmljb0tTbzFOamM0T1RwRFJFVkdSMGhKU2xOVVZWWlhXRmxhWTJSbFptZG9hV3B6ZEhWMmQzaDVlb0tEaElXR2g0aUppcEtUbEpXV2w1aVptcUtqcEtXbXA2aXBxckt6dExXMnQ3aTV1c0xEeE1YR3g4akp5dExUMU5YVzE5aloydUxqNU9YbTUranA2dkx6OVBYMjkvajUrdi9hQUF3REFRQUNFUU1SQUQ4QStRZkVPaE1sd05veUs2andibzhjZHUyOEFaSGVvdkxlOWZMOUtTNFM1Z1haREp0cnFxWXlsVGJsSGNpblNyVmw3S1pTOFhhSWthbVJlUlhFU1FCVHhYcC8yS1M0MHIvU01zUUs1TzQ4T003c1VIQjZDdDNWcFZLZnRObVJTOXRTcmV4ZXlPcnRmOVZTUy82NUtLSytHcS94WkgwK0czUnRPUDhBaVduNlZsd0FiUnhSUlhxUi9nSTRLdjhBdkQ5VC85az0iPgoJPC9pbWFnZT4KPC9zdmc+ 32w" onload="window.requestAnimationFrame(function(){if(!(size=getBoundingClientRect().width))return;onload=null;sizes=Math.ceil(size/window.innerWidth*100)+'vw';});" sizes="1px" src="/media/1/testimage.png" width="340" height="280" alt="testimage">
```

### Generating responsive images for conversions

You can also generate responsive images for any [image conversion](/docs/laravel-medialibrary/v11/converting-images/defining-conversions) by chaining `withResponsiveImages()` on the conversion definition.

Here's an example that creates a greyscale conversion along with responsive variations:

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class YourModel extends Model implements HasMedia
{
    use InteractsWithMedia;

    public function registerMediaConversions(?Media $media = null): void
    {
        $this
            ->addMediaConversion('my-conversion')
            ->greyscale()
            ->quality(80)
            ->withResponsiveImages();
    }
}
```

When you add a file, Media Library generates both the greyscale conversion (with its [quality](https://spatie.be/docs/image/v3/image-manipulations/optimizing-images#content-quality) set to 80) and its responsive variations:

```php
$yourModel->addMedia($yourImage)->toMediaCollection();
```

*The conversion's `quality` setting is applied to its responsive images as well.*

Pass the `Media` model to a view from your controller:

```php
// in a controller

public function index()
{
    $media = $yourModel->getFirstMedia();

    return view('my-view', compact('media'));
}
```

Then invoke `$media` with the conversion name in Blade to render the `<img>` tag with its `srcset`:

```blade
<h1>My greyscale responsive image</h1>
{{ $media('my-conversion') }}
```
