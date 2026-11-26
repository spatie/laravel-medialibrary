---
title: Regenerating images
weight: 4
---

When you add or change a conversion on a model, previously uploaded media won't update on its own. Run the `media-library:regenerate` Artisan command to rebuild them:

```bash
php artisan media-library:regenerate
```

Keep in mind that queued conversions are still dispatched to your queue, so changes may take a moment to show up while your workers catch up.

## Limiting by model

Pass a model class name to regenerate conversions for a single model:

```bash
php artisan media-library:regenerate "App\Models\Post"
```

If you use a morph map, pass the morph alias instead:

```bash
php artisan media-library:regenerate "post"
```

## Specific media IDs

Pass one or more IDs with `--ids` (either repeated or comma-separated):

```bash
php artisan media-library:regenerate --ids=1 --ids=2 --ids=3
php artisan media-library:regenerate --ids=1,2,3
```

Note that passing a `modelType` argument or `--starting-from-id` takes precedence over `--ids`.

## Specific conversions

Use `--only` to regenerate one or more named conversions instead of all of them:

```bash
php artisan media-library:regenerate --only=thumb --only=banner
```

## Missing conversions only

Skip conversions that already exist on disk with `--only-missing`:

```bash
php artisan media-library:regenerate --only-missing
```

## Including responsive images

To regenerate responsive images as well, pass `--with-responsive-images`:

```bash
php artisan media-library:regenerate --with-responsive-images
```

## Resuming from a specific ID

Use `--starting-from-id` to start at a given media ID (inclusive), or combine it with `--exclude-starting-id` (`-X`) to start right after it:

```bash
php artisan media-library:regenerate --starting-from-id=100
php artisan media-library:regenerate --starting-from-id=100 --exclude-starting-id
php artisan media-library:regenerate --starting-from-id=100 -X
```

You can combine `--starting-from-id` with a `modelType` argument:

```bash
php artisan media-library:regenerate "App\Models\Post" --starting-from-id=100
```

## Queueing all conversions & running in production

Use `--queue-all` to push every conversion onto the queue, even ones marked `nonQueued()`:

```bash
php artisan media-library:regenerate --queue-all
```

In production, the command asks for confirmation unless you pass `--force`:

```bash
php artisan media-library:regenerate --force
```
