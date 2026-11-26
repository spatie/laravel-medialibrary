---
title: Rendering media
weight: 5
---

When a `Media` model represents an image, you can echo it straight in a Blade view:

```blade
Here is the original image: {{ $media }}
```

This renders an `<img>` tag pointing to the media's URL.

To render a specific conversion, call `img()` or invoke `$media` directly:

```blade
Here is the converted image: {{ $media->img('thumb') }}
```

```blade
Here is the converted image: {{ $media('thumb') }}
```

## Adding HTML attributes

Chain `attributes()` on `img()` to add extra HTML attributes:

```blade
{{ $media->img()->attributes(['class' => 'my-class']) }}
```

Both `class` and `style` also accept conditional arrays, using Laravel's [`Arr::toCssClasses()`](https://laravel.com/docs/helpers#method-array-to-css-classes) and [`Arr::toCssStyles()`](https://laravel.com/docs/helpers#method-array-to-css-styles) helpers under the hood. Entries with a numeric key are always included; entries with a string key are included when their value is truthy:

```blade
{{ $media->img()->attributes([
    'class' => [
        'my-class',
        'my-other-class' => true,
        'my-third-class' => false,
    ],
    'style' => [
        'border-radius: 4px',
        'opacity: 0.8' => $isMuted,
    ],
]) }}
```

## Lazy loading

To add `loading="lazy"` to the rendered `<img>` tag, chain `lazy()`:

```blade
{{ $media()->lazy() }}
```

You can also set a default `loading` attribute globally with `default_loading_attribute_value` in `config/media-library.php`, or per conversion with `useLoadingAttributeValue()`.

## Customizing the views

To take full control of the rendered markup, publish the package views:

```bash
php artisan vendor:publish --provider="Spatie\MediaLibrary\MediaLibraryServiceProvider" --tag="media-library-views"
```

This publishes four Blade files into `resources/views/vendor/media-library`:

- `image.blade.php`: renders media without responsive images
- `responsiveImage.blade.php`: renders media with responsive images and no tiny placeholder
- `responsiveImageWithPlaceholder.blade.php`: renders media with responsive images and a tiny placeholder
- `placeholderSvg.blade.php`: renders the inline base64 SVG wrapper used for the tiny placeholder

Edit them to your heart's content.
