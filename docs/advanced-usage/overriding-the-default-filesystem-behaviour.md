---
title: Overriding default filesystem behavior
weight: 10
---

`Spatie\MediaLibrary\MediaCollections\Filesystem` handles adding, copying, renaming, and deleting files across your configured disks.

If you need to change how those operations work, extend `Spatie\MediaLibrary\MediaCollections\Filesystem` and bind your subclass in `AppServiceProvider`:

```php
use App\CustomFilesystem;
use Illuminate\Support\ServiceProvider;
use Spatie\MediaLibrary\MediaCollections\Filesystem;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(Filesystem::class, CustomFilesystem::class);
    }
}
```

Generally speaking, you don't want to mess with this class, so only override it if you know what you're doing.
