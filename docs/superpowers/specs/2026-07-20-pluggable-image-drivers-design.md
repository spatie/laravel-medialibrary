# Pluggable image drivers (spatie/image and Laravel image) design

## Summary

Let Media Library run conversions through more than one image engine. Ship spatie/image as the default (unchanged behavior), add Laravel's image (`Illuminate\Image`, over Intervention v4) as a first class alternative, and let users register their own driver. Conversions are written against the configured engine directly (passthrough, not a neutral vocabulary), and an ide-helper generator keeps autocompletion accurate for whichever engine is configured.

## Decision record

Settled during design discussion:

1. Passthrough, not neutral. A conversion speaks the configured engine's manipulation vocabulary directly. We do not build an engine neutral manipulation set.
2. Autocompletion parity via a generated ide-helper stub (the `barryvdh/laravel-ide-helper` pattern), because static generics and `.phpstorm.meta.php` cannot read runtime config.
3. The default driver is spatie/image. Out of the box nothing changes, and generation is only needed when switching engines.

## Consequence of passthrough

Because a conversion is written against the configured engine, switching engines is a migration, not a transparent toggle. A conversion that uses a spatie only manipulation (for example `focalCropAndResize`) will not run on the Laravel engine. This is intended and must be documented clearly. The benefit is that users keep the exact fluent autocompletion and full manipulation surface they have today, per engine.

## Current coupling (what we are abstracting)

- `Support\ImageFactory::load()` and `Conversions\Actions\PerformManipulationsAction::execute()` both call `Spatie\Image\Image::useImageDriver(config('media-library.image_driver'))`. Today `image_driver` selects a spatie engine (`gd`, `imagick`, `vips`).
- `Conversions\Manipulations` records manipulations as a name to args map and replays them with `$image->$name(...$args)` onto a `Spatie\Image\Drivers\ImageDriver`. It also coerces raw scalar arguments to spatie enums in `transformParameters()`.
- `Conversions\Conversion` is `@mixin \Spatie\Image\Drivers\ImageDriver`. Its `__call` records onto `Manipulations`. It also owns format concerns (`format()`, `keepOriginalImageFormat()`, `getResultExtension()`), quality, and the optimizer toggle.
- `PerformManipulationsAction` defaults output to `jpg`, applies `keepOriginalImageFormat`, and swallows `Spatie\Image\Exceptions\UnsupportedImageFormat`.

## Architecture

### Driver manager

