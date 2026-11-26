---
title: Using your own width calculator
weight: 2
---

When generating responsive images for a `srcset`, Media Library calculates a set of target widths first. You can configure the class that chooses those widths under `responsive_images.width_calculator` in `config/media-library.php`.

## The default calculator

By default, Media Library uses `FileSizeOptimizedWidthCalculator`. It picks target widths so each variation is roughly 70% the file size of the previous one, stopping once the predicted file size drops below 10 KB or the width goes under 20 pixels. Larger images naturally end up with more variations than smaller ones.

## Using your own width calculator

In most apps, `FileSizeOptimizedWidthCalculator` gives great results out of the box. If you need fixed breakpoints or your own formula, implement `Spatie\MediaLibrary\ResponsiveImages\WidthCalculator\WidthCalculator` and register it under `responsive_images.width_calculator` in `config/media-library.php`.

Both `calculateWidthsFromFile()` and `calculateWidths()` should return an `Illuminate\Support\Collection` containing the target widths in pixels.

You can also override the width calculator for a single conversion with `withWidthCalculator()`:

```php
$this->addMediaConversion('hero')
    ->withResponsiveImages()
    ->withWidthCalculator(new CustomWidthCalculator());
```
