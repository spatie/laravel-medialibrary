---
title: Overriding default filesystem behavior
weight: 10
---

The `Spatie\MediaLibrary\MediaCollections\Filesystem` class contains the behavior for actions like adding files, renaming files and deleting files. It applies these actions to the disks (local, S3, etc) that you configured.

If you want to override the default behavior you can create your own  implementation by extending `Spatie\MediaLibrary\MediaCollections\Filesystem`. You then bind your own class to the service container in the `AppServiceProvider`:

```php
use App\CustomFilesystem;
use Spatie\MediaLibrary\MediaCollections\Filesystem;
 
class AppServiceProvider extends ServiceProvider
{
    ...
    public function register()
    {
        $this->app->bind(Filesystem::class, CustomFilesystem::class);
    }
}
```

Generally speaking you do not want to mess with this class, so only override it if you know what you're doing.
