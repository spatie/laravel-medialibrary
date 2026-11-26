---
title: Adding files
weight: 1
---

Adding a file to Media Library follows a simple three-step chain: pick a **starting method**, chain any **middle methods** you need, and end with a **finishing method**.

```php
$yourModel
    ->addMedia($pathToFile) // starting method
    ->withCustomProperties(['primaryColor' => 'red']) // middle method
    ->preservingOriginal() // middle method
    ->toMediaCollection(); // finishing method
```

## Starting methods

### `addMedia`

Adds a local file path or `UploadedFile` to Media Library. By default, the source file is moved (deleted from its original location).

```php
public function addMedia(string|UploadedFile $file): FileAdder
```

### `addMediaFromUrl`

Downloads a remote file from an `http://` or `https://` URL. Optionally pass allowed MIME types as extra arguments.

```php
public function addMediaFromUrl(string $url, array|string ...$allowedMimeTypes): FileAdder
```

> **Security note:** `addMediaFromUrl` fetches whatever URL you pass to it from your server and only checks that it starts with `http://` or `https://`. Passing user-supplied URLs directly exposes your app to SSRF (attackers can target internal hosts, private IP ranges, loopback, or cloud metadata endpoints like `http://169.254.169.254/`). Only pass URLs you control or have validated against a strict host allowlist.

### `addMediaFromDisk`

Copies a file that already exists on one of your Laravel storage disks into Media Library. Unless you chain `preservingOriginal()`, the source file on that disk is deleted afterward.

```php
public function addMediaFromDisk(string $key, ?string $disk = null): FileAdder
```

### `addMediaFromRequest`

Adds a single uploaded file from the current HTTP request by form field name.

```php
public function addMediaFromRequest(string $key): FileAdder
```

### `addMultipleMediaFromRequest`

Adds several uploaded files from the current request. Returns a `Collection` of `FileAdder` instances, so iterate over them to call middle and finishing methods:

```php
public function addMultipleMediaFromRequest(array $keys): Collection
```

```php
$this->model
    ->addMultipleMediaFromRequest(['file-one', 'file-two'])
    ->each(function (FileAdder $fileAdder) {
        $fileAdder->toMediaCollection();
    });
```

### `addAllMediaFromRequest`

Adds every uploaded file in the current request. Like `addMultipleMediaFromRequest()`, this returns a `Collection` of `FileAdder` instances.

```php
public function addAllMediaFromRequest(): Collection
```

### `addMediaFromBase64`

Decodes a base64-encoded string (or data URI) and adds it as a file. Optionally pass allowed MIME types as extra arguments.

```php
public function addMediaFromBase64(string $base64data, array|string ...$allowedMimeTypes): FileAdder
```

### `addMediaFromString`

Creates a file containing the given string. The default filename is `text.txt`, which you can override with `usingFileName()`.

```php
public function addMediaFromString(string $text): FileAdder
```

```php
$model
    ->addMediaFromString('my string')
    ->usingFileName('custom-filename.txt')
    ->toMediaCollection();
```

### `addMediaFromStream`

Creates a file from a PHP stream resource. The default filename is `text.txt`, which you can override with `usingFileName()`.

```php
public function addMediaFromStream($stream): FileAdder
```

```php
$model
    ->addMediaFromStream($stream)
    ->usingFileName('custom-filename.txt')
    ->toMediaCollection();
```

### `copyMedia`

Shortcut for `addMedia($file)->preservingOriginal()`.

```php
public function copyMedia(string|UploadedFile $file): FileAdder
```

## Middle methods

### `preservingOriginal`

Keeps the original file in place instead of deleting it after storing.

```php
public function preservingOriginal(bool $preserveOriginal = true): self
```

### `usingName` / `setName`

Sets the `name` attribute on the `Media` model (defaults to the filename without its extension). `setName()` is an alias for `usingName()`.

```php
public function usingName(string $name): self
```

### `usingFileName` / `setFileName`

Sets the filename stored on disk. `setFileName()` is an alias for `usingFileName()`.

```php
public function usingFileName(string $fileName): self
```

### `sanitizingFileName`

Replaces the default filename sanitizer with your own callable. Note that this also replaces the built-in `disallowed_extensions` check.

```php
public function sanitizingFileName(callable $fileNameSanitizer): self
```

### `withCustomProperties`

Attaches custom metadata stored in the `custom_properties` JSON column.

```php
public function withCustomProperties(array $customProperties): self
```

### `withManipulations`

Sets media-specific manipulations applied before each conversion's own manipulations.

```php
public function withManipulations(array $manipulations): self
```

### `withResponsiveImages` / `withResponsiveImagesIf`

Generates responsive image variations for this media item (or conditionally based on a boolean or closure).

```php
public function withResponsiveImages(): self
public function withResponsiveImagesIf(bool|callable $condition): self
```

### `storingConversionsOnDisk`

Stores conversions and responsive images for this upload on a different disk than the original file.

```php
public function storingConversionsOnDisk(string $diskName): self
```

### `addCustomHeaders`

Merges extra upload headers (such as S3 `CacheControl` or `ACL`) with `remote.extra_headers` from `config/media-library.php`.

```php
public function addCustomHeaders(array $customRemoteHeaders): self
```

### `setOrder`

Sets an explicit `order_column` value on the `Media` model.

```php
public function setOrder(?int $order): self
```

### `withProperties` / `withAttributes`

Sets raw attributes directly on the `Media` Eloquent model before saving.

```php
public function withProperties(array $properties): self
public function withAttributes(array $properties): self
```

### `onQueue`

Overrides the queue name used for `GenerateResponsiveImagesJob` on this upload.

```php
public function onQueue(?string $queue = null): self
```

## Finishing methods

### `toMediaCollection`

Saves the `Media` record, moves (or copies) the file to the target disk, and triggers conversions.

```php
public function toMediaCollection(string $collectionName = 'default', string $diskName = ''): Media
```

### `toMediaCollectionOnCloudDisk`

Stores the media on the disk configured under `filesystems.cloud` in `config/filesystems.php`. Make sure you have defined a `'cloud'` key in `config/filesystems.php` before using this method (Laravel 12+ doesn't include one by default).

```php
public function toMediaCollectionOnCloudDisk(string $collectionName = 'default'): Media
```
