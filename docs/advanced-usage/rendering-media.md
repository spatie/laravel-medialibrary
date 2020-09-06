---
title: Rendering media
weight: 5
---

If your `$media` instance concerns an image, you can render it directly in a Blade view.
 
 ```blade
Here is the original image: {{ $media }}
```

This will output an `img` tag with a `src` attribute that contains an url to the media.

You can also render an `img` to a conversion.

 ```blade
Here is the converted image: {{ $media->conversion('thumb') }}
```

You can also use this shorthand:

 ```blade
Here is the converted image: {{ $media('thumb') }}
```

You can extra attributes by calling `attributes`.

```blade
Here is the image with some attributes: {{ $media->img()->attributes(['class' => 'my-class']) }}
```

If you want [defer loading offscreen images](https://css-tricks.com/native-lazy-loading/) you can use the `lazy` function.

 ```blade
Lazy loading this one: {{ $media()->lazy() }}
```

## Customizing the views
  
You can customize the rendered output even further by publishing the `views` with:

```bash
php artisan vendor:publish --provider="Spatie\MediaLibrary\MediaLibraryServiceProvider" --tag="views"
```

The following files will be published in the `resources/views/vendor/media-library` directory:

- `image.blade.php`: will be used to render media without responsive images
- `responsiveImage.blade.php`: will be used to render media with responsive images without a tiny placeholder
- `responsiveImageWithPlaceholder.blade.php`: will be used to render media with responsive images including a tiny placeholder.

You may modify these published views to your heart's content.
