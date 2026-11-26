---
title: Cleaning up media
weight: 14
---

Media Library ships with two Artisan commands for cleaning up stale files and clearing collections (in addition to [`media-library:regenerate`](/docs/laravel-medialibrary/v11/converting-images/regenerating-images)).

## Cleaning deprecated conversions and orphaned files

When you remove a conversion from a model, or files are left behind on disk without a matching `Media` record, run `media-library:clean`:

```bash
php artisan media-library:clean
```

Preview what will be removed without deleting anything by passing `--dry-run`:

```bash
php artisan media-library:clean --dry-run
```

> **Heads up:** `media-library:clean` treats every top-level directory below your configured media `prefix` that isn't referenced by a `Media` record as orphaned and deletes it. Always use a dedicated disk or `prefix` for media, and run `--dry-run` first if you use a custom `PathGenerator`.

### Filtering by model, collection, or disk

You can scope the command with optional positional arguments:

```bash
php artisan media-library:clean "App\Models\Post" images s3
```

### Useful options

- `--dry-run`: list what would be deleted without removing anything
- `--force`: skip the production confirmation prompt
- `--skip-conversions`: skip deleting deprecated conversions and responsive images (only clean orphaned directories)
- `--delete-orphaned`: also delete `Media` database records whose parent model no longer exists
- `--rate-limit=10`: cap filesystem/delete operations to a given number of requests per second

## Clearing collections from the command line

To delete all media records (and their files) across your app, for a specific model, or for a specific model and collection, use `media-library:clear`:

```bash
php artisan media-library:clear
php artisan media-library:clear "App\Models\Post"
php artisan media-library:clear "App\Models\Post" images
```
