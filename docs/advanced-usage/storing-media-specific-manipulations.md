---
title: Storing media-specific manipulations
weight: 3
---

Sometimes you only want to tweak a single image rather than every image on a model. For example, one uploaded photo might be sideways and need a 90-degree rotation.

You can attach media-specific manipulations when adding a file with `withManipulations()`. Each key is a conversion name, and each manipulation maps to an array of arguments matching [`spatie/image` v3](https://spatie.be/docs/image/v3):

```php
use Spatie\Image\Enums\Orientation;

$yourModel
    ->addMedia($pathToFile)
    ->withManipulations([
        'thumb' => [
            'orientation' => ['orientation' => Orientation::Rotate90],
        ],
    ])
    ->toMediaCollection();
```

Media Library runs your media-specific manipulations *first*, then applies the rest of the manipulations defined on that conversion.

You can also set or update manipulations on an existing `Media` model. Saving the model automatically triggers conversion regeneration:

```php
use Spatie\Image\Enums\Orientation;

$mediaItem = $yourModel->getFirstMedia('images');
$mediaItem->manipulations = [
    'thumb' => [
        'orientation' => ['orientation' => Orientation::Rotate90],
    ],
];

// Saving regenerates the affected conversions.
$mediaItem->save();
```

## Targeting multiple conversions

You can specify manipulations for several conversions at once:

```php
use Spatie\Image\Enums\Orientation;

$yourModel
    ->addMedia($pathToFile)
    ->withManipulations([
        'thumb' => ['orientation' => ['orientation' => Orientation::Rotate90]],
        'otherConversion' => ['orientation' => ['orientation' => Orientation::Rotate90]],
    ])
    ->toMediaCollection();
```

## Applying manipulations to all conversions with `*`

If your model defines lots of conversions (`thumb`, `small`, `banner`, `print`), repeating the same rotation under every key gets tedious fast. Use `*` to apply manipulations to every conversion on that media item:

```php
use Spatie\Image\Enums\Orientation;

$yourModel
    ->addMedia($pathToFile)
    ->withManipulations([
        '*' => ['orientation' => ['orientation' => Orientation::Rotate90]],
    ])
    ->toMediaCollection();
```

You can combine `*` with conversion-specific entries. Wildcard manipulations always run before conversion-specific ones:

```php
use Spatie\Image\Enums\Orientation;

$yourModel
    ->addMedia($pathToFile)
    ->withManipulations([
        '*' => ['orientation' => ['orientation' => Orientation::Rotate90]],
        'thumb' => ['greyscale' => []],
    ])
    ->toMediaCollection();
```
