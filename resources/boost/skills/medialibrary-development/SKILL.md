---
name: medialibrary-development
description: Build and work with spatie/laravel-medialibrary features including associating files with Eloquent models, defining media collections and conversions, generating responsive images, and retrieving media URLs and paths.
license: MIT
metadata:
  author: Spatie
---

# Media Library Development

## Overview
Use spatie/laravel-medialibrary to associate files with Eloquent models. Supports image/video conversions, responsive images, multiple collections, and various storage disks.

## When to Activate
- Activate when working with file uploads, media attachments, or image processing in Laravel.
- Activate when code references `HasMedia`, `InteractsWithMedia`, the `Media` model, or media collections/conversions.
- Activate when the user wants to add, retrieve, convert, or manage files attached to Eloquent models.

## Scope
- In scope: media uploads, collections, conversions, responsive images, custom properties, file retrieval, path/URL generation.
- Out of scope: general file storage without Eloquent association, non-Laravel frameworks.

## Workflow
1. Identify the task (model setup, adding media, defining conversions, retrieving files, etc.).
2. Read `references/medialibrary-guide.md` and focus on the relevant section.
3. Apply the patterns from the reference, keeping code minimal and Laravel-native.

## Core Concepts

### Model Setup
Every model that should have media must implement `HasMedia` and use the `InteractsWithMedia` trait:

```php
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class BlogPost extends Model implements HasMedia
{
    use InteractsWithMedia;
}
```

### Adding Media
```php
$blogPost->addMedia($file)->toMediaCollection('images');
$blogPost->addMediaFromUrl($url)->toMediaCollection('images');
$blogPost->addMediaFromRequest('file')->toMediaCollection('images');
```

### Defining Collections
```php
public function registerMediaCollections(): void
{
    $this->addMediaCollection('avatar')->singleFile();
    $this->addMediaCollection('downloads')->useDisk('s3');
}
```

### Defining Conversions
```php
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Image\Enums\Fit;

public function registerMediaConversions(?Media $media = null): void
{
    $this->addMediaConversion('thumb')
        ->fit(Fit::Contain, 300, 300)
        ->nonQueued();
}
```

### Retrieving Media
```php
$url = $model->getFirstMediaUrl('images');
$thumbUrl = $model->getFirstMediaUrl('images', 'thumb');
$allMedia = $model->getMedia('images');
```

## Do and Don't

Do:
- Always implement the `HasMedia` interface alongside the `InteractsWithMedia` trait.
- Use `?Media $media = null` as the parameter for `registerMediaConversions()`.
- Call `->toMediaCollection()` to finalize adding media.
- Use `->nonQueued()` for conversions that should run synchronously.
- Use `->singleFile()` on collections that should only hold one file.
- Use `Spatie\Image\Enums\Fit` enum values for fit methods.

Don't:
- Don't forget to run `php artisan vendor:publish --provider="Spatie\MediaLibrary\MediaLibraryServiceProvider" --tag="medialibrary-migrations"` before migrating.
- Don't use `env()` for disk configuration; use `config()` or set it in `config/media-library.php`.
- Don't call `addMedia()` without calling `toMediaCollection()` — the media won't be saved.
- Don't reference conversion names that aren't registered in `registerMediaConversions()`.

## References
- `references/medialibrary-guide.md`