A `Spatie\MediaLibrary\Conversions\ImageDrivers\ImageDriverManager` (built on Laravel's `Illuminate\Support\Manager`) resolves a driver by name from config, and supports `extend()` for custom drivers.

```php
'image_driver' => env('MEDIA_LIBRARY_IMAGE_DRIVER', 'spatie'),

'image_drivers' => [
    'spatie' => [
        'driver' => Spatie\MediaLibrary\Conversions\ImageDrivers\SpatieImageDriver::class,
        'engine' => 'gd', // gd | imagick | vips
    ],
    'laravel' => [
        'driver' => Spatie\MediaLibrary\Conversions\ImageDrivers\LaravelImageDriver::class,
        'engine' => 'gd', // gd | imagick
    ],
],
```

Note: the existing scalar `image_driver` values (`gd`/`imagick`/`vips`) become driver names. We keep backward compatibility by mapping a legacy engine value to the spatie driver with that engine (see Migration).

### Driver contract

The contract is deliberately thin. It produces and drives an engine native image object; it does not describe manipulations (those pass through).

```php
namespace Spatie\MediaLibrary\Conversions\ImageDrivers;

use Spatie\MediaLibrary\Conversions\Conversion;

interface MediaImageDriver
{
    public function loadFile(string $path): static;

    /** The engine native image object that manipulations are replayed onto. */
    public function image(): object;

    /** Replay the conversion's recorded manipulations onto the image. */
    public function applyManipulations(Conversion $conversion): static;

    /** Apply format and quality, then write to disk. */
    public function save(string $path): void;

    public function getWidth(): int;

    public function getHeight(): int;

    /** The file extension this driver will produce for the conversion. */
    public function resultExtension(Conversion $conversion, string $originalExtension): string;
}
```

- `SpatieImageDriver` wraps `Spatie\Image\Image` (default). Its `applyManipulations()` runs the existing `Manipulations::apply()` path, including the spatie enum coercion, which moves out of the shared `Manipulations` class into this adapter.
- `LaravelImageDriver` wraps `Illuminate\Image` / Intervention v4. Its `applyManipulations()` replays the recorded calls onto the Intervention image, and its `save()` maps format and quality to Intervention's encoders.

### Where format, quality, and the optimizer live

These are NOT passed through, because engines name them differently (spatie `->format('webp')` vs Intervention `->toWebp()`), and Media Library itself needs the resulting extension for storage, URLs, and responsive images.

- `format`, `quality`, and `keepOriginalImageFormat` stay first class settings on `Conversion` (as today).
- Each driver applies them in `save()` and reports the produced extension via `resultExtension()`.
- The optimizer chain (`spatie/image-optimizer`) runs in Media Library's pipeline after `save()`, so it is engine agnostic and both drivers benefit. Today spatie/image runs it internally; we move the optimize step up into the Media Library pipeline so the Laravel driver gets it too.

### Manipulations become engine agnostic as a recorder

`Manipulations` keeps recording name to args, but stops importing spatie enums and stops doing spatie specific `transformParameters()`. That coercion moves into `SpatieImageDriver`. The recorder only stores and replays.

### Unsupported manipulations

If a recorded manipulation does not exist on the configured engine's image object, the driver throws `Spatie\MediaLibrary\Conversions\Exceptions\UnsupportedManipulation` with the manipulation name and driver name, instead of a fatal `BadMethodCallException`. This makes a bad engine switch a clear, actionable error.

## Usage

### Defining conversions (spatie default, unchanged)

```php
use Spatie\Image\Enums\Fit;

public function registerMediaConversions(?Media $media = null): void
{
    $this->addMediaConversion('thumb')
        ->fit(Fit::Crop, 300, 300)
        ->format('webp')
        ->quality(80);
}
```

### Defining conversions on the Laravel engine

With `image_driver` set to `laravel` and the ide-helper regenerated, the same fluent call autocompletes Intervention v4's surface:

```php
public function registerMediaConversions(?Media $media = null): void
{
    $this->addMediaConversion('thumb')
        ->cover(300, 300)   // Intervention v4 vocabulary
        ->format('webp')
        ->quality(80);
}
```

### Per conversion engine override

```php
$this->addMediaConversion('hero')
    ->useImageDriver('laravel')
    ->cover(1600, 900);
```

### Registering a custom driver

```php
use Spatie\MediaLibrary\Conversions\ImageDrivers\ImageDriverManager;

public function boot(ImageDriverManager $manager): void
{
    $manager->extend('imgproxy', fn (array $config) => new ImgproxyImageDriver($config));
}
```

## Autocompletion: the ide-helper generator

Static analysis cannot read `config('media-library.image_driver')`, so we generate an ide-helper stub from the booted app.

- Command: `php artisan media-library:ide-helper`.
- It resolves the configured driver, finds that engine's image type, and writes an ide-only file (not in the autoload map, so PHP never executes it) that re declares `Conversion` with the configured `@mixin`:

```php
// _media-library_ide_helper.php (generated)
namespace Spatie\MediaLibrary\Conversions {
    /** @mixin \Spatie\Image\Drivers\ImageDriver */          // image_driver = spatie
    class Conversion {}
}
```

```php
namespace Spatie\MediaLibrary\Conversions {
    /** @mixin \Intervention\Image\Interfaces\ImageInterface */ // image_driver = laravel
    class Conversion {}
}
```

- The package ships with the spatie `@mixin` as the committed default on the real `Conversion` class, so out of the box DX is unchanged and generation is only needed after switching drivers.
- Wire regeneration into `composer.json` `post-autoload-dump` (and document a manual run), the same way `barryvdh/laravel-ide-helper` does.
- PHPStan and larastan can consume the same stub through `stubFiles`, so static analysis stays accurate too.

## Non goals

1. An engine neutral manipulation vocabulary or portable conversions. Explicitly rejected in favor of passthrough.
2. Making a driver switch transparent. It is a migration.
3. Remote or on the fly transformation services beyond what the `MediaImageDriver` contract naturally allows. A Cloudflare Images style driver could be a later community driver, but it does not fit the load, manipulate, save shape cleanly and is out of scope here.

## Migration and breaking changes

- The `media-library.image_driver` config value changes meaning from a spatie engine name to a Media Library driver name. Provide a compatibility shim: if the value is `gd`, `imagick`, or `vips`, resolve the spatie driver with that engine and emit a deprecation notice suggesting the new `image_drivers` config.
- Add `image_drivers` to the published config.
- The optimizer step moves from inside spatie/image to the Media Library pipeline. Confirm parity (same optimizer chain, same defaults).
- This targets the next major, aligned with the attribute and callback work already on `next-major`.

## Testing

- Conversions produce identical output on the spatie driver as today (snapshot parity).
- The same simple conversion (fit, format, quality) runs on both spatie and laravel drivers and produces a valid image of the expected dimensions and format.
- Per conversion `useImageDriver()` override runs that conversion on the named driver.
- A manipulation unsupported by the configured engine throws `UnsupportedManipulation` with a clear message.
- The ide-helper command generates the expected `@mixin` for each configured driver.
- A custom `extend()`ed driver is resolved and used.

## Open questions

1. Does the Laravel driver depend on `laravel/framework` bringing `Illuminate\Image` in, or on `intervention/image` directly. Prefer depending on what Laravel exposes so we track its engine, and make the driver a suggested (not required) dependency so spatie only users pull nothing new.
2. Exact ide-helper file location and whether to integrate with `barryvdh/laravel-ide-helper` as an extension rather than shipping our own command.
3. Whether `resultExtension()` needs to consult the engine (some engines infer format from the output path) or can stay driven by the `format` setting alone.
