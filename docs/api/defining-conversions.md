---
title: Defining conversions
weight: 2
---

Define conversions inside your model's `registerMediaConversions()` method by starting with `addMediaConversion()` and chaining any of the methods below (plus any manipulation from `spatie/image`).

See [defining conversions](/docs/laravel-medialibrary/v11/converting-images/defining-conversions) for full usage examples.

## General methods

### `addMediaConversion`

Registers a new conversion on the model.

```php
public function addMediaConversion(string $name): Conversion
```

### `performOnCollections`

Limits the conversion to one or more named collections.

```php
public function performOnCollections(string ...$collectionNames): self
```

### `queued`

Marks the conversion to run on the configured queue.

```php
public function queued(): self
```

### `nonQueued`

Runs the conversion synchronously during the current request.

```php
public function nonQueued(): self
```

### `deferred`

Schedules the conversion to run right after the HTTP response is sent using Laravel's `defer()` helper.

```php
public function deferred(): self
```

### `keepOriginalImageFormat`

Keeps the source image's format instead of converting the output to `jpg`.

```php
public function keepOriginalImageFormat(): self
```

### `nonOptimized`

Skips running image optimizers on the converted file.

```php
public function nonOptimized(): self
```

### `withResponsiveImages`

Generates responsive image variations for this conversion.

```php
public function withResponsiveImages(bool $withResponsiveImages = true): self
```

### `withWidthCalculator`

Uses a custom `WidthCalculator` for this conversion's responsive images.

```php
public function withWidthCalculator(WidthCalculator $widthCalculator): self
```

### `pdfPageNumber`

Chooses which page of a PDF (1-indexed) to render when generating a thumbnail.

```php
public function pdfPageNumber(int $pageNumber): self
```

### `extractVideoFrameAtSecond`

Chooses the timestamp (in seconds) to capture when generating a video thumbnail.

```php
public function extractVideoFrameAtSecond(float $timeCode): self
```

### `useLoadingAttributeValue`

Sets the `loading` attribute used when this conversion is rendered as an `<img>` tag (for example `'lazy'` or `'eager'`).

```php
public function useLoadingAttributeValue(string $value): self
```

## Image manipulations

You can also chain any [manipulation method from `spatie/image`](https://spatie.be/docs/image/v3).
