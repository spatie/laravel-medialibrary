---
title: Using your own model
weight: 4
---

A custom model allows you to add your own methods, add relationships and so on.

The easiest way to use your own custom model would be to extend the
default `Spatie\MediaLibrary\Models\Media`-class. Here's an example:

```php
namespace App\Models;
use Spatie\MediaLibrary\Models\Media as BaseMedia;

class Media extends BaseMedia
{
...
```

In the config file of the package you must specify the name of your custom class:

```php
// config/medialibrary.php
...
   'media_model' => App\Models\CustomMedia::class
...
```
