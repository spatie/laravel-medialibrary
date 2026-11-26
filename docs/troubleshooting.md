---
title: Troubleshooting
weight: 8
---

Here are a few common issues and how to solve them.

## Images return 404 on the local `public` disk

By default, Media Library stores files on Laravel's `public` disk (`storage/app/public`). Make sure you've created the symbolic link from `public/storage` to `storage/app/public`:

```bash
php artisan storage:link
```

Also double-check that `APP_URL` in `.env` matches the URL your browser uses to load the app.

## A converted image returns 404 right after upload

Conversions are queued by default. Until your queue worker finishes processing the job, `$media->getUrl('thumb')` returns the URL where the file *will* live, which will 404.

- If you want an automatic fallback to the original image until the conversion exists, use `$model->getFirstMediaUrl('images', 'thumb')` or `$media->getAvailableUrl(['thumb'])`.
- If the conversion must be ready immediately, chain `->nonQueued()` (or `->deferred()` to run it right after the HTTP response) on the conversion definition.
- Make sure a queue worker is actually running if `QUEUE_CONNECTION` is set to something other than `sync`.

## Original files disappear after `addMedia()` or `addMediaFromDisk()`

By default, Media Library moves the source file into its own storage location and deletes the original (including files on remote disks like S3 when using `addMediaFromDisk()`).

Chain `->preservingOriginal()` (or use `copyMedia()`) if you want to keep the source file intact.

## Returning `$model->getMedia()` from a JSON API returns an unexpected format

By default, `MediaCollection` serializes in the UUID-keyed format expected by Media Library Pro components (and serializes as `[]` if `collectionName` isn't set).

If you don't use Media Library Pro and want standard Laravel collection serialization in JSON responses, set `use_default_collection_serialization` to `true` in `config/media-library.php`.

## PDF thumbnails fail with Ghostscript errors

ImageMagick often ships with a restrictive `policy.xml` that blocks PDF rendering. Check the [Ghostscript troubleshooting guide](https://github.com/spatie/pdf-to-image/blob/main/README.md#issues-regarding-ghostscript) in `spatie/pdf-to-image`.
