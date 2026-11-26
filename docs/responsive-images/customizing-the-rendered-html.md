---
title: Customizing the rendered HTML
weight: 3
---

Whenever you output a `Media` instance in a Blade view, Media Library renders an `<img>` tag with `src`, `srcset`, and `alt` attributes. You can customize those views by publishing them:

```bash
php artisan vendor:publish --provider="Spatie\MediaLibrary\MediaLibraryServiceProvider" --tag="media-library-views"
```

This publishes four Blade files to `resources/views/vendor/media-library`:

- `image.blade.php`: renders media without responsive images
- `responsiveImage.blade.php`: renders media with responsive images and no tiny placeholder
- `responsiveImageWithPlaceholder.blade.php`: renders media with responsive images and a tiny placeholder
- `placeholderSvg.blade.php`: renders the inline base64 SVG wrapper used for the tiny placeholder

Edit these published views to your heart's content.
