---
title: Customizing the rendered html
weight: 3
---

Whenever you use a `$media` instance as output in a Blade view the medialibrary will generate a `img` tag with the necessary `src`, `srcset` and `alt` attributes. You can customize the rendered output by publishing the `views` with:

```bash
php artisan vendor:publish --provider="Spatie\MediaLibrary\MediaLibraryServiceProvider" --tag="views"
```

The following files will be published in the `resources/views/vendor/medialibrary` directory:
- `image.blade.php`: will be used to render media without responsive images
- `responsiveImage.blade.php`: will be used to render media with responsive images without a tiny placeholder
- `responsiveImageWithPlaceholder.blade.php`: will be used to render media with responsive images including a tiny placeholder.

You may modify these published views to your heart's content.
