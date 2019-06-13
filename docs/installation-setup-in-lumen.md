---
title: Installation & setup in Lumen
weight: 5
---

Lumen configuration is slightly more involved but features and API are identical to Laravel.

Install using this command:

```bash
composer require spatie/laravel-medialibrary:^4.0.0
```

Uncomment the following lines in the bootstrap file:

```php
// bootstrap/app.php:
$app->withFacades();
$app->withEloquent();
```

Configure the laravel-medialibrary service provider (and `AppServiceProvider` if not already enabled):
```php
// bootstrap/app.php:
$app->register(App\Providers\AppServiceProvider::class);
$app->register(Spatie\MediaLibrary\MediaLibraryServiceProvider::class);
```

Update the `AppServiceProvider` register method to bind the filesystem manager to the IOC container:

```php
// app/Providers/AppServiceProvider.php
public function register()
{
    $this->app->singleton('filesystem', function ($app) {
        return $app->loadComponent('filesystems', 'Illuminate\Filesystem\FilesystemServiceProvider', 'filesystem');
    });

    $this->app->bind('Illuminate\Contracts\Filesystem\Factory', function($app) {
        return new \Illuminate\Filesystem\FilesystemManager($app);
    });
}
```

Manually copy the package config file to `<yourproject>\config\medialibrary.php` (you may need to
create the config directory if it does not already exist).

Copy the [Laravel filesystem config file](https://github.com/laravel/laravel/blob/v5.2.31/config/filesystems.php) into `<yourproject>\config\filesystems.php`. You should add a disk configuration to the filesystem config matching the `defaultFilesystem` specified in the laravel-medialibrary config file.

Finally, update `boostrap/app.php` to load both config files:

```php
// bootstrap/app.php
$app->configure('laravel-medialibrary');
$app->configure('filesystems');
```
