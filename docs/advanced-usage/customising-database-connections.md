---
title: Customising database connections
weight: 13
---

## Media model connection

By default, `Spatie\MediaLibrary\MediaCollections\Models\Media` uses your application's default database connection.

To store media records on another connection, [create a custom `Media` model](/docs/laravel-medialibrary/v11/advanced-usage/using-your-own-model) and set its [`$connection` property](https://laravel.com/docs/eloquent#database-connections):

```php
namespace App\Models;

use Spatie\MediaLibrary\MediaCollections\Models\Media as BaseMedia;

class Media extends BaseMedia
{
    protected $connection = 'tenant';
}
```

## Parent model connection

The `InteractsWithMedia` trait defines a `MorphMany` relationship to the media model, so Eloquent automatically uses the parent model's database connection when querying media. In the example below, user media queries use the `tenant` connection instead of the application default:

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class User extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $connection = 'tenant';
}
```

When you attach files to that model, `FileAdder` also uses the parent model's database connection automatically:

```php
$model
    ->addMedia($path)
    ->toMediaCollection();
```

If you need to switch connections dynamically before fetching or adding media, call `$model->setConnection('landlord')`.
