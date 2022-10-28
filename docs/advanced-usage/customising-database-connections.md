---
title: Customising Database Connections
weight: 13
---

### Media model connection

The built-in model (`Spatie\MediaLibrary\MediaCollections\Models\Media`) will use the default database connection set for your application.

If you need to change this database connection, you can create a custom model and set the `$connection` property (https://laravel.com/docs/9.x/eloquent#database-connections). See <a href="https://docs.spatie.be/laravel-medialibrary/v10/advanced-usage/using-your-own-model">Using your own model</a> for more information.

```php
<?php

namespace App\Models;

use Spatie\MediaLibrary\MediaCollections\Models\Media as BaseMedia;

class Media extends BaseMedia {

    protected $connection = 'tenant';

}
```

### Parent model connection

The `Spatie\MediaLibrary\InteractsWithMedia` trait defines a `MorphMany` relationship to the media model. Eloquent automatically uses the database connection of your parent model when querying the database. In the example below, the user media results will use the `tenant` database connection rather than the application's default connection.

```php
<?php

namespace App\Models;

use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;

class User extends Model implements HasMedia {

    use InteractsWithMedia;

    protected $connection = 'tenant';

}
```

When you save files using the code below, the `Spatie\MediaLibrary\MediaCollections\FileAdder` will also automatically use the parent model's database connection if that is set.

```php
$model
    ->addMedia($path)
    ->toMediaCollection();
```

If you need to customise the database connection further before fetching or adding media, use you can do `$model->setConnection('landlord')`.