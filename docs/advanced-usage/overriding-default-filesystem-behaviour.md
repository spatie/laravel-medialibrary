---
title: Overriding the default filesystem behavior
weight: 9
---

The `Spatie\MediaLibrary\Filesystem` class contains the behavior for actions like adding files, renaming files and deleting files. It applies these actions to the disks (local, S3, etc) that you configured.

If you want to override the default behavior you can create your own Filesystem implementation by implementing `Spatie\MediaLibrary\FilesystemInterface`. You then bind your own class to the service container in the AppServiceProvider:

```php
use App\CustomFilesystem;
use Spatie\MediaLibrary\FilesystemInterface;
 
class AppServiceProvider extends ServiceProvider
{
    ...
    public function register()
    {
        $this->app->bind(FilesystemInterface::class, CustomFilesystem::class);
    }
}
```

Generally speaking you do not want to mess with this class, so only override this if you know what you're doing.
