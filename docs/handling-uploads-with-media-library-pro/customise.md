---
title: Customise
weight: 2
---

## Only allow authenticated users to upload files

If in your project, you only want authenticated users to upload files, you can put the macro in a group that applies authentication middleware.

```php
Route::middleware('auth')->group(function() {
    Route::mediaLibrary();
});
```

We highly encourage you to do this, if you only need authenticated users to upload files.

## Configure allowed mime types

For security purposes, only files that pass [Laravel's `mimes` validation](https://laravel.com/docs/master/validation#rule-mimetypes) with the extensions [mentioned in this class](https://github.com/spatie/laravel-medialibrary-pro/blob/ba6eedd5b2a7f743909b441c0b6fd111d1a73794/src/Support/DefaultAllowedExtensions.php#L5) are allowed by the temporary upload controllers.

If you want your components to accept other mimetypes, add a key `temporary_uploads_allowed_extensions` in the `media-library.php` config file.

```php
// in config/medialibrary.php

return [
   // ...
   
   'temporary_uploads_allowed_extensions' => [
        // your extensions
        ... \Spatie\MediaLibraryPro\Support\DefaultAllowedExtensions::all(), // add this if you want to allow the default ones too
   ],
],
]
```

## Rate limiting

To protect you from users that upload too many files, the temporary uploads controllers are rate limited. By default, only 10 files can be upload per minute per ip iddress.

To customize rate limiting, add [a rate limiter](https://laravel.com/docs/master/rate-limiting#introduction) named `medialibrary-pro-uploads`. Typically, this would be done in a service provider.

Here's an example where's we'll allow 15 files:

```php
// in a service provider

use Illuminate\Support\Facades\RateLimiter;

RateLimiter::for('medialibrary-pro-uploads', function (Request $request) {
    return [
        Limit::perMinute(10)->by($request->ip()),
    ];
});
```

